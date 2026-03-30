use std::io::Write;
use std::os::fd::AsRawFd;
use std::os::unix::net::UnixStream;
use std::sync::Arc;
use std::sync::atomic::{AtomicBool, Ordering};
use std::thread::{self, JoinHandle};

use ext_php_rs::flags::ClassFlags;
use ext_php_rs::prelude::*;
use ext_php_rs::types::Zval;
use parking_lot::Mutex;
use typst::diag::Warned;
use typst::layout::PagedDocument;

use super::document::Document;
use super::error;
use crate::internal::world::TypstWorld;

type CompilationResult = Arc<Mutex<Option<Result<Arc<PagedDocument>, String>>>>;

/// Wraps a raw fd into a PHP stream resource via `fopen("php://fd/$n", "r")`.
///
/// The returned Zval is a PHP resource. `php://fd/...` does NOT take
/// ownership of the fd - the caller must keep it alive.
fn fd_to_php_stream(fd: i32) -> PhpResult<Zval> {
    let path = format!("php://fd/{fd}");

    let mut fn_name = Zval::new();
    fn_name.set_string("fopen", false).map_err(|_| {
        error::throw_runtime(
            error::RuntimeException::COMPILATION_FAILED,
            "Failed to create function name".to_string(),
        )
    })?;

    fn_name
        .try_call(vec![&path as &dyn ext_php_rs::convert::IntoZvalDyn, &"r"])
        .map_err(|_| {
            error::throw_runtime(
                error::RuntimeException::COMPILATION_FAILED,
                "Failed to open notification stream".to_string(),
            )
        })
}

#[php_class]
#[php(name = "Typst\\PendingDocument")]
#[php(flags = ClassFlags::Final)]
pub struct PendingDocument {
    completed: Arc<AtomicBool>,
    result: CompilationResult,
    handle: Mutex<Option<JoinHandle<()>>>,
    /// Read end of the notification socket pair.  Becomes readable when
    /// the background thread writes a null byte to the write end.
    read_end: Mutex<Option<UnixStream>>,
    /// Whether `join()` has already been called.
    joined: AtomicBool,
}

#[php_impl]
impl PendingDocument {
    /// Returns whether the background compilation has finished.
    #[php(name = "isReady")]
    pub fn is_ready(&self) -> bool {
        self.completed.load(Ordering::Acquire)
    }

    /// Returns a readable PHP stream resource that becomes readable when
    /// background compilation finishes.
    ///
    /// Register this with your event loop (e.g. `EventLoop::onReadable()`)
    /// to get notified without polling.
    ///
    /// Throws `LogicException` if `join()` has already been called.
    #[php(name = "getNotificationStream")]
    pub fn get_notification_stream(&self) -> PhpResult<Zval> {
        if self.joined.load(Ordering::Acquire) {
            return Err(error::throw_logic(
                "Cannot get notification stream: this PendingDocument has already been resolved"
                    .to_string(),
            ));
        }

        let read = self.read_end.lock();
        let stream = read.as_ref().ok_or_else(|| {
            error::throw_logic(
                "Cannot get notification stream: this PendingDocument has already been resolved"
                    .to_string(),
            )
        })?;

        fd_to_php_stream(stream.as_raw_fd())
    }

    /// Blocks until compilation finishes and returns the document.
    ///
    /// Closes the notification pipe and consumes the result.
    /// Throws `LogicException` if already called.
    /// Throws `RuntimeException` if compilation failed or the thread panicked.
    pub fn join(&self) -> PhpResult<Document> {
        if self.joined.swap(true, Ordering::AcqRel) {
            return Err(error::throw_logic(
                "Cannot join: this PendingDocument has already been resolved".to_string(),
            ));
        }

        let _ = self.read_end.lock().take();

        if let Some(handle) = self.handle.lock().take() {
            handle.join().map_err(|_| {
                error::throw_runtime(
                    error::RuntimeException::COMPILATION_FAILED,
                    "Background compilation thread panicked".to_string(),
                )
            })?;
        }

        let result = self.result.lock().take().ok_or_else(|| {
            error::throw_runtime(
                error::RuntimeException::COMPILATION_FAILED,
                "Result already consumed".to_string(),
            )
        })?;

        match result {
            Ok(doc) => Ok(Document::from_arc(doc)),
            Err(msg) => Err(error::throw_runtime(
                error::RuntimeException::COMPILATION_FAILED,
                msg,
            )),
        }
    }
}

/// Create a `PendingDocument` that compiles on a background thread.
///
/// The returned object exposes a notification stream (readable PHP resource)
/// that becomes readable when compilation finishes.  Call `join()` to
/// retrieve the result.
pub fn spawn(
    mut world: TypstWorld,
    file_id: typst::syntax::FileId,
    text: &str,
    root: &std::path::Path,
    dict: typst::foundations::Dict,
) -> PhpResult<PendingDocument> {
    let (read_end, write_end) = UnixStream::pair().map_err(|e| {
        error::throw_runtime(
            error::RuntimeException::COMPILATION_FAILED,
            format!("Failed to create notification socket pair: {e}"),
        )
    })?;

    read_end.set_nonblocking(true).map_err(|e| {
        error::throw_runtime(
            error::RuntimeException::COMPILATION_FAILED,
            format!("Failed to set socket non-blocking: {e}"),
        )
    })?;

    let completed = Arc::new(AtomicBool::new(false));
    let result: CompilationResult = Arc::new(Mutex::new(None));

    world.set_source(file_id, text, root, dict);

    let completed_bg = Arc::clone(&completed);
    let result_bg = Arc::clone(&result);

    let handle = thread::spawn(move || {
        let Warned { output, .. } = typst::compile::<PagedDocument>(&world);

        let res = match output {
            Ok(doc) => Ok(Arc::new(doc)),
            Err(errors) => {
                let msgs: Vec<String> = errors.iter().map(|d| d.message.to_string()).collect();
                Err(msgs.join("\n"))
            }
        };

        *result_bg.lock() = Some(res);
        completed_bg.store(true, Ordering::Release);

        // Write a null byte so the read end becomes readable, waking any
        // event-loop poll watching it.
        let mut w = write_end;
        let _ = w.write_all(&[0u8]);
    });

    Ok(PendingDocument {
        completed,
        result,
        handle: Mutex::new(Some(handle)),
        read_end: Mutex::new(Some(read_end)),
        joined: AtomicBool::new(false),
    })
}

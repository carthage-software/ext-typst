use std::sync::Arc;
use std::sync::atomic::{AtomicU64, Ordering};

use parking_lot::Mutex;
use typst::foundations::Bytes;
use typst::text::{Font, FontBook, FontInfo};
use typst::utils::LazyHash;

use super::{FontSlot, SharedFonts};

/// Internal shared state for font management.
///
/// Held via `Arc` so that multiple `Compiler` instances (and the PHP
/// `FontManager` object) share the same mutable font set.
pub struct FontManagerInner {
    shared: Arc<SharedFonts>,
    instance_fonts: Mutex<Vec<FontSlot>>,
    /// Monotonically increasing counter; bumped on every `add_font_*` call
    /// so consumers (`TypstWorld`) know when to rebuild the font book.
    generation: AtomicU64,
}

impl FontManagerInner {
    pub fn new(shared: Arc<SharedFonts>) -> Self {
        Self {
            shared,
            instance_fonts: Mutex::new(Vec::new()),
            generation: AtomicU64::new(0),
        }
    }

    #[allow(clippy::cast_possible_truncation)]
    pub fn add_font_data(&self, data: Vec<u8>) -> Result<(), String> {
        let buffer = Bytes::new(data);
        let mut count = 0u32;
        let mut fonts = self.instance_fonts.lock();

        for (index, _info) in FontInfo::iter(buffer.as_slice()).enumerate() {
            fonts.push(FontSlot::from_data(buffer.clone(), index as u32));
            count += 1;
        }

        if count == 0 {
            return Err("No valid fonts found in data".to_string());
        }

        self.generation.fetch_add(1, Ordering::Release);
        Ok(())
    }

    pub fn build_book(&self) -> LazyHash<FontBook> {
        let fonts = self.instance_fonts.lock();
        if fonts.is_empty() {
            return LazyHash::new((*self.shared.book).clone());
        }

        let mut book = (*self.shared.book).clone();
        for slot in fonts.iter() {
            if let Some(font) = slot.get() {
                book.push(font.info().clone());
            }
        }

        LazyHash::new(book)
    }

    pub fn font(&self, index: usize) -> Option<Font> {
        let shared_count = self.shared.fonts.len();
        if index < shared_count {
            self.shared.fonts[index].get()
        } else {
            let fonts = self.instance_fonts.lock();
            fonts.get(index - shared_count)?.get()
        }
    }

    pub fn generation(&self) -> u64 {
        self.generation.load(Ordering::Acquire)
    }

    pub fn shared_font_count(&self) -> usize {
        self.shared.fonts.len()
    }

    pub fn instance_font_count(&self) -> usize {
        self.instance_fonts.lock().len()
    }

    /// Returns a sorted, deduplicated list of font family names.
    pub fn font_families(&self) -> Vec<String> {
        let book = self.build_book();
        book.families().map(|(name, _)| name.to_string()).collect()
    }

    /// Clone the inner state: shares base `SharedFonts` via `Arc`, copies instance fonts.
    pub fn clone_from(original: &Arc<Self>) -> Arc<Self> {
        let instance_fonts = original
            .instance_fonts
            .lock()
            .iter()
            .filter_map(|slot| {
                let font = slot.get()?;
                Some(FontSlot::from_font(font))
            })
            .collect();

        Arc::new(Self {
            shared: Arc::clone(&original.shared),
            instance_fonts: Mutex::new(instance_fonts),
            generation: AtomicU64::new(0),
        })
    }
}

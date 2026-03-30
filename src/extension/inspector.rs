use std::sync::Arc;

use ext_php_rs::flags::ClassFlags;
use ext_php_rs::prelude::*;
use ext_php_rs::types::ZendHashTable;

use typst::diag::Warned;
use typst::layout::PagedDocument;

use crate::internal::world::TypstWorld;

use super::diagnostic::{CompilationResult, Diagnostic};
use super::document::Document;
use super::error;
use super::input;
use super::source::{self, Source};
use super::world::World;

#[php_class]
#[php(name = "Typst\\Inspector")]
#[php(flags = ClassFlags::Final)]
pub struct Inspector {
    world: TypstWorld,
    php_world: World,
}

impl Inspector {
    pub fn from_parts(world: TypstWorld, php_world: World) -> Self {
        Self { world, php_world }
    }

    pub fn typst_world(&self) -> &TypstWorld {
        &self.world
    }

    pub fn php_world(&self) -> &World {
        &self.php_world
    }
}

#[php_impl]
impl Inspector {
    pub fn __construct(world: &World) -> Self {
        let typst_world = TypstWorld::new(
            world.configured_root().to_owned(),
            Arc::clone(world.font_inner()),
            world.package_dir().map(ToOwned::to_owned),
            world.cache_size(),
        );

        Self {
            world: typst_world,
            php_world: World::clone_from(world),
        }
    }

    #[php(defaults(inputs = None))]
    pub fn inspect(
        &mut self,
        source: &Source,
        inputs: Option<&ZendHashTable>,
    ) -> PhpResult<CompilationResult> {
        check_source_world(source, &self.php_world)?;
        let dict = input::convert_inputs(inputs)?;
        self.world
            .set_source(source.file_id(), source.text(), source.root(), dict);
        Ok(inspect_and_report(&self.world))
    }

    #[php(name = "inspectString")]
    #[php(defaults(inputs = None))]
    pub fn inspect_string(
        &mut self,
        input: String,
        inputs: Option<&ZendHashTable>,
    ) -> PhpResult<CompilationResult> {
        let source =
            source::load_string(input, self.php_world.configured_root(), self.php_world.id());
        let dict = input::convert_inputs(inputs)?;
        self.world
            .set_source(source.file_id(), source.text(), source.root(), dict);
        Ok(inspect_and_report(&self.world))
    }

    #[php(name = "inspectFile")]
    #[php(defaults(inputs = None))]
    pub fn inspect_file(
        &mut self,
        path: &str,
        inputs: Option<&ZendHashTable>,
    ) -> PhpResult<CompilationResult> {
        let source = source::load_file(
            path,
            Some(self.php_world.configured_root()),
            self.php_world.id(),
        )?;
        let dict = input::convert_inputs(inputs)?;
        self.world
            .set_source(source.file_id(), source.text(), source.root(), dict);
        Ok(inspect_and_report(&self.world))
    }

    /// Clears all internal caches and returns the number of cleared entries.
    #[allow(clippy::cast_possible_wrap)]
    #[php(name = "clearCache")]
    pub fn clear_cache(&self) -> i64 {
        self.world.clear_cache() as i64
    }

    #[php(name = "getWorld")]
    pub fn get_world(&self) -> World {
        World::clone_from(&self.php_world)
    }
}

fn inspect_and_report(world: &TypstWorld) -> CompilationResult {
    let Warned { output, warnings } = typst::compile::<PagedDocument>(world);
    let warning_diags: Vec<Diagnostic> = warnings
        .iter()
        .map(|w| Diagnostic::from_typst(w, world))
        .collect();

    match output {
        Ok(doc) => {
            let document = Document::from_paged(doc);
            CompilationResult::new_success(&document, warning_diags)
        }
        Err(errors) => {
            let error_diags: Vec<Diagnostic> = errors
                .iter()
                .map(|e| Diagnostic::from_typst(e, world))
                .collect();
            CompilationResult::new_failure(error_diags, warning_diags)
        }
    }
}

fn check_source_world(source: &Source, world: &World) -> PhpResult<()> {
    if source.world_id() != world.id() {
        return Err(error::throw_invalid_argument(
            "Source was created by a different World instance".to_string(),
        ));
    }
    Ok(())
}

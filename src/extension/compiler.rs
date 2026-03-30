use std::collections::HashMap;
use std::sync::Arc;

use ext_php_rs::flags::ClassFlags;
use ext_php_rs::prelude::*;
use ext_php_rs::types::ZendHashTable;

use typst::diag::Warned;
use typst::layout::PagedDocument;

use crate::internal::world::TypstWorld;

use super::document::Document;
use super::error;
use super::input;
use super::pending::{self, PendingDocument};
use super::source::{self, Source};
use super::world::World;

#[php_class]
#[php(name = "Typst\\Compiler")]
#[php(flags = ClassFlags::Final)]
pub struct Compiler {
    world: TypstWorld,
    php_world: World,
}

impl Compiler {
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
impl Compiler {
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

    #[php(name = "compile")]
    #[php(defaults(inputs = None))]
    pub fn php_compile(
        &mut self,
        source: &Source,
        inputs: Option<&ZendHashTable>,
    ) -> PhpResult<Document> {
        check_source_world(source, &self.php_world)?;
        let dict = input::convert_inputs(inputs)?;
        self.world
            .set_source(source.file_id(), source.text(), source.root(), dict);
        compile_and_throw(&self.world)
    }

    #[php(name = "compileString")]
    #[php(defaults(inputs = None))]
    pub fn compile_string(
        &mut self,
        input: String,
        inputs: Option<&ZendHashTable>,
    ) -> PhpResult<Document> {
        let source =
            source::load_string(input, self.php_world.configured_root(), self.php_world.id());
        let dict = input::convert_inputs(inputs)?;
        self.world
            .set_source(source.file_id(), source.text(), source.root(), dict);
        compile_and_throw(&self.world)
    }

    #[php(name = "compileFile")]
    #[php(defaults(inputs = None))]
    pub fn compile_file(
        &mut self,
        path: &str,
        inputs: Option<&ZendHashTable>,
    ) -> PhpResult<Document> {
        let source = source::load_file(
            path,
            Some(self.php_world.configured_root()),
            self.php_world.id(),
        )?;
        let dict = input::convert_inputs(inputs)?;
        self.world
            .set_source(source.file_id(), source.text(), source.root(), dict);
        compile_and_throw(&self.world)
    }

    #[php(name = "getWorld")]
    pub fn get_world(&self) -> World {
        World::clone_from(&self.php_world)
    }

    /// Clears all internal caches and returns the number of cleared entries.
    #[allow(clippy::cast_possible_wrap)]
    #[php(name = "clearCache")]
    pub fn clear_cache(&self) -> i64 {
        self.world.clear_cache() as i64
    }

    #[php(name = "compileInBackground")]
    #[php(defaults(inputs = None))]
    pub fn compile_in_background(
        &self,
        source: &Source,
        inputs: Option<&ZendHashTable>,
    ) -> PhpResult<PendingDocument> {
        check_source_world(source, &self.php_world)?;
        let dict = input::convert_inputs(inputs)?;

        let world = TypstWorld::new(
            self.php_world.configured_root().to_owned(),
            Arc::clone(self.php_world.font_inner()),
            self.php_world.package_dir().map(ToOwned::to_owned),
            self.php_world.cache_size(),
        );

        pending::spawn(world, source.file_id(), source.text(), source.root(), dict)
    }

    #[php(name = "__debugInfo")]
    pub fn debug_info(&self) -> HashMap<String, String> {
        let fi = self.world.font_inner();
        let mut map = HashMap::new();
        map.insert("cacheSize".to_string(), self.world.cache_size().to_string());
        map.insert(
            "packageDir".to_string(),
            self.world
                .package_dir()
                .map_or_else(|| "(none)".to_string(), |p| p.display().to_string()),
        );
        map.insert(
            "sharedFonts".to_string(),
            fi.shared_font_count().to_string(),
        );
        map.insert(
            "instanceFonts".to_string(),
            fi.instance_font_count().to_string(),
        );
        map.insert("version".to_string(), env!("CARGO_PKG_VERSION").to_string());
        map.insert(
            "typstVersion".to_string(),
            env!("TYPST_VERSION").to_string(),
        );
        map
    }
}

fn compile_and_throw(world: &TypstWorld) -> PhpResult<Document> {
    let Warned { output, .. } = typst::compile::<PagedDocument>(world);
    output.map(Document::from_paged).map_err(|errors| {
        let messages: Vec<String> = errors.iter().map(|d| d.message.to_string()).collect();
        error::throw_runtime(
            error::RuntimeException::COMPILATION_FAILED,
            messages.join("\n"),
        )
    })
}

fn check_source_world(source: &Source, world: &World) -> PhpResult<()> {
    if source.world_id() != world.id() {
        return Err(error::throw_invalid_argument(
            "Source was created by a different World instance".to_string(),
        ));
    }
    Ok(())
}

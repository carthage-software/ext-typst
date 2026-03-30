use std::path::{Path, PathBuf};

use typst::foundations::Bytes;
use typst::text::{Font, FontBook, FontInfo};
use typst::utils::LazyHash;

use super::FontSlot;

pub struct SharedFonts {
    pub book: LazyHash<FontBook>,
    pub fonts: Vec<FontSlot>,
}

impl SharedFonts {
    pub fn new(font_dirs: &[PathBuf], embed_default: bool) -> Result<Self, String> {
        let mut book = FontBook::new();
        let mut fonts = Vec::new();

        if embed_default {
            Self::load_embedded_fonts(&mut book, &mut fonts);
        }

        for dir in font_dirs {
            if !dir.is_dir() {
                return Err(format!(
                    "Font directory '{}' does not exist or is not a directory",
                    dir.display()
                ));
            }

            Self::load_font_dir(dir, &mut book, &mut fonts);
        }

        Ok(Self {
            book: LazyHash::new(book),
            fonts,
        })
    }

    fn load_embedded_fonts(book: &mut FontBook, fonts: &mut Vec<FontSlot>) {
        for data in typst_assets::fonts() {
            let buffer = Bytes::new(data);
            for font in Font::iter(buffer) {
                book.push(font.info().clone());
                fonts.push(FontSlot::from_font(font));
            }
        }
    }

    fn load_font_dir(dir: &Path, book: &mut FontBook, fonts: &mut Vec<FontSlot>) {
        for path in walkdir(dir) {
            Self::load_font_file(&path, book, fonts);
        }
    }

    #[allow(clippy::cast_possible_truncation)]
    fn load_font_file(path: &Path, book: &mut FontBook, fonts: &mut Vec<FontSlot>) {
        let ext = path
            .extension()
            .and_then(|e| e.to_str())
            .map(str::to_lowercase);

        match ext.as_deref() {
            Some("ttf" | "otf" | "ttc" | "otc" | "woff" | "woff2") => {}
            _ => return,
        }

        let Ok(data) = std::fs::read(path) else {
            return;
        };

        let buffer = Bytes::new(data);
        for (index, info) in FontInfo::iter(buffer.as_slice()).enumerate() {
            book.push(info);
            fonts.push(FontSlot::from_path(path.to_owned(), index as u32));
        }
    }
}

fn walkdir(dir: &Path) -> Vec<PathBuf> {
    let mut paths = Vec::new();
    if !dir.is_dir() {
        return paths;
    }

    let mut stack = vec![dir.to_owned()];
    while let Some(current) = stack.pop() {
        let Ok(entries) = std::fs::read_dir(&current) else {
            continue;
        };

        for entry in entries.flatten() {
            let path = entry.path();
            if path.is_dir() {
                stack.push(path);
            } else {
                paths.push(path);
            }
        }
    }

    paths
}

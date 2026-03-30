use std::path::PathBuf;
use std::sync::OnceLock;

use typst::foundations::Bytes;
use typst::text::Font;

pub struct FontSlot {
    path: Option<PathBuf>,
    index: u32,
    font: OnceLock<Option<Font>>,
}

impl FontSlot {
    pub fn from_path(path: PathBuf, index: u32) -> Self {
        Self {
            path: Some(path),
            index,
            font: OnceLock::new(),
        }
    }

    pub fn from_data(data: Bytes, index: u32) -> Self {
        let slot = Self {
            path: None,
            index,
            font: OnceLock::new(),
        };
        let _ = slot.font.set(Font::new(data, index));
        slot
    }

    pub fn from_font(font: Font) -> Self {
        let slot = Self {
            path: None,
            index: 0,
            font: OnceLock::new(),
        };
        let _ = slot.font.set(Some(font));
        slot
    }

    pub fn get(&self) -> Option<Font> {
        self.font
            .get_or_init(|| {
                let path = self.path.as_ref()?;
                let data = std::fs::read(path).ok()?;
                Font::new(Bytes::new(data), self.index)
            })
            .clone()
    }
}

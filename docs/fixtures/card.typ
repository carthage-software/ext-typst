#let data = sys.inputs.at("data")

#set page(width: 3.5in, height: 2in, margin: 0.3in)
#set text(font: "New Computer Modern", size: 9pt)

#align(bottom + left)[
  #text(size: 16pt, weight: "bold", fill: rgb("#1a1a1a"))[#data.name]
  #v(2pt)
  #text(size: 9pt, fill: rgb("#666"))[#data.title]
  #v(12pt)
  #grid(
    columns: (auto, auto),
    column-gutter: 8pt,
    row-gutter: 4pt,
    text(size: 7.5pt, fill: rgb("#999"))[email],
    text(size: 7.5pt)[#data.email],
    text(size: 7.5pt, fill: rgb("#999"))[web],
    text(size: 7.5pt)[#data.website],
    text(size: 7.5pt, fill: rgb("#999"))[tel],
    text(size: 7.5pt)[#data.phone],
  )
]

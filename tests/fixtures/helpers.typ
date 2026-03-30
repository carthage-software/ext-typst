#let greet(name) = [Hello, #name!]

#let format-date(d) = [Date: #d]

#let badge(label, color: blue) = box(
  fill: color,
  inset: 4pt,
  radius: 2pt,
  text(fill: white, weight: "bold", label),
)

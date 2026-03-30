#let data = sys.inputs.at("data")

#let items = data.items
#let currency = data.currency
#let tax-rate = data.tax_rate

#let subtotal = items.map(item => item.quantity * item.rate).sum()
#let tax = subtotal * (tax-rate / 100)
#let total = subtotal + tax

#let fmt(n) = {
  let integer = calc.floor(n)
  let decimal = calc.round(n - integer, digits: 2)
  let dec-str = str(calc.round(decimal * 100))
  if dec-str.len() == 1 { dec-str = "0" + dec-str }
  str(integer) + "." + dec-str
}

#set page(
  paper: "a4",
  margin: (top: 2.5cm, bottom: 2.5cm, left: 2cm, right: 2cm),
  footer: context {
    let current = counter(page).get().first()
    let last = counter(page).final().first()
    set text(size: 8pt, fill: luma(150))
    grid(
      columns: (1fr, 1fr),
      align(left)[#data.from.name --- #data.number],
      align(right)[Page #current / #last],
    )
  },
)
#set text(font: "New Computer Modern", size: 10pt)
#set par(justify: true)

#grid(
  columns: (1fr, auto),
  column-gutter: 1cm,
  [
    #text(size: 14pt, weight: "bold", fill: rgb("#2563eb"))[#data.from.name]
    #v(4pt)
    #text(size: 8pt, fill: luma(100))[
      #data.from.address \
      #data.from.email #sym.dot.c #data.from.website \
      Business ID: #data.from.business_id
    ]
  ],
  align(right)[
    #text(size: 28pt, weight: "bold", fill: luma(200))[INVOICE]
  ],
)

#v(1cm)

#grid(
  columns: (1fr, 1fr),
  column-gutter: 1cm,
  [
    #text(size: 8pt, weight: "bold", fill: luma(100))[BILL TO]
    #v(4pt)
    #text(weight: "bold")[#data.to.name] \
    #text(size: 9pt)[
      #data.to.address \
      #data.to.email
    ]
  ],
  align(right)[
    #grid(
      columns: (auto, auto),
      column-gutter: 12pt,
      row-gutter: 6pt,
      text(size: 9pt, fill: luma(100))[Invoice No:],
      text(size: 9pt, weight: "bold")[#data.number],
      text(size: 9pt, fill: luma(100))[Date:],
      text(size: 9pt)[#data.date],
      text(size: 9pt, fill: luma(100))[Due Date:],
      text(size: 9pt, weight: "bold")[#data.due_date],
      text(size: 9pt, fill: luma(100))[Currency:],
      text(size: 9pt)[#currency],
    )
  ],
)

#v(1cm)

#table(
  columns: (1fr, auto, auto, auto),
  align: (left, right, right, right),
  stroke: none,
  inset: (x: 8pt, y: 6pt),
  fill: (_, row) => if row == 0 { rgb("#2563eb") } else if calc.odd(row) { luma(245) } else { none },
  table.header(
    text(fill: white, weight: "bold", size: 9pt)[Description],
    text(fill: white, weight: "bold", size: 9pt)[Quantity],
    text(fill: white, weight: "bold", size: 9pt)[Unit Price],
    text(fill: white, weight: "bold", size: 9pt)[Amount],
  ),
  ..for item in items {
    let unit = if item.unit == "hour" { "hrs" } else { item.unit }
    let line-total = item.quantity * item.rate
    (
      [#item.description \ #text(size: 8pt, fill: luma(100))[#item.details]],
      [#item.quantity #unit],
      [#currency #fmt(item.rate)],
      [#currency #fmt(line-total)],
    )
  }
)

#v(4pt)

#align(right)[
  #grid(
    columns: (auto, 100pt),
    column-gutter: 12pt,
    row-gutter: 6pt,
    text(size: 10pt, fill: luma(100))[Subtotal:],
    align(right, text(size: 10pt)[#currency #fmt(subtotal)]),
    text(size: 10pt, fill: luma(100))[VAT (#calc.round(tax-rate, digits: 0)%):],
    align(right, text(size: 10pt)[#currency #fmt(tax)]),
    grid.cell(colspan: 2, line(length: 100%, stroke: 0.5pt + luma(200))),
    text(size: 12pt, weight: "bold")[Total Due:],
    align(right, text(size: 12pt, weight: "bold", fill: rgb("#2563eb"))[#currency #fmt(total)]),
  )
]

#v(1cm)

#rect(
  width: 100%,
  inset: 12pt,
  radius: 4pt,
  stroke: 0.5pt + luma(200),
  fill: luma(248),
)[
  #text(size: 9pt, weight: "bold")[Payment Details]
  #v(4pt)
  #grid(
    columns: (auto, auto),
    column-gutter: 12pt,
    row-gutter: 4pt,
    text(size: 9pt, fill: luma(100))[Method:],
    text(size: 9pt)[#data.payment.method],
    text(size: 9pt, fill: luma(100))[Bank:],
    text(size: 9pt)[#data.payment.bank],
    text(size: 9pt, fill: luma(100))[IBAN:],
    text(size: 9pt, weight: "bold")[#data.payment.iban],
    text(size: 9pt, fill: luma(100))[BIC/SWIFT:],
    text(size: 9pt)[#data.payment.bic],
    text(size: 9pt, fill: luma(100))[Reference:],
    text(size: 9pt)[#data.number],
  )
]

#v(8pt)

#text(size: 8pt, weight: "bold", fill: luma(100))[Terms & Conditions]
#v(2pt)
#text(size: 8pt, fill: luma(100))[#data.terms]

#v(8pt)

#text(size: 8pt, weight: "bold", fill: luma(100))[Notes]
#v(2pt)
#text(size: 8pt, fill: luma(100))[#data.notes]

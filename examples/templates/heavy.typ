#set page(paper: "a4", margin: 2cm)
#set text(font: "Linux Libertine", size: 11pt)
#set heading(numbering: "1.1.1")
#set par(justify: true)

// ── Helper: generate a fake data table with N rows ──────────────────
#let metric-table(rows) = {
  table(
    columns: (1fr, 1fr, 1fr, 1fr, 1fr, 1fr, 1fr),
    align: (left, right, right, right, right, right, right),
    table.header([*Service*], [*p50*], [*p90*], [*p95*], [*p99*], [*p99.9*], [*Max*]),
    ..for i in range(rows) {
      (
        [svc-#(i + 1)],
        [#calc.rem(i * 7 + 3, 50) ms],
        [#calc.rem(i * 13 + 20, 200) ms],
        [#calc.rem(i * 17 + 50, 400) ms],
        [#calc.rem(i * 23 + 100, 800) ms],
        [#calc.rem(i * 31 + 200, 2000) ms],
        [#calc.rem(i * 37 + 500, 5000) ms],
      )
    },
  )
}

#let capacity-table(rows) = {
  table(
    columns: (1fr, 1fr, 1fr, 1fr, 1fr, 1fr),
    align: (left, right, right, right, right, right),
    table.header([*Resource*], [*Allocated*], [*Used*], [*Available*], [*Util %*], [*Trend*]),
    ..for i in range(rows) {
      (
        [res-#(i + 1)],
        [#(i * 128 + 256) GB],
        [#(i * 96 + 180) GB],
        [#(i * 32 + 76) GB],
        [#calc.rem(i * 11 + 62, 100)%],
        [#if calc.rem(i, 3) == 0 [▲ +#calc.rem(i * 3 + 5, 20)%] else if calc.rem(i, 3) == 1 [▼ -#calc.rem(i * 2 + 1, 10)%] else [- stable]],
      )
    },
  )
}

// ── Title Page ──────────────────────────────────────────────────────

#align(center + horizon)[
  #text(size: 36pt, weight: "bold")[Global Infrastructure\ Operations Report]
  #v(1cm)
  #text(size: 18pt)[Carthage Software - Fiscal Year 2025]
  #v(0.5cm)
  #line(length: 70%, stroke: 1pt)
  #v(0.5cm)
  #text(size: 12pt, style: "italic")[Prepared by the Engineering Division - All Regions]
  #v(0.3cm)
  #text(size: 10pt)[Classification: Internal - Confidential]
  #v(2cm)
  #text(size: 10pt)[Document Version 4.7 - Last Updated March 2025]
]

#pagebreak()

#outline(title: "Table of Contents", depth: 3)

#pagebreak()

// ════════════════════════════════════════════════════════════════════
// PART I - EXECUTIVE OVERVIEW
// ════════════════════════════════════════════════════════════════════

= Executive Summary

#lorem(500)

== Key Performance Indicators

#lorem(300)

#table(
  columns: (2fr, 1fr, 1fr, 1fr, 1fr),
  align: (left, right, right, right, right),
  table.header([*KPI*], [*FY2023*], [*FY2024*], [*FY2025*], [*Target*]),
  [Overall Uptime], [99.94%], [99.96%], [99.97%], [99.99%],
  [MTTR (minutes)], [18.7], [12.3], [8.7], [5.0],
  [MTTD (minutes)], [4.2], [3.1], [2.4], [1.0],
  [Change Failure Rate], [8.2%], [5.7%], [3.9%], [2.0%],
  [Deployment Frequency], [12/day], [23/day], [47/day], [100/day],
  [Lead Time (hours)], [72], [36], [18], [4],
  [Customer-Facing Errors], [0.12%], [0.08%], [0.04%], [0.01%],
  [P1 Incidents], [14], [9], [5], [0],
  [P2 Incidents], [47], [31], [22], [10],
  [Data Loss Events], [0], [0], [0], [0],
  [Security Breaches], [0], [0], [0], [0],
  [Cost per Transaction], [\$0.0034], [\$0.0028], [\$0.0021], [\$0.0015],
)

#lorem(200)

== Financial Summary

#lorem(400)

#table(
  columns: (1fr, 1fr, 1fr, 1fr, 1fr),
  align: (left, right, right, right, right),
  table.header([*Category*], [*FY2023*], [*FY2024*], [*FY2025*], [*FY2026 Est.*]),
  [Compute (IaaS)], [\$3.1M], [\$4.2M], [\$5.8M], [\$7.2M],
  [Storage (all tiers)], [\$1.2M], [\$1.8M], [\$2.4M], [\$3.0M],
  [Network / CDN], [\$0.7M], [\$0.9M], [\$1.1M], [\$1.3M],
  [Licensing (DB, tools)], [\$0.4M], [\$0.6M], [\$0.7M], [\$0.9M],
  [Personnel (SRE/Ops)], [\$2.4M], [\$3.1M], [\$3.6M], [\$4.2M],
  [Monitoring / Observability], [\$0.2M], [\$0.4M], [\$0.5M], [\$0.7M],
  [Security / Compliance], [\$0.5M], [\$0.8M], [\$1.0M], [\$1.3M],
  [Disaster Recovery], [\$0.2M], [\$0.3M], [\$0.4M], [\$0.5M],
  [ML / GPU Infrastructure], [\$0.0M], [\$0.3M], [\$0.9M], [\$1.8M],
  [Miscellaneous], [\$0.3M], [\$0.4M], [\$0.5M], [\$0.6M],
  [*Total*], [*\$9.0M*], [*\$12.8M*], [*\$16.9M*], [*\$21.5M*],
)

#lorem(300)

#pagebreak()

// ════════════════════════════════════════════════════════════════════
// PART II - COMPUTE
// ════════════════════════════════════════════════════════════════════

= Compute Infrastructure

== Global Cluster Overview

#lorem(500)

=== Production Clusters

#for i in range(1, 25) {
  [==== Cluster PROD-#i

  #lorem(120)

  #table(
    columns: (1fr, 1fr, 1fr, 1fr),
    align: (left, right, right, right),
    table.header([*Metric*], [*Current*], [*Peak*], [*Target*]),
    [Node Count], [#calc.rem(i * 37 + 12, 200) nodes], [#calc.rem(i * 37 + 50, 250) nodes], [#calc.rem(i * 37 + 80, 300) nodes],
    [vCPU Total], [#(calc.rem(i * 37 + 12, 200) * 64) vCPUs], [-], [-],
    [vCPU Utilization], [#calc.rem(i * 13 + 45, 100)%], [#calc.min(calc.rem(i * 13 + 65, 100), 98)%], [< 75%],
    [Memory Total], [#(calc.rem(i * 37 + 12, 200) * 256) GB], [-], [-],
    [Memory Utilization], [#calc.rem(i * 17 + 38, 100)%], [#calc.min(calc.rem(i * 17 + 58, 100), 97)%], [< 80%],
    [Pod Count], [#(i * 234 + 567)], [#(i * 290 + 700)], [#(i * 350 + 800)],
    [Network In], [#calc.rem(i * 7 + 23, 100) Gbps], [#calc.rem(i * 7 + 45, 150) Gbps], [-],
    [Network Out], [#calc.rem(i * 11 + 15, 100) Gbps], [#calc.rem(i * 11 + 35, 150) Gbps], [-],
    [Disk IOPS], [#(i * 12000 + 45000)], [#(i * 15000 + 60000)], [-],
    [Pod Restarts (24h)], [#calc.rem(i * 3 + 2, 20)], [#calc.rem(i * 7 + 8, 50)], [< 5],
  )

  Top namespaces by resource consumption:

  #table(
    columns: (1fr, 1fr, 1fr, 1fr),
    align: (left, right, right, right),
    table.header([*Namespace*], [*Pods*], [*CPU Req*], [*Mem Req*]),
    [prod-api], [#(i * 12 + 45)], [#(i * 8 + 32) cores], [#(i * 16 + 64) GB],
    [prod-workers], [#(i * 8 + 23)], [#(i * 6 + 18) cores], [#(i * 12 + 48) GB],
    [prod-data], [#(i * 4 + 12)], [#(i * 10 + 24) cores], [#(i * 32 + 96) GB],
    [monitoring], [#(i * 2 + 8)], [#(i * 2 + 4) cores], [#(i * 4 + 16) GB],
  )

  #lorem(80)
  ]
}

=== Staging & Development Clusters

#lorem(300)

#for i in range(1, 13) {
  [==== Cluster DEV-#i

  #lorem(100)

  #table(
    columns: (1fr, 1fr, 1fr),
    align: (left, right, right),
    table.header([*Metric*], [*Value*], [*Limit*]),
    [Node Count], [#calc.rem(i * 11 + 5, 30) nodes], [40 nodes],
    [vCPU Utilization], [#calc.rem(i * 19 + 22, 100)%], [-],
    [Memory Utilization], [#calc.rem(i * 23 + 31, 100)%], [-],
    [Pod Count], [#(i * 89 + 123)], [2000],
    [Active Developers], [#(i * 3 + 5)], [-],
    [Deployments/day], [#(i * 7 + 12)], [-],
  )

  #lorem(60)
  ]
}

=== GPU Clusters

#lorem(400)

#for i in range(1, 7) {
  [==== GPU Cluster ML-#i

  #lorem(150)

  #table(
    columns: (1fr, 1fr, 1fr),
    align: (left, right, right),
    table.header([*Spec*], [*Value*], [*Cost/hr*]),
    [GPU Type], [#if calc.rem(i, 3) == 0 [NVIDIA A100 80GB] else if calc.rem(i, 3) == 1 [NVIDIA H100 80GB] else [NVIDIA L40S 48GB]], [-],
    [GPU Count], [#(i * 8)], [-],
    [GPU Utilization], [#calc.rem(i * 19 + 67, 100)%], [-],
    [VRAM Used], [#calc.rem(i * 23 + 45, 100)%], [-],
    [Training Jobs/day], [#(i * 12 + 34)], [-],
    [Inference QPS], [#(i * 890 + 2300)], [-],
    [Spot Instance %], [#calc.rem(i * 17 + 30, 80)%], [-],
    [Total Cost/month], [-], [\$#(i * 12000 + 23000)],
  )

  #lorem(100)
  ]
}

#pagebreak()

// ════════════════════════════════════════════════════════════════════
// PART III - STORAGE
// ════════════════════════════════════════════════════════════════════

= Storage Infrastructure

== Object Storage

#lorem(400)

#table(
  columns: (1fr, 1fr, 1fr, 1fr, 1fr, 1fr),
  align: (left, right, right, right, right, right),
  table.header([*Bucket*], [*Objects*], [*Size*], [*Reads/s*], [*Writes/s*], [*Cost/mo*]),
  [media-assets], [847M], [1.2 PB], [45,000], [2,300], [\$28,400],
  [user-uploads], [234M], [890 TB], [12,000], [8,900], [\$21,200],
  [backups-daily], [12M], [670 TB], [200], [150], [\$8,040],
  [backups-archive], [89M], [2.3 PB], [10], [50], [\$6,900],
  [logs-hot], [456M], [120 TB], [34,000], [67,000], [\$14,400],
  [logs-warm], [1.2B], [450 TB], [800], [12,000], [\$5,400],
  [logs-cold], [4.5B], [1.8 PB], [20], [500], [\$3,600],
  [cdn-origin], [567M], [340 TB], [89,000], [1,200], [\$16,300],
  [ml-datasets], [45M], [290 TB], [3,400], [890], [\$6,960],
  [ml-checkpoints], [2.3M], [78 TB], [120], [340], [\$1,560],
  [config-store], [2.3M], [12 TB], [67,000], [450], [\$2,880],
  [terraform-state], [890K], [2.3 TB], [12,000], [8,900], [\$552],
  [audit-logs], [3.4B], [560 TB], [100], [23,000], [\$6,720],
  [container-images], [12M], [45 TB], [23,000], [890], [\$5,400],
  [static-assets], [34M], [8.9 TB], [120,000], [200], [\$4,280],
)

#lorem(300)

== Block Storage

#lorem(300)

=== Performance Tiers

#for tier in ("Ultra", "Premium", "Standard", "Cold Archive") {
  [==== #tier Tier

  #lorem(200)

  #table(
    columns: (1fr, 1fr, 1fr),
    align: (left, right, right),
    table.header([*Specification*], [*Value*], [*Cost/GB/mo*]),
    [IOPS (read)], [#if tier == "Ultra" [1,000,000] else if tier == "Premium" [250,000] else if tier == "Standard" [16,000] else [1,000]], [-],
    [IOPS (write)], [#if tier == "Ultra" [500,000] else if tier == "Premium" [125,000] else if tier == "Standard" [8,000] else [500]], [-],
    [Throughput (read)], [#if tier == "Ultra" [4 GB/s] else if tier == "Premium" [1 GB/s] else if tier == "Standard" [250 MB/s] else [60 MB/s]], [-],
    [Throughput (write)], [#if tier == "Ultra" [2 GB/s] else if tier == "Premium" [500 MB/s] else if tier == "Standard" [125 MB/s] else [30 MB/s]], [-],
    [Latency (p50)], [#if tier == "Ultra" [0.05 ms] else if tier == "Premium" [0.2 ms] else if tier == "Standard" [1 ms] else [5 ms]], [-],
    [Latency (p99)], [#if tier == "Ultra" [0.1 ms] else if tier == "Premium" [0.5 ms] else if tier == "Standard" [2 ms] else [10 ms]], [-],
    [Capacity Provisioned], [#if tier == "Ultra" [120 TB] else if tier == "Premium" [450 TB] else if tier == "Standard" [890 TB] else [2.4 PB]], [-],
    [Capacity Used], [#if tier == "Ultra" [98 TB] else if tier == "Premium" [387 TB] else if tier == "Standard" [712 TB] else [1.9 PB]], [-],
    [Snapshots], [#if tier == "Ultra" [hourly] else if tier == "Premium" [4-hourly] else if tier == "Standard" [daily] else [weekly]], [-],
    [Encryption], [AES-256-GCM], [AES-256-GCM], [-],
  )

  #lorem(150)
  ]
}

== Database Storage

#lorem(400)

=== Relational Databases

#for db in ("users", "orders", "inventory", "payments", "shipments", "accounts", "products", "reviews", "notifications", "audit") {
  [==== Database: #db

  #lorem(100)

  #table(
    columns: (1fr, 1fr),
    align: (left, right),
    table.header([*Property*], [*Value*]),
    [Engine], [PostgreSQL 16.2],
    [Instance Type], [r6g.4xlarge],
    [Replicas], [3 (1 primary + 2 read)],
    [Size on Disk], [#if db == "orders" [8.7 TB] else if db == "users" [2.3 TB] else if db == "products" [1.8 TB] else if db == "audit" [12 TB] else [890 GB]],
    [QPS (avg)], [#if db == "users" [45,000] else if db == "orders" [23,000] else if db == "inventory" [67,000] else [12,000]],
    [QPS (peak)], [#if db == "users" [89,000] else if db == "orders" [56,000] else if db == "inventory" [134,000] else [28,000]],
    [Active Connections], [#if db == "users" [890] else if db == "orders" [1,200] else [450]],
    [Replication Lag (p99)], [< 2ms],
    [Backup Schedule], [Continuous WAL + daily full],
    [Point-in-Time Recovery], [30 days],
  )

  #lorem(60)
  ]
}

=== NoSQL & Specialized Stores

#lorem(300)

#for store in ("sessions-redis", "cache-redis", "search-elastic", "analytics-clickhouse", "events-kafka", "graph-neo4j", "timeseries-influx", "vector-qdrant") {
  [==== #store

  #lorem(120)

  #table(
    columns: (1fr, 1fr),
    align: (left, right),
    table.header([*Property*], [*Value*]),
    [Cluster Nodes], [#if store == "events-kafka" [24] else if store == "cache-redis" [12] else [6]],
    [Total Memory], [#if store == "cache-redis" [512 GB] else if store == "sessions-redis" [256 GB] else [128 GB]],
    [Data Size], [#if store == "analytics-clickhouse" [34 TB] else if store == "events-kafka" [12 TB] else if store == "search-elastic" [4.5 TB] else [890 GB]],
    [Operations/s], [#if store == "cache-redis" [1,200,000] else if store == "sessions-redis" [890,000] else if store == "events-kafka" [450,000] else [34,000]],
    [Uptime (30d)], [99.99%],
  )

  #lorem(60)
  ]
}

#pagebreak()

// ════════════════════════════════════════════════════════════════════
// PART IV - NETWORK
// ════════════════════════════════════════════════════════════════════

= Network Infrastructure

== Global Traffic Distribution

#lorem(500)

=== Points of Presence - Detailed

#for region in ("US East", "US West", "US Central", "Canada East", "Canada West", "EU West (Frankfurt)", "EU West (London)", "EU West (Paris)", "EU West (Amsterdam)", "EU North (Stockholm)", "EU South (Milan)", "EU South (Madrid)", "AP Northeast (Tokyo)", "AP Northeast (Seoul)", "AP Southeast (Singapore)", "AP Southeast (Sydney)", "AP South (Mumbai)", "AP East (Hong Kong)", "SA East (São Paulo)", "SA West (Santiago)", "ME Central (Dubai)", "ME West (Tel Aviv)", "AF South (Johannesburg)", "AF North (Cairo)") {
  [==== PoP: #region

  #lorem(100)

  #table(
    columns: (1fr, 1fr, 1fr, 1fr, 1fr),
    align: (left, right, right, right, right),
    table.header([*Metric*], [*Avg*], [*Peak*], [*p99*], [*Target*]),
    [Throughput], [34 Gbps], [89 Gbps], [-], [100 Gbps],
    [Cache Hit Rate], [93.2%], [-], [-], [> 90%],
    [Latency to Origin], [12 ms], [45 ms], [89 ms], [< 50 ms],
    [Error Rate], [0.002%], [0.012%], [-], [< 0.01%],
    [Active Connections], [234,000], [567,000], [-], [-],
    [TLS Handshake (ms)], [2.3], [8.9], [23], [< 10],
    [DNS Resolution (ms)], [1.2], [4.5], [12], [< 5],
    [Bandwidth Cost/mo], [-], [-], [-], [\$12,300],
  )

  #lorem(60)
  ]
}

== DNS Infrastructure

#lorem(400)

#table(
  columns: (1fr, 1fr, 1fr, 1fr, 1fr),
  align: (left, right, right, right, right),
  table.header([*Zone*], [*Records*], [*QPS*], [*Latency (p50)*], [*Latency (p99)*]),
  [carthage.io], [12,345], [234,000], [1.2 ms], [4.5 ms],
  [api.carthage.io], [890], [456,000], [0.8 ms], [3.2 ms],
  [cdn.carthage.io], [234], [890,000], [0.5 ms], [2.1 ms],
  [internal.carthage.io], [45,678], [123,000], [1.5 ms], [5.8 ms],
  [staging.carthage.io], [23,456], [45,000], [2.1 ms], [8.9 ms],
)

#lorem(200)

== Load Balancing

#lorem(300)

#for lb in ("api-gateway-prod", "web-frontend-prod", "internal-api", "websocket-gateway", "grpc-mesh", "graphql-federation", "admin-panel", "webhook-ingress") {
  [=== Load Balancer: #lb

  #lorem(100)

  #table(
    columns: (1fr, 1fr, 1fr, 1fr, 1fr, 1fr),
    align: (left, right, right, right, right, right),
    table.header([*Metric*], [*Current*], [*Peak*], [*p95*], [*p99*], [*Limit*]),
    [Requests/s], [#if lb == "web-frontend-prod" [567,000] else if lb == "grpc-mesh" [890,000] else [123,000]], [-], [-], [-], [2,000,000],
    [Active Conns], [#if lb == "websocket-gateway" [340,000] else [45,000]], [-], [-], [-], [1,000,000],
    [Bandwidth], [12 Gbps], [34 Gbps], [-], [-], [100 Gbps],
    [Latency (ms)], [0.3], [-], [1.2], [4.5], [-],
    [Error Rate], [0.002%], [0.015%], [-], [-], [< 0.1%],
    [Health Checks/s], [12,000], [-], [-], [-], [-],
  )

  #lorem(60)
  ]
}

== Firewall & DDoS Mitigation

#lorem(400)

#table(
  columns: (1fr, 1fr, 1fr, 1fr),
  align: (left, right, right, right),
  table.header([*Rule Category*], [*Rules*], [*Matches/day*], [*Blocked/day*]),
  [Rate Limiting], [234], [12.3M], [890K],
  [Geo-blocking], [45], [2.1M], [2.1M],
  [Bot Detection], [123], [45.6M], [34.2M],
  [WAF (OWASP)], [567], [890K], [234K],
  [IP Reputation], [89], [5.6M], [3.4M],
  [Custom Rules], [345], [23.4M], [12.1M],
  [DDoS L3/L4], [-], [1.2B], [890M],
  [DDoS L7], [-], [234M], [123M],
)

#lorem(300)

#pagebreak()

// ════════════════════════════════════════════════════════════════════
// PART V - SECURITY & COMPLIANCE
// ════════════════════════════════════════════════════════════════════

= Security & Compliance

== Threat Landscape Analysis

#lorem(600)

== Vulnerability Management

#lorem(300)

=== By Severity - Monthly Breakdown

#for month in ("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December") {
  [==== #month 2025

  #table(
    columns: (1fr, 1fr, 1fr, 1fr, 1fr, 1fr),
    align: (left, right, right, right, right, right),
    table.header([*Severity*], [*New*], [*Resolved*], [*MTTR*], [*Open*], [*SLA Met*]),
    [Critical], [2], [2], [3.8 hours], [0], [100%],
    [High], [8], [7], [16 hours], [1], [94%],
    [Medium], [21], [18], [4.9 days], [3], [89%],
    [Low], [47], [39], [12 days], [8], [82%],
    [Info], [103], [89], [28 days], [14], [-],
  )

  #lorem(80)
  ]
}

== Compliance Certifications

#lorem(200)

#for cert in ("SOC 2 Type II", "ISO 27001:2022", "ISO 27017:2015", "ISO 27018:2019", "PCI DSS Level 1 v4.0", "GDPR", "CCPA", "HIPAA", "FedRAMP Moderate", "CSA STAR Level 2") {
  [=== #cert

  #lorem(200)

  #table(
    columns: (1fr, 1fr),
    align: (left, right),
    table.header([*Aspect*], [*Status*]),
    [Last Audit], [2025-01-15],
    [Next Audit], [2026-01-15],
    [Findings (Critical)], [0],
    [Findings (Major)], [#if cert == "FedRAMP Moderate" [2] else [0]],
    [Findings (Minor)], [#if cert == "SOC 2 Type II" [3] else [1]],
    [Remediation Status], [100%],
    [Auditor], [#if cert == "SOC 2 Type II" [Deloitte] else if cert == "PCI DSS Level 1 v4.0" [Coalfire] else [EY]],
  )

  #lorem(100)
  ]
}

== Penetration Testing Results

#lorem(400)

#table(
  columns: (1fr, 1fr, 1fr, 1fr, 1fr),
  align: (left, left, right, right, left),
  table.header([*Test*], [*Scope*], [*Findings*], [*Critical*], [*Status*]),
  [External Network], [All public IPs], [12], [0], [Remediated],
  [Web Application], [All web properties], [23], [1], [Remediated],
  [API Security], [REST + GraphQL + gRPC], [8], [0], [Remediated],
  [Mobile App], [iOS + Android], [5], [0], [Remediated],
  [Cloud Config], [AWS + GCP], [15], [2], [Remediated],
  [Social Engineering], [All employees], [3], [1], [Training completed],
  [Red Team Exercise], [Full scope], [7], [1], [Remediated],
  [Supply Chain], [Dependencies + CI/CD], [4], [0], [In progress],
)

#lorem(300)

#pagebreak()

// ════════════════════════════════════════════════════════════════════
// PART VI - INCIDENT ANALYSIS
// ════════════════════════════════════════════════════════════════════

= Incident Analysis

== Monthly Incident Reports

#for month in ("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December") {
  [== #month 2025

  #lorem(200)

  === Incident Log

  #table(
    columns: (1fr, 0.5fr, 1fr, 1fr, 1fr),
    align: (left, center, right, right, left),
    table.header([*Incident*], [*Sev*], [*Duration*], [*Impact*], [*Root Cause*]),
    [API latency spike], [P2], [23 min], [3% users], [Connection pool exhaustion],
    [CDN cache invalidation storm], [P3], [45 min], [< 1% users], [Deploy triggered full purge],
    [Database failover], [P2], [12 min], [5% users], [Primary OOM on analytics query],
    [Certificate expiry], [P3], [8 min], [< 0.1% users], [Monitoring gap for internal cert],
    [Network partition], [P1], [34 min], [15% users], [BGP misconfiguration by upstream],
  )

  === Post-Incident Reviews

  #lorem(150)

  === Corrective Actions

  #for j in range(1, 6) {
    [- *Action #j*: #lorem(20)
    ]
  }

  #lorem(100)
  ]
}

#pagebreak()

// ════════════════════════════════════════════════════════════════════
// PART VII - CAPACITY PLANNING
// ════════════════════════════════════════════════════════════════════

= Capacity Planning & Forecasting

== Growth Projections by Service

#lorem(400)

#for service in ("API Platform", "Web Frontend", "Mobile Backend", "Data Pipeline", "ML Platform", "Search", "Messaging", "Payments", "Identity", "Analytics", "Monitoring", "CI/CD") {
  [=== #service

  #lorem(150)

  #table(
    columns: (1fr, 1fr, 1fr, 1fr, 1fr),
    align: (left, right, right, right, right),
    table.header([*Resource*], [*Current*], [*Q2 2025*], [*Q4 2025*], [*Q2 2026*]),
    [Compute (vCPUs)], [1,200], [1,500], [1,900], [2,400],
    [Memory (GB)], [4,800], [6,000], [7,600], [9,600],
    [Storage (TB)], [23], [29], [36], [45],
    [Network (Gbps)], [12], [15], [19], [24],
    [Estimated Cost/mo], [\$45,000], [\$56,000], [\$71,000], [\$89,000],
  )

  #lorem(80)
  ]
}

== Budget Forecast - Detailed

#lorem(400)

#table(
  columns: (1fr, 1fr, 1fr, 1fr, 1fr, 1fr, 1fr),
  align: (left, right, right, right, right, right, right),
  table.header([*Category*], [*Q1*], [*Q2*], [*Q3*], [*Q4*], [*FY2026*], [*vs FY2025*]),
  [Compute], [\$1.6M], [\$1.7M], [\$1.8M], [\$2.1M], [\$7.2M], [+24%],
  [Storage], [\$0.7M], [\$0.7M], [\$0.8M], [\$0.8M], [\$3.0M], [+25%],
  [Network], [\$0.3M], [\$0.3M], [\$0.3M], [\$0.4M], [\$1.3M], [+18%],
  [Licensing], [\$0.2M], [\$0.2M], [\$0.2M], [\$0.3M], [\$0.9M], [+29%],
  [Personnel], [\$1.0M], [\$1.0M], [\$1.1M], [\$1.1M], [\$4.2M], [+17%],
  [Monitoring], [\$0.15M], [\$0.17M], [\$0.18M], [\$0.2M], [\$0.7M], [+40%],
  [Security], [\$0.3M], [\$0.3M], [\$0.3M], [\$0.4M], [\$1.3M], [+30%],
  [DR], [\$0.1M], [\$0.1M], [\$0.12M], [\$0.18M], [\$0.5M], [+25%],
  [ML/GPU], [\$0.4M], [\$0.4M], [\$0.5M], [\$0.5M], [\$1.8M], [+100%],
  [Misc], [\$0.15M], [\$0.15M], [\$0.15M], [\$0.15M], [\$0.6M], [+20%],
  [*Total*], [*\$4.9M*], [*\$5.0M*], [*\$5.4M*], [*\$6.1M*], [*\$21.5M*], [*+27%*],
)

#lorem(300)

#pagebreak()

// ════════════════════════════════════════════════════════════════════
// PART VIII - APPENDICES
// ════════════════════════════════════════════════════════════════════

= Appendices

== Appendix A: Full Service Metrics - 90 Services

#lorem(300)

#for i in range(1, 91) {
  [=== Service #i: svc-#i

  #lorem(60)

  #table(
    columns: (1fr, 1fr, 1fr, 1fr, 1fr, 1fr),
    align: (left, right, right, right, right, right),
    table.header([*Metric*], [*p50*], [*p90*], [*p95*], [*p99*], [*p99.9*]),
    [Latency (ms)], [#calc.rem(i * 7 + 3, 50)], [#calc.rem(i * 13 + 20, 200)], [#calc.rem(i * 17 + 50, 400)], [#calc.rem(i * 23 + 100, 800)], [#calc.rem(i * 31 + 200, 2000)],
    [Error Rate], [#calc.rem(i * 3 + 1, 100) / 10000%], [#calc.rem(i * 7 + 3, 100) / 1000%], [#calc.rem(i * 11 + 5, 100) / 1000%], [#calc.rem(i * 13 + 8, 100) / 100%], [#calc.rem(i * 17 + 12, 100) / 100%],
    [RPS], [#(i * 234 + 1200)], [#(i * 300 + 1500)], [#(i * 350 + 1800)], [#(i * 400 + 2000)], [#(i * 450 + 2200)],
    [CPU %], [#calc.rem(i * 11 + 25, 100)], [#calc.rem(i * 13 + 40, 100)], [#calc.rem(i * 17 + 55, 100)], [#calc.rem(i * 19 + 65, 100)], [#calc.rem(i * 23 + 80, 100)],
    [Mem %], [#calc.rem(i * 7 + 30, 100)], [#calc.rem(i * 11 + 45, 100)], [#calc.rem(i * 13 + 55, 100)], [#calc.rem(i * 17 + 65, 100)], [#calc.rem(i * 19 + 78, 100)],
    [Pods], [#(calc.rem(i * 3 + 2, 20) + 2)], [-], [-], [-], [-],
  )
  ]
}

== Appendix B: Change Log - 200 Entries

#lorem(200)

#for i in range(1, 201) {
  [- *CR-#(2025000 + i)* (#if calc.rem(i, 4) == 0 [infra] else if calc.rem(i, 4) == 1 [security] else if calc.rem(i, 4) == 2 [database] else [network]): #lorem(20)
  ]
}

== Appendix C: Capacity Tables

#lorem(200)

=== Compute Capacity - All Regions

#capacity-table(40)

=== Storage Capacity - All Tiers

#capacity-table(30)

=== Network Capacity - All PoPs

#capacity-table(24)

== Appendix D: Latency Matrices

#lorem(200)

=== Inter-Region Latency (ms)

#table(
  columns: (1fr, 1fr, 1fr, 1fr, 1fr, 1fr, 1fr, 1fr),
  align: (left, right, right, right, right, right, right, right),
  table.header([*From \\ To*], [US-E], [US-W], [EU-W], [EU-N], [AP-NE], [AP-SE], [SA-E]),
  [US-East], [-], [62], [89], [102], [178], [234], [145],
  [US-West], [62], [-], [145], [156], [112], [178], [189],
  [EU-West], [89], [145], [-], [23], [234], [267], [234],
  [EU-North], [102], [156], [23], [-], [245], [278], [245],
  [AP-Northeast], [178], [112], [234], [245], [-], [67], [312],
  [AP-Southeast], [234], [178], [267], [278], [67], [-], [345],
  [SA-East], [145], [189], [234], [245], [312], [345], [-],
)

#lorem(200)

=== Service-to-Service Latency - Top 50

#metric-table(50)

== Appendix E: Vendor Assessments - Detailed

#for vendor in ("Amazon Web Services", "Google Cloud Platform", "Microsoft Azure", "Cloudflare", "Fastly", "Akamai", "Datadog", "New Relic", "Grafana Labs", "PagerDuty", "Opsgenie", "HashiCorp", "Confluent", "Elastic", "Redis Labs", "CockroachDB", "MongoDB Atlas", "Snowflake", "Databricks", "Vercel") {
  [=== #vendor

  #lorem(200)

  #table(
    columns: (1fr, 1fr, 1fr),
    align: (left, right, left),
    table.header([*Dimension*], [*Score*], [*Notes*]),
    [Reliability / SLA], [4.#calc.rem(vendor.len(), 10)/5], [#lorem(8)],
    [Support Quality], [4.#calc.rem(vendor.len() + 3, 10)/5], [#lorem(8)],
    [Cost Efficiency], [3.#calc.rem(vendor.len() + 7, 10)/5], [#lorem(8)],
    [Innovation / Roadmap], [4.#calc.rem(vendor.len() + 2, 10)/5], [#lorem(8)],
    [Security / Compliance], [4.#calc.rem(vendor.len() + 5, 10)/5], [#lorem(8)],
    [Documentation], [3.#calc.rem(vendor.len() + 1, 10)/5], [#lorem(8)],
    [API / Integration], [4.#calc.rem(vendor.len() + 4, 10)/5], [#lorem(8)],
    [Overall], [4.#calc.rem(vendor.len() + 6, 10)/5], [#lorem(8)],
  )

  #lorem(100)
  ]
}

== Appendix F: Organizational Structure

#lorem(500)

#table(
  columns: (1fr, 1fr, 1fr, 1fr),
  align: (left, left, right, left),
  table.header([*Team*], [*Lead*], [*Headcount*], [*Focus Areas*]),
  [Platform Engineering], [A. Chen], [14], [Kubernetes, CI/CD, IaC],
  [Site Reliability], [M. Petrov], [11], [Incident response, SLOs, automation],
  [Database Engineering], [S. Nakamura], [8], [PostgreSQL, Redis, Kafka],
  [Network Engineering], [L. Fischer], [6], [CDN, DNS, load balancing],
  [Security Engineering], [R. Okafor], [9], [AppSec, infrastructure security],
  [ML Infrastructure], [J. Santos], [7], [GPU clusters, model serving],
  [Observability], [K. Andersson], [5], [Metrics, logging, tracing],
  [Cloud Architecture], [D. Kim], [4], [Multi-cloud, cost optimization],
)

#lorem(300)

== Appendix G: Glossary - Extended

#lorem(100)

#table(
  columns: (1fr, 2fr),
  align: (left, left),
  table.header([*Term*], [*Definition*]),
  [MTTR], [Mean Time To Recovery - average time to restore service after an incident],
  [MTTD], [Mean Time To Detection - average time to detect an incident after it begins],
  [SLO], [Service Level Objective - target reliability metric for a service],
  [SLA], [Service Level Agreement - contractual reliability commitment to customers],
  [SLI], [Service Level Indicator - quantitative measure of a service's behavior],
  [RTO], [Recovery Time Objective - maximum acceptable downtime after a disaster],
  [RPO], [Recovery Point Objective - maximum acceptable data loss window],
  [PoP], [Point of Presence - edge network location for CDN or DNS],
  [QPS], [Queries Per Second - database or API query throughput metric],
  [RPS], [Requests Per Second - HTTP request throughput metric],
  [IOPS], [Input/Output Operations Per Second - storage performance metric],
  [vCPU], [Virtual CPU - compute unit in cloud infrastructure],
  [CDN], [Content Delivery Network - geographically distributed caching layer],
  [WAF], [Web Application Firewall - HTTP-level traffic filtering],
  [BGP], [Border Gateway Protocol - internet routing protocol],
  [IaC], [Infrastructure as Code - managing infrastructure through version-controlled definitions],
  [CI/CD], [Continuous Integration / Continuous Delivery - automated build and deploy pipeline],
  [OOM], [Out Of Memory - process termination due to memory exhaustion],
  [WAL], [Write-Ahead Log - database durability mechanism],
  [VRAM], [Video Random Access Memory - GPU memory],
)

#lorem(200)

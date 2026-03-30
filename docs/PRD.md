# Product Requirements Document

## Product

Peshawar Civic GIS Atlas is a portfolio-grade web GIS that helps users explore public facilities and district indicators for Peshawar through a public map and a secured operator console.

## Problem

Public-service information is usually fragmented across spreadsheets, portals, and static reports. The product brings those records into one geospatial workflow with:

- a public-facing map for discovery
- an operator-facing pipeline for ingestion and publishing
- traceable provenance so users know where the data came from

## Audience

- Public users exploring schools, hospitals, and civic services
- Analysts who want quick category and area-level insights
- Data managers responsible for importing and validating updated datasets

## Scope

- Public atlas with filters, category layers, and facility detail pages
- Admin dataset catalog and import workflow
- Peshawar-first seeded data, KP-ready structure
- Batch imports from CSV/XLSX

## Out Of Scope For V1

- Incident reporting
- Parcel or land-record workflows
- Mobile apps
- Advanced routing and network analysis
- Real-time upstream integrations

## Success Signals

- A user can filter and inspect facilities on the public map
- An operator can create a dataset, upload a spreadsheet, validate it, and publish rows to the atlas
- Each facility shows source lineage and dataset version context
- The app demonstrates professional Laravel architecture rather than raw-PHP page scripting

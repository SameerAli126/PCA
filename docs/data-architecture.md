# Data Architecture

## Tables

- `facility_categories`
  Maps human-facing categories like education, health, and civic services.
- `datasets`
  Represents a data source definition.
- `dataset_versions`
  Represents a concrete uploaded version of a dataset.
- `import_runs`
  Tracks one validation/publish cycle against a dataset version.
- `import_errors`
  Stores row-level errors and warnings for import QA.
- `facilities`
  Stores mapped facility records with provenance and publish status.
- `administrative_areas`
  Stores district or lower-area polygons and centers.
- `service_metrics`
  Stores area-level indicator values.
- `saved_views`
  Stores saved filters and map viewport state.
- `audit_logs`
  Stores operator actions against auditable records.

## Geometry Conventions

- Coordinate reference: WGS84 / EPSG:4326
- Public API geometry format: GeoJSON
- Facility geometry: point
- Area geometry: polygon or multipolygon

## Provenance Rules

- Every imported facility should preserve dataset name, dataset version, and optional source year
- Import runs keep row counts, preview rows, and validation issue history
- Audit logs record important admin actions such as dataset creation and import publishing

## Seeded Demo Data

- One Peshawar district area polygon
- Three high-level categories
- Six sample facilities
- Three service metrics

## Real Data Roadmap

- Replace demo area polygon with authoritative Peshawar/KP boundary geometry
- Load official KP Open Data spreadsheets into the import workflow
- Add refined admin-area hierarchy beyond district level

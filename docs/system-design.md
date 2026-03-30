# System Design

## Architecture

- Laravel 12 monolith
- Blade-rendered UI for public and admin flows
- Alpine.js for lightweight interactivity
- Leaflet for public map rendering
- MariaDB as the primary datastore
- Optional MariaDB geometry columns for GIS readiness

## Main Subsystems

- Public Atlas
  Serves the map page, filter UX, results panels, and facility detail pages.
- Admin Console
  Provides dataset registration, import upload, validation, and publishing.
- Import Service
  Parses CSV/XLSX files, detects column mappings, records validation issues, and publishes facilities.
- Analytics Service
  Aggregates counts and service metrics for dashboard summaries.
- Map Layer Service
  Converts facilities and areas into API-friendly layer payloads and GeoJSON.

## Request Flow

1. Browser loads `/`
2. Blade view renders the atlas shell
3. Frontend fetches `/api/map/layers` and `/api/facilities`
4. Leaflet renders features and updates markers when filters change

## Import Flow

1. Operator registers or selects a dataset
2. Operator uploads a CSV/XLSX file in `/admin/imports`
3. `AtlasImportService` stores the file and creates a draft dataset version + import run
4. Validation inspects headings and sample rows, then records errors/warnings
5. Publish converts rows into facilities and updates provenance/version state

## Persistence Model

- `datasets`
- `dataset_versions`
- `import_runs`
- `import_errors`
- `facility_categories`
- `facilities`
- `administrative_areas`
- `service_metrics`
- `saved_views`
- `audit_logs`
- Spatie `roles`, `permissions`, and pivot tables

## GIS Handling

- Canonical UI queries use `latitude` and `longitude`
- MariaDB deployments also get `POINT` and `MULTIPOLYGON` columns when available
- The app tolerates environments where advanced spatial indexing is not practical

## Security

- Authentication via Laravel Breeze
- Authorization via Spatie roles
- Admin routes gated to `super_admin`, `analyst`, and `data_manager`
- Import files stored on the local private disk

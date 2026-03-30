<?php

namespace App\Services;

use App\Models\AdministrativeArea;
use App\Models\Facility;
use Illuminate\Support\Facades\DB;
use Throwable;

class SpatialColumnSynchronizer
{
    public function syncFacility(Facility $facility): void
    {
        if (DB::getDriverName() !== 'mysql' || ! $facility->latitude || ! $facility->longitude) {
            return;
        }

        try {
            DB::statement(
                'UPDATE facilities SET location = ST_GeomFromText(?, 4326) WHERE id = ?',
                [sprintf('POINT(%F %F)', $facility->longitude, $facility->latitude), $facility->id]
            );
        } catch (Throwable) {
            // Spatial columns are an optimization for MariaDB/MySQL; app behavior should not fail without them.
        }
    }

    public function syncAdministrativeArea(AdministrativeArea $area): void
    {
        if (DB::getDriverName() !== 'mysql' || empty($area->boundary_geojson)) {
            return;
        }

        try {
            DB::statement(
                'UPDATE administrative_areas SET boundary = ST_GeomFromGeoJSON(?) WHERE id = ?',
                [json_encode($area->boundary_geojson, JSON_THROW_ON_ERROR), $area->id]
            );
        } catch (Throwable) {
            // Boundary geometry remains available through boundary_geojson if MySQL GIS write support is unavailable.
        }
    }
}

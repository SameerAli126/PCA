<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('administrative_area_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('dataset_version_id')->nullable()->constrained()->nullOnDelete();
            $table->string('metric_key');
            $table->string('metric_label');
            $table->decimal('metric_value', 14, 2);
            $table->string('unit', 50)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_metrics');
    }
};

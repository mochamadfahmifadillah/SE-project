<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('software_feature_map', function (Blueprint $table) {
            $table->id();

            $table->foreignId('software_id')
                ->constrained('software')
                ->cascadeOnDelete();

            $table->foreignId('software_feature_id')
                ->constrained('software_features')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique([
                'software_id',
                'software_feature_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('software_feature_map');
    }
};
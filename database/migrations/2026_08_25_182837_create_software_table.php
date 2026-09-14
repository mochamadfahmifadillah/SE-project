<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('software', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->string('category');
        $table->decimal('rating', 2, 1)->default(0);
        $table->string('price')->nullable();
        $table->unsignedInteger('views')->default(0);
        $table->string('fit')->nullable();
        $table->string('tag')->nullable();
        $table->text('description')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}
};

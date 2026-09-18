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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('job_title')->nullable()->after('phone');
            $table->string('location')->nullable()->after('job_title');
            $table->string('company')->nullable()->after('location');
            $table->string('business_size')->nullable()->after('company');
            $table->string('industry')->nullable()->after('business_size');
            $table->string('business_need')->nullable()->after('industry');
            $table->text('bio')->nullable()->after('business_need');
            $table->string('avatar')->nullable()->after('bio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'job_title',
                'location',
                'company',
                'business_size',
                'industry',
                'business_need',
                'bio',
                'avatar',
            ]);
        });
    }
};
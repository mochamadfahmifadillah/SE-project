<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('implementation_requests', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Lead Assignment
            |--------------------------------------------------------------------------
            */

            $table->foreignId('assigned_to')
                ->nullable()
                ->after('user_id')
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Lead Status
            |--------------------------------------------------------------------------
            */

            $table->string('status')
                ->default('NEW')
                ->change();

            /*
            |--------------------------------------------------------------------------
            | Lead Tracking
            |--------------------------------------------------------------------------
            */

            $table->timestamp('qualified_at')
                ->nullable()
                ->after('status');

            $table->timestamp('proposal_sent_at')
                ->nullable()
                ->after('qualified_at');

            $table->timestamp('closed_at')
                ->nullable()
                ->after('proposal_sent_at');

            $table->text('lost_reason')
                ->nullable()
                ->after('closed_at');
        });
    }

    public function down(): void
    {
        Schema::table('implementation_requests', function (Blueprint $table) {

            $table->dropForeign(['assigned_to']);
            $table->dropColumn([
                'assigned_to',
                'qualified_at',
                'proposal_sent_at',
                'closed_at',
                'lost_reason',
            ]);

            $table->string('status')
                ->default('pending')
                ->change();
        });
    }
};
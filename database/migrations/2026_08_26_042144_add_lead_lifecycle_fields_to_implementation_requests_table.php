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
        Schema::table('implementation_requests', function (Blueprint $table) {
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('implementation_requests', function (Blueprint $table) {
            $table->dropColumn([
                'qualified_at',
                'proposal_sent_at',
                'closed_at',
                'lost_reason',
            ]);
        });
    }
};
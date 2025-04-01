<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrentPeriodColumnsToSubscriptionsTable extends Migration
{
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Add these columns if they do not already exist
            $table->timestamp('current_period_start')->nullable()->after('trial_ends_at');
            $table->timestamp('current_period_end')->nullable()->after('current_period_start');
        });
    }

    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['current_period_start', 'current_period_end']);
        });
    }
}

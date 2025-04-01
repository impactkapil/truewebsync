<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStripeIdToUserPackagesTable extends Migration
{
    public function up()
    {
        Schema::table('user_packages', function (Blueprint $table) {
            $table->string('stripe_id')->nullable()->after('package_id');
        });
    }

    public function down()
    {
        Schema::table('user_packages', function (Blueprint $table) {
            $table->dropColumn('stripe_id');
        });
    }
}

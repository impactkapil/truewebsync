<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUserPackagesForeignKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_packages', function (Blueprint $table) {
            // Drop the existing foreign key
            $table->dropForeign(['customer_id']);

            // Re-add the foreign key referencing 'customers' table
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_packages', function (Blueprint $table) {
            // Drop the foreign key referencing 'customers' table
            $table->dropForeign(['customer_id']);

            // Re-add the foreign key referencing 'users' table
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
}

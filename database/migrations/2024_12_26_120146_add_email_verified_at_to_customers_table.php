<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmailVerifiedAtToCustomersTable extends Migration
{
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            // Add email_verified_at column after the email column
            $table->timestamp('email_verified_at')->nullable()->after('email');
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            // Drop the email_verified_at column
            $table->dropColumn('email_verified_at');
        });
    }
}

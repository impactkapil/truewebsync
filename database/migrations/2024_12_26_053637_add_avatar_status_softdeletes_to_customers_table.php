<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAvatarStatusSoftdeletesToCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
        Schema::table('customers', function (Blueprint $table) {
            // Add avatar column
            $table->string('avatar')->nullable()->after('password');
            
            // Add status column with default value true (active)
            $table->boolean('status')->default(true)->after('avatar');
            
            // Add soft deletes column after 'updated_at' instead of 'timestamps'
            $table->softDeletes()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            // Drop the added columns
            $table->dropColumn(['avatar', 'status', 'deleted_at']);
        });
    }
}

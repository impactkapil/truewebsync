<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('feature_name');  // To store the functionality/feature name
            $table->boolean('is_enabled')->default(false);  // On/Off flag (0 or 1)
            $table->timestamp('updated_at')->nullable();  // Only the updated_at column
            // If you also want a created_at column, you can use:
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}

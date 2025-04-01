<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPackagesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id'); // Foreign key to users table
            $table->unsignedBigInteger('package_id');  // Foreign key to packages table
            $table->string('card_number');             // Simulated card details
            $table->string('card_holder');
            $table->string('expiry_date');
            $table->string('cvv');
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_packages');
    }
}

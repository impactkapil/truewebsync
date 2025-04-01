<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shopify_stores', function (Blueprint $table) {
            $table->string('webhooks_secret_key')->nullable()->after('access_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shopify_stores', function (Blueprint $table) {
            $table->dropColumn('webhooks_secret_key');
        });
    }
};


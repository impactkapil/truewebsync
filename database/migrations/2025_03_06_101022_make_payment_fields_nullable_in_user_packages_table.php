<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MakePaymentFieldsNullableInUserPackagesTable extends Migration
{
    public function up()
    {
        // Use raw SQL statements to modify columns
        DB::statement("ALTER TABLE user_packages MODIFY card_number VARCHAR(255) NULL");
        DB::statement("ALTER TABLE user_packages MODIFY expiry_date VARCHAR(255) NULL");
        DB::statement("ALTER TABLE user_packages MODIFY cvv VARCHAR(255) NULL");
    }

    public function down()
    {
        // Revert the changes – adjust the type/length if needed
        DB::statement("ALTER TABLE user_packages MODIFY card_number VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE user_packages MODIFY expiry_date VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE user_packages MODIFY cvv VARCHAR(255) NOT NULL");
    }
}

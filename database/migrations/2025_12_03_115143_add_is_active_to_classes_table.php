<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
// database/migrations/xxxx_add_is_active_to_classes_table.php
public function up()
{
    Schema::table('classes', function (Blueprint $table) {
        $table->boolean('is_active')->default(true)->after('description');
    });
}

public function down()
{
    Schema::table('classes', function (Blueprint $table) {
        $table->dropColumn('is_active');
    });
}
};

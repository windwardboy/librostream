<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('audiobooks', function (Blueprint $table) {
            // Change 'narrator' column to TEXT type
            $table->text('narrator')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('audiobooks', function (Blueprint $table) {
            // Revert 'narrator' column back to string (e.g., varchar(255)) if needed
            // Note: This might truncate data if longer strings were stored as TEXT
            $table->string('narrator', 255)->change();
        });
    }
};

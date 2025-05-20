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
        Schema::table('audiobook_sections', function (Blueprint $table) {
            $table->string('reader_name')->nullable()->after('duration'); // Add reader_name column
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('audiobook_sections', function (Blueprint $table) {
            $table->dropColumn('reader_name'); // Drop reader_name column
        });
    }
};

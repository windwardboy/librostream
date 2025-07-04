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
            $table->unique(['audiobook_id', 'librivox_section_id']);
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
            $table->dropUnique(['audiobook_id', 'librivox_section_id']);
        });
    }
};

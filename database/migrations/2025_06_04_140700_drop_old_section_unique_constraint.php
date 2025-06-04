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
            $table->dropUnique('audiobook_sections_audiobook_id_section_number_unique');
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
            // Re-add the unique constraint if rolling back
            $table->unique(['audiobook_id', 'section_number'], 'audiobook_sections_audiobook_id_section_number_unique');
        });
    }
};

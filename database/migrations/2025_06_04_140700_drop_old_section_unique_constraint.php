<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Import DB facade

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
            // Safely drop the unique index if it exists
            // This handles cases where the index might have been dropped manually or by another migration
            $indexName = 'audiobook_sections_audiobook_id_section_number_unique';
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('audiobook_sections');

            if (array_key_exists($indexName, $indexes)) {
                $table->dropUnique($indexName);
            }
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
            // This assumes the column types are compatible for re-adding the index
            $table->unique(['audiobook_id', 'section_number'], 'audiobook_sections_audiobook_id_section_number_unique');
        });
    }
};

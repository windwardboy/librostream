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
            $table->unsignedBigInteger('librivox_id')->nullable()->unique()->after('source_url')->comment('LibriVox API ID for this audiobook');
            $table->string('language')->nullable()->after('librivox_id')->comment('Language of the audiobook');
            $table->index('language'); // Add index for language if it will be filtered often
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
            $table->dropIndex(['language']); // Drop index first if it was created
            $table->dropColumn(['librivox_id', 'language']);
        });
    }
};

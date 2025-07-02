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
            // Change librivox_id to string, assuming it's currently an integer
            // Make it nullable first if it's not already, to avoid issues with existing data during type change
            // Then change to string, and make it unique
            $table->string('librivox_id')->change();
            // If you want to ensure uniqueness, you can add it here, but be careful if there are existing duplicates
            // $table->unique('librivox_id');
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
            // Revert librivox_id back to integer if needed, or drop it
            // Note: Reverting to integer might cause data loss if string IDs were inserted
            // If you added a unique constraint in up(), you should drop it here
            // $table->dropUnique(['librivox_id']);
            $table->integer('librivox_id')->change();
        });
    }
};

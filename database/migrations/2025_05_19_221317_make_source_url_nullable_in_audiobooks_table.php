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
            $table->string('source_url')->nullable()->change(); // Make the column nullable
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
            // Note: Making a nullable column non-nullable might fail if there are existing null values.
            // For simplicity in this migration, we'll just revert the nullable change.
            // In a real app, you might need to handle existing nulls before making it non-nullable.
            $table->string('source_url')->nullable(false)->change(); // Revert to non-nullable
        });
    }
};

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
        Schema::create('audiobook_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('audiobook_id');
            $table->foreign('audiobook_id')->references('id')->on('audiobooks')->onDelete('cascade'); // Link to audiobooks table

            $table->integer('section_number');
            $table->string('title');
            $table->string('source_url');
            $table->string('duration')->nullable(); // Duration of this section
            $table->unsignedBigInteger('librivox_section_id')->nullable()->unique()->comment('LibriVox API Section ID'); // Optional LibriVox section ID

            $table->timestamps();

            // Optional: Add a unique index on audiobook_id and section_number to prevent duplicates
            $table->unique(['audiobook_id', 'section_number']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('audiobook_sections');
    }
};

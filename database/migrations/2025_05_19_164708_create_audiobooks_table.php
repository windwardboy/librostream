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
        Schema::create('audiobooks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author');
            $table->text('description');
            $table->string('cover_image')->nullable(); // URL to the cover image, can be optional
            $table->string('duration')->nullable(); // e.g., "2h 30m" or total seconds
            $table->string('source_url'); // URL to the audio stream
            $table->unsignedBigInteger('category_id')->nullable(); // Foreign key for category
            // $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null'); // Optional: Define foreign key constraint
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('audiobooks');
    }
};

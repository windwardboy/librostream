<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('import_progress', function (Blueprint $table) {
            $table->id();
            $table->string('type')->comment('e.g. "librivox_audiobooks"');
            $table->string('last_id')->nullable()->comment('Last successfully processed ID');
            $table->integer('offset')->default(0);
            $table->text('errors')->nullable()->comment('JSON array of failed IDs and errors');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('import_progress');
    }
};

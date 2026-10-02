<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('record_release_dates', function (Blueprint $table) {
            $table->unsignedBigInteger('discogs_release_id')->primary();
            $table->string('original_release_date', 10)->nullable();
            $table->string('original_date_source')->nullable();
            $table->string('pressing_release_date', 10)->nullable();
            $table->string('musicbrainz_id')->nullable();
            $table->timestamps();
        });
    }
};

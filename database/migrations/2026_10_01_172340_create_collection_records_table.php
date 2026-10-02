<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_records', function (Blueprint $table) {
            $table->unsignedBigInteger('instance_id')->primary();
            $table->unsignedBigInteger('discogs_release_id');
            $table->string('added_at');
            $table->json('payload');
            $table->timestamp('updated_at')->nullable();
        });
    }
};

<?php

use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('collection_records')
            ->select(['instance_id', 'added_at'])
            ->orderBy('instance_id')
            ->each(function (object $record): void {
                DB::table('collection_records')
                    ->where('instance_id', $record->instance_id)
                    ->update(['added_at' => CarbonImmutable::parse($record->added_at)->utc()->toDateTimeString()]);
            });

        Schema::table('collection_records', function (Blueprint $table) {
            $table->index('added_at');
            $table->index('discogs_release_id');
        });
    }
};

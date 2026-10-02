<?php

namespace App\Console\Commands;

use App\Support\RecordCollection;
use App\Support\RecordReleaseDates;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportRecordSnapshotCommand extends Command
{
    protected $signature = 'records:export-snapshot';

    protected $description = 'Export collection data and verified dates for importing into another database';

    public function handle(RecordCollection $collection, RecordReleaseDates $dates): int
    {
        $records = $dates->enrich($collection->records());

        $snapshot = [
            'username' => config('services.discogs.username'),
            'total' => count($records),
            'syncedAt' => now()->toDateString(),
            'records' => $records,
        ];

        $json = json_encode($snapshot, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        File::replace(resource_path('data/collection.json'), $json.PHP_EOL);

        $count = count($records);

        $this->info("Exported {$count} records and their verified dates.");

        return self::SUCCESS;
    }
}

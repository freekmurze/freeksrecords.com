<?php

namespace App\Console\Commands;

use App\Support\RecordCollection;
use Illuminate\Console\Command;

class ImportRecordSnapshotCommand extends Command
{
    protected $signature = 'records:import-snapshot';

    protected $description = 'Import the bundled collection into the configured database';

    public function handle(RecordCollection $collection): int
    {
        $records = $collection->snapshot();
        $count = count($records);

        $this->info("Importing {$count} records into the database...");

        $collection->storeMany($records);

        $this->call('records:sync-dates');

        $this->comment('Collection imported.');

        return self::SUCCESS;
    }
}

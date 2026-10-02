<?php

namespace App\Console\Commands;

use App\Support\DiscogsRecordMapper;
use App\Support\RecordCollection;
use App\Support\RecordCoverImages;
use Illuminate\Console\Command;
use Throwable;

class CopyRecordCoversCommand extends Command
{
    protected $signature = 'records:copy-covers {--limit=0 : Maximum covers to process, or zero for all}';

    protected $description = 'Copy bundled and Discogs covers into our public object storage';

    public function handle(RecordCollection $collection, RecordCoverImages $images, DiscogsRecordMapper $mapper): int
    {
        $copied = 0;
        $failed = 0;
        $limit = max(0, (int) $this->option('limit'));

        foreach ($collection->records() as $record) {
            if ($record['cover'] === $images->url($record['id'])) {
                continue;
            }

            $source = $record['coverSource'] ?? $record['cover'];

            if ($source === $mapper->placeholderCover) {
                continue;
            }

            $this->line("Copying cover of {$record['artist']} / {$record['displayTitle']}...");

            try {
                $record['cover'] = $images->copy($record['id'], $source);
                $record['coverSource'] = $source;

                $collection->store($record);

                $copied++;
            } catch (Throwable $exception) {
                $failed++;

                $this->warn("Cover {$record['id']} could not be copied: {$exception->getMessage()}");
            }

            if ($limit > 0) {
                if ($copied + $failed >= $limit) {
                    break;
                }
            }
        }

        $this->info("Copied {$copied} covers. Failed: {$failed}.");

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}

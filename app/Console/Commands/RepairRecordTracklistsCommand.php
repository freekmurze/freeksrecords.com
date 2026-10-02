<?php

namespace App\Console\Commands;

use App\Support\DiscogsRecordMapper;
use App\Support\RecordCollection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RepairRecordTracklistsCommand extends Command
{
    protected $signature = 'records:repair-tracklists';

    protected $description = 'Group movements on the same physical track while preserving existing listening links';

    public function handle(RecordCollection $collection, DiscogsRecordMapper $mapper): int
    {
        $username = config('services.discogs.username');

        $releases = File::json(storage_path("app/discogs/collection-{$username}-all.json"))['releases'];
        $masterIds = array_column(array_column($releases, 'basic_information'), 'master_id', 'id');

        $repaired = 0;

        foreach ($collection->records() as $record) {
            $this->line("Checking tracklist of {$record['artist']} / {$record['displayTitle']}...");

            $entries = $this->sourceTracklist($record['id'], $masterIds[$record['id']] ?? 0);

            if (! array_filter($entries, fn (array $track): bool => ! empty($track['sub_tracks']))) {
                continue;
            }

            $existingTracks = array_column($record['tracks'], null, 'position');

            $record['tracks'] = array_map(function (array $track) use ($existingTracks): array {
                $previous = $existingTracks[$track['position']] ?? null;

                if (! $previous) {
                    return $track;
                }

                return $previous['title'] === $track['title'] ? $previous : $track;
            }, $mapper->tracks($entries, $record['artist']));

            $collection->store($record);

            $repaired++;
        }

        $this->info("Repaired {$repaired} multipart tracklists.");

        return self::SUCCESS;
    }

    /** @return array<int, array<string, mixed>> */
    protected function sourceTracklist(int $releaseId, int $masterId): array
    {
        $releasePath = storage_path("app/discogs/releases-{$releaseId}.json");

        $sourcePath = File::exists($releasePath)
            ? $releasePath
            : storage_path("app/discogs/masters-{$masterId}.json");

        if (! File::exists($sourcePath)) {
            return [];
        }

        return File::json($sourcePath)['tracklist'] ?? [];
    }
}

<?php

namespace App\Support;

class DiscogsTracklist
{
    /**
     * Flattens a Discogs tracklist into playable tracks. Movements that share
     * one physical track (A1.1, B.a, B (i), ...) collapse into their parent.
     *
     * @param  array<int, array<string, mixed>>  $entries
     * @return array<int, array<string, mixed>>
     */
    public function tracks(array $entries): array
    {
        $tracks = [];

        foreach ($entries as $entry) {
            $children = $entry['sub_tracks'] ?? [];

            if (! $children) {
                if (! in_array($entry['type_'] ?? 'track', ['heading', 'index'])) {
                    $tracks[] = $entry;
                }

                continue;
            }

            $parentPosition = $this->sharedParentPosition($children);

            if ($parentPosition === null) {
                array_push($tracks, ...$this->tracks($children));

                continue;
            }

            $entry['position'] = $entry['position'] ?: $parentPosition;
            unset($entry['sub_tracks']);

            $tracks[] = $entry;
        }

        return $tracks;
    }

    /** @param array<int, array<string, mixed>> $children */
    protected function sharedParentPosition(array $children): ?string
    {
        $positions = array_values(array_unique(array_map(
            fn (array $track): string => $this->parentPosition($track['position'] ?? ''),
            $children,
        )));

        if (count($positions) !== 1) {
            return null;
        }

        return $positions[0] === '' ? null : $positions[0];
    }

    protected function parentPosition(string $position): string
    {
        return preg_replace('/(?:\.[0-9a-z]+|\s*\([0-9a-z]+\))$/i', '', $position) ?? $position;
    }
}

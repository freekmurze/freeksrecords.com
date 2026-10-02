<?php

namespace App\Support;

use Inertia\Inertia;
use Inertia\Response;

class CollectionPage
{
    public function __construct(
        protected RecordCollection $collection,
    ) {}

    /** @param array<string, mixed>|null $sharedRecord */
    public function render(?array $sharedRecord = null): Response
    {
        return Inertia::render('welcome', [
            'sharedRecord' => $sharedRecord ? $this->sharedRecordMeta($sharedRecord) : null,
            'social' => $this->socialMeta(),
            'collection' => [
                'records' => $this->collection->summaries(),
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $record
     * @return array<string, mixed>
     */
    protected function sharedRecordMeta(array $record): array
    {
        return [
            'instanceId' => $record['instanceId'],
            'title' => $record['displayTitle'],
            'artist' => $record['artist'],
            'description' => $record['shareDescription'],
            'url' => url($record['shareUrl']),
            'image' => route('recordShareImage', [
                'instanceId' => $record['instanceId'],
                'v' => 3,
            ]),
        ];
    }

    /** @return array<string, string> */
    protected function socialMeta(): array
    {
        return [
            'title' => "Freek's records",
            'description' => "A lifetime of digging, one shelf at a time. Browse Freek's vinyl collection by artist, decade or genre, and find your next favourite record.",
            'url' => route('home'),
            'image' => route('collectionShareImage', ['v' => 2]),
            'imageAlt' => "Freek's records: a wooden shelf of album sleeves beneath a vintage turntable and speakers",
        ];
    }
}

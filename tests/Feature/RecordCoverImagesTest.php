<?php

use App\Support\RecordCollection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Http::preventStrayRequests();

    $this->disk = Storage::fake('public');
    $this->collection = app(RecordCollection::class);
    $this->record = $this->collection->snapshot()[0];
});

it('moves bundled covers to public storage and can safely copy them again', function () {
    $this->collection->store($this->record);

    $this->artisan('records:copy-covers')->assertSuccessful();
    $this->artisan('records:copy-covers')->expectsOutput('Copied 0 covers. Failed: 0.')->assertSuccessful();

    $path = "records/{$this->record['id']}.jpg";
    $this->disk->assertExists($path);
    expect($this->disk->get($path))->toBe(file_get_contents(public_path(ltrim($this->record['cover'], '/'))))
        ->and($this->collection->find($this->record['instanceId'])['cover'])->toBe($this->disk->url($path));
    Http::assertNothingSent();
});

it('copies a deferred Discogs cover and replaces the placeholder with its public url', function () {
    $bytes = file_get_contents(public_path(ltrim($this->record['cover'], '/')));
    $this->collection->store([
        ...$this->record,
        'coverSource' => 'https://i.discogs.com/test-cover.jpg',
        'cover' => '/images/record-placeholder.svg',
    ]);
    Http::fake(['https://i.discogs.com/test-cover.jpg' => Http::response($bytes)]);

    $this->artisan('records:copy-covers')->assertSuccessful();

    $path = "records/{$this->record['id']}.jpg";
    $this->disk->assertExists($path);
    expect($this->collection->find($this->record['instanceId'])['cover'])->toBe($this->disk->url($path));
    Http::assertSentCount(1);
    $this->get(route('recordShareImage', $this->record['instanceId']))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png');
});

it('keeps the record when a cover download fails so it can be retried', function () {
    $this->collection->store([...$this->record, 'cover' => 'https://i.discogs.com/unavailable.jpg']);
    Http::fake(['https://i.discogs.com/unavailable.jpg' => Http::response('', 503)]);

    $this->artisan('records:copy-covers')->assertFailed();

    $this->disk->assertMissing("records/{$this->record['id']}.jpg");
    expect($this->collection->find($this->record['instanceId'])['cover'])->toBe('https://i.discogs.com/unavailable.jpg');
    Http::assertSentCount(1);
});

it('rejects covers from unexpected hosts without fetching them', function () {
    $this->collection->store([...$this->record, 'cover' => 'http://127.0.0.1/private']);

    $this->artisan('records:copy-covers')->assertFailed();

    $this->disk->assertMissing("records/{$this->record['id']}.jpg");
    Http::assertNothingSent();
});

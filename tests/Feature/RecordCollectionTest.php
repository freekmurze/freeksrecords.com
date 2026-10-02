<?php

use App\Support\RecordCollection;
use Inertia\Testing\AssertableInertia as Assert;

it('lets guests browse the whole collection without downloading every track link', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('welcome')
            ->where('sharedRecord', null)
            ->has('collection.records', 850)
            ->where('collection.records.0.artist', 'George Harrison')
            ->where('collection.records.0.originalYear', 1970)
            ->where('collection.records.0.year', 2021)
            ->where('collection.records.4.artist', 'Elbow')
            ->where('collection.records.1.title', 'Shebang')
            ->where('collection.records.0.trackTitles.1', 'My Sweet Lord')
            ->missing('collection.records.0.tracks')
            ->where('collection.records.1.appleMusicUrl', 'https://music.apple.com/be/album/shebang/1630427079')
            ->where('collection.records.1.spotifyUrl', 'https://open.spotify.com/album/1khpMdVH3EXz5RWt4WRAbg')
            ->where('collection.records.3.spotifySearch', true)
        );
});

it('provides track positions when opening a record', function () {
    $this->getJson(route('recordDetails', ['instanceId' => 2189134863]))
        ->assertOk()
        ->assertJsonPath('title', 'Shebang')
        ->assertJsonPath('tracks.0.position', 'A1')
        ->assertJsonPath('tracks.2.position', 'B1');
});

it('provides direct and search listening links when opening a record', function () {
    $this->getJson(route('recordDetails', ['instanceId' => 2189134981]))
        ->assertOk()
        ->assertJsonPath('tracks.1.appleMusicSearch', false)
        ->assertJsonPath('tracks.1.appleMusicUrl', fn (string $url) => str_contains($url, '?i='))
        ->assertJsonPath('tracks.1.spotifySearch', true)
        ->assertJsonPath('tracks.1.spotifyUrl', 'https://open.spotify.com/search/George%20Harrison%20My%20Sweet%20Lord');
});

it('returns 404 for unknown collection copies', function () {
    $this->getJson(route('recordDetails', ['instanceId' => 9999999999]))->assertNotFound();
});

it('does not route invalid collection copy ids', function () {
    $this->getJson('/api/records/not-a-record')->assertNotFound();
});

it('renders social metadata for a shared record', function () {
    $this->get(route('recordShare', ['instanceId' => 2189134863, 'slug' => 'oren-ambarchi-shebang']))
        ->assertOk()
        ->assertSee('property="og:title" content="Shebang by Oren Ambarchi"', false)
        ->assertSee('Shebang by Oren Ambarchi (2022), from Freek&#039;s vinyl collection.', false)
        ->assertSee('name="twitter:description"', false)
        ->assertSee('property="og:locale" content="en_GB"', false)
        ->assertSee('name="twitter:site" content="@freekmurze"', false)
        ->assertSee('name="theme-color" content="#292a22"', false)
        ->assertSee('name="twitter:card" content="summary_large_image"', false)
        ->assertSee(route('recordShareImage', 2189134863), false);
});

it('passes the shared record to the page so it opens immediately', function () {
    $path = '/record/2189134863/oren-ambarchi-shebang';

    $this->get($path)
        ->assertSee('property="og:url" content="'.url($path).'"', false)
        ->assertSee('rel="canonical" href="'.url($path).'"', false)
        ->assertInertia(fn (Assert $page) => $page
            ->where('sharedRecord.instanceId', 2189134863)
            ->where('sharedRecord.title', 'Shebang')
            ->where('sharedRecord.url', url($path))
            ->where('collection.records.1.shareUrl', $path));
});

it('redirects outdated or missing record slugs to the canonical url', function (string $path) {
    $this->get($path)->assertRedirect('/record/2189134863/oren-ambarchi-shebang')->assertStatus(301);
})->with([
    'outdated slug' => ['/record/2189134863/old-album-title'],
    'missing slug' => ['/record/2189134863'],
]);

it('returns 404 for unknown shared records', function () {
    $this->get('/record/9999999999/unknown-album')->assertNotFound();
});

it('renders a full size preview image for a shared record', function () {
    $response = $this->get(route('recordShareImage', 2189134863));

    $response->assertOk()->assertHeader('Content-Type', 'image/png');

    [$width, $height] = getimagesizefromstring($response->getContent());

    expect($width)->toBe(1200)->and($height)->toBe(630);
});

it('returns 404 for the preview image of an unknown record', function () {
    $this->get(route('recordShareImage', 9999999999))->assertNotFound();
});

it('renders a full size preview image for the collection', function () {
    $response = $this->get(route('collectionShareImage'));

    $response->assertOk()->assertHeader('Content-Type', 'image/png');

    [$width, $height] = getimagesizefromstring($response->getContent());

    expect($width)->toBe(1200)->and($height)->toBe(630);
});

it('renders social metadata for the collection homepage', function () {
    $collectionImage = route('collectionShareImage', ['v' => 2]);
    $home = route('home');

    $this->get(route('home'))
        ->assertSee('property="og:type" content="website"', false)
        ->assertSee('A lifetime of digging, one shelf at a time.')
        ->assertSee('name="twitter:description"', false)
        ->assertSee('property="og:locale" content="en_GB"', false)
        ->assertSee('name="twitter:site" content="@freekmurze"', false)
        ->assertSee('name="theme-color" content="#292a22"', false)
        ->assertSee("property=\"og:image\" content=\"{$collectionImage}\"", false)
        ->assertSee("rel=\"canonical\" href=\"{$home}\"", false);
});

it('describes the collection with structured data', function () {
    $schema = structuredData($this->get(route('home'))->assertOk()->getContent());

    expect($schema['@context'])->toBe('https://schema.org')
        ->and($schema['@type'])->toBe('CollectionPage')
        ->and($schema['author']['name'])->toBe('Freek Van der Herten');
});

it('describes a shared album with structured data', function () {
    $html = $this->get('/record/2189134863/oren-ambarchi-shebang')->assertOk()->getContent();

    $schema = structuredData($html);

    expect($schema['@context'])->toBe('https://schema.org')
        ->and($schema['@type'])->toBe('MusicAlbum')
        ->and($schema['name'])->toBe('Shebang')
        ->and($schema['byArtist']['name'])->toBe('Oren Ambarchi');
});

it('reads the persisted collection once records are stored', function () {
    $collection = app(RecordCollection::class);
    $record = $collection->snapshot()[0];

    $collection->store($record);

    $this->get(route('home'))->assertInertia(fn (Assert $page) => $page
        ->component('welcome')
        ->has('collection.records', 1)
        ->where('collection.records.0.instanceId', $record['instanceId'])
        ->missing('collection.records.0.tracks'));
});

it('refreshes the cached collection when a record is stored', function () {
    $collection = app(RecordCollection::class);
    [$first, $second] = $collection->snapshot();

    $collection->store($first);

    expect($collection->summaries())->toHaveCount(1);

    $collection->store($second);

    expect($collection->summaries())->toHaveCount(2);
});

/** @return array<string, mixed> */
function structuredData(string $html): array
{
    preg_match('/<script[^>]+type="application\/ld\+json"[^>]*>(.*?)<\/script>/s', $html, $matches);

    return json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR);
}

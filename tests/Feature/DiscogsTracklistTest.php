<?php

use App\Support\DiscogsTracklist;

it('collapses numbered movements on one physical track into their parent', function () {
    $tracks = app(DiscogsTracklist::class)->tracks([
        ['type_' => 'index', 'position' => '', 'title' => 'Shine On You Crazy Diamond (1-5)', 'sub_tracks' => [
            ['position' => 'A1.1', 'title' => 'Part 1'],
            ['position' => 'A1.2', 'title' => 'Part 2'],
        ]],
        ['position' => 'A2', 'title' => 'Welcome To The Machine'],
    ]);

    expect(array_column($tracks, 'title'))->toBe(['Shine On You Crazy Diamond (1-5)', 'Welcome To The Machine'])
        ->and(array_column($tracks, 'position'))->toBe(['A1', 'A2']);
});

it('collapses lettered and roman numeral movements into their parent', function (array $positions) {
    $tracks = app(DiscogsTracklist::class)->tracks([
        ['type_' => 'index', 'position' => '', 'title' => 'Discreet Music', 'sub_tracks' => [
            ['position' => $positions[0], 'title' => 'First movement'],
            ['position' => $positions[1], 'title' => 'Second movement'],
        ]],
    ]);

    expect($tracks)->toHaveCount(1)
        ->and($tracks[0]['title'])->toBe('Discreet Music')
        ->and($tracks[0]['position'])->toBe('B');
})->with([
    'letters' => [['B.a', 'B.b']],
    'roman numerals' => [['B (i)', 'B (ii)']],
]);

it('keeps separate songs that are grouped under one index', function () {
    $tracks = app(DiscogsTracklist::class)->tracks([
        ['type_' => 'heading', 'title' => 'Side A'],
        ['type_' => 'index', 'position' => '', 'title' => 'Suite', 'sub_tracks' => [
            ['position' => 'A1', 'title' => 'First Song'],
            ['position' => 'A2', 'title' => 'Second Song'],
        ]],
    ]);

    expect(array_column($tracks, 'title'))->toBe(['First Song', 'Second Song']);
});

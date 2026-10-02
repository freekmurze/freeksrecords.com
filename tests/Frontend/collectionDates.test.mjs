import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';

import { collectionAddedDate } from '../../resources/js/components/records/collectionDates.ts';

const { records } = JSON.parse(
    readFileSync(
        new URL('../../resources/data/collection.json', import.meta.url),
        'utf8',
    ),
);

await test('only the thirteen confirmed purchases show a date from the November 23 batch', () => {
    const batch = records.filter((record) =>
        record.addedAt.startsWith('2025-11-23'),
    );
    const dated = batch.filter((record) => collectionAddedDate(record));

    assert.equal(batch.length, 100);
    assert.deepEqual(
        dated
            .map((record) => `${record.artist}: ${record.title.trim()}`)
            .sort(),
        [
            'Andreas Vollenweider: White Winds',
            'Animal Collective: Feels',
            'Hiroshi Yoshimura: Soundscape 1: Surround',
            'Thom Yorke: Anima',
            'Thom Yorke: The Eraser',
            "Thom Yorke: Tomorrow's Modern Boxes",
            'Tony Joe White: Tony Joe',
            'Viet Cong: Viet Cong',
            'Wooden Shjips: V.',
            'Yellow Magic Orchestra: Yellow Magic Orchestra USA & Yellow Magic Orchestra',
            'Yo La Tengo: Danelectro',
            'Yo La Tengo: Painful',
            'Yo La Tengo: Popular Songs',
        ],
    );
    assert.ok(
        dated.every((record) => collectionAddedDate(record) === '2025-11-23'),
    );
    assert.ok(
        batch
            .filter((record) => !dated.includes(record))
            .every((record) => collectionAddedDate(record) === null),
    );
});

await test('bulk catalogue dates are hidden and later additions use the Brussels calendar day', () => {
    for (const [addedAt, expected] of [
        ['2025-11-04T05:54:51-08:00', null],
        ['2025-11-12T08:00:00-08:00', null],
        ['2025-11-23T14:59:59-08:00', null],
        ['2025-11-23T15:00:00-08:00', '2025-11-24'],
        ['2025-11-25T08:00:00-08:00', '2025-11-25'],
        ['2026-06-10T16:00:00-07:00', '2026-06-11'],
        ['invalid', null],
        ['', null],
    ]) {
        assert.equal(collectionAddedDate({ instanceId: 1, addedAt }), expected);
    }
});

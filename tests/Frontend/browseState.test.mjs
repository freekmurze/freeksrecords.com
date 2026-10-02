import assert from 'node:assert/strict';
import test from 'node:test';
import {
    browseFromHash,
    browseHash,
    restoreBrowseState,
} from '../../resources/js/components/records/browseState.ts';

await test('share links preserve categories, Unicode, punctuation and searches', () => {
    for (const [mode, group] of [
        ['genre', 'Blues'],
        ['decade', '1990s'],
        ['artist', 'Comité & Friends + #1'],
    ]) {
        const state = {
            mode,
            group,
            query: 'live & rare + 1',
            visibleCount: 25,
        };
        assert.deepEqual(browseFromHash(browseHash(state)), state);
    }
    assert.equal(
        browseHash({
            mode: 'genre',
            group: 'Blues',
            query: '',
            visibleCount: 25,
        }),
        '#genre=Blues',
    );
});

await test('latest additions, search-only links and legacy record fragments remain supported', () => {
    const recent = { mode: 'recent', group: null, query: '', visibleCount: 25 };
    assert.equal(browseHash(recent), '');
    for (const hash of ['', '#record-123', '#records', '#invalid=value'])
        assert.deepEqual(browseFromHash(hash), recent);
    assert.deepEqual(browseFromHash('#q=Pink+Floyd'), {
        ...recent,
        query: 'Pink Floyd',
    });
    assert.deepEqual(browseFromHash('#artist='), { ...recent, mode: 'artist' });
});

await test('closing a record preserves all loaded shelves even when scroll tracking removes history metadata', () => {
    const current = {
        mode: 'recent',
        group: null,
        query: '',
        visibleCount: 150,
    };
    const destination = browseFromHash('');
    assert.equal(restoreBrowseState(current, destination), current);
    assert.equal(
        restoreBrowseState(current, destination, {
            ...current,
            visibleCount: 25,
        }),
        current,
    );
    const filtered = {
        ...current,
        mode: 'genre',
        group: 'Rock',
        query: 'live',
    };
    assert.equal(
        restoreBrowseState(filtered, browseFromHash('#genre=Rock&q=live')),
        filtered,
    );
});

await test('navigating to a different collection restores only its own loaded record count', () => {
    const current = {
        mode: 'recent',
        group: null,
        query: '',
        visibleCount: 150,
    };
    const destination = browseFromHash('#artist=U2');
    assert.deepEqual(
        restoreBrowseState(current, destination, current),
        destination,
    );
    assert.deepEqual(
        restoreBrowseState(current, destination, {
            ...destination,
            visibleCount: 75,
        }),
        { ...destination, visibleCount: 75 },
    );
});

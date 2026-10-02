import type { CollectionMode } from './types';

export type BrowseState = {
    mode: CollectionMode;
    group: string | null;
    query: string;
    visibleCount: number;
};

export function browseFromHash(hash: string): BrowseState {
    const params = new URLSearchParams(hash.replace(/^#/, ''));
    const mode =
        (['decade', 'genre', 'artist'] as const).find((key) =>
            params.has(key),
        ) ?? 'recent';
    return {
        mode,
        group: mode === 'recent' ? null : params.get(mode) || null,
        query: params.get('q') ?? '',
        visibleCount: 25,
    };
}

export function browseHash(browse: BrowseState): string {
    const params = new URLSearchParams();
    if (browse.mode !== 'recent') params.set(browse.mode, browse.group ?? '');
    if (browse.query) params.set('q', browse.query);
    const fragment = params.toString();
    return fragment ? `#${fragment}` : '';
}

export function restoreBrowseState(
    current: BrowseState,
    destination: BrowseState,
    saved?: BrowseState,
): BrowseState {
    const destinationHash = browseHash(destination);
    // Inertia's scroll tracking can replace custom history fields. Keep a mounted shelf intact when closing its record.
    if (browseHash(current) === destinationHash) return current;
    if (saved && browseHash(saved) === destinationHash)
        return { ...destination, visibleCount: saved.visibleCount };
    return destination;
}

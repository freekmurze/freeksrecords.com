const confirmedPurchaseDates: Record<number, string> = {
    2082975628: '2025-11-23', // Andreas Vollenweider: White Winds
    2083005892: '2025-11-23', // Animal Collective: Feels
    2082980045: '2025-11-23', // Hiroshi Yoshimura: Soundscape 1: Surround
    2082981242: '2025-11-23', // Thom Yorke: Anima
    2082981009: '2025-11-23', // Thom Yorke: The Eraser
    2082981111: '2025-11-23', // Thom Yorke: Tomorrow's Modern Boxes
    2082977360: '2025-11-23', // Tony Joe White: Tony Joe
    2082975802: '2025-11-23', // Viet Cong: Viet Cong
    2082980180: '2025-11-23', // Wooden Shjips: V.
    2082980619: '2025-11-23', // Yellow Magic Orchestra: USA & Yellow Magic Orchestra
    2082980708: '2025-11-23', // Yo La Tengo: Danelectro
    2082980818: '2025-11-23', // Yo La Tengo: Painful
    2082980907: '2025-11-23', // Yo La Tengo: Popular Songs
};

const collectionDay = new Intl.DateTimeFormat('en-CA', {
    timeZone: 'Europe/Brussels',
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
});

export function collectionAddedDate(record: {
    instanceId: number;
    addedAt: string;
}): string | null {
    const confirmedDate = confirmedPurchaseDates[record.instanceId];
    if (confirmedDate) return confirmedDate;

    const timestamp = new Date(record.addedAt);
    if (Number.isNaN(timestamp.getTime())) return null;

    const date = collectionDay.format(timestamp);

    // Older records were catalogued in bulk; Freek confirmed the exceptions above.
    return date <= '2025-11-23' ? null : date;
}

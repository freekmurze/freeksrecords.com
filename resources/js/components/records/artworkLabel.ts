export function contrastingInk(
    red: number,
    green: number,
    blue: number,
): string {
    const linear = [red, green, blue].map((channel) => {
        const value = channel / 255;
        return value <= 0.04045
            ? value / 12.92
            : ((value + 0.055) / 1.055) ** 2.4;
    });
    const luminance =
        linear[0] * 0.2126 + linear[1] * 0.7152 + linear[2] * 0.0722;
    return luminance > 0.179 ? '#000000' : '#ffffff';
}

export function artworkLabelStyle(image: HTMLImageElement): {
    backgroundColor: string;
    color: string;
} {
    const fallback = { backgroundColor: '#97492e', color: '#ffffff' };
    try {
        const canvas = document.createElement('canvas');
        canvas.width = canvas.height = 32;
        const context = canvas.getContext('2d', { willReadFrequently: true });
        if (!context) return fallback;
        context.drawImage(image, 0, 0, 32, 32);
        const pixels = context.getImageData(0, 0, 32, 32).data;
        const buckets = new Map<
            string,
            {
                count: number;
                red: number;
                green: number;
                blue: number;
                score: number;
            }
        >();
        for (let index = 0; index < pixels.length; index += 4) {
            const [red, green, blue] = pixels.slice(index, index + 3);
            const maximum = Math.max(red, green, blue);
            const minimum = Math.min(red, green, blue);
            if (maximum < 35 || minimum > 230) continue;
            const key = [red, green, blue]
                .map((value) => Math.floor(value / 32))
                .join(',');
            const bucket = buckets.get(key) ?? {
                count: 0,
                red: 0,
                green: 0,
                blue: 0,
                score: 0,
            };
            bucket.count++;
            bucket.red += red;
            bucket.green += green;
            bucket.blue += blue;
            bucket.score += 1 + (maximum - minimum) / 80;
            buckets.set(key, bucket);
        }
        const colors = [...buckets.values()];
        const saturated = colors.filter(
            (color) =>
                color.count >= 5 &&
                (Math.max(color.red, color.green, color.blue) -
                    Math.min(color.red, color.green, color.blue)) /
                    color.count >=
                    45,
        );
        const dominant = (saturated.length ? saturated : colors).sort(
            (first, second) => second.score - first.score,
        )[0];
        if (!dominant) return fallback;
        const red = Math.round(dominant.red / dominant.count);
        const green = Math.round(dominant.green / dominant.count);
        const blue = Math.round(dominant.blue / dominant.count);
        return {
            backgroundColor: `rgb(${red}, ${green}, ${blue})`,
            color: contrastingInk(red, green, blue),
        };
    } catch {
        return fallback;
    }
}

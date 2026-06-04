export const formatNumber = (value: number): string =>
    new Intl.NumberFormat('en', { notation: 'compact', maximumFractionDigits: 1 }).format(value).toUpperCase();

export const formatDate = (value: string | null): string => (value ? new Date(value).toLocaleDateString() : 'Unknown');

export const formatRelativeDate = (value: string | null): string => {
    if (!value) return 'unknown';

    const timestamp = new Date(value).getTime();
    if (Number.isNaN(timestamp)) return 'unknown';

    const seconds = Math.max(0, Math.floor((Date.now() - timestamp) / 1000));
    const units: Array<[Intl.RelativeTimeFormatUnit, number]> = [
        ['year', 31536000],
        ['month', 2592000],
        ['week', 604800],
        ['day', 86400],
        ['hour', 3600],
        ['minute', 60],
    ];
    const formatter = new Intl.RelativeTimeFormat(undefined, { numeric: 'always' });

    for (const [unit, size] of units) {
        if (seconds >= size) return formatter.format(-Math.floor(seconds / size), unit);
    }

    return formatter.format(0, 'second');
};

export const installedKey = (value: string): string =>
    value
        .toLowerCase()
        .replace(/\.jar$/, '')
        .replace(/[^a-z0-9]+/g, '');

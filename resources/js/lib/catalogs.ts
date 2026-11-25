const numberFormatter = new Intl.NumberFormat('es-MX', {
    maximumFractionDigits: 2,
});

/**
 * "1 requisición" or "3 requisiciones".
 */
export function formatRequisitionCount(count: number): string {
    return count === 1 ? '1 requisición' : `${count} requisiciones`;
}

/**
 * Format a decimal string from the server ("12.50") without trailing zeros.
 */
export function formatNumber(value: string): string {
    return numberFormatter.format(Number(value));
}

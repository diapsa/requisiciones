const currencyFormatter = new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN',
});

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
 * Format a decimal string from the server ("1250.50") as Mexican pesos.
 */
export function formatCurrency(amount: string): string {
    return currencyFormatter.format(Number(amount));
}

/**
 * Format a decimal string from the server ("12.50") without trailing zeros.
 */
export function formatNumber(value: string): string {
    return numberFormatter.format(Number(value));
}

/**
 * Format a "YYYY-MM-DD" date as "DD/MM/YYYY" without going through Date, so the time zone cannot shift the day.
 */
export function formatDate(date: string): string {
    const [year, month, day] = date.split('-');

    return `${day}/${month}/${year}`;
}

/**
 * Today's local date as "YYYY-MM-DD".
 */
export function today(): string {
    const now = new Date();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');

    return `${now.getFullYear()}-${month}-${day}`;
}

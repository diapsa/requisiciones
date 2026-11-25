/**
 * "1 requisición" or "3 requisiciones".
 */
export function formatRequisitionCount(count: number): string {
    return count === 1 ? '1 requisición' : `${count} requisiciones`;
}

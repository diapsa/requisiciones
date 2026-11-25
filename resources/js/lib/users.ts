import {
    BadgeCheck,
    CircleHelp,
    ShieldCheck,
    UserRound,
    UsersRound,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

/** Filter value that matches every user. */
export const ALL = 'all';

/** Coordinator value for "no coordinator", because Radix does not allow an empty option value. */
export const WITHOUT_COORDINATOR = 'none';

export const ACTIVE_ACCOUNTS = 'active';
export const PENDING_ACCOUNTS = 'pending';

export const REQUESTER = 'requester';
export const COORDINATOR = 'coordinator';

export type RoleAppearance = {
    icon: LucideIcon;
    step: string;
    tone: string;
    leadsToNextStep: boolean;
    isOutsideChain: boolean;
};

/**
 * Icon, color and place in the authorization chain of each role.
 */
const roleAppearances: Record<string, RoleAppearance> = {
    requester: {
        icon: UserRound,
        step: 'Paso 1 · Captura',
        tone: 'bg-sky-500/10 text-sky-700 dark:bg-sky-400/15 dark:text-sky-300',
        leadsToNextStep: true,
        isOutsideChain: false,
    },
    coordinator: {
        icon: UsersRound,
        step: 'Paso 2 · Primera autorización',
        tone: 'bg-violet-500/10 text-violet-700 dark:bg-violet-400/15 dark:text-violet-300',
        leadsToNextStep: true,
        isOutsideChain: false,
    },
    manager: {
        icon: BadgeCheck,
        step: 'Paso 3 · Autorización final',
        tone: 'bg-emerald-500/10 text-emerald-700 dark:bg-emerald-400/15 dark:text-emerald-300',
        leadsToNextStep: false,
        isOutsideChain: false,
    },
    admin: {
        icon: ShieldCheck,
        step: 'Fuera de la cadena',
        tone: 'bg-rose-500/10 text-rose-700 dark:bg-rose-400/15 dark:text-rose-300',
        leadsToNextStep: false,
        isOutsideChain: true,
    },
};

const unknownRoleAppearance: RoleAppearance = {
    icon: CircleHelp,
    step: '',
    tone: 'bg-muted text-muted-foreground',
    leadsToNextStep: false,
    isOutsideChain: true,
};

export function roleAppearance(role: string | null): RoleAppearance {
    return (
        (role === null ? undefined : roleAppearances[role]) ??
        unknownRoleAppearance
    );
}

/**
 * "1 persona" or "3 personas".
 */
export function countLabel(
    count: number,
    singular: string,
    plural: string,
): string {
    return `${count} ${count === 1 ? singular : plural}`;
}

/**
 * Lowercase without accents, so "jose" finds "José".
 */
export function normalizeForSearch(text: string): string {
    return text
        .normalize('NFD')
        .replace(/\p{Diacritic}/gu, '')
        .toLocaleLowerCase('es-MX');
}

const shortDateFormatter = new Intl.DateTimeFormat('es-MX', {
    dateStyle: 'medium',
});

/**
 * Format an ISO 8601 timestamp from the server as "25 nov 2026".
 */
export function formatShortDate(isoDate: string): string {
    return shortDateFormatter.format(new Date(isoDate));
}

import type { UserOption } from './catalogs';

export type RoleOption = {
    value: string;
    label: string;
    description: string;
};

/**
 * Invitation of a user who has not chosen a password yet.
 */
export type UserInvitationStatus = {
    sent_at: string | null;
    is_expired: boolean;
};

export type UserListItem = {
    id: number;
    name: string;
    email: string;
    role: string | null;
    coordinator: UserOption | null;
    requisitions_count: number;
    approvals_count: number;
    coordinated_users_count: number;
    is_active: boolean;
    invitation: UserInvitationStatus | null;
    has_history: boolean;
};

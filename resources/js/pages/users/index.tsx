import { Head, usePage } from '@inertiajs/react';
import { TriangleAlert, UserPlus } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { AuthorizationChain } from '@/components/users/authorization-chain';
import { emptyUserFilters, UserFilters } from '@/components/users/user-filters';
import type { UserFilterValues } from '@/components/users/user-filters';
import { UserFormDialog } from '@/components/users/user-form-dialog';
import { UserTable } from '@/components/users/user-table';
import {
    ACTIVE_ACCOUNTS,
    ALL,
    countLabel,
    normalizeForSearch,
    PENDING_ACCOUNTS,
    REQUESTER,
    WITHOUT_COORDINATOR,
} from '@/lib/users';
import { index } from '@/routes/users';
import type { RoleOption, UserListItem, UserOption } from '@/types';

/**
 * Whether the user matches every filter. The search ignores case and accents.
 */
function matchesFilters(
    user: UserListItem,
    filters: UserFilterValues,
): boolean {
    if (filters.role !== ALL && user.role !== filters.role) {
        return false;
    }

    if (filters.coordinator === WITHOUT_COORDINATOR) {
        if (user.coordinator !== null) {
            return false;
        }
    } else if (
        filters.coordinator !== ALL &&
        String(user.coordinator?.id) !== filters.coordinator
    ) {
        return false;
    }

    if (
        (filters.account === ACTIVE_ACCOUNTS && !user.is_active) ||
        (filters.account === PENDING_ACCOUNTS && user.is_active)
    ) {
        return false;
    }

    const searchTerm = normalizeForSearch(filters.search.trim());

    return (
        searchTerm === '' ||
        normalizeForSearch(`${user.name} ${user.email}`).includes(searchTerm)
    );
}

export default function UsersIndex({
    users,
    roles,
    coordinators,
    invitationExpiresInHours,
}: {
    users: UserListItem[];
    roles: RoleOption[];
    coordinators: UserOption[];
    invitationExpiresInHours: number;
}) {
    const { auth } = usePage().props;
    const [filters, setFilters] = useState(emptyUserFilters);
    const [isFormOpen, setIsFormOpen] = useState(false);
    const [editingUserId, setEditingUserId] = useState<number | null>(null);

    const editingUser = users.find((user) => user.id === editingUserId);
    const visibleUsers = users.filter((user) => matchesFilters(user, filters));
    const pendingAccounts = users.filter((user) => !user.is_active).length;
    const requestersWithoutCoordinator = users.filter(
        (user) => user.role === REQUESTER && user.coordinator === null,
    ).length;

    function openCreateForm() {
        setEditingUserId(null);
        setIsFormOpen(true);
    }

    function openEditForm(userId: number) {
        setEditingUserId(userId);
        setIsFormOpen(true);
    }

    return (
        <>
            <Head title="Usuarios" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                <header className="flex flex-wrap items-center justify-between gap-4">
                    <div className="border-l-4 border-brand-orange pl-3">
                        <h2 className="text-xl font-semibold tracking-tight">
                            Usuarios
                        </h2>
                        <p className="text-sm text-muted-foreground">
                            Da de alta a cada empleado, asígnale su rol y quién
                            autoriza sus requisiciones.
                        </p>
                    </div>
                    <Button type="button" onClick={openCreateForm}>
                        <UserPlus />
                        Nuevo usuario
                    </Button>
                </header>

                <AuthorizationChain
                    roles={roles}
                    users={users}
                    selectedRole={filters.role}
                    onSelectRole={(role) => setFilters({ ...filters, role })}
                />

                {requestersWithoutCoordinator > 0 && (
                    <MissingCoordinatorNotice
                        count={requestersWithoutCoordinator}
                        onShow={() =>
                            setFilters({
                                ...emptyUserFilters,
                                role: REQUESTER,
                                coordinator: WITHOUT_COORDINATOR,
                            })
                        }
                    />
                )}

                <section
                    aria-labelledby="users-list-title"
                    className="min-w-0 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <div className="grid gap-4 border-b border-sidebar-border/70 p-4 dark:border-sidebar-border">
                        <div className="flex flex-wrap items-baseline justify-between gap-2">
                            <h3 id="users-list-title" className="font-semibold">
                                Lista de usuarios
                            </h3>
                            <p
                                aria-live="polite"
                                className="text-sm text-muted-foreground"
                            >
                                Mostrando {visibleUsers.length} de{' '}
                                {countLabel(
                                    users.length,
                                    'usuario',
                                    'usuarios',
                                )}
                                {pendingAccounts > 0 &&
                                    ` · ${countLabel(pendingAccounts, 'cuenta pendiente', 'cuentas pendientes')} de activar`}
                            </p>
                        </div>

                        <UserFilters
                            roles={roles}
                            coordinators={coordinators}
                            filters={filters}
                            onChange={setFilters}
                        />
                    </div>

                    <UserTable
                        users={visibleUsers}
                        roles={roles}
                        currentUserId={auth.user.id}
                        onEdit={openEditForm}
                        onClearFilters={() => setFilters(emptyUserFilters)}
                    />
                </section>
            </div>

            <UserFormDialog
                open={isFormOpen}
                onOpenChange={setIsFormOpen}
                user={editingUser}
                roles={roles}
                coordinators={coordinators}
                invitationExpiresInHours={invitationExpiresInHours}
            />
        </>
    );
}

function MissingCoordinatorNotice({
    count,
    onShow,
}: {
    count: number;
    onShow: () => void;
}) {
    return (
        <div className="flex flex-wrap items-center gap-3 rounded-xl border border-amber-500/40 bg-amber-500/10 px-4 py-3 text-sm text-amber-900 dark:text-amber-100">
            <TriangleAlert className="size-4 shrink-0 text-amber-600 dark:text-amber-400" />
            <p className="min-w-0 flex-1">
                <span className="font-medium">
                    {count === 1
                        ? 'Un solicitante no tiene coordinador.'
                        : `${count} solicitantes no tienen coordinador.`}
                </span>{' '}
                Sus requisiciones no tendrán quién dé la primera autorización.
            </p>
            <Button type="button" variant="outline" size="sm" onClick={onShow}>
                Ver cuáles
            </Button>
        </div>
    );
}

UsersIndex.layout = {
    breadcrumbs: [
        {
            title: 'Usuarios',
            href: index(),
        },
    ],
};

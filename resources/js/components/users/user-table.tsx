import { Form } from '@inertiajs/react';
import { MailPlus, Pencil, TriangleAlert } from 'lucide-react';
import { destroy } from '@/actions/App/Http/Controllers/UserController';
import { store as resendInvitation } from '@/actions/App/Http/Controllers/UserInvitationController';
import { DeleteCatalogItemDialog } from '@/components/delete-catalog-item-dialog';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { useInitials } from '@/hooks/use-initials';
import { formatRequisitionCount } from '@/lib/catalogs';
import {
    COORDINATOR,
    countLabel,
    formatShortDate,
    REQUESTER,
    roleAppearance,
} from '@/lib/users';
import { cn } from '@/lib/utils';
import type { RoleOption, UserListItem } from '@/types';

export function UserTable({
    users,
    roles,
    currentUserId,
    onEdit,
    onClearFilters,
}: {
    users: UserListItem[];
    roles: RoleOption[];
    currentUserId: number;
    onEdit: (userId: number) => void;
    onClearFilters: () => void;
}) {
    return (
        <div className="overflow-x-auto">
            <table aria-label="Usuarios" className="w-full text-sm">
                <thead className="bg-muted/50 text-left text-xs tracking-wide text-muted-foreground uppercase">
                    <tr>
                        <th scope="col" className="px-4 py-3 font-medium">
                            Usuario
                        </th>
                        <th scope="col" className="px-4 py-3 font-medium">
                            Rol
                        </th>
                        <th scope="col" className="px-4 py-3 font-medium">
                            Cuenta
                        </th>
                        <th scope="col" className="px-4 py-3 font-medium">
                            Coordinador y equipo
                        </th>
                        <th scope="col" className="px-4 py-3 font-medium">
                            Actividad
                        </th>
                        <th scope="col" className="w-px px-4 py-3">
                            <span className="sr-only">Acciones</span>
                        </th>
                    </tr>
                </thead>
                <tbody className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                    {users.length === 0 ? (
                        <tr>
                            <td
                                colSpan={6}
                                className="px-4 py-10 text-center text-muted-foreground"
                            >
                                Ningún usuario coincide con los filtros.{' '}
                                <Button
                                    type="button"
                                    variant="link"
                                    onClick={onClearFilters}
                                    className="h-auto p-0"
                                >
                                    Limpiar filtros
                                </Button>
                            </td>
                        </tr>
                    ) : (
                        users.map((user) => (
                            <UserRow
                                key={user.id}
                                user={user}
                                roles={roles}
                                isCurrentUser={user.id === currentUserId}
                                onEdit={onEdit}
                            />
                        ))
                    )}
                </tbody>
            </table>
        </div>
    );
}

function UserRow({
    user,
    roles,
    isCurrentUser,
    onEdit,
}: {
    user: UserListItem;
    roles: RoleOption[];
    isCurrentUser: boolean;
    onEdit: (userId: number) => void;
}) {
    const getInitials = useInitials();
    const appearance = roleAppearance(user.role);
    const RoleIcon = appearance.icon;
    const roleLabel =
        roles.find((role) => role.value === user.role)?.label ?? 'Sin rol';

    function deleteBlockedReason(): string | undefined {
        if (isCurrentUser) {
            return 'No puedes eliminar tu propia cuenta.';
        }

        if (user.has_history) {
            return 'No se puede eliminar: tiene historial en el sistema.';
        }

        return undefined;
    }

    return (
        <tr>
            <td className="px-4 py-3">
                <div className="flex items-center gap-3">
                    <Avatar className="size-9">
                        <AvatarFallback
                            className={cn(
                                'text-xs font-semibold',
                                appearance.tone,
                            )}
                        >
                            {getInitials(user.name)}
                        </AvatarFallback>
                    </Avatar>
                    <div className="min-w-0">
                        <div className="font-medium wrap-anywhere">
                            {user.name}
                            {isCurrentUser && (
                                <span className="font-normal text-muted-foreground">
                                    {' '}
                                    (tú)
                                </span>
                            )}
                        </div>
                        <div className="text-xs wrap-anywhere text-muted-foreground">
                            {user.email}
                        </div>
                    </div>
                </div>
            </td>
            <td className="px-4 py-3">
                <Badge
                    variant="outline"
                    className={cn('border-transparent', appearance.tone)}
                >
                    <RoleIcon />
                    {roleLabel}
                </Badge>
            </td>
            <td className="px-4 py-3">
                <AccountStatus user={user} />
            </td>
            <td className="px-4 py-3">
                <CoordinatorAndTeam user={user} />
            </td>
            <td className="px-4 py-3">
                <div className="grid gap-0.5">
                    <span
                        className={cn(
                            user.requisitions_count === 0 &&
                                'text-muted-foreground',
                        )}
                    >
                        {formatRequisitionCount(user.requisitions_count)}
                    </span>
                    {user.approvals_count > 0 && (
                        <span className="text-xs text-muted-foreground">
                            {countLabel(
                                user.approvals_count,
                                'autorización',
                                'autorizaciones',
                            )}
                        </span>
                    )}
                </div>
            </td>
            <td className="px-4 py-2">
                <div className="flex justify-end gap-1">
                    {!user.is_active && <ResendInvitationButton user={user} />}
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        aria-haspopup="dialog"
                        onClick={() => onEdit(user.id)}
                    >
                        <Pencil />
                        <span className="sr-only">Editar a {user.name}</span>
                    </Button>
                    <DeleteCatalogItemDialog
                        itemName={user.name}
                        description="El usuario se borrará y ya no podrá iniciar sesión. Esta acción no se puede deshacer."
                        form={destroy.form(user)}
                        blockedReason={deleteBlockedReason()}
                    />
                </div>
            </td>
        </tr>
    );
}

/**
 * Active once the user chose a password from the invitation; until then, when it was sent and whether it expired.
 */
function AccountStatus({ user }: { user: UserListItem }) {
    if (user.is_active || user.invitation === null) {
        return (
            <span className="inline-flex items-center gap-2 whitespace-nowrap">
                <span
                    aria-hidden="true"
                    className="size-2 rounded-full bg-emerald-500"
                />
                Activa
            </span>
        );
    }

    const { sent_at: sentAt, is_expired: isExpired } = user.invitation;

    return (
        <div className="grid gap-0.5">
            <span className="inline-flex items-center gap-2 whitespace-nowrap">
                <span
                    aria-hidden="true"
                    className={cn(
                        'size-2 rounded-full',
                        isExpired ? 'bg-red-500' : 'bg-amber-500',
                    )}
                />
                {sentAt === null
                    ? 'Sin invitación'
                    : isExpired
                      ? 'Invitación vencida'
                      : 'Pendiente'}
            </span>
            <span className="text-xs whitespace-nowrap text-muted-foreground">
                {sentAt === null
                    ? 'Reenvíala para que pueda entrar'
                    : isExpired
                      ? `Se envió el ${formatShortDate(sentAt)}`
                      : `Invitación enviada el ${formatShortDate(sentAt)}`}
            </span>
        </div>
    );
}

function ResendInvitationButton({ user }: { user: UserListItem }) {
    return (
        <Form
            {...resendInvitation.form(user)}
            options={{ preserveScroll: true }}
        >
            {({ processing }) => (
                <Tooltip>
                    <TooltipTrigger asChild>
                        <Button
                            type="submit"
                            variant="ghost"
                            size="icon"
                            disabled={processing}
                        >
                            <MailPlus />
                            <span className="sr-only">
                                Reenviar invitación a {user.name}
                            </span>
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Reenviar invitación</TooltipContent>
                </Tooltip>
            )}
        </Form>
    );
}

/**
 * Who authorizes the user's requisitions and, for coordinators, how many people they authorize.
 */
function CoordinatorAndTeam({ user }: { user: UserListItem }) {
    const isCoordinator = user.role === COORDINATOR;

    return (
        <div className="grid gap-0.5">
            {user.coordinator ? (
                <span className="wrap-anywhere">
                    <span className="text-muted-foreground">Lo autoriza </span>
                    {user.coordinator.name}
                </span>
            ) : user.role === REQUESTER ? (
                <span className="inline-flex items-center gap-1.5 font-medium text-amber-700 dark:text-amber-400">
                    <TriangleAlert className="size-3.5" />
                    Sin coordinador
                </span>
            ) : (
                <span className="text-muted-foreground">—</span>
            )}

            {user.coordinated_users_count > 0 && (
                <span
                    className={cn(
                        'text-xs',
                        isCoordinator
                            ? 'text-muted-foreground'
                            : 'text-amber-700 dark:text-amber-400',
                    )}
                >
                    Coordina a{' '}
                    {countLabel(
                        user.coordinated_users_count,
                        'persona',
                        'personas',
                    )}
                    {!isCoordinator && ', pero ya no es coordinador'}
                </span>
            )}

            {isCoordinator && user.coordinated_users_count === 0 && (
                <span className="text-xs text-muted-foreground">
                    Todavía no tiene equipo
                </span>
            )}
        </div>
    );
}

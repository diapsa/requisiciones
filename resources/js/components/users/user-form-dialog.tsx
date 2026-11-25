import { Form } from '@inertiajs/react';
import { Check, Mail, TriangleAlert, UserPen, UserPlus } from 'lucide-react';
import { useState } from 'react';
import { store, update } from '@/actions/App/Http/Controllers/UserController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { REQUESTER, roleAppearance, WITHOUT_COORDINATOR } from '@/lib/users';
import { cn } from '@/lib/utils';
import type { RoleOption, UserListItem, UserOption } from '@/types';

/**
 * Create a user, who then receives the invitation by email, or edit an existing one.
 */
export function UserFormDialog({
    open,
    onOpenChange,
    user,
    roles,
    coordinators,
    invitationExpiresInHours,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    user?: UserListItem;
    roles: RoleOption[];
    coordinators: UserOption[];
    invitationExpiresInHours: number;
}) {
    const HeaderIcon = user ? UserPen : UserPlus;

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="gap-0 overflow-hidden p-0 sm:max-w-2xl">
                <DialogHeader className="flex-row items-center gap-4 border-b px-6 py-5 text-left">
                    <span className="flex size-10 shrink-0 items-center justify-center rounded-full bg-brand-orange/15 text-brand-navy dark:text-brand-orange">
                        <HeaderIcon className="size-5" />
                    </span>
                    <div className="grid gap-1">
                        <DialogTitle>
                            {user ? 'Editar usuario' : 'Nuevo usuario'}
                        </DialogTitle>
                        <DialogDescription>
                            {user
                                ? `Cambia los datos, el rol o el coordinador de ${user.name}.`
                                : 'Elige su rol y quién autoriza sus requisiciones.'}
                        </DialogDescription>
                    </div>
                </DialogHeader>

                <UserForm
                    key={user?.id ?? 'new'}
                    user={user}
                    roles={roles}
                    coordinators={coordinators}
                    invitationExpiresInHours={invitationExpiresInHours}
                    onCancel={() => onOpenChange(false)}
                    onSaved={() => onOpenChange(false)}
                />
            </DialogContent>
        </Dialog>
    );
}

function UserForm({
    user,
    roles,
    coordinators,
    invitationExpiresInHours,
    onCancel,
    onSaved,
}: {
    user?: UserListItem;
    roles: RoleOption[];
    coordinators: UserOption[];
    invitationExpiresInHours: number;
    onCancel: () => void;
    onSaved: () => void;
}) {
    const isEditing = user !== undefined;
    const initialRole = user?.role ?? REQUESTER;
    const [selectedRole, setSelectedRole] = useState(initialRole);
    const [selectedCoordinator, setSelectedCoordinator] = useState(
        user?.coordinator ? String(user.coordinator.id) : WITHOUT_COORDINATOR,
    );
    const isRequesterWithoutCoordinator =
        selectedRole === REQUESTER &&
        selectedCoordinator === WITHOUT_COORDINATOR;
    const coordinatorOptions = coordinators.filter(
        (coordinator) => coordinator.id !== user?.id,
    );

    const footerNote = !isEditing
        ? `Le enviaremos la invitación a su correo. El enlace vence en ${invitationExpiresInHours} horas.`
        : !user.is_active
          ? 'Su cuenta sigue pendiente. Si cambias el correo, le reenviaremos la invitación.'
          : null;

    return (
        <Form
            {...(isEditing ? update.form(user) : store.form())}
            options={{ preserveScroll: true }}
            onSuccess={onSaved}
        >
            {({ processing, errors }) => (
                <>
                    <div className="grid max-h-[calc(100dvh-13rem)] gap-6 overflow-y-auto px-6 py-5">
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="grid content-start gap-2">
                                <Label htmlFor="user-name">Nombre</Label>
                                <Input
                                    id="user-name"
                                    name="name"
                                    defaultValue={user?.name}
                                    maxLength={255}
                                    required
                                    autoComplete="off"
                                    placeholder="Nombre y apellidos"
                                    disabled={processing}
                                    aria-invalid={Boolean(errors.name)}
                                    aria-describedby={
                                        errors.name
                                            ? 'user-name-error'
                                            : undefined
                                    }
                                />
                                <InputError
                                    id="user-name-error"
                                    message={errors.name}
                                />
                            </div>

                            <div className="grid content-start gap-2">
                                <Label htmlFor="user-email">Correo</Label>
                                <Input
                                    id="user-email"
                                    name="email"
                                    type="email"
                                    defaultValue={user?.email}
                                    maxLength={255}
                                    required
                                    autoComplete="off"
                                    placeholder="nombre@empresa.com"
                                    disabled={processing}
                                    aria-invalid={Boolean(errors.email)}
                                    aria-describedby={
                                        errors.email
                                            ? 'user-email-error'
                                            : undefined
                                    }
                                />
                                <InputError
                                    id="user-email-error"
                                    message={errors.email}
                                />
                            </div>
                        </div>

                        <fieldset
                            className="grid gap-2"
                            aria-describedby={
                                errors.role ? 'user-role-error' : undefined
                            }
                        >
                            <legend className="mb-2 text-sm font-medium">
                                Rol
                            </legend>
                            <div className="grid gap-2 sm:grid-cols-2">
                                {roles.map((role) => {
                                    const appearance = roleAppearance(
                                        role.value,
                                    );
                                    const RoleIcon = appearance.icon;

                                    return (
                                        <label
                                            key={role.value}
                                            className="group flex cursor-pointer items-start gap-3 rounded-lg border p-3 transition-colors hover:bg-muted/50 has-checked:border-foreground/40 has-checked:bg-muted/60 has-focus-visible:ring-[3px] has-focus-visible:ring-ring/50 has-disabled:cursor-not-allowed has-disabled:opacity-60"
                                        >
                                            <input
                                                type="radio"
                                                name="role"
                                                value={role.value}
                                                defaultChecked={
                                                    role.value === initialRole
                                                }
                                                onChange={() =>
                                                    setSelectedRole(role.value)
                                                }
                                                required
                                                disabled={processing}
                                                className="sr-only"
                                            />
                                            <span
                                                className={cn(
                                                    'flex size-8 shrink-0 items-center justify-center rounded-full',
                                                    appearance.tone,
                                                )}
                                            >
                                                <RoleIcon className="size-4" />
                                            </span>
                                            <span className="grid min-w-0 flex-1 gap-0.5">
                                                <span className="text-sm font-medium">
                                                    {role.label}
                                                </span>
                                                <span className="text-xs text-muted-foreground">
                                                    {role.description}
                                                </span>
                                            </span>
                                            <Check className="size-4 shrink-0 opacity-0 transition-opacity group-has-checked:opacity-100" />
                                        </label>
                                    );
                                })}
                            </div>
                            <InputError
                                id="user-role-error"
                                message={errors.role}
                            />
                        </fieldset>

                        <div className="grid gap-2">
                            <Label htmlFor="user-coordinator">
                                Coordinador{' '}
                                <span className="font-normal text-muted-foreground">
                                    (opcional)
                                </span>
                            </Label>
                            <Select
                                name="coordinator_id"
                                defaultValue={selectedCoordinator}
                                onValueChange={setSelectedCoordinator}
                                disabled={processing}
                            >
                                <SelectTrigger
                                    id="user-coordinator"
                                    className="w-full"
                                    aria-invalid={Boolean(
                                        errors.coordinator_id,
                                    )}
                                    aria-describedby={
                                        errors.coordinator_id
                                            ? 'user-coordinator-error'
                                            : 'user-coordinator-hint'
                                    }
                                >
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value={WITHOUT_COORDINATOR}>
                                        Sin coordinador
                                    </SelectItem>
                                    {coordinatorOptions.map((coordinator) => (
                                        <SelectItem
                                            key={coordinator.id}
                                            value={String(coordinator.id)}
                                        >
                                            {coordinator.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <p
                                id="user-coordinator-hint"
                                className={cn(
                                    'flex items-start gap-1.5 text-xs',
                                    isRequesterWithoutCoordinator
                                        ? 'text-amber-700 dark:text-amber-400'
                                        : 'text-muted-foreground',
                                )}
                            >
                                {isRequesterWithoutCoordinator && (
                                    <TriangleAlert className="mt-px size-3.5 shrink-0" />
                                )}
                                {isRequesterWithoutCoordinator
                                    ? 'Sin coordinador, nadie podrá dar la primera autorización a sus requisiciones.'
                                    : 'Da la primera autorización a sus requisiciones.'}
                            </p>
                            <InputError
                                id="user-coordinator-error"
                                message={errors.coordinator_id}
                            />
                        </div>
                    </div>

                    <div className="flex flex-col-reverse gap-3 border-t bg-muted/40 px-6 py-4 sm:flex-row sm:items-center">
                        {footerNote && (
                            <p className="flex items-start gap-2 text-xs text-muted-foreground">
                                <Mail className="mt-px size-4 shrink-0" />
                                {footerNote}
                            </p>
                        )}
                        <div className="flex shrink-0 justify-end gap-2 sm:ml-auto">
                            <Button
                                type="button"
                                variant="ghost"
                                onClick={onCancel}
                                disabled={processing}
                            >
                                Cancelar
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {isEditing
                                    ? 'Guardar cambios'
                                    : 'Enviar invitación'}
                            </Button>
                        </div>
                    </div>
                </>
            )}
        </Form>
    );
}

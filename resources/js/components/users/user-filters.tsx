import { FilterX, Search } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    ACTIVE_ACCOUNTS,
    ALL,
    PENDING_ACCOUNTS,
    WITHOUT_COORDINATOR,
} from '@/lib/users';
import type { RoleOption, UserOption } from '@/types';

export type UserFilterValues = {
    search: string;
    role: string;
    coordinator: string;
    account: string;
};

export const emptyUserFilters: UserFilterValues = {
    search: '',
    role: ALL,
    coordinator: ALL,
    account: ALL,
};

/**
 * Search by name or email, and filter by role, coordinator and account status.
 */
export function UserFilters({
    roles,
    coordinators,
    filters,
    onChange,
}: {
    roles: RoleOption[];
    coordinators: UserOption[];
    filters: UserFilterValues;
    onChange: (filters: UserFilterValues) => void;
}) {
    const hasFilters =
        filters.search !== '' ||
        filters.role !== ALL ||
        filters.coordinator !== ALL ||
        filters.account !== ALL;

    return (
        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-[minmax(0,2fr)_repeat(3,minmax(0,1fr))_auto] lg:items-end">
            <div className="grid gap-1.5 sm:col-span-2 lg:col-span-1">
                <Label
                    htmlFor="users-search"
                    className="text-xs text-muted-foreground"
                >
                    Buscar
                </Label>
                <div className="relative">
                    <Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        id="users-search"
                        type="search"
                        value={filters.search}
                        onChange={(event) =>
                            onChange({ ...filters, search: event.target.value })
                        }
                        placeholder="Nombre o correo"
                        autoComplete="off"
                        className="pl-9"
                    />
                </div>
            </div>

            <div className="grid gap-1.5">
                <Label
                    htmlFor="users-role-filter"
                    className="text-xs text-muted-foreground"
                >
                    Rol
                </Label>
                <Select
                    value={filters.role}
                    onValueChange={(role) => onChange({ ...filters, role })}
                >
                    <SelectTrigger id="users-role-filter" className="w-full">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value={ALL}>Todos los roles</SelectItem>
                        {roles.map((role) => (
                            <SelectItem key={role.value} value={role.value}>
                                {role.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>

            <div className="grid gap-1.5">
                <Label
                    htmlFor="users-coordinator-filter"
                    className="text-xs text-muted-foreground"
                >
                    Coordinador
                </Label>
                <Select
                    value={filters.coordinator}
                    onValueChange={(coordinator) =>
                        onChange({ ...filters, coordinator })
                    }
                >
                    <SelectTrigger
                        id="users-coordinator-filter"
                        className="w-full"
                    >
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value={ALL}>Todos</SelectItem>
                        <SelectItem value={WITHOUT_COORDINATOR}>
                            Sin coordinador
                        </SelectItem>
                        {coordinators.map((coordinator) => (
                            <SelectItem
                                key={coordinator.id}
                                value={String(coordinator.id)}
                            >
                                Equipo de {coordinator.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>

            <div className="grid gap-1.5">
                <Label
                    htmlFor="users-account-filter"
                    className="text-xs text-muted-foreground"
                >
                    Cuenta
                </Label>
                <Select
                    value={filters.account}
                    onValueChange={(account) =>
                        onChange({ ...filters, account })
                    }
                >
                    <SelectTrigger id="users-account-filter" className="w-full">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value={ALL}>Todas</SelectItem>
                        <SelectItem value={ACTIVE_ACCOUNTS}>Activas</SelectItem>
                        <SelectItem value={PENDING_ACCOUNTS}>
                            Pendientes de activar
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <Button
                type="button"
                variant="ghost"
                onClick={() => onChange(emptyUserFilters)}
                disabled={!hasFilters}
                className="justify-self-start"
            >
                <FilterX />
                Limpiar
            </Button>
        </div>
    );
}

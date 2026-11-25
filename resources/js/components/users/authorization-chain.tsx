import { ChevronRight, Info, Workflow } from 'lucide-react';
import { ALL, roleAppearance } from '@/lib/users';
import { cn } from '@/lib/utils';
import type { RoleOption, UserListItem } from '@/types';

/**
 * One card per role in the order a requisition goes through them, with how many users have it.
 * Selecting a card filters the list by that role.
 */
export function AuthorizationChain({
    roles,
    users,
    selectedRole,
    onSelectRole,
}: {
    roles: RoleOption[];
    users: UserListItem[];
    selectedRole: string;
    onSelectRole: (role: string) => void;
}) {
    return (
        <section
            aria-labelledby="authorization-chain-title"
            className="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
        >
            <div className="mb-4 flex items-start gap-3">
                <span className="flex size-8 shrink-0 items-center justify-center rounded-lg bg-brand-orange/15 text-brand-navy dark:text-brand-orange">
                    <Workflow className="size-4" />
                </span>
                <div>
                    <h3
                        id="authorization-chain-title"
                        className="font-semibold"
                    >
                        Cadena de autorización
                    </h3>
                    <p className="text-sm text-muted-foreground">
                        El rol decide qué hace cada persona con una requisición.
                        Elige uno para filtrar la lista.
                    </p>
                </div>
            </div>

            <ol className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                {roles.map((role) => {
                    const appearance = roleAppearance(role.value);
                    const RoleIcon = appearance.icon;
                    const count = users.filter(
                        (user) => user.role === role.value,
                    ).length;
                    const isSelected = selectedRole === role.value;

                    return (
                        <li key={role.value} className="relative">
                            <button
                                type="button"
                                aria-pressed={isSelected}
                                onClick={() =>
                                    onSelectRole(isSelected ? ALL : role.value)
                                }
                                className={cn(
                                    'flex h-full w-full flex-col gap-3 rounded-lg border bg-card p-4 text-left transition-colors outline-none hover:bg-muted/50 focus-visible:ring-[3px] focus-visible:ring-ring/50',
                                    appearance.isOutsideChain &&
                                        'border-dashed',
                                    isSelected &&
                                        'border-foreground/40 bg-muted/60',
                                )}
                            >
                                <span className="flex items-center justify-between gap-3">
                                    <span
                                        className={cn(
                                            'flex size-9 items-center justify-center rounded-full',
                                            appearance.tone,
                                        )}
                                    >
                                        <RoleIcon className="size-4" />
                                    </span>
                                    <span className="text-2xl font-semibold tabular-nums">
                                        {count}
                                        <span className="sr-only">
                                            {count === 1
                                                ? ' usuario'
                                                : ' usuarios'}
                                        </span>
                                    </span>
                                </span>
                                <span className="grid gap-1">
                                    <span className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                        {appearance.step}
                                    </span>
                                    <span className="font-medium">
                                        {role.label}
                                    </span>
                                    <span className="text-sm text-muted-foreground">
                                        {role.description}
                                    </span>
                                </span>
                            </button>

                            {appearance.leadsToNextStep && (
                                <span
                                    aria-hidden="true"
                                    className="absolute top-1/2 -right-4 z-10 hidden size-5 -translate-y-1/2 items-center justify-center rounded-full border bg-background text-muted-foreground xl:flex"
                                >
                                    <ChevronRight className="size-3" />
                                </span>
                            )}
                        </li>
                    );
                })}
            </ol>

            <p className="mt-4 flex gap-2 text-sm text-muted-foreground">
                <Info className="mt-0.5 size-4 shrink-0" />
                <span>
                    Coordinadores y gerentes también capturan sus propias
                    requisiciones, pero nadie autoriza las suyas. El gerente
                    deposita fuera del sistema y el solicitante registra lo que
                    recibió.
                </span>
            </p>
        </section>
    );
}

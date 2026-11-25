import { Form, Head } from '@inertiajs/react';
import { Power, PowerOff } from 'lucide-react';
import { useState } from 'react';
import {
    destroy,
    store,
    update,
} from '@/actions/App/Http/Controllers/BankAccountController';
import { DeleteCatalogItemDialog } from '@/components/delete-catalog-item-dialog';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
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
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { formatRequisitionCount } from '@/lib/catalogs';
import { index } from '@/routes/bank-accounts';
import type { BankAccount, BankAccountType, UserOption } from '@/types';

const accountTypeLabels: Record<BankAccountType, string> = {
    clabe: 'CLABE',
    account: 'Cuenta',
    debit_card: 'Tarjeta de débito',
};

export default function BankAccountsIndex({
    bankAccounts,
    users,
}: {
    bankAccounts: BankAccount[];
    users: UserOption[];
}) {
    const [createdCount, setCreatedCount] = useState(0);

    return (
        <>
            <Head title="Cuentas bancarias" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Cuentas bancarias"
                    description="Cuentas de los empleados para depósitos y reembolsos. No se editan: si cambia una cuenta, registra la nueva y desactiva la anterior."
                />

                <div className="grid min-w-0 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,22rem)]">
                    <BankAccountList bankAccounts={bankAccounts} />
                    <BankAccountForm
                        key={createdCount}
                        users={users}
                        onCreated={() => setCreatedCount((count) => count + 1)}
                    />
                </div>
            </div>
        </>
    );
}

function BankAccountList({ bankAccounts }: { bankAccounts: BankAccount[] }) {
    return (
        <div className="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            {bankAccounts.length === 0 ? (
                <p className="p-8 text-center text-sm text-muted-foreground">
                    Todavía no hay cuentas bancarias.
                </p>
            ) : (
                <table
                    aria-label="Listado de cuentas bancarias"
                    className="w-full text-sm"
                >
                    <thead className="bg-muted/50 text-left text-muted-foreground">
                        <tr>
                            <th scope="col" className="px-4 py-3 font-medium">
                                Empleado
                            </th>
                            <th scope="col" className="px-4 py-3 font-medium">
                                Cuenta
                            </th>
                            <th scope="col" className="px-4 py-3 font-medium">
                                Estado
                            </th>
                            <th
                                scope="col"
                                className="px-4 py-3 text-right font-medium"
                            >
                                Requisiciones
                            </th>
                            <th scope="col" className="w-px px-4 py-3">
                                <span className="sr-only">Acciones</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                        {bankAccounts.map((bankAccount) => {
                            const accountLabel = `${bankAccount.bank_name} ${accountTypeLabels[bankAccount.account_type]} •••• ${bankAccount.account_number_last_digits}`;

                            return (
                                <tr
                                    key={bankAccount.id}
                                    data-active={bankAccount.is_active}
                                    className="data-[active=false]:text-muted-foreground"
                                >
                                    <td className="px-4 py-3">
                                        <div className="font-medium wrap-anywhere">
                                            {bankAccount.user.name}
                                        </div>
                                        {bankAccount.account_holder !==
                                            bankAccount.user.name && (
                                            <div className="text-xs wrap-anywhere text-muted-foreground">
                                                Titular:{' '}
                                                {bankAccount.account_holder}
                                            </div>
                                        )}
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="font-medium">
                                            {bankAccount.bank_name}
                                        </div>
                                        <div className="text-xs whitespace-nowrap text-muted-foreground">
                                            {
                                                accountTypeLabels[
                                                    bankAccount.account_type
                                                ]
                                            }{' '}
                                            <span className="font-mono">
                                                ••••{' '}
                                                {
                                                    bankAccount.account_number_last_digits
                                                }
                                            </span>
                                        </div>
                                        {(bankAccount.bank_city ||
                                            bankAccount.branch_number) && (
                                            <div className="text-xs text-muted-foreground">
                                                {[
                                                    bankAccount.bank_city &&
                                                        `Plaza ${bankAccount.bank_city}`,
                                                    bankAccount.branch_number &&
                                                        `Sucursal ${bankAccount.branch_number}`,
                                                ]
                                                    .filter(Boolean)
                                                    .join(' · ')}
                                            </div>
                                        )}
                                    </td>
                                    <td className="px-4 py-3">
                                        <Badge
                                            variant={
                                                bankAccount.is_active
                                                    ? 'secondary'
                                                    : 'outline'
                                            }
                                        >
                                            {bankAccount.is_active
                                                ? 'Activa'
                                                : 'Inactiva'}
                                        </Badge>
                                    </td>
                                    <td className="px-4 py-3 text-right tabular-nums">
                                        {bankAccount.requisitions_count}
                                    </td>
                                    <td className="px-4 py-2">
                                        <div className="flex justify-end gap-1">
                                            <ToggleBankAccountButton
                                                bankAccount={bankAccount}
                                                accountLabel={accountLabel}
                                            />
                                            <DeleteCatalogItemDialog
                                                itemName={accountLabel}
                                                description="La cuenta se borrará del catálogo. Esta acción no se puede deshacer."
                                                form={destroy.form(bankAccount)}
                                                blockedReason={
                                                    bankAccount.requisitions_count >
                                                    0
                                                        ? `No se puede eliminar: aparece en ${formatRequisitionCount(bankAccount.requisitions_count)}. Desactívala en su lugar.`
                                                        : undefined
                                                }
                                            />
                                        </div>
                                    </td>
                                </tr>
                            );
                        })}
                    </tbody>
                </table>
            )}
        </div>
    );
}

function ToggleBankAccountButton({
    bankAccount,
    accountLabel,
}: {
    bankAccount: BankAccount;
    accountLabel: string;
}) {
    const action = bankAccount.is_active ? 'Desactivar' : 'Activar';

    return (
        <Form {...update.form(bankAccount)} options={{ preserveScroll: true }}>
            {({ processing }) => (
                <>
                    <input
                        type="hidden"
                        name="is_active"
                        value={bankAccount.is_active ? '0' : '1'}
                    />
                    <Tooltip>
                        <TooltipTrigger asChild>
                            <Button
                                type="submit"
                                variant="ghost"
                                size="icon"
                                disabled={processing}
                            >
                                {bankAccount.is_active ? (
                                    <PowerOff />
                                ) : (
                                    <Power />
                                )}
                                <span className="sr-only">
                                    {action} {accountLabel}
                                </span>
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>{action}</TooltipContent>
                    </Tooltip>
                </>
            )}
        </Form>
    );
}

function BankAccountForm({
    users,
    onCreated,
}: {
    users: UserOption[];
    onCreated: () => void;
}) {
    return (
        <Card>
            <CardHeader>
                <h3 id="bank-account-form-title" className="font-semibold">
                    Nueva cuenta bancaria
                </h3>
            </CardHeader>
            <CardContent>
                <Form
                    {...store.form()}
                    id="bank-account-form"
                    aria-labelledby="bank-account-form-title"
                    options={{ preserveScroll: true }}
                    onSuccess={onCreated}
                    className="grid gap-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="bank-account-user">
                                    Empleado
                                </Label>
                                <Select
                                    name="user_id"
                                    required
                                    disabled={processing}
                                >
                                    <SelectTrigger
                                        id="bank-account-user"
                                        className="w-full"
                                        aria-invalid={Boolean(errors.user_id)}
                                        aria-describedby={
                                            errors.user_id
                                                ? 'bank-account-user-error'
                                                : undefined
                                        }
                                    >
                                        <SelectValue placeholder="Elige al empleado" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {users.map((user) => (
                                            <SelectItem
                                                key={user.id}
                                                value={String(user.id)}
                                            >
                                                {user.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError
                                    id="bank-account-user-error"
                                    message={errors.user_id}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="bank-account-holder">
                                    Titular
                                </Label>
                                <Input
                                    id="bank-account-holder"
                                    name="account_holder"
                                    maxLength={150}
                                    required
                                    autoComplete="off"
                                    disabled={processing}
                                    aria-invalid={Boolean(
                                        errors.account_holder,
                                    )}
                                    aria-describedby={
                                        errors.account_holder
                                            ? 'bank-account-holder-error'
                                            : undefined
                                    }
                                />
                                <InputError
                                    id="bank-account-holder-error"
                                    message={errors.account_holder}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="bank-account-bank-name">
                                    Banco
                                </Label>
                                <Input
                                    id="bank-account-bank-name"
                                    name="bank_name"
                                    maxLength={100}
                                    required
                                    autoComplete="off"
                                    placeholder="BBVA"
                                    disabled={processing}
                                    aria-invalid={Boolean(errors.bank_name)}
                                    aria-describedby={
                                        errors.bank_name
                                            ? 'bank-account-bank-name-error'
                                            : undefined
                                    }
                                />
                                <InputError
                                    id="bank-account-bank-name-error"
                                    message={errors.bank_name}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="bank-account-type">Tipo</Label>
                                <Select
                                    name="account_type"
                                    defaultValue="clabe"
                                    required
                                    disabled={processing}
                                >
                                    <SelectTrigger
                                        id="bank-account-type"
                                        className="w-full"
                                        aria-invalid={Boolean(
                                            errors.account_type,
                                        )}
                                        aria-describedby={
                                            errors.account_type
                                                ? 'bank-account-type-error'
                                                : undefined
                                        }
                                    >
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {Object.entries(accountTypeLabels).map(
                                            ([type, label]) => (
                                                <SelectItem
                                                    key={type}
                                                    value={type}
                                                >
                                                    {label}
                                                </SelectItem>
                                            ),
                                        )}
                                    </SelectContent>
                                </Select>
                                <InputError
                                    id="bank-account-type-error"
                                    message={errors.account_type}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="bank-account-number">
                                    Número
                                </Label>
                                <Input
                                    id="bank-account-number"
                                    name="account_number"
                                    inputMode="numeric"
                                    maxLength={30}
                                    required
                                    autoComplete="off"
                                    className="font-mono"
                                    disabled={processing}
                                    aria-invalid={Boolean(
                                        errors.account_number,
                                    )}
                                    aria-describedby={
                                        errors.account_number
                                            ? 'bank-account-number-error'
                                            : 'bank-account-number-hint'
                                    }
                                />
                                <p
                                    id="bank-account-number-hint"
                                    className="text-sm text-muted-foreground"
                                >
                                    CLABE de 18 dígitos o tarjeta de 16. Se
                                    guarda cifrado y solo se muestran los
                                    últimos 4.
                                </p>
                                <InputError
                                    id="bank-account-number-error"
                                    message={errors.account_number}
                                />
                            </div>

                            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                                <div className="grid content-start gap-2">
                                    <Label htmlFor="bank-account-city">
                                        Plaza{' '}
                                        <span className="font-normal text-muted-foreground">
                                            (opcional)
                                        </span>
                                    </Label>
                                    <Input
                                        id="bank-account-city"
                                        name="bank_city"
                                        maxLength={100}
                                        autoComplete="off"
                                        disabled={processing}
                                        aria-invalid={Boolean(errors.bank_city)}
                                        aria-describedby={
                                            errors.bank_city
                                                ? 'bank-account-city-error'
                                                : undefined
                                        }
                                    />
                                    <InputError
                                        id="bank-account-city-error"
                                        message={errors.bank_city}
                                    />
                                </div>

                                <div className="grid content-start gap-2">
                                    <Label htmlFor="bank-account-branch">
                                        Sucursal{' '}
                                        <span className="font-normal text-muted-foreground">
                                            (opcional)
                                        </span>
                                    </Label>
                                    <Input
                                        id="bank-account-branch"
                                        name="branch_number"
                                        maxLength={20}
                                        autoComplete="off"
                                        disabled={processing}
                                        aria-invalid={Boolean(
                                            errors.branch_number,
                                        )}
                                        aria-describedby={
                                            errors.branch_number
                                                ? 'bank-account-branch-error'
                                                : undefined
                                        }
                                    />
                                    <InputError
                                        id="bank-account-branch-error"
                                        message={errors.branch_number}
                                    />
                                </div>
                            </div>

                            <div className="flex justify-end">
                                <Button type="submit" disabled={processing}>
                                    Registrar cuenta
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </CardContent>
        </Card>
    );
}

BankAccountsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Cuentas bancarias',
            href: index(),
        },
    ],
};

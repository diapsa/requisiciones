import { Form, Head, Link } from '@inertiajs/react';
import { Pencil } from 'lucide-react';
import { useState } from 'react';
import {
    destroy,
    store,
    update,
} from '@/actions/App/Http/Controllers/ExpenseRateController';
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
import { formatCurrency, formatDate, today } from '@/lib/catalogs';
import { index } from '@/routes/expense-rates';
import { index as expenseTypesIndex } from '@/routes/expense-types';
import type { ExpenseRate, ExpenseTypeOption } from '@/types';

type RateStatus = 'current' | 'upcoming' | 'expired';

const rateStatusLabels: Record<RateStatus, string> = {
    current: 'Vigente',
    upcoming: 'Programada',
    expired: 'Vencida',
};

function rateStatus(rate: ExpenseRate, date: string): RateStatus {
    if (rate.valid_from > date) {
        return 'upcoming';
    }

    if (rate.valid_until !== null && rate.valid_until < date) {
        return 'expired';
    }

    return 'current';
}

export default function ExpenseRatesIndex({
    expenseRates,
    expenseTypes,
}: {
    expenseRates: ExpenseRate[];
    expenseTypes: ExpenseTypeOption[];
}) {
    const [editingExpenseRateId, setEditingExpenseRateId] = useState<
        number | null
    >(null);
    const [createdCount, setCreatedCount] = useState(0);
    const editingExpenseRate = expenseRates.find(
        (expenseRate) => expenseRate.id === editingExpenseRateId,
    );

    return (
        <>
            <Head title="Tarifas de gasto" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Tarifas de gasto"
                    description="Montos autorizados con su vigencia. Cuando cambie un monto, cierra la tarifa anterior y da de alta una nueva."
                />

                <div className="grid min-w-0 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,22rem)]">
                    <ExpenseRateList
                        expenseRates={expenseRates}
                        editingExpenseRateId={editingExpenseRateId}
                        onEdit={setEditingExpenseRateId}
                    />
                    <ExpenseRateForm
                        key={editingExpenseRate?.id ?? `create-${createdCount}`}
                        expenseRate={editingExpenseRate}
                        expenseTypes={expenseTypes}
                        onCancel={() => setEditingExpenseRateId(null)}
                        onCreated={() => setCreatedCount((count) => count + 1)}
                        onSaved={() =>
                            setEditingExpenseRateId((currentId) =>
                                currentId === editingExpenseRate?.id
                                    ? null
                                    : currentId,
                            )
                        }
                    />
                </div>
            </div>
        </>
    );
}

function ExpenseRateList({
    expenseRates,
    editingExpenseRateId,
    onEdit,
}: {
    expenseRates: ExpenseRate[];
    editingExpenseRateId: number | null;
    onEdit: (expenseRateId: number) => void;
}) {
    const currentDate = today();

    return (
        <div className="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            {expenseRates.length === 0 ? (
                <p className="p-8 text-center text-sm text-muted-foreground">
                    Todavía no hay tarifas.
                </p>
            ) : (
                <table
                    aria-label="Listado de tarifas de gasto"
                    className="w-full text-sm"
                >
                    <thead className="bg-muted/50 text-left text-muted-foreground">
                        <tr>
                            <th scope="col" className="px-4 py-3 font-medium">
                                Tarifa
                            </th>
                            <th
                                scope="col"
                                className="px-4 py-3 text-right font-medium"
                            >
                                Monto
                            </th>
                            <th scope="col" className="px-4 py-3 font-medium">
                                Vigencia
                            </th>
                            <th scope="col" className="px-4 py-3 font-medium">
                                Estado
                            </th>
                            <th scope="col" className="w-px px-4 py-3">
                                <span className="sr-only">Acciones</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                        {expenseRates.map((expenseRate) => {
                            const status = rateStatus(expenseRate, currentDate);

                            return (
                                <tr
                                    key={expenseRate.id}
                                    data-editing={
                                        expenseRate.id === editingExpenseRateId
                                    }
                                    className="data-[editing=true]:bg-muted/50"
                                >
                                    <td className="px-4 py-3">
                                        <div className="font-medium wrap-anywhere">
                                            {expenseRate.name}
                                        </div>
                                        <div className="text-xs text-muted-foreground">
                                            {expenseRate.expense_type.name}
                                        </div>
                                    </td>
                                    <td className="px-4 py-3 text-right whitespace-nowrap tabular-nums">
                                        {formatCurrency(expenseRate.amount)}
                                    </td>
                                    <td className="px-4 py-3 whitespace-nowrap tabular-nums">
                                        {formatDate(expenseRate.valid_from)} –{' '}
                                        {expenseRate.valid_until
                                            ? formatDate(
                                                  expenseRate.valid_until,
                                              )
                                            : 'sin fin'}
                                    </td>
                                    <td className="px-4 py-3">
                                        <Badge
                                            variant={
                                                status === 'current'
                                                    ? 'secondary'
                                                    : 'outline'
                                            }
                                        >
                                            {rateStatusLabels[status]}
                                        </Badge>
                                    </td>
                                    <td className="px-4 py-2">
                                        <div className="flex justify-end gap-1">
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                aria-controls="expense-rate-form"
                                                onClick={() =>
                                                    onEdit(expenseRate.id)
                                                }
                                            >
                                                <Pencil />
                                                <span className="sr-only">
                                                    Editar {expenseRate.name}
                                                </span>
                                            </Button>
                                            <DeleteCatalogItemDialog
                                                itemName={expenseRate.name}
                                                description="La tarifa se borrará del catálogo. Las requisiciones ya capturadas conservan sus montos. Esta acción no se puede deshacer."
                                                form={destroy.form(expenseRate)}
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

function ExpenseRateForm({
    expenseRate,
    expenseTypes,
    onCancel,
    onCreated,
    onSaved,
}: {
    expenseRate?: ExpenseRate;
    expenseTypes: ExpenseTypeOption[];
    onCancel: () => void;
    onCreated: () => void;
    onSaved: () => void;
}) {
    const isEditing = expenseRate !== undefined;

    if (expenseTypes.length === 0) {
        return (
            <Card>
                <CardHeader>
                    <h3 className="font-semibold">Nueva tarifa</h3>
                </CardHeader>
                <CardContent className="text-sm text-muted-foreground">
                    Cada tarifa pertenece a un tipo de gasto. Primero da de alta
                    uno en{' '}
                    <Link
                        href={expenseTypesIndex()}
                        className="font-medium text-foreground underline underline-offset-4"
                    >
                        Tipos de gasto
                    </Link>
                    .
                </CardContent>
            </Card>
        );
    }

    return (
        <Card>
            <CardHeader>
                <h3 id="expense-rate-form-title" className="font-semibold">
                    {isEditing ? 'Editar tarifa' : 'Nueva tarifa'}
                </h3>
            </CardHeader>
            <CardContent>
                <Form
                    {...(isEditing ? update.form(expenseRate) : store.form())}
                    id="expense-rate-form"
                    aria-labelledby="expense-rate-form-title"
                    options={{ preserveScroll: true }}
                    onSuccess={isEditing ? onSaved : onCreated}
                    className="grid gap-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="expense-rate-expense-type">
                                    Tipo de gasto
                                </Label>
                                <Select
                                    name="expense_type_id"
                                    defaultValue={
                                        expenseRate
                                            ? String(
                                                  expenseRate.expense_type_id,
                                              )
                                            : undefined
                                    }
                                    required
                                    disabled={processing}
                                >
                                    <SelectTrigger
                                        id="expense-rate-expense-type"
                                        className="w-full"
                                        autoFocus={isEditing}
                                        aria-invalid={Boolean(
                                            errors.expense_type_id,
                                        )}
                                        aria-describedby={
                                            errors.expense_type_id
                                                ? 'expense-rate-expense-type-error'
                                                : undefined
                                        }
                                    >
                                        <SelectValue placeholder="Elige un tipo de gasto" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {expenseTypes.map((expenseType) => (
                                            <SelectItem
                                                key={expenseType.id}
                                                value={String(expenseType.id)}
                                            >
                                                {expenseType.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError
                                    id="expense-rate-expense-type-error"
                                    message={errors.expense_type_id}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="expense-rate-name">
                                    Nombre
                                </Label>
                                <Input
                                    id="expense-rate-name"
                                    name="name"
                                    defaultValue={expenseRate?.name}
                                    maxLength={100}
                                    required
                                    autoComplete="off"
                                    placeholder="Comida con factura"
                                    disabled={processing}
                                    aria-invalid={Boolean(errors.name)}
                                    aria-describedby={
                                        errors.name
                                            ? 'expense-rate-name-error'
                                            : undefined
                                    }
                                />
                                <InputError
                                    id="expense-rate-name-error"
                                    message={errors.name}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="expense-rate-amount">
                                    Monto (MXN)
                                </Label>
                                <Input
                                    id="expense-rate-amount"
                                    name="amount"
                                    type="number"
                                    inputMode="decimal"
                                    step="0.01"
                                    min="0"
                                    defaultValue={expenseRate?.amount}
                                    required
                                    disabled={processing}
                                    aria-invalid={Boolean(errors.amount)}
                                    aria-describedby={
                                        errors.amount
                                            ? 'expense-rate-amount-error'
                                            : undefined
                                    }
                                />
                                <InputError
                                    id="expense-rate-amount-error"
                                    message={errors.amount}
                                />
                            </div>

                            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                                <div className="grid content-start gap-2">
                                    <Label htmlFor="expense-rate-valid-from">
                                        Vigente desde
                                    </Label>
                                    <Input
                                        id="expense-rate-valid-from"
                                        name="valid_from"
                                        type="date"
                                        defaultValue={
                                            expenseRate?.valid_from ?? today()
                                        }
                                        required
                                        disabled={processing}
                                        aria-invalid={Boolean(
                                            errors.valid_from,
                                        )}
                                        aria-describedby={
                                            errors.valid_from
                                                ? 'expense-rate-valid-from-error'
                                                : undefined
                                        }
                                    />
                                    <InputError
                                        id="expense-rate-valid-from-error"
                                        message={errors.valid_from}
                                    />
                                </div>

                                <div className="grid content-start gap-2">
                                    <Label htmlFor="expense-rate-valid-until">
                                        Hasta{' '}
                                        <span className="font-normal text-muted-foreground">
                                            (opcional)
                                        </span>
                                    </Label>
                                    <Input
                                        id="expense-rate-valid-until"
                                        name="valid_until"
                                        type="date"
                                        defaultValue={
                                            expenseRate?.valid_until ??
                                            undefined
                                        }
                                        disabled={processing}
                                        aria-invalid={Boolean(
                                            errors.valid_until,
                                        )}
                                        aria-describedby={
                                            errors.valid_until
                                                ? 'expense-rate-valid-until-error'
                                                : 'expense-rate-valid-until-hint'
                                        }
                                    />
                                    <p
                                        id="expense-rate-valid-until-hint"
                                        className="text-sm text-muted-foreground"
                                    >
                                        Vacío: sigue vigente.
                                    </p>
                                    <InputError
                                        id="expense-rate-valid-until-error"
                                        message={errors.valid_until}
                                    />
                                </div>
                            </div>

                            <div className="flex flex-wrap justify-end gap-2">
                                {isEditing && (
                                    <Button
                                        type="button"
                                        variant="secondary"
                                        onClick={onCancel}
                                        disabled={processing}
                                    >
                                        Cancelar
                                    </Button>
                                )}
                                <Button type="submit" disabled={processing}>
                                    {isEditing
                                        ? 'Guardar cambios'
                                        : 'Crear tarifa'}
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </CardContent>
        </Card>
    );
}

ExpenseRatesIndex.layout = {
    breadcrumbs: [
        {
            title: 'Tarifas de gasto',
            href: index(),
        },
    ],
};

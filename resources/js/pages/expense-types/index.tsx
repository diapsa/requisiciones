import { Form, Head } from '@inertiajs/react';
import { Pencil } from 'lucide-react';
import { useState } from 'react';
import {
    destroy,
    store,
    update,
} from '@/actions/App/Http/Controllers/ExpenseTypeController';
import { DeleteCatalogItemDialog } from '@/components/delete-catalog-item-dialog';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { index } from '@/routes/expense-types';
import type { CalculationMethod, ExpenseType } from '@/types';

const calculationMethodLabels: Record<CalculationMethod, string> = {
    fuel_by_distance: 'Gasolina por distancia',
    per_person_meals: 'Alimentos por persona',
    breakfast_reference: 'Desayuno / despensa',
    per_night: 'Por noche',
    fixed: 'Monto fijo',
};

export default function ExpenseTypesIndex({
    expenseTypes,
}: {
    expenseTypes: ExpenseType[];
}) {
    const [editingExpenseTypeId, setEditingExpenseTypeId] = useState<
        number | null
    >(null);
    const [createdCount, setCreatedCount] = useState(0);
    const editingExpenseType = expenseTypes.find(
        (expenseType) => expenseType.id === editingExpenseTypeId,
    );
    const nextSortOrder =
        Math.max(
            0,
            ...expenseTypes.map((expenseType) => expenseType.sort_order),
        ) + 1;

    return (
        <>
            <Head title="Tipos de gasto" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Tipos de gasto"
                    description="Conceptos de la requisición y categorías de la comprobación, en el orden en que aparecen en pantalla."
                />

                <div className="grid min-w-0 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,22rem)]">
                    <ExpenseTypeList
                        expenseTypes={expenseTypes}
                        editingExpenseTypeId={editingExpenseTypeId}
                        onEdit={setEditingExpenseTypeId}
                    />
                    <ExpenseTypeForm
                        key={editingExpenseType?.id ?? `create-${createdCount}`}
                        expenseType={editingExpenseType}
                        nextSortOrder={nextSortOrder}
                        onCancel={() => setEditingExpenseTypeId(null)}
                        onCreated={() => setCreatedCount((count) => count + 1)}
                        onSaved={() =>
                            setEditingExpenseTypeId((currentId) =>
                                currentId === editingExpenseType?.id
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

function deleteBlockedReason(expenseType: ExpenseType): string | undefined {
    if (expenseType.expense_rates_count > 0) {
        return 'No se puede eliminar: tiene tarifas registradas.';
    }

    if (expenseType.requisition_items_count + expenseType.expenses_count > 0) {
        return 'No se puede eliminar: se usa en requisiciones o comprobaciones.';
    }

    return undefined;
}

function ExpenseTypeList({
    expenseTypes,
    editingExpenseTypeId,
    onEdit,
}: {
    expenseTypes: ExpenseType[];
    editingExpenseTypeId: number | null;
    onEdit: (expenseTypeId: number) => void;
}) {
    return (
        <div className="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            {expenseTypes.length === 0 ? (
                <p className="p-8 text-center text-sm text-muted-foreground">
                    Todavía no hay tipos de gasto.
                </p>
            ) : (
                <table
                    aria-label="Listado de tipos de gasto"
                    className="w-full text-sm"
                >
                    <thead className="bg-muted/50 text-left text-muted-foreground">
                        <tr>
                            <th
                                scope="col"
                                className="px-4 py-3 text-right font-medium"
                            >
                                Orden
                            </th>
                            <th scope="col" className="px-4 py-3 font-medium">
                                Tipo de gasto
                            </th>
                            <th scope="col" className="px-4 py-3 font-medium">
                                Cálculo
                            </th>
                            <th scope="col" className="px-4 py-3 font-medium">
                                Unidad
                            </th>
                            <th scope="col" className="px-4 py-3 font-medium">
                                En requisición
                            </th>
                            <th
                                scope="col"
                                className="px-4 py-3 text-right font-medium"
                            >
                                Tarifas
                            </th>
                            <th scope="col" className="w-px px-4 py-3">
                                <span className="sr-only">Acciones</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                        {expenseTypes.map((expenseType) => (
                            <tr
                                key={expenseType.id}
                                data-editing={
                                    expenseType.id === editingExpenseTypeId
                                }
                                className="data-[editing=true]:bg-muted/50"
                            >
                                <td className="px-4 py-3 text-right tabular-nums">
                                    {expenseType.sort_order}
                                </td>
                                <td className="px-4 py-3">
                                    <div className="font-medium wrap-anywhere">
                                        {expenseType.name}
                                    </div>
                                    <div className="font-mono text-xs text-muted-foreground">
                                        {expenseType.code}
                                    </div>
                                </td>
                                <td className="px-4 py-3">
                                    {
                                        calculationMethodLabels[
                                            expenseType.calculation_method
                                        ]
                                    }
                                </td>
                                <td className="px-4 py-3">
                                    {expenseType.default_unit}
                                </td>
                                <td className="px-4 py-3">
                                    <Badge
                                        variant={
                                            expenseType.is_requestable
                                                ? 'secondary'
                                                : 'outline'
                                        }
                                    >
                                        {expenseType.is_requestable
                                            ? 'Sí'
                                            : 'No'}
                                    </Badge>
                                </td>
                                <td className="px-4 py-3 text-right tabular-nums">
                                    {expenseType.expense_rates_count}
                                </td>
                                <td className="px-4 py-2">
                                    <div className="flex justify-end gap-1">
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon"
                                            aria-controls="expense-type-form"
                                            onClick={() =>
                                                onEdit(expenseType.id)
                                            }
                                        >
                                            <Pencil />
                                            <span className="sr-only">
                                                Editar {expenseType.name}
                                            </span>
                                        </Button>
                                        <DeleteCatalogItemDialog
                                            itemName={expenseType.name}
                                            description="El tipo de gasto se borrará del catálogo. Esta acción no se puede deshacer."
                                            form={destroy.form(expenseType)}
                                            blockedReason={deleteBlockedReason(
                                                expenseType,
                                            )}
                                        />
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            )}
        </div>
    );
}

function ExpenseTypeForm({
    expenseType,
    nextSortOrder,
    onCancel,
    onCreated,
    onSaved,
}: {
    expenseType?: ExpenseType;
    nextSortOrder: number;
    onCancel: () => void;
    onCreated: () => void;
    onSaved: () => void;
}) {
    const isEditing = expenseType !== undefined;

    return (
        <Card>
            <CardHeader>
                <h3 id="expense-type-form-title" className="font-semibold">
                    {isEditing ? 'Editar tipo de gasto' : 'Nuevo tipo de gasto'}
                </h3>
            </CardHeader>
            <CardContent>
                <Form
                    {...(isEditing ? update.form(expenseType) : store.form())}
                    id="expense-type-form"
                    aria-labelledby="expense-type-form-title"
                    options={{ preserveScroll: true }}
                    onSuccess={isEditing ? onSaved : onCreated}
                    className="grid gap-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="expense-type-name">
                                    Nombre
                                </Label>
                                <Input
                                    id="expense-type-name"
                                    name="name"
                                    defaultValue={expenseType?.name}
                                    maxLength={100}
                                    required
                                    autoFocus={isEditing}
                                    autoComplete="off"
                                    placeholder="Casetas"
                                    disabled={processing}
                                    aria-invalid={Boolean(errors.name)}
                                    aria-describedby={
                                        errors.name
                                            ? 'expense-type-name-error'
                                            : undefined
                                    }
                                />
                                <InputError
                                    id="expense-type-name-error"
                                    message={errors.name}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="expense-type-code">Clave</Label>
                                <Input
                                    id="expense-type-code"
                                    name="code"
                                    defaultValue={expenseType?.code}
                                    maxLength={30}
                                    required
                                    autoComplete="off"
                                    className="font-mono lowercase"
                                    placeholder="tolls"
                                    disabled={processing}
                                    aria-invalid={Boolean(errors.code)}
                                    aria-describedby={
                                        errors.code
                                            ? 'expense-type-code-error'
                                            : 'expense-type-code-hint'
                                    }
                                />
                                <p
                                    id="expense-type-code-hint"
                                    className="text-sm text-muted-foreground"
                                >
                                    Uso interno del sistema: minúsculas y guion
                                    bajo.
                                </p>
                                <InputError
                                    id="expense-type-code-error"
                                    message={errors.code}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="expense-type-calculation-method">
                                    Cálculo del importe
                                </Label>
                                <Select
                                    name="calculation_method"
                                    defaultValue={
                                        expenseType?.calculation_method ??
                                        'fixed'
                                    }
                                    required
                                    disabled={processing}
                                >
                                    <SelectTrigger
                                        id="expense-type-calculation-method"
                                        className="w-full"
                                        aria-invalid={Boolean(
                                            errors.calculation_method,
                                        )}
                                        aria-describedby={
                                            errors.calculation_method
                                                ? 'expense-type-calculation-method-error'
                                                : undefined
                                        }
                                    >
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {Object.entries(
                                            calculationMethodLabels,
                                        ).map(([method, label]) => (
                                            <SelectItem
                                                key={method}
                                                value={method}
                                            >
                                                {label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError
                                    id="expense-type-calculation-method-error"
                                    message={errors.calculation_method}
                                />
                            </div>

                            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                                <div className="grid content-start gap-2">
                                    <Label htmlFor="expense-type-default-unit">
                                        Unidad
                                    </Label>
                                    <Input
                                        id="expense-type-default-unit"
                                        name="default_unit"
                                        defaultValue={expenseType?.default_unit}
                                        maxLength={20}
                                        required
                                        autoComplete="off"
                                        className="uppercase"
                                        placeholder="TICKET"
                                        disabled={processing}
                                        aria-invalid={Boolean(
                                            errors.default_unit,
                                        )}
                                        aria-describedby={
                                            errors.default_unit
                                                ? 'expense-type-default-unit-error'
                                                : undefined
                                        }
                                    />
                                    <InputError
                                        id="expense-type-default-unit-error"
                                        message={errors.default_unit}
                                    />
                                </div>

                                <div className="grid content-start gap-2">
                                    <Label htmlFor="expense-type-sort-order">
                                        Orden
                                    </Label>
                                    <Input
                                        id="expense-type-sort-order"
                                        name="sort_order"
                                        type="number"
                                        inputMode="numeric"
                                        step="1"
                                        min="0"
                                        max="65535"
                                        defaultValue={
                                            expenseType?.sort_order ??
                                            nextSortOrder
                                        }
                                        required
                                        disabled={processing}
                                        aria-invalid={Boolean(
                                            errors.sort_order,
                                        )}
                                        aria-describedby={
                                            errors.sort_order
                                                ? 'expense-type-sort-order-error'
                                                : undefined
                                        }
                                    />
                                    <InputError
                                        id="expense-type-sort-order-error"
                                        message={errors.sort_order}
                                    />
                                </div>
                            </div>

                            <div className="flex items-start gap-3">
                                <Checkbox
                                    id="expense-type-is-requestable"
                                    name="is_requestable"
                                    value="1"
                                    defaultChecked={
                                        expenseType?.is_requestable ?? true
                                    }
                                    disabled={processing}
                                    aria-describedby="expense-type-is-requestable-hint"
                                />
                                <div className="grid gap-1">
                                    <Label htmlFor="expense-type-is-requestable">
                                        Se puede pedir en una requisición
                                    </Label>
                                    <p
                                        id="expense-type-is-requestable-hint"
                                        className="text-sm text-muted-foreground"
                                    >
                                        Si no, solo aparece como categoría en la
                                        comprobación.
                                    </p>
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
                                        : 'Crear tipo de gasto'}
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </CardContent>
        </Card>
    );
}

ExpenseTypesIndex.layout = {
    breadcrumbs: [
        {
            title: 'Tipos de gasto',
            href: index(),
        },
    ],
};

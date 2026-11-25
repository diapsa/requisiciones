import { Form, Head } from '@inertiajs/react';
import { Pencil } from 'lucide-react';
import { useState } from 'react';
import {
    destroy,
    store,
    update,
} from '@/actions/App/Http/Controllers/BusinessUnitController';
import { DeleteCatalogItemDialog } from '@/components/delete-catalog-item-dialog';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatRequisitionCount } from '@/lib/catalogs';
import { index } from '@/routes/business-units';
import type { BusinessUnit } from '@/types';

export default function BusinessUnitsIndex({
    businessUnits,
}: {
    businessUnits: BusinessUnit[];
}) {
    const [editingBusinessUnitId, setEditingBusinessUnitId] = useState<
        number | null
    >(null);
    const editingBusinessUnit = businessUnits.find(
        (businessUnit) => businessUnit.id === editingBusinessUnitId,
    );

    return (
        <>
            <Head title="Unidades de negocio" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Unidades de negocio"
                    description="Sucursales que emiten las requisiciones. Su clave va en el folio (089_SALT_PROYECTO)."
                />

                <div className="grid min-w-0 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,22rem)]">
                    <BusinessUnitList
                        businessUnits={businessUnits}
                        editingBusinessUnitId={editingBusinessUnitId}
                        onEdit={setEditingBusinessUnitId}
                    />
                    <BusinessUnitForm
                        key={editingBusinessUnit?.id ?? 'create'}
                        businessUnit={editingBusinessUnit}
                        onCancel={() => setEditingBusinessUnitId(null)}
                        onSaved={() =>
                            setEditingBusinessUnitId((currentId) =>
                                currentId === editingBusinessUnit?.id
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

function BusinessUnitList({
    businessUnits,
    editingBusinessUnitId,
    onEdit,
}: {
    businessUnits: BusinessUnit[];
    editingBusinessUnitId: number | null;
    onEdit: (businessUnitId: number) => void;
}) {
    return (
        <div className="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            {businessUnits.length === 0 ? (
                <p className="p-8 text-center text-sm text-muted-foreground">
                    Todavía no hay unidades de negocio.
                </p>
            ) : (
                <table
                    aria-label="Listado de unidades de negocio"
                    className="w-full text-sm"
                >
                    <thead className="bg-muted/50 text-left text-muted-foreground">
                        <tr>
                            <th scope="col" className="px-4 py-3 font-medium">
                                Clave
                            </th>
                            <th scope="col" className="px-4 py-3 font-medium">
                                Nombre
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
                        {businessUnits.map((businessUnit) => (
                            <tr
                                key={businessUnit.id}
                                data-editing={
                                    businessUnit.id === editingBusinessUnitId
                                }
                                className="data-[editing=true]:bg-muted/50"
                            >
                                <td className="px-4 py-3 font-mono">
                                    {businessUnit.code}
                                </td>
                                <td className="px-4 py-3 font-medium wrap-anywhere">
                                    {businessUnit.name}
                                </td>
                                <td className="px-4 py-3 text-right tabular-nums">
                                    {businessUnit.requisitions_count}
                                </td>
                                <td className="px-4 py-2">
                                    <div className="flex justify-end gap-1">
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon"
                                            aria-controls="business-unit-form"
                                            onClick={() =>
                                                onEdit(businessUnit.id)
                                            }
                                        >
                                            <Pencil />
                                            <span className="sr-only">
                                                Editar {businessUnit.name}
                                            </span>
                                        </Button>
                                        <DeleteCatalogItemDialog
                                            itemName={businessUnit.name}
                                            description="La unidad de negocio se borrará del catálogo. Esta acción no se puede deshacer."
                                            form={destroy.form(businessUnit)}
                                            blockedReason={
                                                businessUnit.requisitions_count >
                                                0
                                                    ? `No se puede eliminar: aparece en ${formatRequisitionCount(businessUnit.requisitions_count)}.`
                                                    : undefined
                                            }
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

function BusinessUnitForm({
    businessUnit,
    onCancel,
    onSaved,
}: {
    businessUnit?: BusinessUnit;
    onCancel: () => void;
    onSaved: () => void;
}) {
    const isEditing = businessUnit !== undefined;

    return (
        <Card>
            <CardHeader>
                <h3 id="business-unit-form-title" className="font-semibold">
                    {isEditing
                        ? 'Editar unidad de negocio'
                        : 'Nueva unidad de negocio'}
                </h3>
            </CardHeader>
            <CardContent>
                <Form
                    {...(isEditing ? update.form(businessUnit) : store.form())}
                    id="business-unit-form"
                    aria-labelledby="business-unit-form-title"
                    options={{ preserveScroll: true }}
                    resetOnSuccess={!isEditing}
                    onSuccess={isEditing ? onSaved : undefined}
                    className="grid gap-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="business-unit-code">
                                    Clave
                                </Label>
                                <Input
                                    id="business-unit-code"
                                    name="code"
                                    defaultValue={businessUnit?.code}
                                    maxLength={10}
                                    required
                                    autoFocus={isEditing}
                                    autoComplete="off"
                                    className="font-mono uppercase"
                                    placeholder="SALT"
                                    disabled={processing}
                                    aria-invalid={Boolean(errors.code)}
                                    aria-describedby={
                                        errors.code
                                            ? 'business-unit-code-error'
                                            : undefined
                                    }
                                />
                                <InputError
                                    id="business-unit-code-error"
                                    message={errors.code}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="business-unit-name">
                                    Nombre
                                </Label>
                                <Input
                                    id="business-unit-name"
                                    name="name"
                                    defaultValue={businessUnit?.name}
                                    maxLength={100}
                                    required
                                    autoComplete="off"
                                    placeholder="Saltillo"
                                    disabled={processing}
                                    aria-invalid={Boolean(errors.name)}
                                    aria-describedby={
                                        errors.name
                                            ? 'business-unit-name-error'
                                            : undefined
                                    }
                                />
                                <InputError
                                    id="business-unit-name-error"
                                    message={errors.name}
                                />
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
                                        : 'Crear unidad de negocio'}
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </CardContent>
        </Card>
    );
}

BusinessUnitsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Unidades de negocio',
            href: index(),
        },
    ],
};

import { Form, Head } from '@inertiajs/react';
import { Pencil } from 'lucide-react';
import { useState } from 'react';
import {
    destroy,
    store,
    update,
} from '@/actions/App/Http/Controllers/DepartmentController';
import { DeleteCatalogItemDialog } from '@/components/delete-catalog-item-dialog';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatRequisitionCount } from '@/lib/catalogs';
import { index } from '@/routes/departments';
import type { Department } from '@/types';

export default function DepartmentsIndex({
    departments,
}: {
    departments: Department[];
}) {
    const [editingDepartmentId, setEditingDepartmentId] = useState<
        number | null
    >(null);
    const editingDepartment = departments.find(
        (department) => department.id === editingDepartmentId,
    );

    return (
        <>
            <Head title="Departamentos" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Departamentos"
                    description="Áreas a las que se asigna cada requisición."
                />

                <div className="grid min-w-0 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,22rem)]">
                    <DepartmentList
                        departments={departments}
                        editingDepartmentId={editingDepartmentId}
                        onEdit={setEditingDepartmentId}
                    />
                    <DepartmentForm
                        key={editingDepartment?.id ?? 'create'}
                        department={editingDepartment}
                        onCancel={() => setEditingDepartmentId(null)}
                        onSaved={() =>
                            setEditingDepartmentId((currentId) =>
                                currentId === editingDepartment?.id
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

function DepartmentList({
    departments,
    editingDepartmentId,
    onEdit,
}: {
    departments: Department[];
    editingDepartmentId: number | null;
    onEdit: (departmentId: number) => void;
}) {
    return (
        <div className="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            {departments.length === 0 ? (
                <p className="p-8 text-center text-sm text-muted-foreground">
                    Todavía no hay departamentos.
                </p>
            ) : (
                <table
                    aria-label="Listado de departamentos"
                    className="w-full text-sm"
                >
                    <thead className="bg-muted/50 text-left text-muted-foreground">
                        <tr>
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
                        {departments.map((department) => (
                            <tr
                                key={department.id}
                                data-editing={
                                    department.id === editingDepartmentId
                                }
                                className="data-[editing=true]:bg-muted/50"
                            >
                                <td className="px-4 py-3 font-medium wrap-anywhere">
                                    {department.name}
                                </td>
                                <td className="px-4 py-3 text-right tabular-nums">
                                    {department.requisitions_count}
                                </td>
                                <td className="px-4 py-2">
                                    <div className="flex justify-end gap-1">
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon"
                                            aria-controls="department-form"
                                            onClick={() =>
                                                onEdit(department.id)
                                            }
                                        >
                                            <Pencil />
                                            <span className="sr-only">
                                                Editar {department.name}
                                            </span>
                                        </Button>
                                        <DeleteCatalogItemDialog
                                            itemName={department.name}
                                            description="El departamento se borrará del catálogo. Esta acción no se puede deshacer."
                                            form={destroy.form(department)}
                                            blockedReason={
                                                department.requisitions_count >
                                                0
                                                    ? `No se puede eliminar: aparece en ${formatRequisitionCount(department.requisitions_count)}.`
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

function DepartmentForm({
    department,
    onCancel,
    onSaved,
}: {
    department?: Department;
    onCancel: () => void;
    onSaved: () => void;
}) {
    const isEditing = department !== undefined;

    return (
        <Card>
            <CardHeader>
                <h3 id="department-form-title" className="font-semibold">
                    {isEditing ? 'Editar departamento' : 'Nuevo departamento'}
                </h3>
            </CardHeader>
            <CardContent>
                <Form
                    {...(isEditing ? update.form(department) : store.form())}
                    id="department-form"
                    aria-labelledby="department-form-title"
                    options={{ preserveScroll: true }}
                    resetOnSuccess={!isEditing}
                    onSuccess={isEditing ? onSaved : undefined}
                    className="grid gap-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="department-name">Nombre</Label>
                                <Input
                                    id="department-name"
                                    name="name"
                                    defaultValue={department?.name}
                                    maxLength={100}
                                    required
                                    autoFocus={isEditing}
                                    autoComplete="off"
                                    disabled={processing}
                                    aria-invalid={Boolean(errors.name)}
                                    aria-describedby={
                                        errors.name
                                            ? 'department-name-error'
                                            : undefined
                                    }
                                />
                                <InputError
                                    id="department-name-error"
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
                                        : 'Crear departamento'}
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </CardContent>
        </Card>
    );
}

DepartmentsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Departamentos',
            href: index(),
        },
    ],
};

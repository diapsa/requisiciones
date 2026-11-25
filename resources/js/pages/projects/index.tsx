import { Form, Head } from '@inertiajs/react';
import { Pencil } from 'lucide-react';
import { useState } from 'react';
import {
    destroy,
    store,
    update,
} from '@/actions/App/Http/Controllers/ProjectController';
import { DeleteCatalogItemDialog } from '@/components/delete-catalog-item-dialog';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatRequisitionCount } from '@/lib/catalogs';
import { index } from '@/routes/projects';
import type { Project } from '@/types';

export default function ProjectsIndex({ projects }: { projects: Project[] }) {
    const [editingProjectId, setEditingProjectId] = useState<number | null>(
        null,
    );
    const editingProject = projects.find(
        (project) => project.id === editingProjectId,
    );

    return (
        <>
            <Head title="Proyectos" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Proyectos"
                    description="Clientes o servicios a los que se cargan las requisiciones. Su clave va en el folio (089_SALT_SAN_MARCOS)."
                />

                <div className="grid min-w-0 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,22rem)]">
                    <ProjectList
                        projects={projects}
                        editingProjectId={editingProjectId}
                        onEdit={setEditingProjectId}
                    />
                    <ProjectForm
                        key={editingProject?.id ?? 'create'}
                        project={editingProject}
                        onCancel={() => setEditingProjectId(null)}
                        onSaved={() =>
                            setEditingProjectId((currentId) =>
                                currentId === editingProject?.id
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

function ProjectList({
    projects,
    editingProjectId,
    onEdit,
}: {
    projects: Project[];
    editingProjectId: number | null;
    onEdit: (projectId: number) => void;
}) {
    return (
        <div className="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            {projects.length === 0 ? (
                <p className="p-8 text-center text-sm text-muted-foreground">
                    Todavía no hay proyectos.
                </p>
            ) : (
                <table
                    aria-label="Listado de proyectos"
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
                        {projects.map((project) => (
                            <tr
                                key={project.id}
                                data-editing={project.id === editingProjectId}
                                className="data-[editing=true]:bg-muted/50"
                            >
                                <td className="px-4 py-3 font-mono">
                                    {project.code}
                                </td>
                                <td className="px-4 py-3 font-medium wrap-anywhere">
                                    {project.name}
                                </td>
                                <td className="px-4 py-3 text-right tabular-nums">
                                    {project.requisitions_count}
                                </td>
                                <td className="px-4 py-2">
                                    <div className="flex justify-end gap-1">
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon"
                                            aria-controls="project-form"
                                            onClick={() => onEdit(project.id)}
                                        >
                                            <Pencil />
                                            <span className="sr-only">
                                                Editar {project.name}
                                            </span>
                                        </Button>
                                        <DeleteCatalogItemDialog
                                            itemName={project.name}
                                            description="El proyecto se borrará del catálogo. Esta acción no se puede deshacer."
                                            form={destroy.form(project)}
                                            blockedReason={
                                                project.requisitions_count > 0
                                                    ? `No se puede eliminar: aparece en ${formatRequisitionCount(project.requisitions_count)}.`
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

function ProjectForm({
    project,
    onCancel,
    onSaved,
}: {
    project?: Project;
    onCancel: () => void;
    onSaved: () => void;
}) {
    const isEditing = project !== undefined;

    return (
        <Card>
            <CardHeader>
                <h3 id="project-form-title" className="font-semibold">
                    {isEditing ? 'Editar proyecto' : 'Nuevo proyecto'}
                </h3>
            </CardHeader>
            <CardContent>
                <Form
                    {...(isEditing ? update.form(project) : store.form())}
                    id="project-form"
                    aria-labelledby="project-form-title"
                    options={{ preserveScroll: true }}
                    resetOnSuccess={!isEditing}
                    onSuccess={isEditing ? onSaved : undefined}
                    className="grid gap-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="project-code">Clave</Label>
                                <Input
                                    id="project-code"
                                    name="code"
                                    defaultValue={project?.code}
                                    maxLength={50}
                                    required
                                    autoFocus={isEditing}
                                    autoComplete="off"
                                    className="font-mono uppercase"
                                    placeholder="SAN_MARCOS"
                                    disabled={processing}
                                    aria-invalid={Boolean(errors.code)}
                                    aria-describedby={
                                        errors.code
                                            ? 'project-code-error'
                                            : undefined
                                    }
                                />
                                <InputError
                                    id="project-code-error"
                                    message={errors.code}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="project-name">Nombre</Label>
                                <Input
                                    id="project-name"
                                    name="name"
                                    defaultValue={project?.name}
                                    maxLength={150}
                                    required
                                    autoComplete="off"
                                    placeholder="San Marcos"
                                    disabled={processing}
                                    aria-invalid={Boolean(errors.name)}
                                    aria-describedby={
                                        errors.name
                                            ? 'project-name-error'
                                            : undefined
                                    }
                                />
                                <InputError
                                    id="project-name-error"
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
                                        : 'Crear proyecto'}
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </CardContent>
        </Card>
    );
}

ProjectsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Proyectos',
            href: index(),
        },
    ],
};

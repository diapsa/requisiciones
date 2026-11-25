import { Form, Head } from '@inertiajs/react';
import { Pencil } from 'lucide-react';
import { useState } from 'react';
import {
    destroy,
    store,
    update,
} from '@/actions/App/Http/Controllers/VehicleController';
import { DeleteCatalogItemDialog } from '@/components/delete-catalog-item-dialog';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatNumber, formatRequisitionCount } from '@/lib/catalogs';
import { index } from '@/routes/vehicles';
import type { Vehicle } from '@/types';

export default function VehiclesIndex({ vehicles }: { vehicles: Vehicle[] }) {
    const [editingVehicleId, setEditingVehicleId] = useState<number | null>(
        null,
    );
    const editingVehicle = vehicles.find(
        (vehicle) => vehicle.id === editingVehicleId,
    );

    return (
        <>
            <Head title="Vehículos" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Vehículos"
                    description="Unidades para calcular la gasolina de los traslados con su tanque y rendimiento."
                />

                <div className="grid min-w-0 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,22rem)]">
                    <VehicleList
                        vehicles={vehicles}
                        editingVehicleId={editingVehicleId}
                        onEdit={setEditingVehicleId}
                    />
                    <VehicleForm
                        key={editingVehicle?.id ?? 'create'}
                        vehicle={editingVehicle}
                        onCancel={() => setEditingVehicleId(null)}
                        onSaved={() =>
                            setEditingVehicleId((currentId) =>
                                currentId === editingVehicle?.id
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

function VehicleList({
    vehicles,
    editingVehicleId,
    onEdit,
}: {
    vehicles: Vehicle[];
    editingVehicleId: number | null;
    onEdit: (vehicleId: number) => void;
}) {
    return (
        <div className="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            {vehicles.length === 0 ? (
                <p className="p-8 text-center text-sm text-muted-foreground">
                    Todavía no hay vehículos.
                </p>
            ) : (
                <table
                    aria-label="Listado de vehículos"
                    className="w-full text-sm"
                >
                    <thead className="bg-muted/50 text-left text-muted-foreground">
                        <tr>
                            <th scope="col" className="px-4 py-3 font-medium">
                                Vehículo
                            </th>
                            <th
                                scope="col"
                                className="px-4 py-3 text-right font-medium"
                            >
                                Tanque
                            </th>
                            <th
                                scope="col"
                                className="px-4 py-3 text-right font-medium"
                            >
                                Rendimiento
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
                        {vehicles.map((vehicle) => (
                            <tr
                                key={vehicle.id}
                                data-editing={vehicle.id === editingVehicleId}
                                className="data-[editing=true]:bg-muted/50"
                            >
                                <td className="px-4 py-3">
                                    <div className="font-medium wrap-anywhere">
                                        {vehicle.model}
                                    </div>
                                    <div className="font-mono text-xs text-muted-foreground">
                                        {vehicle.plate_number ?? 'Sin placas'}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-right whitespace-nowrap tabular-nums">
                                    {formatNumber(vehicle.tank_capacity_liters)}{' '}
                                    L
                                </td>
                                <td className="px-4 py-3 text-right whitespace-nowrap tabular-nums">
                                    {formatNumber(vehicle.km_per_liter)} km/L
                                </td>
                                <td className="px-4 py-3">
                                    <Badge
                                        variant={
                                            vehicle.is_active
                                                ? 'secondary'
                                                : 'outline'
                                        }
                                    >
                                        {vehicle.is_active
                                            ? 'Activo'
                                            : 'Inactivo'}
                                    </Badge>
                                </td>
                                <td className="px-4 py-3 text-right tabular-nums">
                                    {vehicle.requisitions_count}
                                </td>
                                <td className="px-4 py-2">
                                    <div className="flex justify-end gap-1">
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon"
                                            aria-controls="vehicle-form"
                                            onClick={() => onEdit(vehicle.id)}
                                        >
                                            <Pencil />
                                            <span className="sr-only">
                                                Editar {vehicle.model}
                                            </span>
                                        </Button>
                                        <DeleteCatalogItemDialog
                                            itemName={vehicle.model}
                                            description="El vehículo se borrará del catálogo. Esta acción no se puede deshacer."
                                            form={destroy.form(vehicle)}
                                            blockedReason={
                                                vehicle.requisitions_count > 0
                                                    ? `No se puede eliminar: aparece en ${formatRequisitionCount(vehicle.requisitions_count)}. Desactívalo en su lugar.`
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

function VehicleForm({
    vehicle,
    onCancel,
    onSaved,
}: {
    vehicle?: Vehicle;
    onCancel: () => void;
    onSaved: () => void;
}) {
    const isEditing = vehicle !== undefined;

    return (
        <Card>
            <CardHeader>
                <h3 id="vehicle-form-title" className="font-semibold">
                    {isEditing ? 'Editar vehículo' : 'Nuevo vehículo'}
                </h3>
            </CardHeader>
            <CardContent>
                <Form
                    {...(isEditing ? update.form(vehicle) : store.form())}
                    id="vehicle-form"
                    aria-labelledby="vehicle-form-title"
                    options={{ preserveScroll: true }}
                    resetOnSuccess={!isEditing}
                    onSuccess={isEditing ? onSaved : undefined}
                    className="grid gap-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="vehicle-model">Modelo</Label>
                                <Input
                                    id="vehicle-model"
                                    name="model"
                                    defaultValue={vehicle?.model}
                                    maxLength={100}
                                    required
                                    autoFocus={isEditing}
                                    autoComplete="off"
                                    placeholder="Nissan NP300"
                                    disabled={processing}
                                    aria-invalid={Boolean(errors.model)}
                                    aria-describedby={
                                        errors.model
                                            ? 'vehicle-model-error'
                                            : undefined
                                    }
                                />
                                <InputError
                                    id="vehicle-model-error"
                                    message={errors.model}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="vehicle-plate-number">
                                    Placas{' '}
                                    <span className="font-normal text-muted-foreground">
                                        (opcional)
                                    </span>
                                </Label>
                                <Input
                                    id="vehicle-plate-number"
                                    name="plate_number"
                                    defaultValue={
                                        vehicle?.plate_number ?? undefined
                                    }
                                    maxLength={20}
                                    autoComplete="off"
                                    className="font-mono uppercase"
                                    disabled={processing}
                                    aria-invalid={Boolean(errors.plate_number)}
                                    aria-describedby={
                                        errors.plate_number
                                            ? 'vehicle-plate-number-error'
                                            : undefined
                                    }
                                />
                                <InputError
                                    id="vehicle-plate-number-error"
                                    message={errors.plate_number}
                                />
                            </div>

                            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                                <div className="grid content-start gap-2">
                                    <Label htmlFor="vehicle-tank-capacity">
                                        Tanque (litros)
                                    </Label>
                                    <Input
                                        id="vehicle-tank-capacity"
                                        name="tank_capacity_liters"
                                        type="number"
                                        inputMode="decimal"
                                        step="0.01"
                                        min="0.01"
                                        max="9999.99"
                                        defaultValue={
                                            vehicle?.tank_capacity_liters
                                        }
                                        required
                                        disabled={processing}
                                        aria-invalid={Boolean(
                                            errors.tank_capacity_liters,
                                        )}
                                        aria-describedby={
                                            errors.tank_capacity_liters
                                                ? 'vehicle-tank-capacity-error'
                                                : undefined
                                        }
                                    />
                                    <InputError
                                        id="vehicle-tank-capacity-error"
                                        message={errors.tank_capacity_liters}
                                    />
                                </div>

                                <div className="grid content-start gap-2">
                                    <Label htmlFor="vehicle-km-per-liter">
                                        Rendimiento (km/L)
                                    </Label>
                                    <Input
                                        id="vehicle-km-per-liter"
                                        name="km_per_liter"
                                        type="number"
                                        inputMode="decimal"
                                        step="0.01"
                                        min="0.01"
                                        max="9999.99"
                                        defaultValue={vehicle?.km_per_liter}
                                        required
                                        disabled={processing}
                                        aria-invalid={Boolean(
                                            errors.km_per_liter,
                                        )}
                                        aria-describedby={
                                            errors.km_per_liter
                                                ? 'vehicle-km-per-liter-error'
                                                : undefined
                                        }
                                    />
                                    <InputError
                                        id="vehicle-km-per-liter-error"
                                        message={errors.km_per_liter}
                                    />
                                </div>
                            </div>

                            <div className="flex items-start gap-3">
                                <Checkbox
                                    id="vehicle-is-active"
                                    name="is_active"
                                    value="1"
                                    defaultChecked={vehicle?.is_active ?? true}
                                    disabled={processing}
                                    aria-describedby="vehicle-is-active-hint"
                                />
                                <div className="grid gap-1">
                                    <Label htmlFor="vehicle-is-active">
                                        Activo
                                    </Label>
                                    <p
                                        id="vehicle-is-active-hint"
                                        className="text-sm text-muted-foreground"
                                    >
                                        Solo los vehículos activos se pueden
                                        elegir en una requisición.
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
                                        : 'Crear vehículo'}
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </CardContent>
        </Card>
    );
}

VehiclesIndex.layout = {
    breadcrumbs: [
        {
            title: 'Vehículos',
            href: index(),
        },
    ],
};

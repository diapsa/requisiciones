<?php

namespace Database\Seeders;

use App\Models\ExpenseType;
use Illuminate\Database\Seeder;

class ExpenseTypeSeeder extends Seeder
{
    /**
     * Initial catalog from docs/reglas-de-negocio.md#tipos-de-gasto, in screen order:
     * code, name, calculation method, unit and whether it can be requested in a requisition.
     *
     * @var list<array{string, string, string, string, bool}>
     */
    private const EXPENSE_TYPES = [
        ['fuel', 'Combustibles y lubricantes', 'fuel_by_distance', 'CARGA', true],
        ['tolls', 'Casetas', 'fixed', 'TICKET', true],
        ['meals', 'Comida', 'per_person_meals', 'CONSUMO', true],
        ['breakfast', 'Desayuno (despensa)', 'breakfast_reference', 'DESAYUNO', true],
        ['lodging', 'Hospedaje', 'per_night', 'NOCHE', true],
        ['materials', 'Materiales', 'fixed', 'ÚNICO', true],
        ['airfare', 'Boletos de avión', 'fixed', 'ÚNICO', false],
        ['bus_fare', 'Boletos de autobús', 'fixed', 'ÚNICO', false],
        ['taxi', 'Taxis', 'fixed', 'ÚNICO', false],
        ['parking', 'Estacionamiento', 'fixed', 'ÚNICO', false],
        ['office_supplies', 'Papelería', 'fixed', 'ÚNICO', false],
        ['courier', 'Mensajería', 'fixed', 'ÚNICO', false],
        ['other', 'Otros', 'fixed', 'ÚNICO', true],
    ];

    /**
     * Create the missing expense types. Safe to run more than once, and it keeps the administrator's edits.
     */
    public function run(): void
    {
        foreach (self::EXPENSE_TYPES as $index => [$code, $name, $calculationMethod, $defaultUnit, $isRequestable]) {
            ExpenseType::query()->firstOrCreate(['code' => $code], [
                'name' => $name,
                'calculation_method' => $calculationMethod,
                'default_unit' => $defaultUnit,
                'is_requestable' => $isRequestable,
                'sort_order' => $index + 1,
            ]);
        }
    }
}

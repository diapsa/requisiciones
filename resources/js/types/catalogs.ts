export type Department = {
    id: number;
    name: string;
    requisitions_count: number;
};

export type BusinessUnit = {
    id: number;
    code: string;
    name: string;
    requisitions_count: number;
};

export type Project = {
    id: number;
    code: string;
    name: string;
    requisitions_count: number;
};

export type Vehicle = {
    id: number;
    model: string;
    plate_number: string | null;
    tank_capacity_liters: string;
    km_per_liter: string;
    is_active: boolean;
    requisitions_count: number;
};

export type CalculationMethod =
    | 'fuel_by_distance'
    | 'per_person_meals'
    | 'breakfast_reference'
    | 'per_night'
    | 'fixed';

export type ExpenseType = {
    id: number;
    code: string;
    name: string;
    calculation_method: CalculationMethod;
    default_unit: string;
    is_requestable: boolean;
    sort_order: number;
    expense_rates_count: number;
    requisition_items_count: number;
    expenses_count: number;
};

export type ExpenseTypeOption = Pick<ExpenseType, 'id' | 'name'>;

export type ExpenseRate = {
    id: number;
    expense_type_id: number;
    name: string;
    amount: string;
    valid_from: string;
    valid_until: string | null;
    expense_type: ExpenseTypeOption;
};

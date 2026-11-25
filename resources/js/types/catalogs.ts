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

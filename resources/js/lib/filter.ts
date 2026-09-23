import type { FurnitureFilterData } from '@/types';

export type FilterNumeric = {
    price_min: string;
    price_max: string;
    width_min: string;
    width_max: string;
    height_min: string;
    height_max: string;
    length_min: string;
    length_max: string;
};

export type FilterMulti = {
    foldingType: string[];
    surface: string[];
    materials: string[];
    manufacture: string[];
};

export type FilterValue = {
    numeric: FilterNumeric;
    multi: FilterMulti;
};

export type FilterParams = Partial<{
    price_min: number;
    price_max: number;
    width_min: number;
    width_max: number;
    height_min: number;
    height_max: number;
    length_min: number;
    length_max: number;
    foldingType: string[];
    surface: string[];
    materials: string[];
    manufacture: string[];
}>;

const EMPTY_NUMERIC: FilterNumeric = {
    price_min: '',
    price_max: '',
    width_min: '',
    width_max: '',
    height_min: '',
    height_max: '',
    length_min: '',
    length_max: '',
};

const EMPTY_MULTI: FilterMulti = {
    foldingType: [],
    surface: [],
    materials: [],
    manufacture: [],
};

export function fromActive(active: FurnitureFilterData | null): FilterValue {
    const numeric: FilterNumeric = {
        price_min: active?.price_min?.toString() ?? '',
        price_max: active?.price_max?.toString() ?? '',
        width_min: active?.width_min?.toString() ?? '',
        width_max: active?.width_max?.toString() ?? '',
        height_min: active?.height_min?.toString() ?? '',
        height_max: active?.height_max?.toString() ?? '',
        length_min: active?.length_min?.toString() ?? '',
        length_max: active?.length_max?.toString() ?? '',
    };
    const multi: FilterMulti = {
        foldingType: active?.foldingType?.map(String) ?? [],
        surface: active?.surface?.map(String) ?? [],
        materials: active?.materials?.map(String) ?? [],
        manufacture: active?.manufacture?.map(String) ?? [],
    };

    return { numeric, multi };
}

export function toQuery(
    numeric: FilterNumeric,
    multi: FilterMulti,
): FilterParams {
    const filter: FilterParams = {};

    if (numeric.price_min !== '') {
        filter.price_min = Number(numeric.price_min);
    }
    if (numeric.price_max !== '') {
        filter.price_max = Number(numeric.price_max);
    }
    if (numeric.width_min !== '') {
        filter.width_min = Number(numeric.width_min);
    }
    if (numeric.width_max !== '') {
        filter.width_max = Number(numeric.width_max);
    }
    if (numeric.height_min !== '') {
        filter.height_min = Number(numeric.height_min);
    }
    if (numeric.height_max !== '') {
        filter.height_max = Number(numeric.height_max);
    }
    if (numeric.length_min !== '') {
        filter.length_min = Number(numeric.length_min);
    }
    if (numeric.length_max !== '') {
        filter.length_max = Number(numeric.length_max);
    }
    if (multi.foldingType.length > 0) {
        filter.foldingType = multi.foldingType;
    }
    if (multi.surface.length > 0) {
        filter.surface = multi.surface;
    }
    if (multi.materials.length > 0) {
        filter.materials = multi.materials;
    }
    if (multi.manufacture.length > 0) {
        filter.manufacture = multi.manufacture;
    }

    return filter;
}

export function emptyFilterValue(): FilterValue {
    return { numeric: { ...EMPTY_NUMERIC }, multi: { ...EMPTY_MULTI } };
}

export function toggleListItem<T>(list: T[], value: T): T[] {
    return list.includes(value)
        ? list.filter((item) => item !== value)
        : [...list, value];
}

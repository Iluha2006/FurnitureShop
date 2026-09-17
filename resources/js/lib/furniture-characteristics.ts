import type { FurnitureSpecificationData } from '@/types';

export function buildCharacteristics(
    specifications: FurnitureSpecificationData | null,
): Record<string, string | null> {
    return {
        'Ширина, см': formatNumber(specifications?.width_cm),
        'Длина, см': formatNumber(specifications?.length_cm),
        'Высота, см': formatNumber(specifications?.height_cm),
        'Механизм': specifications?.folding_type ?? null,
        'Вставки': specifications?.insert_type ?? null,
        'Материалы': specifications?.materials ?? null,
        'Покрытие': specifications?.surface ?? null,
        'Вес, кг': formatNumber(specifications?.weight_kg),
        'Объём упаковки, м³': formatNumber(specifications?.package_volume_m3),
        'Гарантия': specifications?.warranty ?? null,
    };
}



function formatNumber(value: number | null | undefined): string | null {
    return value === null || value === undefined ? null : String(value).replace('.', ',');
}
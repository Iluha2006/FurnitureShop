import { router } from '@inertiajs/react';
import { useState, type FormEvent } from 'react';
import { furniture as catalogFilterRoute } from '@/routes/catalog/category';
import CheckboxGroup from '@/components/filter/checkbox-group';
import RangeField from '@/components/filter/range-field';
import {
    emptyFilterValue,
    fromActive,
    toQuery,
    toggleListItem,
    type FilterValue,
} from '@/lib/filter';
import type { FurnitureFilterData, FurnitureFilterOptionsData } from '@/types';

type Props = {
    categoryId: number;
    options: FurnitureFilterOptionsData;
    active: FurnitureFilterData | null;
};

export default function CatalogFilter({ categoryId, options, active }: Props) {
    const initial = fromActive(active);
    const [numeric, setNumeric] = useState(initial.numeric);
    const [multi, setMulti] = useState(initial.multi);

    function setNumericField(
        field: keyof FilterValue['numeric'],
        value: string,
    ) {
        setNumeric((current) => ({ ...current, [field]: value }));
    }

    function toggleGroup(group: keyof FilterValue['multi'], value: string) {
        setMulti((current) => ({
            ...current,
            [group]: toggleListItem(current[group], value),
        }));
    }

    function apply(event: FormEvent) {
        event.preventDefault();

        const params = {
            filter: toQuery(numeric, multi),
            page: 1,
        };

        router.get(
            catalogFilterRoute({ category_id: categoryId }).url,
            params,
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    }

    function reset() {
        const empty = emptyFilterValue();
        setNumeric(empty.numeric);
        setMulti(empty.multi);

        router.get(
            catalogFilterRoute({ category_id: categoryId }).url,
            { filter: {}, page: 1 },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    }

    const price = options.price_range;
    const widths = options.widthRangeData;
    const heights = options.heightRangeData;
    const lengths = options.lengthRangeData;

    return (
        <aside className="filter" aria-label="Фильтр товаров">
            <h2 className="filter__title">Фильтр</h2>

            <form onSubmit={apply}>
                <RangeField
                    label="Цена, ₽"
                    minValue={numeric.price_min}
                    maxValue={numeric.price_max}
                    minPlaceholder={String(Math.round(price.min_cm))}
                    maxPlaceholder={String(Math.round(price.max_cm))}
                    onMinChange={(value) => setNumericField('price_min', value)}
                    onMaxChange={(value) => setNumericField('price_max', value)}
                />

                <RangeField
                    label="Ширина, см"
                    minValue={numeric.width_min}
                    maxValue={numeric.width_max}
                    minPlaceholder={String(Math.round(widths.min_cm))}
                    maxPlaceholder={String(Math.round(widths.max_cm))}
                    onMinChange={(value) => setNumericField('width_min', value)}
                    onMaxChange={(value) => setNumericField('width_max', value)}
                />

                <RangeField
                    label="Высота, см"
                    minValue={numeric.height_min}
                    maxValue={numeric.height_max}
                    minPlaceholder={String(Math.round(heights.min_cm))}
                    maxPlaceholder={String(Math.round(heights.max_cm))}
                    onMinChange={(value) =>
                        setNumericField('height_min', value)
                    }
                    onMaxChange={(value) =>
                        setNumericField('height_max', value)
                    }
                />

                <RangeField
                    label="Длина, см"
                    minValue={numeric.length_min}
                    maxValue={numeric.length_max}
                    minPlaceholder={String(Math.round(lengths.min_cm))}
                    maxPlaceholder={String(Math.round(lengths.max_cm))}
                    onMinChange={(value) =>
                        setNumericField('length_min', value)
                    }
                    onMaxChange={(value) =>
                        setNumericField('length_max', value)
                    }
                />

                <CheckboxGroup
                    label="Производитель"
                    options={options.manufacture}
                    selected={multi.manufacture}
                    onToggle={(value) => toggleGroup('manufacture', value)}
                />

                <CheckboxGroup
                    label="Механизм"
                    options={options.folding_type}
                    selected={multi.foldingType}
                    onToggle={(value) => toggleGroup('foldingType', value)}
                />

                <CheckboxGroup
                    label="Покрытие"
                    options={options.surface}
                    selected={multi.surface}
                    onToggle={(value) => toggleGroup('surface', value)}
                />

                <CheckboxGroup
                    label="Материалы"
                    options={options.materials}
                    selected={multi.materials}
                    onToggle={(value) => toggleGroup('materials', value)}
                />

                <div className="filter__actions">
                    <button
                        type="submit"
                        className="btn btn--primary filter__apply"
                    >
                        Применить
                    </button>
                    <button
                        type="button"
                        className="filter__reset"
                        onClick={reset}
                    >
                        Сбросить фильтры
                    </button>
                </div>
            </form>
        </aside>
    );
}

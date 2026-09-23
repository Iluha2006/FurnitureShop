import CheckboxGroup from '@/components/filter/checkbox-group';
import type { FilterOptionData } from '@/types';

type Props = {
    options: FilterOptionData[];
    selected: string[];
    onToggle: (value: string) => void;
    onReset: () => void;
};

export default function CategoryFilter({
    options,
    selected,
    onToggle,
    onReset,
}: Props) {
    return (
        <aside className="filter" aria-label="Фильтр категорий">
            <h2 className="filter__title">Фильтр</h2>

            <CheckboxGroup
                label="Категория"
                options={options}
                selected={selected}
                onToggle={onToggle}
            />

            <div className="filter__actions">
                <button
                    type="button"
                    className="filter__reset"
                    onClick={onReset}
                >
                    Сбросить фильтры
                </button>
            </div>
        </aside>
    );
}

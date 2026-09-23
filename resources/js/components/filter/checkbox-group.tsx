import type { FilterOptionData } from '@/types';

type Props = {
    label: string;
    options: FilterOptionData[];
    selected: string[];
    onToggle: (value: string) => void;
};

export default function CheckboxGroup({
    label,
    options,
    selected,
    onToggle,
}: Props) {
    if (options.length === 0) {
        return null;
    }

    return (
        <div className="filter__group">
            <h3 className="filter__group-title">{label}</h3>
            <ul className="filter__list">
                {options.map((option) => (
                    <li key={option.value}>
                        <label className="checkbox">
                            <input
                                type="checkbox"
                                checked={selected.includes(option.value)}
                                onChange={() => onToggle(option.value)}
                            />
                            <span className="checkbox__box" aria-hidden="true">
                                <svg
                                    width="11"
                                    height="11"
                                    viewBox="0 0 12 12"
                                    fill="none"
                                >
                                    <path
                                        d="M2.5 6.5 5 9l4.5-6"
                                        strokeWidth="1.8"
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                    />
                                </svg>
                            </span>
                            <span className="checkbox__label">
                                {option.label}
                            </span>
                        </label>
                    </li>
                ))}
            </ul>
        </div>
    );
}

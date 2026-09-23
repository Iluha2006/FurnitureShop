type Props = {
    label: string;
    minValue: string;
    maxValue: string;
    minPlaceholder: string;
    maxPlaceholder: string;
    onMinChange: (value: string) => void;
    onMaxChange: (value: string) => void;
};

function sanitizeNumeric(value: string): string {
    if (value === '') {
        return '';
    }

    const number = Number(value);

    return Number.isFinite(number) && number >= 0 ? value : '';
}

export default function RangeField({
    label,
    minValue,
    maxValue,
    minPlaceholder,
    maxPlaceholder,
    onMinChange,
    onMaxChange,
}: Props) {
    return (
        <div className="filter__group">
            <h3 className="filter__group-title">{label}</h3>
            <div className="filter__price">
                <label>
                    <span>от</span>
                    <input
                        type="number"
                        min={0}
                        step={1}
                        value={minValue}
                        placeholder={minPlaceholder}
                        onChange={(event) =>
                            onMinChange(sanitizeNumeric(event.target.value))
                        }
                    />
                </label>
                <label>
                    <span>до</span>
                    <input
                        type="number"
                        min={0}
                        step={1}
                        value={maxValue}
                        placeholder={maxPlaceholder}
                        onChange={(event) =>
                            onMaxChange(sanitizeNumeric(event.target.value))
                        }
                    />
                </label>
            </div>
        </div>
    );
}

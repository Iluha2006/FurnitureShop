import type { FormEvent } from 'react';

type SearchFormProps = {
    query: string;
    onChange: (value: string) => void;
    onFocus: () => void;
    onBlur: () => void;
    onEscape: () => void;
};

export default function SearchForm({
    query,
    onChange,
    onFocus,
    onBlur,
    onEscape,
}: SearchFormProps) {
    function onSubmit(event: FormEvent) {
        event.preventDefault();
    }

    return (
        <form
            onSubmit={onSubmit}
            className="header__search"
            role="search"
            aria-label="Поиск по каталогу"
        >
            <svg
                width="18"
                height="18"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
                aria-hidden="true"
            >
                <circle cx="11" cy="11" r="8" />
                <path d="m21 21-4.35-4.35" />
            </svg>
            <input
                className="header__search-input"
                type="search"
                name="q"
                value={query}
                onChange={(event) => onChange(event.target.value)}
                onFocus={onFocus}
                onBlur={onBlur}
                onKeyDown={(event) => {
                    if (event.key === 'Escape') {
                        event.preventDefault();
                        onEscape();
                        event.currentTarget.blur();
                    }
                }}
                placeholder="Поиск по каталогу"
            />
        </form>
    );
}
import { useState } from 'react';
import { useSearch } from '@/hooks/use-search';
import SearchForm from '@/components/search-form';
import SearchSuggestions from '@/components/search-suggestions';

export default function HeaderSearch() {
    const { query, setQuery, categories, items, loading, reset } = useSearch();
    const [open, setOpen] = useState(false);

    const showSuggestions = open && query.trim() !== '';

    function hide() {
        window.setTimeout(() => setOpen(false), 120);
    }

    function select() {
        reset();
        setOpen(false);
    }

    return (
        <div className="header__search-wrap">
            <SearchForm
                query={query}
                onChange={(value) => {
                    setQuery(value);
                    setOpen(true);
                }}
                onFocus={() => setOpen(true)}
                onBlur={hide}
                onEscape={() => setOpen(false)}
            />

            {showSuggestions && (
                <SearchSuggestions
                    query={query}
                    loading={loading}
                    categories={categories}
                    items={items}
                    onSelect={select}
                />
            )}
        </div>
    );
}

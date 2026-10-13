import { useEffect, useRef, useState } from 'react';
import { search as searchRoute } from '@/routes/api';
import type { FurnitureCardData } from '@/types';

const DEBOUNCE_MS = 220;

export type CategorySuggestion = {
    category_id: number;
    name: string;
};

export type SearchResponse = {
    categories: CategorySuggestion[];
    furniture: FurnitureCardData[];
};

export type UseSearchReturn = {
    query: string;
    setQuery: (query: string) => void;
    categories: CategorySuggestion[];
    items: FurnitureCardData[];
    loading: boolean;
    reset: () => void;
};

const EMPTY_RESULT: SearchResponse = {
    categories: [],
    furniture: [],
};

export function useSearch(): UseSearchReturn {
    const [query, setQuery] = useState('');
    const [result, setResult] = useState<SearchResponse>(EMPTY_RESULT);
    const [loading, setLoading] = useState(false);

    const debounceRef = useRef<number | null>(null);
    const abortRef = useRef<AbortController | null>(null);

    const trimmed = query.trim();

    useEffect(() => {
        if (debounceRef.current !== null) {
            window.clearTimeout(debounceRef.current);
        }
        abortRef.current?.abort();

        if (trimmed === '') {
            setResult(EMPTY_RESULT);
            setLoading(false);

            return;
        }

        setLoading(true);

        debounceRef.current = window.setTimeout(() => {
            const controller = new AbortController();
            abortRef.current = controller;

            fetch(`${searchRoute().url}?q=${encodeURIComponent(trimmed)}`, {
                signal: controller.signal,
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Search request failed.');
                    }

                    return response.json() as Promise<Partial<SearchResponse>>;
                })
                .then((data) => {
                    setResult({
                        categories: Array.isArray(data.categories)
                            ? data.categories
                            : [],
                        furniture: Array.isArray(data.furniture)
                            ? data.furniture
                            : [],
                    });
                })
                .catch((error: unknown) => {
                    if (
                        error instanceof DOMException &&
                        error.name === 'AbortError'
                    ) {
                        return;
                    }

                    setResult(EMPTY_RESULT);
                })
                .finally(() => setLoading(false));
        }, DEBOUNCE_MS);
    }, [trimmed]);

    useEffect(() => {
        return () => {
            if (debounceRef.current !== null) {
                window.clearTimeout(debounceRef.current);
            }
            abortRef.current?.abort();
        };
    }, []);

    const reset = (): void => setQuery('');

    return {
        query,
        setQuery,
        categories: result.categories,
        items: result.furniture,
        loading,
        reset,
    };
}

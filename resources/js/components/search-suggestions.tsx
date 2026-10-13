import { Link } from '@inertiajs/react';
import { category as catalogCategory } from '@/routes/catalog';
import { show as furnitureShow } from '@/routes/catalog/furniture';
import type { CategorySuggestion } from '@/hooks/use-search';
import type { FurnitureCardData } from '@/types';

type SearchSuggestionsProps = {
    query: string;
    loading: boolean;
    categories: CategorySuggestion[];
    items: FurnitureCardData[];
    onSelect: () => void;
};

function CategoriesList({
    categories,
    onSelect,
}: Pick<SearchSuggestionsProps, 'categories' | 'onSelect'>) {
    return (
        <>
            <p className="search-suggestions__label">Категории</p>
            <ul className="search-suggestions__list">
                {categories.map((category) => {
                    const categoryHref = catalogCategory({
                        category_id: category.category_id,
                    }).url;

                    return (
                        <li
                            key={`category-${category.category_id}`}
                            className="search-suggestions__item"
                        >
                            <Link
                                href={categoryHref}
                                className="search-suggestions__link"
                                onClick={onSelect}
                            >
                                <span className="search-suggestions__info">
                                    <span className="search-suggestions__name">
                                        {category.name}
                                    </span>
                                    <span className="search-suggestions__sub">
                                        Все товары раздела
                                    </span>
                                </span>
                            </Link>
                        </li>
                    );
                })}
            </ul>
        </>
    );
}

function FurnitureList({
    items,
    onSelect,
}: Pick<SearchSuggestionsProps, 'items' | 'onSelect'>) {
    return (
        <>
            <p className="search-suggestions__label">Мебель</p>
            <ul className="search-suggestions__list">
                {items.map((card) => {
                    const itemHref = furnitureShow({
                        furniture_id: card.furniture.furniture_id,
                    }).url;

                    return (
                        <li
                            key={card.furniture.furniture_id}
                            className="search-suggestions__item"
                        >
                            <Link
                                href={itemHref}
                                className="search-suggestions__link"
                                onClick={onSelect}
                            >
                                <span className="search-suggestions__info">
                                    <span className="search-suggestions__name">
                                        {card.furniture.name}
                                    </span>
                                    <span className="search-suggestions__sub">
                                        {card.manufacturer
                                            ? card.manufacturer.name
                                            : 'МебельДом'}
                                    </span>
                                </span>
                                <span className="search-suggestions__price">
                                    {Number(
                                        card.furniture.price,
                                    ).toLocaleString('ru-RU')}{' '}
                                    ₽
                                </span>
                            </Link>
                        </li>
                    );
                })}
            </ul>
        </>
    );
}

export default function SearchSuggestions({
    query,
    loading,
    categories,
    items,
    onSelect,
}: SearchSuggestionsProps) {
    const hasCategories = categories.length > 0;
    const hasItems = items.length > 0;
    const hasResults = hasCategories || hasItems;

    if (loading) {
        return (
            <div className="search-suggestions">
                <p className="search-suggestions__status">Поиск…</p>
            </div>
        );
    }

    if (!hasResults) {
        return (
            <div className="search-suggestions">
                <p className="search-suggestions__status">
                    Ничего не найдено по запросу «{query}».
                </p>
            </div>
        );
    }

    return (
        <div className="search-suggestions">
            {hasCategories && (
                <CategoriesList categories={categories} onSelect={onSelect} />
            )}
            {hasItems && <FurnitureList items={items} onSelect={onSelect} />}
        </div>
    );
}

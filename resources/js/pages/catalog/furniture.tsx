import { Head, Link } from '@inertiajs/react';
import { catalog } from '@/routes';
import { category as catalogCategory } from '@/routes/catalog';
import FurnitureCard from '@/components/FurnitureCard';
import type { FurnitureCardData, Paginator } from '@/types';

type Props = {
    categoryId: number;
    categories: { category_id: number; name: string }[];
    furniture: Paginator<FurnitureCardData>;
};

export default function FurnitureByCategory({ categoryId, categories, furniture }: Props) {
    const currentCategory = categories.find((category) => category.category_id === categoryId);

    return (
        <>
            <Head title={currentCategory ? currentCategory.name : 'Каталог'} />

            <div className="container">
                <nav className="breadcrumbs" aria-label="Хлебные крошки">
                    <Link href={catalog().url}>Каталог</Link>
                    <span className="breadcrumbs__sep">/</span>
                    <span>{currentCategory?.name ?? 'Категория'}</span>
                </nav>

                <section className="category__head">
                    <h1>
                        <span className="category__head-icon" aria-hidden="true">
                            ▦
                        </span>
                        {currentCategory?.name ?? 'Категория'}
                    </h1>
                    <p className="category__desc">
                        Мебель категории «{currentCategory?.name}» — в наличии.
                    </p>
                    <div className="category__stats">
                        <span>Найдено: {furniture.total}</span>
                    </div>
                </section>

                <div className="category__layout">
                    <aside className="sidebar" aria-label="Категории">
                        <h2 className="sidebar__title">Каталог</h2>
                        <nav className="sidebar__nav">
                            {categories.map((category) => {
                                const active = category.category_id === categoryId;

                                return (
                                    <Link
                                        key={category.category_id}
                                        href={catalogCategory({ category_id: category.category_id }).url}
                                        className={
                                            'sidebar__link' + (active ? ' sidebar__link--active' : '')
                                        }
                                    >
                                        <span className="sidebar__icon" aria-hidden="true">
                                            ›
                                        </span>
                                        <span className="sidebar__name">{category.name}</span>
                                        {active && <span className="sidebar__arrow">→</span>}
                                    </Link>
                                );
                            })}
                        </nav>
                    </aside>

                    <section className="catalog">
                        {furniture.data.length > 0 ? (
                            <div className="products-grid products-grid--catalog">
                                {furniture.data.map((card) => (
                                    <FurnitureCard key={card.furniture.furniture_id} card={card} />
                                ))}
                            </div>
                        ) : (
                            <p className="catalog__note">
                                В этой категории пока нет товаров в наличии.
                            </p>
                        )}

                        {furniture.last_page > 1 && (
                            <div className="catalog__more">
                                {furniture.prev_page_url ? (
                                    <a href={furniture.prev_page_url} className="btn btn--outline">
                                        ← Назад
                                    </a>
                                ) : null}
                                <span className="catalog-pagination">
                                    Страница {furniture.current_page} из {furniture.last_page}
                                </span>
                                {furniture.next_page_url ? (
                                    <a href={furniture.next_page_url} className="btn btn--primary">
                                        Далее →
                                    </a>
                                ) : null}
                            </div>
                        )}
                    </section>
                </div>
            </div>
        </>
    );
}
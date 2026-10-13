import { Head, Link } from '@inertiajs/react';
import { catalog } from '@/routes';
import { category as catalogCategory } from '@/routes/catalog';
import type { FurnitureCardData, FurnitureCategoryData } from '@/types';
import FurnitureCard from '@/components/FurnitureCard';
type Props = {
    categories: FurnitureCategoryData[];
    hits: FurnitureCardData[];
};

export default function Categories({ categories, hits }: Props) {
    return (
        <>
            <Head title="Каталог мебели" />
            <div className="home-layout container">
                <aside className="sidebar" aria-label="Категории мебели">
                    <h2 className="sidebar__title">Каталог мебели</h2>
                    <nav className="sidebar__nav">
                        {categories.map((category) => (
                            <Link
                                key={category.category_id}
                                href={
                                    catalogCategory({
                                        category_id: category.category_id,
                                    }).url
                                }
                                className="sidebar__link"
                            >
                                <span className="sidebar__name">
                                    {category.name}
                                </span>
                                <span className="sidebar__arrow">›</span>
                            </Link>
                        ))}
                    </nav>
                </aside>

                <div className="home-content">
                    <div className="section-head">
                        <h2>Хиты продаж</h2>
                        <Link
                            href={catalog().url}
                            className="section-head__all"
                        >
                            Весь каталог →
                        </Link>
                    </div>

                    {hits.length > 0 ? (
                        <section className="products-grid products-grid--home">
                            {hits.map((card) => (
                                <FurnitureCard
                                    key={card.furniture.furniture_id}
                                    card={card}
                                />
                            ))}
                        </section>
                    ) : (
                        <p className="catalog__note">
                            Товаров в наличии пока нет — загляните в каталог
                            позже.
                        </p>
                    )}
                </div>
            </div>
        </>
    );
}

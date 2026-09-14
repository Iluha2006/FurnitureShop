import { Head, Link } from '@inertiajs/react';
import { catalog } from '@/routes';
import { category as catalogCategory } from '@/routes/catalog';
import type { FurnitureCategoryData } from '@/types';

type Props = {
    categories: FurnitureCategoryData[];
};

export default function Categories({ categories }: Props) {
    return (
        <>
            <Head title="Каталог мебели" />

            <div className="container">
                <nav className="breadcrumbs" aria-label="Хлебные крошки">
                    <Link href={catalog().url}>Каталог</Link>
                </nav>

                <section className="category__head">
                    <h1>
                        <span className="category__head-icon" aria-hidden="true">
                            ▦
                        </span>
                        Каталог мебели
                    </h1>
                    <p className="category__desc">
                        Выберите категорию, чтобы посмотреть мебель, которая к ней относится.
                    </p>
                </section>

                <section className="catalog-categories">
                    {categories.map((category) => (
                        <Link
                            key={category.category_id}
                            href={catalogCategory({ category_id: category.category_id }).url}
                            className="category-card"
                        >
                            <h3 className="category-card__title">{category.name}</h3>
                            <span className="category-card__link">Смотреть мебель →</span>
                        </Link>
                    ))}
                </section>
            </div>
        </>
    );
}
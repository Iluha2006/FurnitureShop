import { Head, Link } from '@inertiajs/react';
import { catalog } from '@/routes';
import { category as catalogCategory } from '@/routes/catalog';
import FurnitureCard from '@/components/FurnitureCard';
import { buildCharacteristics } from '@/lib/furniture-characteristics';
import type { FurnitureCardData, FurnitureDetailData, FurnitureSpecificationData } from '@/types';

type Props = {
    furniture: FurnitureDetailData;
    specifications: FurnitureSpecificationData | null;
    related: FurnitureCardData[];
};

export default function FurnitureShow({ furniture, specifications, related }: Props) {
    const { furniture: item } = furniture;
    const mainImage =
        furniture.images.find((image) => image.is_main)?.path_image ??
        furniture.images[0]?.path_image ??
        null;

    const characteristics = buildCharacteristics(specifications);
    const hasSpecs = Object.values(characteristics).some((value) => value !== null);

    return (
        <>
            <Head title={item.name} />

            <div className="container">
                <nav className="breadcrumbs" aria-label="Хлебные крошки">
                    <Link href={catalog().url}>Каталог</Link>
                    <span className="breadcrumbs__sep">/</span>
                    {furniture.category ? (
                        <Link
                            href={catalogCategory({
                                category_id: furniture.category.category_id,
                            }).url}
                        >
                            {furniture.category.name}
                        </Link>
                    ) : null}
                    <span className="breadcrumbs__sep">/</span>
                    <span>{item.name}</span>
                </nav>

                <div className="furniture-show">
                    <div className="furniture-show__gallery">
                        {mainImage ? (
                            <img src={mainImage} alt={item.name} className="furniture-show__image" />
                        ) : (
                            <div className="furniture-show__placeholder">{item.name}</div>
                        )}
                        {furniture.images.length > 1 ? (
                            <div className="furniture-show__thumbs">
                                {furniture.images.map((image) => (
                                    <img
                                        key={image.images_id}
                                        src={image.path_image}
                                        alt={item.name}
                                        className="furniture-show__thumb"
                                    />
                                ))}
                            </div>
                        ) : null}
                    </div>

                    <div className="furniture-show__info">
                        <h1 className="furniture-show__title">{item.name}</h1>

                        <div className="card__meta">
                            {furniture.manufacturer ? (
                                <span>{furniture.manufacturer.name}</span>
                            ) : null}
                            {furniture.category ? <span>{furniture.category.name}</span> : null}
                            {item.color ? <span>{item.color}</span> : null}
                        </div>

                        {item.description ? (
                            <p className="furniture-show__desc">{item.description}</p>
                        ) : null}

                        <div className="furniture-show__price">
                            <b>{Number(item.price).toLocaleString('ru-RU')} ₽</b>
                            {item.quantity > 0 ? (
                                <span className="furniture-show__stock">В наличии</span>
                            ) : (
                                <span className="furniture-show__stock furniture-show__stock--out">
                                    Нет в наличии
                                </span>
                            )}
                        </div>

                        <section className="furniture-show__specs">
                            <h2>Характеристики</h2>

                            {hasSpecs ? (
                                <table className="specs-table">
                                    <tbody>
                                        {Object.entries(characteristics).map(([label, value]) =>
                                            value === null ? null : (
                                                <tr key={label}>
                                                    <th scope="row">{label}</th>
                                                    <td>{value}</td>
                                                </tr>
                                            ),
                                        )}
                                    </tbody>
                                </table>
                            ) : (
                                <p className="category__desc">
                                    Характеристики для этого товара не указаны.
                                </p>
                            )}
                        </section>
                    </div>
                </div>

                {related.length > 0 ? (
                    <section className="related">
                        <div className="section-head">
                            <h2>Похожие товары</h2>
                            {furniture.category ? (
                                <Link
                                    href={catalogCategory({
                                        category_id: furniture.category.category_id,
                                    }).url}
                                    className="section-head__all"
                                >
                                    Все товары категории →
                                </Link>
                            ) : (
                                <Link href={catalog().url} className="section-head__all">
                                    Весь каталог →
                                </Link>
                            )}
                        </div>

                        <div className="products-grid products-grid--catalog">
                            {related.map((card) => (
                                <FurnitureCard
                                    key={card.furniture.furniture_id}
                                    card={card}
                                />
                            ))}
                        </div>
                    </section>
                ) : null}

                <section className="promo-grid">
                    <div className="promo promo--sale">
                        <div>
                            <h3>Распродажа месяца — скидки до 30%</h3>
                            <p>
                                Сезонные скидки на спальни, кухни и мягкую мебель. Количество
                                товаров по акции ограничено — успевайте выбрать мебель с выгодой.
                            </p>
                        </div>
                        <ul className="promo__points">
                            <li>До −30% на спальные гарнитуры</li>
                            <li>Кухни со скидкой до 25%</li>
                            <li>Диваны и кресла от 9 990 ₽</li>
                        </ul>
                    </div>

                    <div className="promo promo--bonus">
                        <div>
                            <h3>Подарки и скидки покупателям</h3>
                            <p>
                                Дарим бонусы за заказы и балуем приятными подарками. Скидка
                                действует на любой товар из каталога.
                            </p>
                        </div>
                        <ul className="promo__points">
                            <li>Промокод −10% на первый заказ</li>
                            <li>Подарок к заказу от 25 000 ₽</li>
                            <li>Бесплатная доставка от 30 000 ₽</li>
                        </ul>
                    </div>
                </section>
            </div>
        </>
    );
}
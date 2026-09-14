import { Head, Link } from '@inertiajs/react';
import { catalog } from '@/routes';
import { category as catalogCategory } from '@/routes/catalog';
import type { FurnitureDetailData, FurnitureSpecificationData } from '@/types';

type Props = {
    furniture: FurnitureDetailData;
    specifications: FurnitureSpecificationData | null;
};

export default function FurnitureShow({ furniture, specifications }: Props) {
    const { furniture: item } = furniture;
    const mainImage =
        furniture.images.find((image) => image.is_main)?.path_image ??
        furniture.images[0]?.path_image ??
        null;

    const characteristics: Array<[string, string | null]> = [
        ['Ширина, см', formatNumber(specifications?.width_cm)],
        ['Длина, см', formatNumber(specifications?.length_cm)],
        ['Высота, см', formatNumber(specifications?.height_cm)],
        ['Механизм', specifications?.folding_type ?? null],
        ['Вставки', specifications?.insert_type ?? null],
        ['Материалы', specifications?.materials ?? null],
        ['Покрытие', specifications?.surface ?? null],
        ['Вес, кг', formatNumber(specifications?.weight_kg)],
        ['Объём упаковки, м³', formatNumber(specifications?.package_volume_m3)],
        ['Гарантия', specifications?.warranty ?? null],
    ];

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
                    </div>
                </div>

                <section className="furniture-show__specs">
                    <h2>Характеристики</h2>

                    {specifications ? (
                        <table className="specs-table">
                            <tbody>
                                {characteristics.map(([label, value]) =>
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
                        <p className="category__desc">Характеристики для этого товара не указаны.</p>
                    )}
                </section>
            </div>
        </>
    );
}

function formatNumber(value: number | null | undefined): string | null {
    return value === null || value === undefined ? null : String(value).replace('.', ',');
}
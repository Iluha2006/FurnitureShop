import { Link } from '@inertiajs/react';
import { show as furnitureShow } from '@/routes/catalog/furniture';
import type { FurnitureCardData } from '@/types';

export default function FurnitureCard({ card }: { card: FurnitureCardData }) {
    const { furniture, manufacturer, mainImage } = card;

    return (
        <article className="card">
            <Link
                href={furnitureShow({ furniture_id: furniture.furniture_id }).url}
                className="card__image"
            >
                {mainImage ? (
                    <img
                        src={mainImage}
                        alt={furniture.name}
                        loading="lazy"
                        style={{ width: '100%',  objectFit:"contain" }}
                    />
                ) : (
                    <span className="card__image-label">{furniture.name}</span>
                )}
            </Link>
            <div className="card__body">
                <h3 className="card__title">
                    <Link href={furnitureShow({ furniture_id: furniture.furniture_id }).url}>
                        {furniture.name}
                    </Link>
                </h3>
                <div className="card__meta">
                    {manufacturer ? <span>{manufacturer.name}</span> : null}
                    {furniture.color ? <span>{furniture.color}</span> : null}
                </div>
                <div className="card__buy">
                    <div className="card__price">
                        <b>{Number(furniture.price).toLocaleString('ru-RU')} ₽</b>
                    </div>
                    <span className="card__cart btn btn--primary">Подробнее</span>
                </div>
            </div>
        </article>
    );
}
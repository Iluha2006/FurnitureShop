import { Head, Link } from '@inertiajs/react';
import { Package } from 'lucide-react';
import { catalog } from '@/routes';
import { show as furnitureShow } from '@/routes/catalog/furniture';
import type { OrderData } from '@/types';

type Props = {
    orders: OrderData[];
};

export default function AccountOrders({ orders }: Props) {
    if (orders.length === 0) {
        return (
            <>
                <Head title="Заказы" />

                <section className="account__empty">
                    <span className="account__empty-icon">
                        <Package size={28} aria-hidden="true" />
                    </span>

                    <h1 className="account__empty-title">Заказов пока нет</h1>
                    <p className="account__empty-text">
                        Все ваши заказы и их статусы будут собраны в этом
                        разделе. Начните с выбора мебели в каталоге.
                    </p>

                    <Link href={catalog().url} className="btn btn--primary">
                        Перейти в каталог
                    </Link>
                </section>
            </>
        );
    }

    return (
        <>
            <Head title="Заказы" />

            <section className="orders">
                <header className="orders__head">
                    <h1 className="orders__title">Заказы</h1>
                    <p className="orders__desc">
                        Здесь собраны оформленные заказы, их состав и текущий
                        статус.
                    </p>
                </header>

                {orders.map((order) => (
                    <article className="order-card" key={order.id}>
                        <header className="order-card__head">
                            <div>
                                <h2 className="order-card__title">
                                    Заказ №{order.id}
                                </h2>
                                <p className="order-card__meta">
                                    {order.created_at} · {order.count}{' '}
                                    {order.count === 1 ? 'товар' : 'товаров'}
                                </p>
                            </div>

                            <span
                                className={`order-card__status order-card__status--${order.status}`}
                            >
                                {order.status_label}
                            </span>
                        </header>

                        <div className="order-card__items">
                            {order.items.map((item) => (
                                <div className="order-item" key={item.id}>
                                    <Link
                                        href={
                                            furnitureShow({
                                                furniture_id:
                                                    item.furniture_id,
                                            }).url
                                        }
                                        className="order-item__media"
                                        aria-label={item.name}
                                    >
                                        {item.mainImage ? (
                                            <img
                                                className="order-item__image"
                                                src={item.mainImage}
                                                alt={item.name}
                                                loading="lazy"
                                            />
                                        ) : (
                                            <span className="order-item__image-label">
                                                {item.name}
                                            </span>
                                        )}
                                    </Link>

                                    <div className="order-item__body">
                                        <Link
                                            href={
                                                furnitureShow({
                                                    furniture_id:
                                                        item.furniture_id,
                                                }).url
                                            }
                                            className="order-item__name"
                                        >
                                            {item.name}
                                        </Link>
                                        <span className="order-item__meta">
                                            {item.quantity} ×{' '}
                                            {item.price_formatted}
                                        </span>
                                    </div>

                                    <b className="order-item__sum">
                                        {item.line_total_formatted}
                                    </b>
                                </div>
                            ))}
                        </div>

                        <footer className="order-card__foot">
                            <span className="order-card__address">
                                {order.delivery_address
                                    ? `Доставка: ${order.delivery_address}`
                                    : 'Способ получения уточняется'}
                            </span>
                            <span className="order-card__total">
                                Итого: <b>{order.total_formatted}</b>
                            </span>
                        </footer>
                    </article>
                ))}
            </section>
        </>
    );
}

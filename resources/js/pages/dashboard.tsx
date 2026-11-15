import { Head, Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { ChevronRight, Heart, Package, ShoppingCart, User } from 'lucide-react';
import PendingInvitationsModal from '@/components/pending-invitations-modal';
import { catalog, dashboard } from '@/routes';
import { show as furnitureShow } from '@/routes/catalog/furniture';
import { favorites, orders as ordersRoute } from '@/routes/account';
import { index as cartRoute } from '@/routes/cart';
import { edit as profile } from '@/routes/profile';
import type { CartData, DashboardInvitation, OrderData } from '@/types';

type Props = {
    pendingInvitations?: DashboardInvitation[];
    orders?: OrderData[];
    ordersCount?: number;
    favoritesCount?: number;
    cart?: CartData;
};

const plural = (count: number, one: string, few: string, many: string) => {
    const mod10 = count % 10;
    const mod100 = count % 100;

    if (mod10 === 1 && mod100 !== 11) {
        return one;
    }

    if (mod10 >= 2 && mod10 <= 4 && (mod100 < 10 || mod100 >= 20)) {
        return few;
    }

    return many;
};

export default function Dashboard({
    pendingInvitations = [],
    orders = [],
    ordersCount = 0,
    favoritesCount = 0,
    cart,
}: Props) {
    const user = usePage().props.auth.user;
    const firstName = user.name.trim().split(/\s+/)[0];
    const hasOrders = ordersCount > 0;
    const cartCount = cart?.count ?? 0;
    const lastOrder = orders[0];
    const [showInvitations, setShowInvitations] = useState(
        pendingInvitations.length > 0,
    );

    const cards = [
        {
            href: ordersRoute().url,
            icon: Package,
            value: String(ordersCount),
            label: plural(ordersCount, 'заказ', 'заказа', 'заказов'),
            hint: hasOrders
                ? `Последний: №${lastOrder?.id} · ${lastOrder?.total_formatted}`
                : 'Здесь появятся ваши заказы',
        },
        {
            href: cartRoute().url,
            icon: ShoppingCart,
            value: String(cartCount),
            label: plural(cartCount, 'товар', 'товара', 'товаров'),
            hint:
                cartCount > 0
                    ? `В корзине на ${cart?.total_formatted}`
                    : 'Корзина пока пуста',
        },
        {
            href: favorites().url,
            icon: Heart,
            value: String(favoritesCount),
            label: plural(favoritesCount, 'товар', 'товара', 'товаров'),
            hint:
                favoritesCount > 0
                    ? 'Отложено на будущее'
                    : 'Добавьте мебель из каталога',
        },
        {
            href: profile().url,
            icon: User,
            value: 'Данные',
            label: 'Профиль',
            hint: user.email,
        },
    ];

    return (
        <>
            <Head title="Личный кабинет" />
            <PendingInvitationsModal
                invitations={pendingInvitations}
                open={pendingInvitations.length > 0 && showInvitations}
                onOpenChange={setShowInvitations}
            />

            <div className="dash">
                <header className="dash__head">
                    <div>
                        <h1 className="dash__greeting">
                            Здравствуйте, <span>{firstName}</span>!
                        </h1>
                        <p className="dash__desc">
                            Ваш личный кабинет: заказы, корзина, избранное и
                            персональные данные.
                        </p>
                    </div>
                </header>

                <div className="dash__grid">
                    {cards.map((card) => (
                        <Link
                            key={card.label}
                            href={card.href}
                            className="dash-card"
                        >
                            <div className="dash-card__top">
                                <span className="dash-card__icon">
                                    <card.icon size={22} aria-hidden="true" />
                                </span>
                                <span className="dash-card__more">
                                    <ChevronRight
                                        size={16}
                                        aria-hidden="true"
                                    />
                                </span>
                            </div>
                            <b className="dash-card__value">{card.value}</b>
                            <span className="dash-card__label">
                                {card.label}
                            </span>
                            <span
                                className={
                                    card.value === 'Данные'
                                        ? 'dash-card__hint dash-card__hint--muted'
                                        : 'dash-card__hint'
                                }
                            >
                                {card.hint}
                            </span>
                        </Link>
                    ))}
                </div>

                {!hasOrders ? (
                    <section className="account__empty">
                        <span className="account__empty-icon">
                            <Package size={28} aria-hidden="true" />
                        </span>

                        <h2 className="account__empty-title">
                            Заказов пока нет
                        </h2>
                        <p className="account__empty-text">
                            Все ваши заказы и их статусы будут собраны в этом
                            разделе. Начните с выбора мебели в каталоге.
                        </p>

                        <Link href={catalog().url} className="btn btn--primary">
                            Перейти в каталог
                        </Link>
                    </section>
                ) : (
                    <section className="dash__orders">
                        <header className="dash__orders-head">
                            <h2 className="dash__orders-title">
                                Последние заказы
                            </h2>
                            <Link
                                href={ordersRoute().url}
                                className="dash__orders-link"
                            >
                                Все заказы
                            </Link>
                        </header>

                        {orders.map((order) => (
                            <article className="order-card" key={order.id}>
                                <header className="order-card__head">
                                    <div>
                                        <h3 className="order-card__title">
                                            Заказ №{order.id}
                                        </h3>
                                        <p className="order-card__meta">
                                            {order.created_at} · {order.count}{' '}
                                            {plural(
                                                order.count,
                                                'товар',
                                                'товара',
                                                'товаров',
                                            )}
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
                                        <div
                                            className="order-item"
                                            key={item.id}
                                        >
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
                )}
            </div>
        </>
    );
}

Dashboard.layout = (props: { currentTeam?: { slug: string } | null }) => ({
    breadcrumbs: [
        {
            title: 'Личный кабинет',
            href: props.currentTeam ? dashboard(props.currentTeam.slug) : '/',
        },
    ],
});

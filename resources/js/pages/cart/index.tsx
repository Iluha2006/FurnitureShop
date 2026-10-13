import { Head, Link, router } from '@inertiajs/react';
import { ShoppingCart, Trash2 } from 'lucide-react';
import { useState } from 'react';
import CartQuantityControl from '@/components/cart-quantity-control';
import { useCartNotification } from '@/hooks/use-cart-notification';
import { catalog } from '@/routes';
import { clear as clearCartRoute, remove as removeRoute } from '@/routes/cart';
import { show as furnitureShow } from '@/routes/catalog/furniture';
import type { CartData, CartItemData } from '@/types';

type Props = {
    cart: CartData;
};

export default function CartPage({ cart }: Props) {
    const [pending, setPending] = useState<string | null>(null);
    const { cleared, failed, removed, checkoutUnavailable } =
        useCartNotification();

    const items = cart.items;
    const hasItems = items.length > 0;

    const removeItem = (item: CartItemData): void => {
        setPending(`remove-${item.furniture_id}`);

        router.delete(removeRoute({ furniture_id: item.furniture_id }).url, {
            preserveScroll: true,
            onSuccess: () => removed(item),
            onError: () => failed('удалить товар из корзины'),
            onFinish: () => setPending(null),
        });
    };

    const clearCart = (): void => {
        setPending('clear');

        router.post(
            clearCartRoute().url,
            {},
            {
                preserveScroll: true,
                onSuccess: () => cleared(),
                onError: () => failed('очистить корзину'),
                onFinish: () => setPending(null),
            },
        );
    };

    const checkout = (): void => {
        checkoutUnavailable();
    };

    return (
        <>
            <Head title="Корзина" />

            <div className="container">
                <nav className="breadcrumbs" aria-label="Хлебные крошки">
                    <Link href={catalog().url}>Каталог</Link>
                    <span className="breadcrumbs__sep">/</span>
                    <span>Корзина</span>
                </nav>

                {!hasItems ? (
                    <section className="account__empty cart__empty">
                        <span className="account__empty-icon">
                            <ShoppingCart size={28} aria-hidden="true" />
                        </span>

                        <h1 className="account__empty-title">Корзина пуста</h1>
                        <p className="account__empty-text">
                            Добавьте мебель из каталога — она появится здесь и
                            сохранится, пока вы не оформите заказ.
                        </p>

                        <Link href={catalog().url} className="btn btn--primary">
                            Перейти в каталог
                        </Link>
                    </section>
                ) : (
                    <>
                        <section className="cart__head">
                            <h1>Корзина</h1>
                            <p className="cart__desc">
                                В корзине {cart.count}{' '}
                                {cart.count === 1 ? 'товар' : 'товаров'}.
                                Измените количество или удалите позицию перед
                                оформлением заказа.
                            </p>
                        </section>

                        <div className="cart__layout">
                            <section
                                className="cart__items"
                                aria-label="Товары в корзине"
                            >
                                {items.map((item) => (
                                    <article
                                        key={item.id}
                                        className="cart-item"
                                    >
                                        <Link
                                            href={
                                                furnitureShow({
                                                    furniture_id:
                                                        item.furniture_id,
                                                }).url
                                            }
                                            className="cart-item__media"
                                            aria-label={item.name}
                                        >
                                            {item.mainImage ? (
                                                <img
                                                    className="cart-item__image"
                                                    src={item.mainImage}
                                                    alt={item.name}
                                                    loading="lazy"
                                                />
                                            ) : (
                                                <span className="cart-item__image-label">
                                                    {item.name}
                                                </span>
                                            )}
                                        </Link>

                                        <div className="cart-item__body">
                                            <h2 className="cart-item__name">
                                                <Link
                                                    href={
                                                        furnitureShow({
                                                            furniture_id:
                                                                item.furniture_id,
                                                        }).url
                                                    }
                                                >
                                                    {item.name}
                                                </Link>
                                            </h2>

                                            <span className="cart-item__unit">
                                                Цена: {item.price_formatted}
                                            </span>

                                            <div className="cart-item__controls">
                                                <CartQuantityControl
                                                    furnitureId={
                                                        item.furniture_id
                                                    }
                                                    quantity={item.quantity}
                                                    stock={item.stock}
                                                    furnitureName={item.name}
                                                />

                                                <button
                                                    type="button"
                                                    className="cart-item__remove"
                                                    onClick={() =>
                                                        removeItem(item)
                                                    }
                                                    disabled={pending !== null}
                                                >
                                                    <Trash2
                                                        size={15}
                                                        aria-hidden="true"
                                                    />
                                                    Удалить
                                                </button>

                                                <span className="cart-item__stock">
                                                    {item.quantity >=
                                                    item.stock ? (
                                                        <>
                                                            Больше нет в
                                                            наличии: доступно{' '}
                                                            {item.stock} шт.
                                                        </>
                                                    ) : (
                                                        <>
                                                            Доступно{' '}
                                                            {item.stock} шт.
                                                        </>
                                                    )}
                                                </span>
                                            </div>
                                        </div>

                                        <div className="cart-item__side">
                                            <b className="cart-item__sum">
                                                {item.line_total_formatted}
                                            </b>
                                        </div>
                                    </article>
                                ))}
                            </section>

                            <aside
                                className="cart-summary"
                                aria-label="Итог по корзине"
                            >
                                <h2 className="cart-summary__title">Итог</h2>

                                <div className="cart-summary__row">
                                    <span>Товаров</span>
                                    <b>{cart.count}</b>
                                </div>

                                <div className="cart-summary__total">
                                    <span>Сумма заказа</span>
                                    <b>{cart.total_formatted}</b>
                                </div>

                                <p className="cart-summary__note">
                                    Доставка и сборка рассчитываются при
                                    оформлении заказа.
                                </p>

                                <button
                                    type="button"
                                    className="btn btn--primary cart-summary__checkout"
                                    onClick={checkout}
                                >
                                    Оформить заказ
                                </button>

                                <Link
                                    href={catalog().url}
                                    className="cart-summary__back"
                                >
                                    Продолжить покупки
                                </Link>

                                <button
                                    type="button"
                                    className="cart-summary__clear"
                                    onClick={clearCart}
                                    disabled={pending !== null}
                                >
                                    Очистить корзину
                                </button>
                            </aside>
                        </div>
                    </>
                )}
            </div>
        </>
    );
}

import { Form, Head, Link } from '@inertiajs/react';
import { ChevronLeft, ShieldCheck } from 'lucide-react';
import InputError from '@/components/input-error';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { catalog } from '@/routes';
import { index as cartRoute } from '@/routes/cart';
import { store } from '@/routes/checkout';
import { show as furnitureShow } from '@/routes/catalog/furniture';
import type { CartData } from '@/types';

type Customer = {
    name: string;
    email: string;
};

type Props = {
    cart: CartData;
    customer: Customer;
};

export default function CheckoutPage({ cart, customer }: Props) {
    return (
        <>
            <Head title="Оформление заказа" />

            <div className="container">
                <nav className="breadcrumbs" aria-label="Хлебные крошки">
                    <Link href={catalog().url}>Каталог</Link>
                    <span className="breadcrumbs__sep">/</span>
                    <Link href={cartRoute().url}>Корзина</Link>
                    <span className="breadcrumbs__sep">/</span>
                    <span>Оформление заказа</span>
                </nav>

                <section className="cart__head">
                    <h1>Оформление заказа</h1>
                    <p className="cart__desc">
                        Заполните контактные данные и адрес доставки. Мы
                        свяжемся с вами, чтобы подтвердить заказ и удобное время
                        доставки.
                    </p>
                </section>

                <Form {...store.form()} className="checkout__layout">
                    {({ processing, errors }) => (
                        <>
                            <div className="checkout__main">
                                {errors.cart && (
                                    <div className="checkout__error" role="alert">
                                        {errors.cart}
                                    </div>
                                )}

                                <section className="checkout__panel">
                                    <h2 className="checkout__panel-title">
                                        Контактные данные
                                    </h2>

                                    <div className="checkout__fields">
                                        <div className="auth-field checkout__field checkout__field--full">
                                            <Label
                                                htmlFor="customer_name"
                                                className="auth-label"
                                            >
                                                Имя получателя
                                            </Label>
                                            <Input
                                                id="customer_name"
                                                name="customer_name"
                                                className="auth-input"
                                                defaultValue={customer.name}
                                                required
                                                autoComplete="name"
                                                placeholder="Иван Иванов"
                                            />
                                            <InputError
                                                message={errors.customer_name}
                                                className="auth-error"
                                            />
                                        </div>

                                        <div className="auth-field checkout__field">
                                            <Label
                                                htmlFor="customer_phone"
                                                className="auth-label"
                                            >
                                                Телефон
                                            </Label>
                                            <Input
                                                id="customer_phone"
                                                name="customer_phone"
                                                type="tel"
                                                className="auth-input"
                                                required
                                                autoComplete="tel"
                                                placeholder="+7 900 000-00-00"
                                            />
                                            <InputError
                                                message={errors.customer_phone}
                                                className="auth-error"
                                            />
                                        </div>

                                        <div className="auth-field checkout__field">
                                            <Label
                                                htmlFor="customer_email"
                                                className="auth-label"
                                            >
                                                Email
                                            </Label>
                                            <Input
                                                id="customer_email"
                                                name="customer_email"
                                                type="email"
                                                className="auth-input"
                                                defaultValue={customer.email}
                                                required
                                                autoComplete="email"
                                                placeholder="email@example.com"
                                            />
                                            <InputError
                                                message={errors.customer_email}
                                                className="auth-error"
                                            />
                                        </div>
                                    </div>
                                </section>

                                <section className="checkout__panel">
                                    <h2 className="checkout__panel-title">
                                        Доставка
                                    </h2>

                                    <div className="auth-field checkout__field">
                                        <Label
                                            htmlFor="delivery_address"
                                            className="auth-label"
                                        >
                                            Адрес доставки
                                        </Label>
                                        <textarea
                                            id="delivery_address"
                                            name="delivery_address"
                                            className="auth-input checkout__textarea"
                                            required
                                            rows={3}
                                            autoComplete="street-address"
                                            placeholder="Город, улица, дом, квартира"
                                        />
                                        <InputError
                                            message={errors.delivery_address}
                                            className="auth-error"
                                        />
                                    </div>

                                    <div className="auth-field checkout__field">
                                        <Label
                                            htmlFor="comment"
                                            className="auth-label"
                                        >
                                            Комментарий к заказу
                                        </Label>
                                        <textarea
                                            id="comment"
                                            name="comment"
                                            className="auth-input checkout__textarea"
                                            rows={3}
                                            placeholder="Этаж, лифт, удобное время доставки — всё, что важно знать курьеру"
                                        />
                                        <InputError
                                            message={errors.comment}
                                            className="auth-error"
                                        />
                                    </div>
                                </section>

                                <Link
                                    href={cartRoute().url}
                                    className="checkout__back"
                                >
                                    <ChevronLeft size={16} aria-hidden="true" />
                                    Вернуться в корзину
                                </Link>
                            </div>

                            <aside
                                className="cart-summary checkout__summary"
                                aria-label="Ваш заказ"
                            >
                                <h2 className="cart-summary__title">
                                    Ваш заказ
                                </h2>

                                <div className="checkout__items">
                                    {cart.items.map((item) => (
                                        <div
                                            className="checkout-item"
                                            key={item.id}
                                        >
                                            <Link
                                                href={
                                                    furnitureShow({
                                                        furniture_id:
                                                            item.furniture_id,
                                                    }).url
                                                }
                                                className="checkout-item__media"
                                                aria-label={item.name}
                                            >
                                                {item.mainImage ? (
                                                    <img
                                                        className="checkout-item__image"
                                                        src={item.mainImage}
                                                        alt={item.name}
                                                        loading="lazy"
                                                    />
                                                ) : (
                                                    <span className="checkout-item__image-label">
                                                        {item.name}
                                                    </span>
                                                )}
                                            </Link>

                                            <div className="checkout-item__body">
                                                <Link
                                                    href={
                                                        furnitureShow({
                                                            furniture_id:
                                                                item.furniture_id,
                                                        }).url
                                                    }
                                                    className="checkout-item__name"
                                                >
                                                    {item.name}
                                                </Link>
                                                <span className="checkout-item__meta">
                                                    {item.quantity} ×{' '}
                                                    {item.price_formatted}
                                                </span>
                                            </div>

                                            <b className="checkout-item__sum">
                                                {item.line_total_formatted}
                                            </b>
                                        </div>
                                    ))}
                                </div>

                                <div className="cart-summary__row">
                                    <span>Товаров</span>
                                    <b>{cart.count}</b>
                                </div>

                                <div className="cart-summary__total">
                                    <span>Итого</span>
                                    <b>{cart.total_formatted}</b>
                                </div>

                                <p className="cart-summary__note">
                                    Доставка и сборка рассчитываются отдельно.
                                    Менеджер уточнит детали при подтверждении.
                                </p>

                                <button
                                    type="submit"
                                    className="btn btn--primary cart-summary__checkout"
                                    disabled={processing}
                                >
                                    {processing && <Spinner />}
                                    Подтвердить заказ
                                </button>

                                <p className="checkout__secure">
                                    <ShieldCheck size={15} aria-hidden="true" />
                                    Оплата при получении после согласования
                                    заказа.
                                </p>
                            </aside>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

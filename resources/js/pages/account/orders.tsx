import { Head, Link } from '@inertiajs/react';
import { Package } from 'lucide-react';
import { catalog } from '@/routes';

export default function AccountOrders() {
    return (
        <>
            <Head title="Заказы" />

            <section className="account__empty">
                <span className="account__empty-icon">
                    <Package size={28} aria-hidden="true" />
                </span>

                <h1 className="account__empty-title">Заказов пока нет</h1>
                <p className="account__empty-text">
                    Все ваши заказы и их статусы будут собраны в этом разделе.
                    Начните с выбора мебели в каталоге.
                </p>

                <Link href={catalog().url} className="btn btn--primary">
                    Перейти в каталог
                </Link>
            </section>
        </>
    );
}

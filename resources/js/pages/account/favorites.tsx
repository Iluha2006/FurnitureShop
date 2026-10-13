import { Head, Link } from '@inertiajs/react';
import { Heart } from 'lucide-react';
import { catalog } from '@/routes';

export default function AccountFavorites() {
    return (
        <>
            <Head title="Избранное" />

            <section className="account__empty">
                <span className="account__empty-icon">
                    <Heart size={28} aria-hidden="true" />
                </span>

                <h1 className="account__empty-title">В избранном пусто</h1>
                <p className="account__empty-text">
                    Нажимайте на сердечко в карточке товара, чтобы отложить
                    мебель и вернуться к ней позже.
                </p>

                <Link href={catalog().url} className="btn btn--primary">
                    Перейти в каталог
                </Link>
            </section>
        </>
    );
}

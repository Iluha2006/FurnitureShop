import { Head, Link, usePage } from '@inertiajs/react';
import { catalog, dashboard, login } from '@/routes';
import { category as catalogCategory } from '@/routes/catalog';
import FurnitureCard from '@/components/FurnitureCard';
import type { Auth, FurnitureCardData, FurnitureCategoryData } from '@/types';

type Props = {
    categories: FurnitureCategoryData[];
    hits: FurnitureCardData[];
    productCount: number;
};

export default function Welcome({ categories, hits, productCount }: Props) {
    const { auth, currentTeam } = usePage().props as {
        auth: Auth;
        currentTeam: { slug: string } | null;
    };

    const isAuthenticated = Boolean(auth.user);

    return (
        <>
            <Head title="МебельДом — мебель, которая делает дом уютным" />

            <section className="hero">
                <div className="container hero__inner">
                    <div className="hero__info">
                        <span className="hero__tag">
                            Корпусная и мягкая мебель от производителя
                        </span>
                        <h1>Мебель, которая делает дом уютным</h1>
                       

                        
                        <div className="hero__actions">
                            <Link href={catalog().url} className="btn btn--primary btn--lg">
                                Смотреть каталог
                            </Link>
                            {!isAuthenticated ? (
                                <Link
                                    href={login().url}
                                    className="btn btn--ghost btn--lg"
                                >
                                    Войти в личный кабинет
                                </Link>
                            ) : currentTeam ? (
                                <Link
                                    href={dashboard(currentTeam.slug).url}
                                    className="btn btn--ghost btn--lg"
                                >
                                    Личный кабинет
                                </Link>
                            ) : null}
                        </div>
                       
                    </div>
                    <div className="hero__visual" aria-hidden="true">
                        <img
                            className="hero__featured-img"
                            src="https://cdn.nonton.ru/s3/2784x840/iblock/dc2/dc2cf513f826a264129773406b705fbe/cfbf07dc8764b4975e7dad652272e7e6.webp"
                            alt=""
                        />
                    </div>
                </div>
            </section>

            <section className="benefits">
                <div className="container benefits__grid">
                    <div className="benefit">
                        <h3>Доставка и сборка</h3>
                        <p>Привезём и соберём мебель в удобное время.</p>
                    </div>
                    <div className="benefit">
                        <h3>Рассрочка 0%</h3>
                        <p>До 12 месяцев без переплат.</p>
                    </div>
                    <div className="benefit">
                        <h3>Гарантия 24 месяца</h3>
                        <p>На все товары из нашего каталога.</p>
                    </div>
                    <div className="benefit">
                        <h3>Свой производитель</h3>
                        <p>Мебель собственных фабрик с 2014 года.</p>
                    </div>
                </div>
            </section>

            <div className="container home-layout">
                <aside className="sidebar" aria-label="Категории мебели">
                    <h2 className="sidebar__title">Каталог мебели</h2>
                    <nav className="sidebar__nav">
                        {categories.map((category) => (
                            <Link
                                key={category.category_id}
                                href={catalogCategory({ category_id: category.category_id }).url}
                                className="sidebar__link"
                            >
                                <span className="sidebar__name">{category.name}</span>
                                <span className="sidebar__arrow">›</span>
                            </Link>
                        ))}
                    </nav>
                </aside>

                <div className="home-content">
                    <div className="section-head">
                        <h2>Хиты продаж</h2>
                        <Link href={catalog().url} className="section-head__all">
                            Весь каталог →
                        </Link>
                    </div>

                    {hits.length > 0 ? (
                        <section className="products-grid products-grid--home">
                            {hits.map((card) => (
                                <FurnitureCard key={card.furniture.furniture_id} card={card} />
                            ))}
                        </section>
                    ) : (
                        <p className="catalog__note">
                            Товаров в наличии пока нет — загляните в каталог позже.
                        </p>
                    )}
                </div>
            </div>

            <section className="container">
                <div className="promo promo--delivery" id="delivery">
                    <div>
                        <h3>Доставка и сборка под ключ</h3>
                        <p>
                            Привезём мебель собственным транспортом в удобное время, поднимем на
                            этаж, соберём и установим.
                        </p>
                    </div>
                    <ul className="promo__points">
                        <li>Бесплатно при заказе от 30 000 ₽</li>
                        <li>Самовывоз в день заказа</li>
                        <li>Сборка — от 500 ₽ за предмет</li>
                    </ul>
                </div>
            </section>
        </>
    );
}
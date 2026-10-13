import { Head, Link, usePage } from '@inertiajs/react';
import { catalog } from '@/routes';
import { category as catalogCategory } from '@/routes/catalog';
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
                <div className="hero__inner container">
                    <div className="hero__info">
                        <span className="hero__tag">
                            Корпусная и мягкая мебель от производителя
                        </span>
                        <h1>Мебель, которая делает дом уютным</h1>
                        <p className="hero__text">
                            Производим корпусную и мягкую мебель на собственных
                            фабриках с 2014 года. Без посредников и лишних
                            наценок — только качество, которое проверено годами.
                        </p>
                        <div className="hero__actions">
                            <Link
                                href={catalog().url}
                                className="btn btn--primary btn--lg"
                            >
                                Перейти в каталог
                            </Link>
                        </div>
                        <div className="hero__stats">
                            <div>
                                <b>12 лет</b>
                                <span>собственного производства</span>
                            </div>
                            <div>
                                <b>24 мес</b>
                                <span>гарантии на мебель</span>
                            </div>
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
                <div className="benefits__grid container">
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

            <section className="container">
                <div className="about">
                    <div>
                        <h2>Мебель, которую делают для себя</h2>
                        <p>
                            С 2014 года мы сами проектируем и изготавливаем
                            корпусную и мягкую мебель на собственных фабриках:
                            выбираем материалы, следим за каждой партией и
                            предлагаем цену без посредников.
                        </p>
                        <p>
                            Каждый шкаф, комод и диван проходит контроль перед
                            отгрузкой, поэтому мебель приезжает к вам собранной
                            и готовой к использованию на годы вперёд.
                        </p>
                    </div>
                    <div className="about__gallery" aria-hidden="true">
                        <img
                            src="https://avatars.mds.yandex.net/i?id=9da525ff6ff842c824ed8a663ca21d55_l-5232689-images-thumbs&n=13"
                            alt=""
                        />
                    </div>
                </div>
            </section>

            <div className="container">
                <section className="category__head">
                    <h1>Категории</h1>
                </section>

                <section className="catalog-categories">
                    {categories.map((category) => (
                        <Link
                            key={category.category_id}
                            href={
                                catalogCategory({
                                    category_id: category.category_id,
                                }).url
                            }
                            className={
                                category.image_url
                                    ? 'category-card category-card--photo'
                                    : 'category-card'
                            }
                            style={
                                category.image_url
                                    ? {
                                          backgroundImage: `url("${category.image_url}")`,
                                      }
                                    : undefined
                            }
                        >
                            <h3 className="category-card__title">
                                {category.name}
                            </h3>
                        </Link>
                    ))}
                </section>
            </div>

            <section className="container">
                <div className="promo promo--delivery" id="delivery">
                    <div>
                        <h3>Доставка и сборка под ключ</h3>
                        <p>
                            Привезём мебель собственным транспортом в удобное
                            время, поднимем на этаж, соберём и установим.
                        </p>
                    </div>
                    <ul className="promo__points">
                        <li>Бесплатно при заказе от 30 000 ₽</li>
                        <li>Самовывоз в день заказа</li>
                        <li>Сборка — от 500 ₽ за предмет</li>
                    </ul>
                </div>

                <div className="promo-grid">
                    <div className="promo promo--payment">
                        <div>
                            <h3>Рассрочка 0% до 12 месяцев</h3>
                            <p>
                                Оформите рассрочку прямо на сайте — без
                                переплат, банковских визитов и лишних
                                документов.
                            </p>
                        </div>
                        <ul className="promo__points">
                            <li>Первый взнос от 10%</li>
                            <li>Одобрение онлайн за пару минут</li>
                        </ul>
                    </div>
                    <div className="promo promo--bonus">
                        <div>
                            <h3>Бонусы за покупки</h3>
                            <p>
                                Каждая покупка приносит баллы — копите их и
                                оплачивайте до 20% следующего заказа.
                            </p>
                        </div>
                        <ul className="promo__points">
                            <li>1 ₽ за каждые 100 ₽ покупки</li>
                            <li>Двойные баллы в день рождения</li>
                        </ul>
                    </div>
                </div>
            </section>
        </>
    );
}


import { Link } from "@inertiajs/react"

export default function Header() {
    return (
        <header className="header">
            <div className="header__top">
                <div className="container header__top-inner">
                    <span className="header__phone">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
                        </svg>
                        +7 (495) 123-45-67
                    </span>
                    <span className="header__hours">Ежедневно с 9:00 до 21:00</span>
                    <span className="header__address">г. Москва, ул. Мебельная, 12</span>
                </div>
            </div>
            <div className="container header__main">
                <Link className="logo">
                    <span className="logo__mark">М</span>
                    <span className="logo__text">
                        <strong>МебельДом</strong>
                        <small>корпусная и мягкая мебель</small>
                    </span>
                </Link>
                <nav className="header__nav">
                    <Link >Каталог</Link>
                    <Link onClick={(e) => e.preventDefault()}>Доставка</Link>
                    <Link onClick={(e) => e.preventDefault()}>Оплата</Link>
                    <Link onClick={(e) => e.preventDefault()}>О компании</Link>
                    <Link onClick={(e) => e.preventDefault()}>Контакты</Link>
                </nav>
                <div className="header__actions">
                    <span className="header__search" role="search" aria-label="Поиск по каталогу">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.35-4.35" />
                        </svg>
                        <span className="header__search-label">Поиск по каталогу…</span>
                    </span>
                    <span className="header__cart">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                            <circle cx="9" cy="21" r="1.5" />
                            <circle cx="19" cy="21" r="1.5" />
                            <path d="M2 3h2l2.4 12.2a1.5 1.5 0 0 0 1.5 1.3h8.7a1.5 1.5 0 0 0 1.5-1.2L20.5 8H6" />
                        </svg>
                        <span className="header__cart-label">Корзина</span>
                        <b className="header__cart-count">0</b>
                    </span>
                </div>
            </div>
            <div className="header__bottom">
                <div className="container">
                    <span className="header__promo">Скидка 10% на мягкую мебель при заказе до конца месяца</span>
                </div>
            </div>
        </header>
    )
}
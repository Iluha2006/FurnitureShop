
import { Link } from "@inertiajs/react"

export default function Header() {
    return (
        <header className="header">
            
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
                    <span className="header__icon" aria-label="Избранное" title="Избранное">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                            <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.51 4.05 3 5.5l7 7Z" />
                        </svg>
                    </span>
                    <span className="header__icon" aria-label="Личный кабинет" title="Личный кабинет">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                            <circle cx="12" cy="8" r="5" />
                            <path d="M20 21a8 8 0 0 0-16 0" />
                        </svg>
                    </span>
                    <span className="header__cart" aria-label="Корзина" title="Корзина">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                            <circle cx="9" cy="21" r="1.5" />
                            <circle cx="19" cy="21" r="1.5" />
                            <path d="M2 3h2l2.4 12.2a1.5 1.5 0 0 0 1.5 1.3h8.7a1.5 1.5 0 0 0 1.5-1.2L20.5 8H6" />
                        </svg>
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
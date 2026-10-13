import { Link } from '@inertiajs/react';
import { Heart, Package, ShoppingCart, User } from 'lucide-react';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn } from '@/lib/utils';
import { favorites, orders } from '@/routes/account';
import { index as cart } from '@/routes/cart';
import { edit as profile } from '@/routes/profile';

const navItems = [
    { title: 'Заказы', href: orders, icon: Package },
    { title: 'Корзина', href: cart, icon: ShoppingCart },
    { title: 'Избранное', href: favorites, icon: Heart },
    { title: 'Персональные данные', href: profile, icon: User },
];

export default function AccountNav() {
    const { isCurrentUrl } = useCurrentUrl();

    return (
        <aside className="sidebar" aria-label="Личный кабинет">
            <h2 className="sidebar__title">Личный кабинет</h2>

            <nav className="sidebar__nav">
                {navItems.map((item) => (
                    <Link
                        key={item.title}
                        href={item.href().url}
                        aria-current={
                            isCurrentUrl(item.href().url) ? 'page' : undefined
                        }
                        className={cn('sidebar__link', {
                            'sidebar__link--active': isCurrentUrl(
                                item.href().url,
                            ),
                        })}
                    >
                        <span className="sidebar__icon">
                            <item.icon size={18} aria-hidden="true" />
                        </span>
                        <span className="sidebar__name">{item.title}</span>
                    </Link>
                ))}
            </nav>
        </aside>
    );
}

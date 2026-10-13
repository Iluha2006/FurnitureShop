import { PackageCheck, Trash2 } from 'lucide-react';
import type { ReactNode } from 'react';
import CartNotification from '@/components/cart-notification';
import { notify, type NotifyType } from '@/lib/notify';

export function useCartNotification() {
    const withItem = (
        message: string,
        furnitureName: string,
        type: NotifyType,
        icon: ReactNode,
    ) =>
        notify(message, type, {
            icon,
            description: <CartNotification furnitureName={furnitureName} />,
        });

    const addedIcon = <PackageCheck size={16} aria-hidden="true" />;
    const removedIcon = <Trash2 size={16} aria-hidden="true" />;

    return {
        added: (item: { name: string }) =>
            withItem(
                'Товар добавлен в корзину',
                item.name,
                'success',
                addedIcon,
            ),
        removed: (item: { name: string }) =>
            withItem('Товар удалён из корзины', item.name, 'info', removedIcon),
        cleared: () => notify('Корзина очищена', 'info'),
        checkoutUnavailable: () =>
            notify('Оформление заказа пока не доступно', 'warning'),
        failed: (action: string) =>
            notify(`Не удалось ${action}. Попробуйте ещё раз.`, 'error'),
    };
}

import { router } from '@inertiajs/react';
import { Minus, Plus } from 'lucide-react';
import { useState } from 'react';
import { useCartNotification } from '@/hooks/use-cart-notification';
import { decrement, increment } from '@/routes/cart';
import { cn } from '@/lib/utils';

type Props = {
    furnitureId: number;
    quantity: number;

    /** Units on hand. The user cannot go above it. */
    stock: number;

    furnitureName?: string;
    onQuantityRemoved?: () => void;
    className?: string;
};

export default function CartQuantityControl({
    furnitureId,
    quantity,
    stock,
    furnitureName,
    onQuantityRemoved,
    className,
}: Props) {
    const [pending, setPending] = useState(false);
    const { failed, removed } = useCartNotification();

    const atStockLimit = quantity >= stock;

    const change = (direction: 1 | -1): void => {
        const isRemoving = direction === -1 && quantity <= 1;

        setPending(true);

        router.post(
            direction === 1
                ? increment({ furniture_id: furnitureId }).url
                : decrement({ furniture_id: furnitureId }).url,
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    if (isRemoving) {
                        removed({ name: furnitureName ?? 'Товар' });

                        onQuantityRemoved?.();
                    }
                },
                onError: () => failed('изменить количество товара'),
                onFinish: () => setPending(false),
            },
        );
    };

    return (
        <div className={cn('cart-qty', className)} role="group">
            <button
                type="button"
                className="cart-qty__btn"
                onClick={() => change(-1)}
                disabled={pending}
                aria-label={`Уменьшить количество${furnitureName ? `: ${furnitureName}` : ''}`}
            >
                <Minus size={15} aria-hidden="true" />
            </button>

            <span className="cart-qty__value" aria-live="polite">
                {quantity}
            </span>

            <button
                type="button"
                className="cart-qty__btn"
                onClick={() => change(1)}
                disabled={pending || atStockLimit}
                title={
                    atStockLimit ? `В наличии только ${stock} шт.` : undefined
                }
                aria-label={`Увеличить количество${furnitureName ? `: ${furnitureName}` : ''}`}
            >
                <Plus size={15} aria-hidden="true" />
            </button>
        </div>
    );
}

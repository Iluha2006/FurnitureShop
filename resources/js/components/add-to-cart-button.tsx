import { router } from '@inertiajs/react';
import { ShoppingCart } from 'lucide-react';
import { useState } from 'react';
import { useCartNotification } from '@/hooks/use-cart-notification';
import { add } from '@/routes/cart';
import { cn } from '@/lib/utils';

type Props = {
    furnitureId: number;
    furnitureName?: string;
    label?: string;
    className?: string;
};

export default function AddToCartButton({
    furnitureId,
    furnitureName,
    label = 'В корзину',
    className,
}: Props) {
    const [pending, setPending] = useState(false);
    const { added, failed } = useCartNotification();

    const addToCart = (): void => {
        setPending(true);

        router.post(
            add().url,
            { furniture_id: furnitureId },
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => added({ name: furnitureName ?? 'Товар' }),
                onError: () => failed('добавить товар в корзину'),
                onFinish: () => setPending(false),
            },
        );
    };

    return (
        <button
            type="button"
            className={cn('btn btn--primary', className)}
            onClick={addToCart}
            disabled={pending}
        >
            <ShoppingCart size={15} aria-hidden="true" />
            {label}
        </button>
    );
}

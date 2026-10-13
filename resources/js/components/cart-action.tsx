import { Link } from '@inertiajs/react';
import { Check, ShoppingCart } from 'lucide-react';
import AddToCartButton from '@/components/add-to-cart-button';
import { useCart } from '@/hooks/use-cart';
import { index as cartIndex } from '@/routes/cart';
import { cn } from '@/lib/utils';

type Props = {
    furnitureId: number;
    furnitureName?: string;
    label?: string;
    addClassName?: string;

    inCartClassName?: string;
};

export default function CartAction({
    furnitureId,
    furnitureName,
    label,
    addClassName,
    inCartClassName,
}: Props) {
    const { isInCart } = useCart();

    if (!isInCart(furnitureId)) {
        return (
            <AddToCartButton
                furnitureId={furnitureId}
                furnitureName={furnitureName}
                label={label}
                className={addClassName}
            />
        );
    }

    return (
        <Link
            href={cartIndex().url}
            className={cn('btn cart-action', inCartClassName)}
        >
            <ShoppingCart size={15} aria-hidden="true" />
            <Check size={15} aria-hidden="true" />
            В корзине
        </Link>
    );
}

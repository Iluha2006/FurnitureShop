import { usePage } from '@inertiajs/react';
import { useMemo } from 'react';

export function useCart() {
    const { cartQuantities } = usePage().props;

    return useMemo(() => {
        const quantities = cartQuantities ?? {};

        return {
            quantities,
            isInCart: (furnitureId: number): boolean =>
                (quantities[String(furnitureId)] ?? 0) > 0,
            quantityOf: (furnitureId: number): number =>
                quantities[String(furnitureId)] ?? 0,
            total: Object.values(quantities).reduce(
                (sum, quantity) => sum + quantity,
                0,
            ),
        };
    }, [cartQuantities]);
}

import { useMemo } from 'react';
import type { PickupPointData } from '@/types/pickupPoint';

/**
 * Returns a referentially stable list of pickup points.
 *
 * The list only changes when the set of points actually changes (by id),
 * so memoised markers and the map never re-render or trigger extra
 * Yandex Maps work on unrelated re-renders of the page.
 */
export function useWarehousesCache(apiWarehouses: PickupPointData[]): {
    warehouses: PickupPointData[];
} {
    const ids = useMemo(
        () => apiWarehouses.map((point) => point.id).join('|'),
        [apiWarehouses],
    );

    return useMemo(
        () => ({ warehouses: apiWarehouses }),
        [apiWarehouses, ids], // eslint-disable-line react-hooks/exhaustive-deps
    );
}

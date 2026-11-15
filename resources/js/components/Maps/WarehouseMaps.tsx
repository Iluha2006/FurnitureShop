import { Map, YMaps } from '@pbe/react-yandex-maps';
import { memo, forwardRef, useCallback, useImperativeHandle, useMemo } from 'react';
import WarehouseMarker from '@/components/Maps/WarehouseMarker';
import { YANDEX_MAP_MODULES } from '@/config/yandex';
import { useWarehousesCache } from '@/hooks/use-warehouses-cache';
import { useWarehouseMapController } from '@/hooks/use-warehouse-map-controller';
import type { PickupPointData } from '@/types/pickupPoint';

interface WarehouseMapsProps {
    apiKey: string;
    warehouses: PickupPointData[];
    /** Fallback map center when there are no points yet. */
    cityCenter?: [number, number];
    onBalloonClose?: () => void;
}

export type { WarehouseMapsProps };

export interface WarehouseMapsHandle {
    openBalloon: (name: string) => void;
    closeBalloon: () => void;
    flyTo: (coords: [number, number], name?: string) => void;
}

function WarehouseMapsInner(
    {
        warehouses: apiWarehouses,
        apiKey,
        cityCenter,
        onBalloonClose,
    }: WarehouseMapsProps,
    ref: React.Ref<WarehouseMapsHandle>,
) {
    const { warehouses } = useWarehousesCache(apiWarehouses);
    const controller = useWarehouseMapController(warehouses, cityCenter);

    // The YMaps layer is created exactly once: the query object is stable.
    const yandexQuery = useMemo(() => ({ apikey: apiKey }), [apiKey]);

    useImperativeHandle(
        ref,
        () => ({
            openBalloon: controller.openBalloon,
            closeBalloon: controller.closeBalloons,
            flyTo: controller.flyTo,
        }),
        [controller],
    );

    const handleMarkerClick = useCallback(
        (warehouse: PickupPointData) => {
            controller.openBalloon(warehouse.name);
        },
        [controller],
    );

    const handleMapClick = useCallback(() => {
        controller.closeBalloons();
        onBalloonClose?.();
    }, [controller, onBalloonClose]);

    return (
        <div className="pickup-map">
            <YMaps query={yandexQuery}>
                <Map
                    instanceRef={controller.handleMapLoad}
                    defaultState={controller.initialState}
                    modules={[...YANDEX_MAP_MODULES]}
                    className="pickup-map__canvas"
                    onActionEnd={controller.handleActionEnd}
                    onClick={handleMapClick}
                >
                    {warehouses.map((warehouse) => (
                        <WarehouseMarker
                            key={warehouse.id}
                            warehouse={warehouse}
                            onClick={handleMarkerClick}
                            instanceRef={controller.registerPlacemark(
                                warehouse.name,
                            )}
                        />
                    ))}
                </Map>
            </YMaps>
        </div>
    );
}

const WarehouseMaps = memo(forwardRef(WarehouseMapsInner));

export default WarehouseMaps;
export { WarehouseMaps };

import { Placemark } from '@pbe/react-yandex-maps';
import { memo, useCallback, useMemo } from 'react';
import type { RefCallback } from 'react';
import { buildPickupBalloonContent } from '@/lib/pickup-balloon';
import type { PickupPointData } from '@/types/pickupPoint';

interface WarehouseMarkerProps {
    warehouse: PickupPointData;
    onClick: (warehouse: PickupPointData) => void;
    iconColor?: string;
    instanceRef?: RefCallback<ymaps.Placemark>;
}

function WarehouseMarker({
    warehouse,
    onClick,
    iconColor = '#c05a2e',
    instanceRef,
}: WarehouseMarkerProps) {
    const handleClick = useCallback(() => {
        onClick(warehouse);
    }, [onClick, warehouse]);

    // Geometry, balloon, properties and options are memoised: changing
    // them re-renders the placemark and costs extra Yandex Maps work.
    const geometry = useMemo<[number, number]>(
        () => [warehouse.latitude, warehouse.longitude],
        [warehouse.latitude, warehouse.longitude],
    );

    const balloonContent = useMemo(
        () => buildPickupBalloonContent(warehouse, iconColor),
        [warehouse, iconColor],
    );

    const properties = useMemo(() => ({ balloonContent }), [balloonContent]);

    const options = useMemo(() => ({ iconColor }), [iconColor]);

    return (
        <Placemark
            instanceRef={
                instanceRef as unknown as React.MutableRefObject<
                    ymaps.Map | undefined
                >
            }
            geometry={geometry}
            properties={properties}
            options={options}
            onClick={handleClick}
        />
    );
}

export default memo(WarehouseMarker);

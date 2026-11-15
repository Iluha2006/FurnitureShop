import { useCallback, useEffect, useMemo, useRef } from 'react';
import { CITY_ZOOM, MOSCOW_CENTER } from '@/config/yandex';
import { useMapState } from '@/hooks/use-map-state';
import type { PickupPointData } from '@/types/pickupPoint';

export type WarehouseMapInitialState = {
    center: [number, number];
    zoom: number;
};

export type WarehouseMapController = {
    /** Initial view for <Map defaultState> — memoised, set only once. */
    initialState: WarehouseMapInitialState;
    /** Pass to <Map instanceRef>. */
    handleMapLoad: (map: ymaps.Map | null) => void;
    /** Pass to <Placemark instanceRef> for every point. */
    registerPlacemark: (
        name: string,
    ) => (placemark: ymaps.Placemark | null) => void;
    openBalloon: (name: string) => void;
    closeBalloons: () => void;
    /** Pan the map to a point and open its balloon. */
    flyTo: (coords: [number, number], name?: string) => void;
    /** Pass to <Map onActionEnd> — persists the current view. */
    handleActionEnd: () => void;
};

/**
 * Imperative controller for the warehouse Yandex map: owns the map and
 * placemark instances, balloon bookkeeping and view persistence.
 *
 * All callbacks are stable (useCallback with empty deps), so the map
 * component never re-renders because of the controller itself.
 */
export function useWarehouseMapController(
    warehouses: PickupPointData[],
    fallbackCenter: [number, number] = MOSCOW_CENTER,
): WarehouseMapController {
    const mapRef = useRef<ymaps.Map | null>(null);
    const placemarkRefs = useRef<Record<string, ymaps.Placemark>>({});

    const center = useMemo<[number, number]>(() => {
        const first = warehouses[0];

        return first
            ? [first.latitude, first.longitude]
            : fallbackCenter;
    }, [warehouses, fallbackCenter]);

    const { state, saveState } = useMapState(center, CITY_ZOOM);

    const initialState: WarehouseMapInitialState = {
        center: state.center,
        zoom: state.zoom,
    };

    const handleMapLoad = useCallback((map: ymaps.Map | null) => {
        mapRef.current = map;
    }, []);

    const registerPlacemark = useCallback(
        (name: string) => (placemark: ymaps.Placemark | null) => {
            if (placemark) {
                placemarkRefs.current[name] = placemark;
            }
        },
        [],
    );

    const closeBalloons = useCallback(() => {
        Object.values(placemarkRefs.current).forEach((placemark) => {
            if (placemark.balloon.isOpen()) {
                void placemark.balloon.close();
            }
        });
    }, []);

    const openBalloon = useCallback(
        (name: string) => {
            const placemark = placemarkRefs.current[name];

            if (placemark) {
                void placemark.balloon.open();
            }
        },
        [],
    );

    const flyTo = useCallback(
        (coords: [number, number], name?: string) => {
            const map = mapRef.current;

            if (!map) {
                return;
            }

            closeBalloons();

            void map.panTo(coords, { delay: 300, useMapMargin: true }).then(() => {
                if (name) {
                    openBalloon(name);
                }
            });
        },
        [closeBalloons, openBalloon],
    );

    // Fit all points into the viewport exactly once per data change.
    useEffect(() => {
        const map = mapRef.current;

        if (map && warehouses.length > 1) {
            const coords: [number, number][] = warehouses.map((point) => [
                point.latitude,
                point.longitude,
            ]);

            void map.setBounds(coords, {
                checkZoomRange: true,
                zoomMargin: 50,
            } as unknown as ymaps.IMapBoundsOptions);
        }
    }, [warehouses]);

    const handleActionEnd = useCallback(() => {
        const map = mapRef.current;

        if (!map) {
            return;
        }

        saveState(map.getCenter() as [number, number], map.getZoom());
    }, [saveState]);

    return {
        initialState,
        handleMapLoad,
        registerPlacemark,
        openBalloon,
        closeBalloons,
        flyTo,
        handleActionEnd,
    };
}

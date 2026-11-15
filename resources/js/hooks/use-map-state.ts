import { useCallback, useRef, useState } from 'react';

export type MapState = {
    center: [number, number];
    zoom: number;
};

const areCoordsClose = (a: [number, number], b: [number, number]): boolean =>
    Math.abs(a[0] - b[0]) < 0.0001 && Math.abs(a[1] - b[1]) < 0.0001;

/**
 * Keeps the last map view (center + zoom) without re-rendering the map
 * on every pan step: the state only updates when the view actually
 * changed, so the Yandex Maps SDK never re-initialises.
 */
export function useMapState(
    initialCenter: [number, number],
    initialZoom: number,
) {
    const stateRef = useRef<MapState>({
        center: initialCenter,
        zoom: initialZoom,
    });
    const [state, setState] = useState<MapState>({
        center: initialCenter,
        zoom: initialZoom,
    });

    const saveState = useCallback((center: [number, number], zoom: number) => {
        const current = stateRef.current;

        if (current.zoom === zoom && areCoordsClose(current.center, center)) {
            return;
        }

        const next: MapState = { center, zoom };
        stateRef.current = next;
        setState(next);
    }, []);

    return { state, saveState };
}

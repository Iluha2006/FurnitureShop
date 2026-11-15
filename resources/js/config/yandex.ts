/**
 * Yandex Maps configuration shared by the pickup point picker.
 *
 * The API key comes from the server (see config/yandex.php) and is passed
 * down as a prop so that it never leaks into the client bundle.
 */

/** Geodata of the pickup city (Moscow): [latitude, longitude]. */
export const MOSCOW_CENTER: [number, number] = [55.751244, 37.618423];

/** Default zoom for the city view. */
export const CITY_ZOOM = 10;

/** Yandex Maps modules loaded together with the SDK (exactly once). */
export const YANDEX_MAP_MODULES = [
    'control.ZoomControl',
    'control.FullscreenControl',
    'geoObject.addon.balloon',
] as const;

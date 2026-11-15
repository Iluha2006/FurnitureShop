import { useCallback, useRef } from 'react';
import { MapPin } from 'lucide-react';
import WarehouseMaps from '@/components/Maps/WarehouseMaps';
import type { WarehouseMapsHandle } from '@/components/Maps/WarehouseMaps';
import InputError from '@/components/input-error';
import type { PickupPointData } from '@/types/pickupPoint';

interface PickupPickerProps {
    city: string;
    /** Geodata of the city: [latitude, longitude] fallback map center. */
    cityCenter?: [number, number];
    points: PickupPointData[];
    apiKey: string;
    selectedId: string;
    error?: string;
    onSelect: (point: PickupPointData) => void;
}

export default function PickupPicker({
    city,
    cityCenter,
    points,
    apiKey,
    selectedId,
    error,
    onSelect,
}: PickupPickerProps) {
    const mapHandle = useRef<WarehouseMapsHandle>(null);

    const handleSelect = useCallback(
        (point: PickupPointData) => {
            onSelect(point);
            mapHandle.current?.flyTo(
                [point.latitude, point.longitude],
                point.name,
            );
        },
        [onSelect],
    );

    const selectedPoint = points.find((point) => point.id === selectedId);

    return (
        <div className="pickup">
            <div className="pickup-city">
                <MapPin size={18} aria-hidden="true" />
                <div>
                    <b>Город получения заказа</b>
                    <span>{city}</span>
                </div>
            </div>

            {/* Submitted with the form so the backend gets the chosen point. */}
            <input
                type="hidden"
                name="pickup_point_id"
                value={selectedId}
            />

            {points.length > 0 && (
                <>
                    <div className="pickup-points">
                        {points.map((point) => (
                            <button
                                type="button"
                                key={point.id}
                                onClick={() => handleSelect(point)}
                                className={
                                    point.id === selectedId
                                        ? 'pickup-point pickup-point--active'
                                        : 'pickup-point'
                                }
                            >
                                <b className="pickup-point__name">
                                    {point.name}
                                </b>
                                <span className="pickup-point__address">
                                    {point.address}
                                </span>
                                {point.opening_hours && (
                                    <span className="pickup-point__hours">
                                        {point.opening_hours}
                                    </span>
                                )}
                            </button>
                        ))}
                    </div>

                    <InputError message={error} className="auth-error" />

                    {apiKey && (
                        <WarehouseMaps
                            ref={mapHandle}
                            apiKey={apiKey}
                            warehouses={points}
                            cityCenter={cityCenter}
                        />
                    )}

                    {selectedPoint && (
                        <p className="pickup__selected">
                            Выбрано: <b>{selectedPoint.name}</b>,{' '}
                            {selectedPoint.address}
                        </p>
                    )}
                </>
            )}
        </div>
    );
}

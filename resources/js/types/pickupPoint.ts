export type PickupPointData = {
    id: string;
    name: string;
    address: string;
    phone: string | null;
    email: string | null;
    opening_hours: string | null;
    description: string | null;
    latitude: number;
    longitude: number;
    sort?: number;
    is_active?: boolean;
};

/** How the customer receives the order. */
export type DeliveryMethod = 'delivery' | 'pickup';

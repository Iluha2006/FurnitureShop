export type CartItemData = {
    id: number;
    furniture_id: number;
    name: string;
    quantity: number;

    /** Units on hand: the ceiling for this cart line. */
    stock: number;
    price_amount: string;
    price_currency: string;
    price_formatted: string;
    line_total: string;
    line_total_formatted: string;
    mainImage: string | null;
};

export type CartData = {
    items: CartItemData[];
    total: string;
    total_formatted: string;
    count: number;
};

/** Map of furniture id (as string) to its quantity in the cart. */
export type CartQuantities = Record<string, number>;

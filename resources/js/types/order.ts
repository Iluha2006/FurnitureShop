export type OrderItemData = {
    id: number;
    furniture_id: number;
    name: string;
    quantity: number;
    price_amount: string;
    price_currency: string;
    price_formatted: string;
    line_total: string;
    line_total_formatted: string;
    mainImage: string | null;
};

export type OrderData = {
    id: number;
    status: string;
    status_label: string;
    total_amount: string;
    total_currency: string;
    total_formatted: string;
    created_at: string;
    customer_name: string | null;
    customer_phone: string | null;
    customer_email: string | null;
    delivery_address: string | null;
    comment: string | null;
    items: OrderItemData[];
    count: number;
};

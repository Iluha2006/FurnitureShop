export type FurnitureCategoryData = {
    category_id: number;
    name: string;
};

export type FurnitureManufacturerData = {
    manufacturer_id: number;
    name: string;
    slug: string | null;
};

export type FurnitureImageData = {
    images_id: number;
    furniture_id: number;
    path_image: string;
    is_main: boolean;
};

export type FurnitureData = {
    furniture_id: number;
    name: string;
    description: string | null;
    color: string | null;
    price: string;
    quantity: number;
    manufacturer_id: number | null;
    category_id: number;
    specifications_id: number | null;
};

export type FurnitureCardData = {
    furniture: FurnitureData;
    manufacturer: FurnitureManufacturerData | null;
    mainImage: string | null;
};

export type FurnitureDetailData = {
    furniture: FurnitureData;
    category: FurnitureCategoryData | null;
    manufacturer: FurnitureManufacturerData | null;
    images: FurnitureImageData[];
};

export type FurnitureSpecificationData = {
    id: number;
    width_cm: number | null;
    length_cm: number | null;
    height_cm: number | null;
    folding_type: string | null;
    insert_type: string | null;
    materials: string | null;
    surface: string | null;
    weight_kg: number | null;
    package_volume_m3: number | null;
    warranty: string | null;
};

export type PaginatorLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type Paginator<T> = {
    current_page: number;
    data: T[];
    first_page_url: string | null;
    from: number | null;
    last_page: number;
    last_page_url: string | null;
    links: PaginatorLink[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
};
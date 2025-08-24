
export interface Dish {
    id: number;
    name: string;
    description: string;
    price: number;
    currency_symbol: string;
    has_size: boolean;
    has_addon: boolean;
    image?: string;
    share_link: string;
    is_favorites: boolean;
    mostOrdered: boolean;
  }
export interface Category {
    id: number;
    name: string;
    description?: string | null;
    image_path: string;
    dishes: Dish[];
}

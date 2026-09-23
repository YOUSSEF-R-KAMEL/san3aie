export type UserRole = 'worker' | 'customer';

export interface Category {
  id: number;
  name: string;
  icon: string | null;
}

export interface Area {
  id: number;
  name: string;
}

export interface User {
  id: number;
  name: string;
  phone: string;
  role: UserRole;
  category_id: number | null;
  area_id: number;
  category?: Category;
  area?: Area;
}

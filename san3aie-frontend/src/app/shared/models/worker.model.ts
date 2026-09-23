import { Category } from './user.model';

export interface WorkerArea {
  id: number;
  name: string;
}

export interface Worker {
  id: number;
  name: string;
  phone: string;
  role: 'worker';
  bio?: string;
  experience_years?: number;
  avatar?: string | null;
  is_available: boolean;
  is_verified: boolean;
  is_blocked?: boolean;
  is_premium?: boolean;
  is_featured?: boolean;
  premium_until?: string | null;
  featured_until?: string | null;
  latitude?: number | null;
  longitude?: number | null;
  rating?: number;
  completed_jobs?: number;
  distance?: number;
  category?: Category;
  area?: WorkerArea;
  category_id: number;
  area_id: number;
  created_at?: string;
}

export interface WorkerSearchParams {
  category_id?: number | null;
  area_id?: number | null;
  search?: string;
  rating?: number | null;
  is_available?: boolean | null;
  is_verified?: boolean | null;
  premium?: boolean | null;
  featured?: boolean | null;
  latitude?: number | null;
  longitude?: number | null;
  distance?: number | null;
  sort?: 'rating' | 'distance' | 'completed_jobs' | 'newest';
}

import { Worker } from './worker.model';

export type ServiceRequestStatus =
  | 'pending'
  | 'accepted'
  | 'rejected'
  | 'in_progress'
  | 'completed'
  | 'cancelled';

export interface ServiceRequestReview {
  id: number;
  rating: number;
  comment?: string;
  created_at?: string;
}

export interface ServiceRequest {
  id: number;
  customer_id: number;
  worker_id: number;
  category_id: number;
  area_id: number;
  description: string;
  location: string;
  status: ServiceRequestStatus;
  created_at: string;
  updated_at: string;
  worker?: Worker;
  customer?: {
    id: number;
    name: string;
    phone?: string;
  };
  category?: {
    id: number;
    name: string;
    icon?: string;
  };
  area?: {
    id: number;
    name: string;
  };
  review?: ServiceRequestReview;
}

export interface CreateServiceRequestData {
  worker_id: number;
  category_id: number;
  area_id: number;
  description: string;
  location: string;
}

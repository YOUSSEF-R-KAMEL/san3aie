import { Worker } from './worker.model';

export type ServiceRequestStatus =
  | 'pending'
  | 'accepted'
  | 'rejected'
  | 'in_progress'
  | 'completed'
  | 'cancelled';

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
  category?: {
    id: number;
    name: string;
    icon?: string;
  };
  area?: {
    id: number;
    name: string;
  };
}

export interface CreateServiceRequestData {
  worker_id: number;
  category_id: number;
  area_id: number;
  description: string;
  location: string;
}

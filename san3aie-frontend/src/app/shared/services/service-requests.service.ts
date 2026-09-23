import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import {
  CreateServiceRequestData,
  ServiceRequest,
} from '../models/service-request.model';

@Injectable({
  providedIn: 'root',
})
export class ServiceRequestsService {
  private apiUrl = 'http://127.0.0.1:8000/api';

  constructor(private http: HttpClient) {}

  createRequest(
    data: CreateServiceRequestData
  ): Observable<{
    message: string;
    request: ServiceRequest;
  }> {
    return this.http.post<{
      message: string;
      request: ServiceRequest;
    }>(
      `${this.apiUrl}/service-requests`,
      data
    );
  }

  getCustomerRequests(): Observable<any> {
    return this.http.get(
      `${this.apiUrl}/customer/requests`
    );
  }

  getWorkerRequests(): Observable<any> {
    return this.http.get(
      `${this.apiUrl}/worker/requests`
    );
  }

  getRequest(id: number): Observable<{
    request: ServiceRequest;
  }> {
    return this.http.get<{
      request: ServiceRequest;
    }>(
      `${this.apiUrl}/service-requests/${id}`
    );
  }

  acceptRequest(id: number): Observable<any> {
    return this.http.post(
      `${this.apiUrl}/service-requests/${id}/accept`,
      {}
    );
  }

  rejectRequest(id: number): Observable<any> {
    return this.http.post(
      `${this.apiUrl}/service-requests/${id}/reject`,
      {}
    );
  }

  updateStatus(
    id: number,
    status: 'in_progress' | 'completed'
  ): Observable<any> {
    return this.http.post(
      `${this.apiUrl}/service-requests/${id}/status`,
      { status }
    );
  }

  cancelRequest(id: number): Observable<any> {
    return this.http.post(
      `${this.apiUrl}/service-requests/${id}/cancel`,
      {}
    );
  }
}

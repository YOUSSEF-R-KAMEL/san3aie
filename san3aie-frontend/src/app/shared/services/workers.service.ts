import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Worker, WorkerSearchParams } from '../models/worker.model';

@Injectable({
  providedIn: 'root',
})
export class WorkersService {
  private apiUrl = 'http://127.0.0.1:8000/api';

  constructor(private http: HttpClient) {}

  getWorkers(
    params: WorkerSearchParams = {},
  ): Observable<{ workers: Worker[] }> {
    let httpParams = new HttpParams();

    Object.entries(params).forEach(([key, value]) => {
      if (value !== null && value !== undefined && value !== '') {
        httpParams = httpParams.set(key, String(value));
      }
    });

    return this.http.get<{ workers: Worker[] }>(`${this.apiUrl}/workers`, {
      params: httpParams,
    });
  }
  getWorker(id: number): Observable<Worker> {
    return this.http.get<Worker>(`${this.apiUrl}/workers/${id}`);
  }
}

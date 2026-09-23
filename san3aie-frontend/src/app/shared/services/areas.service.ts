import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Area } from '../models/user.model';

@Injectable({
  providedIn: 'root'
})
export class AreasService {
  private readonly apiUrl = 'http://127.0.0.1:8000/api/areas';

  constructor(private http: HttpClient) {}

  getAreas(): Observable<{ areas: Area[] }> {
    return this.http.get<{ areas: Area[] }>(this.apiUrl);
  }
}

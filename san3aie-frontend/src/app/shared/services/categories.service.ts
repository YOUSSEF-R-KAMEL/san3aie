import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Category } from '../models/user.model';

@Injectable({
  providedIn: 'root'
})
export class CategoriesService {
  private readonly apiUrl = 'http://127.0.0.1:8000/api/categories';

  constructor(private http: HttpClient) {}

  getCategories(): Observable<{ categories: Category[] }> {
    return this.http.get<{ categories: Category[] }>(this.apiUrl);
  }
}

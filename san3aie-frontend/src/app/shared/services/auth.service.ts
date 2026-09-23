import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';
import { User, UserRole } from '../models/user.model';

interface AuthResponse {
  message: string;
  user: User;
  token: string;
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private readonly apiUrl = 'http://127.0.0.1:8000/api';
  private readonly tokenKey = 'san3aie_token';
  private readonly userKey = 'san3aie_user';

  constructor(private http: HttpClient) {}

  register(data: {
    name: string;
    phone: string;
    password: string;
    password_confirmation: string;
    role: UserRole;
    category_id: number | null;
    area_id: number | null;
  }): Observable<AuthResponse> {
    return this.http.post<AuthResponse>(`${this.apiUrl}/register`, data).pipe(
      tap(response => this.saveAuthData(response))
    );
  }

  login(phone: string, password: string): Observable<AuthResponse> {
    return this.http.post<AuthResponse>(`${this.apiUrl}/login`, {
      phone,
      password
    }).pipe(
      tap(response => this.saveAuthData(response))
    );
  }

  me(): Observable<{ user: User }> {
    return this.http.get<{ user: User }>(`${this.apiUrl}/me`);
  }

  logout(): Observable<{ message: string }> {
    return this.http.post<{ message: string }>(`${this.apiUrl}/logout`, {}).pipe(
      tap(() => this.clearAuthData())
    );
  }

  getToken(): string | null {
    return localStorage.getItem(this.tokenKey);
  }

  getCurrentUser(): User | null {
    const user = localStorage.getItem(this.userKey);

    if (!user) {
      return null;
    }

    return JSON.parse(user);
  }

  isLoggedIn(): boolean {
    return !!this.getToken();
  }

  hasRole(role: UserRole): boolean {
    return this.getCurrentUser()?.role === role;
  }

  private saveAuthData(response: AuthResponse): void {
    localStorage.setItem(this.tokenKey, response.token);
    localStorage.setItem(this.userKey, JSON.stringify(response.user));
  }

  private clearAuthData(): void {
    localStorage.removeItem(this.tokenKey);
    localStorage.removeItem(this.userKey);
  }
}

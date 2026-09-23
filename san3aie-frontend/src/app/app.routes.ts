import { Routes } from '@angular/router';

import { authGuard } from './core/guards/auth.guard';

export const routes: Routes = [
  {
    path: '',
    loadComponent: () =>
      import('./features/pages/home/home.component').then(
        (component) => component.HomeComponent,
      ),
  },
  {
    path: 'login',
    loadComponent: () =>
      import('./features/pages/login/login.component').then(
        (component) => component.LoginComponent,
      ),
  },
  {
    path: 'register',
    loadComponent: () =>
      import('./features/pages/register/register.component').then(
        (component) => component.RegisterComponent,
      ),
  },
  {
    path: 'dashboard',
    canActivate: [authGuard],
    loadComponent: () =>
      import('./features/pages/dashboard/dashboard.component').then(
        (component) => component.DashboardComponent,
      ),
  },
  {
    path: 'workers',
    loadComponent: () =>
      import('./features/pages/workers/workers.component').then(
        (component) => component.WorkersComponent,
      ),
  },
  {
    path: 'workers/:id',
    loadComponent: () =>
      import('./features/pages/worker-details/worker-details.component').then(
        (component) => component.WorkerDetailsComponent,
      ),
  },
  {
    path: '403',
    loadComponent: () =>
      import('./features/pages/forbidden/forbidden.component').then(
        (component) => component.ForbiddenComponent,
      ),
  },
  {
    path: '**',
    loadComponent: () =>
      import('./features/pages/not-found/not-found.component').then(
        (component) => component.NotFoundComponent,
      ),
  },
];

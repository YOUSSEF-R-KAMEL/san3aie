import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import {
  Worker,
  WorkerSearchParams,
} from '../../../shared/models/worker.model';
import { Category, Area } from '../../../shared/models/user.model';
import { WorkersService } from '../../../shared/services/workers.service';
import { CategoriesService } from '../../../shared/services/categories.service';
import { AreasService } from '../../../shared/services/areas.service';
import { EmptyStateComponent } from '../../../shared/components/empty-state/empty-state.component';
import { ErrorStateComponent } from '../../../shared/components/error-state/error-state.component';
import { WorkerSkeletonComponent } from '../../../shared/components/worker-skeleton/worker-skeleton.component';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-workers',
  standalone: true,
  imports: [
    FormsModule,
    RouterLink,
    EmptyStateComponent,
    ErrorStateComponent,
    WorkerSkeletonComponent,
  ],
  templateUrl: './workers.component.html',
})
export class WorkersComponent implements OnInit {
  workers: Worker[] = [];

  categories: Category[] = [];
  areas: Area[] = [];

  search = '';
  categoryId: number | null = null;
  areaId: number | null = null;
  rating: number | null = null;
  distance: number | null = null;

  isAvailable = false;
  isVerified = false;
  isPremium = false;
  isFeatured = false;

  sort: WorkerSearchParams['sort'] = 'newest';

  loading = false;
  error = '';

  constructor(
    private workersService: WorkersService,
    private categoriesService: CategoriesService,
    private areasService: AreasService,
  ) {}

  ngOnInit(): void {
    this.loadCategories();
    this.loadAreas();
    this.loadWorkers();
  }

  loadCategories(): void {
    this.categoriesService.getCategories().subscribe({
      next: (response) => {
        this.categories = response.categories;
      },
    });
  }

  loadAreas(): void {
    this.areasService.getAreas().subscribe({
      next: (response) => {
        this.areas = response.areas;
      },
    });
  }

  loadWorkers(): void {
    this.loading = true;
    this.error = '';

    const params: WorkerSearchParams = {
      category_id: this.categoryId,
      area_id: this.areaId,
      search: this.search.trim(),
      is_available: this.isAvailable ? true : null,
      is_verified: this.isVerified ? true : null,
      premium: this.isPremium ? true : null,
      distance: this.distance,
      sort: this.sort,
    };

    this.workersService.getWorkers(params).subscribe({
      next: (response) => {
        this.workers = response.workers;
        this.loading = false;
      },
      error: () => {
        this.loading = false;
        this.error = 'تعذر تحميل الصنايعية.';
      },
    });
  }

  resetFilters(): void {
    this.search = '';
    this.categoryId = null;
    this.areaId = null;
    this.rating = null;
    this.distance = null;
    this.isAvailable = false;
    this.isVerified = false;
    this.isPremium = false;
    this.isFeatured = false;
    this.sort = 'newest';

    this.loadWorkers();
  }
}

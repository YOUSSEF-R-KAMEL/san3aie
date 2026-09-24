import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { Worker } from '../../../shared/models/worker.model';
import { WorkersService } from '../../../shared/services/workers.service';
import { AuthService } from '../../../shared/services/auth.service';
import { LoadingComponent } from '../../../shared/components/loading/loading.component';
import { ErrorStateComponent } from '../../../shared/components/error-state/error-state.component';
import { ServiceRequestFormComponent } from '../../components/service-request-form/service-request-form.component';
import { ToastService } from '../../../shared/services/toast.service';

@Component({
  selector: 'app-worker-details',
  standalone: true,
  imports: [
    RouterLink,
    LoadingComponent,
    ErrorStateComponent,
    ServiceRequestFormComponent,
  ],
  templateUrl: './worker-details.component.html',
})
export class WorkerDetailsComponent implements OnInit {
  worker: Worker | null = null;
  loading = true;
  error = '';

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private workersService: WorkersService,
    public authService: AuthService,
    private toast: ToastService
  ) {}

  ngOnInit(): void {
    const id = Number(
      this.route.snapshot.paramMap.get('id')
    );

    if (!id) {
      this.error = 'الصنايعي غير موجود.';
      this.loading = false;
      return;
    }

    this.loadWorker(id);
  }

  loadWorker(id: number): void {
    this.loading = true;
    this.error = '';

    this.workersService.getWorker(id).subscribe({
      next: (worker) => {
        this.worker = worker;
        this.loading = false;
      },
      error: () => {
        this.loading = false;
        this.error = 'تعذر تحميل بيانات الصنايعي.';
      },
    });
  }

  canRequestService(): boolean {
    const user = this.authService.getCurrentUser();

    return !!user &&
      user.role === 'customer' &&
      !!this.worker;
  }

  requestService(): void {
    if (!this.worker) {
      return;
    }

    const user = this.authService.getCurrentUser();

    if (!user) {
      this.router.navigate(['/login']);
      return;
    }

    if (user.role !== 'customer') {
      this.toast.info(
        'طلب الخدمة متاح للعملاء فقط.'
      );
      return;
    }

    const element = document.getElementById(
      'service-request-form'
    );

    element?.scrollIntoView({
      behavior: 'smooth',
      block: 'start',
    });
  }
}

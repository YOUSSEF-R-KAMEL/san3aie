import { Component, OnInit } from '@angular/core';
import { RouterLink } from '@angular/router';
import { ServiceRequest } from '../../../shared/models/service-request.model';
import { ServiceRequestsService } from '../../../shared/services/service-requests.service';
import { LoadingComponent } from '../../../shared/components/loading/loading.component';
import { EmptyStateComponent } from '../../../shared/components/empty-state/empty-state.component';
import { ErrorStateComponent } from '../../../shared/components/error-state/error-state.component';
import { ToastService } from './../../../shared/services/toast.service';

@Component({
  selector: 'app-worker-requests',
  standalone: true,
  imports: [
  RouterLink,
    LoadingComponent,
    EmptyStateComponent,
    ErrorStateComponent,
  ],
  templateUrl: './worker-requests.component.html',
})
export class WorkerRequestsComponent implements OnInit {
  requests: ServiceRequest[] = [];
  loading = true;
  error = '';
  actionLoading: number | null = null;

  constructor(
    private serviceRequestsService: ServiceRequestsService,
    private toast: ToastService,
  ) {}

  ngOnInit(): void {
    this.loadRequests();
  }

  loadRequests(): void {
    this.loading = true;
    this.error = '';

    this.serviceRequestsService.getWorkerRequests().subscribe({
      next: (response) => {
        this.requests = response.data;
        this.loading = false;
      },
      error: () => {
        this.loading = false;
        this.error = 'تعذر تحميل طلبات العملاء.';
      },
    });
  }

  getStatusLabel(status: string): string {
    const statuses: Record<string, string> = {
      pending: 'قيد الانتظار',
      accepted: 'تم القبول',
      rejected: 'مرفوض',
      in_progress: 'جاري التنفيذ',
      completed: 'مكتمل',
      cancelled: 'ملغي',
    };

    return statuses[status] || status;
  }

  acceptRequest(id: number): void {
    this.actionLoading = id;

    this.serviceRequestsService.acceptRequest(id).subscribe({
      next: (response) => {
        this.actionLoading = null;

        this.toast.success(response.message || 'تم قبول الطلب.');

        this.loadRequests();
      },
      error: () => {
        this.actionLoading = null;
      },
    });
  }

  rejectRequest(id: number): void {
    const confirmed = window.confirm('هل أنت متأكد من رفض الطلب؟');

    if (!confirmed) {
      return;
    }

    this.actionLoading = id;

    this.serviceRequestsService.rejectRequest(id).subscribe({
      next: (response) => {
        this.actionLoading = null;

        this.toast.success(response.message || 'تم رفض الطلب.');

        this.loadRequests();
      },
      error: () => {
        this.actionLoading = null;
      },
    });
  }

  updateStatus(id: number, status: 'in_progress' | 'completed'): void {
    this.actionLoading = id;

    this.serviceRequestsService.updateStatus(id, status).subscribe({
      next: (response) => {
        this.actionLoading = null;

        this.toast.success(response.message || 'تم تحديث حالة الطلب.');

        this.loadRequests();
      },
      error: () => {
        this.actionLoading = null;
      },
    });
  }
}

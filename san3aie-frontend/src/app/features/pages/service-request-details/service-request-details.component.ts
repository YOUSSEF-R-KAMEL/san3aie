import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { ServiceRequest } from '../../../shared/models/service-request.model';
import { ServiceRequestsService } from '../../../shared/services/service-requests.service';
import { LoadingComponent } from '../../../shared/components/loading/loading.component';
import { ErrorStateComponent } from '../../../shared/components/error-state/error-state.component';
import { ToastService } from './../../../shared/services/toast.service';
import { DatePipe } from '@angular/common';
import { ReviewFormComponent } from '../../components/review-form/review-form.component';

@Component({
  selector: 'app-service-request-details',
  standalone: true,
  imports: [RouterLink, DatePipe, LoadingComponent, ErrorStateComponent, ReviewFormComponent],
  templateUrl: './service-request-details.component.html',
})
export class ServiceRequestDetailsComponent implements OnInit {
  request: ServiceRequest | null = null;
  loading = true;
  error = '';
  cancelling = false;

  constructor(
    private route: ActivatedRoute,
    private serviceRequestsService: ServiceRequestsService,
    private toast: ToastService,
  ) {}

  ngOnInit(): void {
    const id = Number(this.route.snapshot.paramMap.get('id'));

    if (!id) {
      this.error = 'الطلب غير موجود.';
      this.loading = false;
      return;
    }

    this.loadRequest(id);
  }

  loadRequest(id: number): void {
    this.loading = true;
    this.error = '';

    this.serviceRequestsService.getRequest(id).subscribe({
      next: (response) => {
        this.request = response.request;
        this.loading = false;
      },
      error: () => {
        this.loading = false;
        this.error = 'تعذر تحميل تفاصيل الطلب.';
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

  canCancel(): boolean {
    return (
      this.request?.status === 'pending' || this.request?.status === 'accepted'
    );
  }

  cancelRequest(): void {
    if (!this.request || !this.canCancel()) {
      return;
    }

    const confirmed = window.confirm('هل أنت متأكد من إلغاء طلب الخدمة؟');

    if (!confirmed) {
      return;
    }

    this.cancelling = true;

    this.serviceRequestsService.cancelRequest(this.request.id).subscribe({
      next: (response) => {
        this.cancelling = false;

        this.toast.success(response.message || 'تم إلغاء الطلب بنجاح.');

        this.loadRequest(this.request!.id);
      },
      error: () => {
        this.cancelling = false;
      },
    });
  }
}

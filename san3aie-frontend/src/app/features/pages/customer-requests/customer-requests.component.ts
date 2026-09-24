import { Component, OnInit } from '@angular/core';
import { RouterLink } from '@angular/router';
import { ServiceRequest } from '../../../shared/models/service-request.model';
import { ServiceRequestsService } from '../../../shared/services/service-requests.service';
import { LoadingComponent } from '../../../shared/components/loading/loading.component';
import { EmptyStateComponent } from '../../../shared/components/empty-state/empty-state.component';
import { ErrorStateComponent } from '../../../shared/components/error-state/error-state.component';

@Component({
  selector: 'app-customer-requests',
  standalone: true,
  imports: [
    RouterLink,
    LoadingComponent,
    EmptyStateComponent,
    ErrorStateComponent,
  ],
  templateUrl: './customer-requests.component.html',
})
export class CustomerRequestsComponent implements OnInit {
  requests: ServiceRequest[] = [];
  loading = true;
  error = '';

  constructor(
    private serviceRequestsService: ServiceRequestsService
  ) {}

  ngOnInit(): void {
    this.loadRequests();
  }

  loadRequests(): void {
    this.loading = true;
    this.error = '';

    this.serviceRequestsService.getCustomerRequests().subscribe({
      next: (response) => {
        this.requests = response.data;
        this.loading = false;
      },
      error: () => {
        this.loading = false;
        this.error = 'تعذر تحميل طلباتك.';
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
}

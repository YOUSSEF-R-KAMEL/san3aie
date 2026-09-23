import { Component, Input } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { Worker } from '../../../shared/models/worker.model';
import { ServiceRequestsService } from '../../../shared/services/service-requests.service';
import { ToastService } from '../../../shared/services/toast.service';

@Component({
  selector: 'app-service-request-form',
  standalone: true,
  imports: [FormsModule],
  templateUrl: './service-request-form.component.html',
})
export class ServiceRequestFormComponent {
  @Input({ required: true }) worker!: Worker;

  description = '';
  location = '';

  loading = false;
  submitted = false;

  constructor(
    private serviceRequestsService: ServiceRequestsService,
    private toast: ToastService,
    private router: Router
  ) {}

  submit(): void {
    this.submitted = true;

    if (!this.isValid()) {
      return;
    }

    this.loading = true;

    this.serviceRequestsService
      .createRequest({
        worker_id: this.worker.id,
        category_id: this.worker.category_id,
        area_id: this.worker.area_id,
        description: this.description.trim(),
        location: this.location.trim(),
      })
      .subscribe({
        next: (response) => {
          this.loading = false;

          this.toast.success(
            response.message || 'تم إرسال طلب الخدمة بنجاح.'
          );

          this.router.navigate([
            '/dashboard',
          ]);
        },
        error: () => {
          this.loading = false;
        },
      });
  }

  private isValid(): boolean {
    return (
      this.description.trim().length >= 10 &&
      this.description.trim().length <= 3000 &&
      this.location.trim().length >= 3 &&
      this.location.trim().length <= 500
    );
  }
}

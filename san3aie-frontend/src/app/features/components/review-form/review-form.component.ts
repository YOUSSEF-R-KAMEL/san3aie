import { Component, Input } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { ServiceRequestsService } from '../../../shared/services/service-requests.service';
import { ToastService } from './../../../shared/services/toast.service';

@Component({
  selector: 'app-review-form',
  standalone: true,
  imports: [FormsModule],
templateUrl: './review-form.component.html',
})
export class ReviewFormComponent {
  @Input({ required: true }) requestId!: number;

  rating = 0;
  comment = '';
  loading = false;
  submitted = false;

  constructor(
    private serviceRequestsService: ServiceRequestsService,
    private toast: ToastService
  ) {}

  setRating(value: number): void {
    this.rating = value;
  }

  submit(): void {
    this.submitted = true;

    if (this.rating < 1 || this.rating > 5) {
      return;
    }

    this.loading = true;

    this.serviceRequestsService
      .createReview(
        this.requestId,
        this.rating,
        this.comment.trim()
      )
      .subscribe({
        next: (response) => {
          this.loading = false;

          this.toast.success(
            response.message || 'تم إرسال التقييم بنجاح.'
          );

          this.submitted = false;
        },
        error: () => {
          this.loading = false;
        },
      });
  }
}

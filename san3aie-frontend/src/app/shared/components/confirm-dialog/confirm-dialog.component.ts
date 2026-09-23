import { Component, EventEmitter, Input, Output } from '@angular/core';

@Component({
  selector: 'app-confirm-dialog',
  standalone: true,
  templateUrl: './confirm-dialog.component.html',
})
export class ConfirmDialogComponent {
  @Input() open = false;
  @Input() title = 'تأكيد العملية';
  @Input() message = 'هل أنت متأكد من تنفيذ هذه العملية؟';

  @Output() confirmed = new EventEmitter<void>();
  @Output() cancelled = new EventEmitter<void>();
}

import { Component, EventEmitter, Input, Output } from '@angular/core';

@Component({
  selector: 'app-error-state',
  standalone: true,
  templateUrl: './error-state.component.html',
})
export class ErrorStateComponent {
  @Input() title = 'حدث خطأ';
  @Input() message = 'حدث خطأ أثناء تحميل البيانات.';
  @Output() retry = new EventEmitter<void>();
}

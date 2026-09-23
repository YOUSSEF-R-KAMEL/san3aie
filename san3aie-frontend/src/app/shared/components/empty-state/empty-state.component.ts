import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-empty-state',
  standalone: true,
  templateUrl: './empty-state.component.html',
})
export class EmptyStateComponent {
  @Input() title = 'لا توجد نتائج';
  @Input() message = 'لم نجد نتائج مطابقة للبحث.';
  @Input() icon = '🔍';
}

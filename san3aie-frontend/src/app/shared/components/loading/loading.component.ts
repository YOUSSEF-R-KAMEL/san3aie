import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-loading',
  standalone: true,
  templateUrl: './loading.component.html',
})
export class LoadingComponent {
  @Input() text = 'جاري التحميل...';
}

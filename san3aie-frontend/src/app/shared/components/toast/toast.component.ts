import { Component } from '@angular/core';
import { AsyncPipe } from '@angular/common';
import { ToastService } from '../../services/toast.service';

@Component({
  selector: 'app-toast',
  standalone: true,
  imports: [AsyncPipe],
  templateUrl: './toast.component.html',
})
export class ToastComponent {
  constructor(public toastService: ToastService) {}
}

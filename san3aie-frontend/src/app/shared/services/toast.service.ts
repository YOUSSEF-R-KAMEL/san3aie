import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';

export type ToastType = 'success' | 'error' | 'info';

export interface Toast {
  id: number;
  message: string;
  type: ToastType;
}

@Injectable({
  providedIn: 'root',
})
export class ToastService {
  private counter = 0;

  private toastSubject = new BehaviorSubject<Toast[]>([]);

  toasts$ = this.toastSubject.asObservable();

  success(message: string): void {
    this.show(message, 'success');
  }

  error(message: string): void {
    this.show(message, 'error');
  }

  info(message: string): void {
    this.show(message, 'info');
  }

  remove(id: number): void {
    this.toastSubject.next(
      this.toastSubject.value.filter(toast => toast.id !== id)
    );
  }

  private show(message: string, type: ToastType): void {
    const id = ++this.counter;

    const toast: Toast = {
      id,
      message,
      type,
    };

    this.toastSubject.next([
      ...this.toastSubject.value,
      toast,
    ]);

    setTimeout(() => {
      this.remove(id);
    }, 4000);
  }
}

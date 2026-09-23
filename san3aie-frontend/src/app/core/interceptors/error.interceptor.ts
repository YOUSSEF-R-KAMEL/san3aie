import {
  HttpErrorResponse,
  HttpInterceptorFn,
} from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { catchError, throwError } from 'rxjs';
import { ToastService } from '../../shared/services/toast.service';

export const errorInterceptor: HttpInterceptorFn = (req, next) => {
  const toast = inject(ToastService);
  const router = inject(Router);

  return next(req).pipe(
    catchError((error: HttpErrorResponse) => {
      if (error.status === 0) {
        toast.error('تعذر الاتصال بالخادم.');
      } else if (error.status === 401) {
        toast.error('انتهت جلسة تسجيل الدخول.');

        localStorage.removeItem('san3aie_token');
        localStorage.removeItem('san3aie_user');

        router.navigate(['/login']);
      } else if (error.status === 403) {
        router.navigate(['/403']);
      } else if (error.status === 422) {
        toast.error('من فضلك راجع البيانات المدخلة.');
      } else if (error.status >= 500) {
        toast.error('حدث خطأ في الخادم.');
      } else {
        toast.error(
          error.error?.message || 'حدث خطأ غير متوقع.'
        );
      }

      return throwError(() => error);
    })
  );
};

import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../../../shared/services/auth.service';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [FormsModule, RouterLink],
  templateUrl: './login.component.html',
})
export class LoginComponent {
  phone = '';
  password = '';
  loading = false;
  error = '';

  constructor(
    private authService: AuthService,
    private router: Router,
  ) {}

  login(): void {
    this.error = '';

    if (!this.phone || !this.password) {
      this.error = 'من فضلك أدخل رقم الهاتف وكلمة المرور.';
      return;
    }

    this.loading = true;

    this.authService.login(this.phone, this.password).subscribe({
      next: () => {
        this.loading = false;
        this.router.navigate(['/dashboard']);
      },
      error: (error) => {
        this.loading = false;
        this.error = error?.error?.message || 'حدث خطأ أثناء تسجيل الدخول.';
      },
    });
  }
}

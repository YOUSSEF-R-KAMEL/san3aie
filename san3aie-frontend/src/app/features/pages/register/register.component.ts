import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { Area, Category, UserRole } from '../../../shared/models/user.model';
import { AuthService } from '../../../shared/services/auth.service';
import { CategoriesService } from '../../../shared/services/categories.service';
import { AreasService } from '../../../shared/services/areas.service';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [FormsModule, RouterLink],
  templateUrl: './register.component.html',
})
export class RegisterComponent implements OnInit {
  name = '';
  phone = '';
  password = '';
  passwordConfirmation = '';

  role: UserRole = 'customer';

  categoryId: number | null = null;
  areaId: number | null = null;

  categories: Category[] = [];
  areas: Area[] = [];

  loading = false;
  submitted = false;
  error = '';

  constructor(
    private authService: AuthService,
    private categoriesService: CategoriesService,
    private areasService: AreasService,
    private router: Router,
  ) {}

  ngOnInit(): void {
    this.loadCategories();
    this.loadAreas();
  }

  loadCategories(): void {
    this.categoriesService.getCategories().subscribe({
      next: (response) => {
        this.categories = response.categories;
      },
    });
  }

  loadAreas(): void {
    this.areasService.getAreas().subscribe({
      next: (response) => {
        this.areas = response.areas;
      },
    });
  }

  register(): void {
    this.submitted = true;
    this.error = '';

    if (!this.isFormValid()) {
      this.error = 'من فضلك راجع البيانات المطلوبة.';
      return;
    }

    this.loading = true;

    this.authService
      .register({
        name: this.name.trim(),
        phone: this.phone.trim(),
        password: this.password,
        password_confirmation: this.passwordConfirmation,
        role: this.role,
        category_id: this.role === 'worker' ? this.categoryId : null,
        area_id: this.areaId? this.areaId : null,
      })
      .subscribe({
        next: () => {
          this.loading = false;
          this.router.navigate(['/dashboard']);
        },
        error: (error) => {
          this.loading = false;

          if (error?.error?.errors) {
            const errors = error.error.errors;
            const firstError = Object.values(errors)[0] as string[];

            this.error =
              firstError?.[0] ||
              'حدث خطأ أثناء إنشاء الحساب.';

            return;
          }

          this.error =
            error?.error?.message ||
            'حدث خطأ أثناء إنشاء الحساب.';
        },
      });
  }

  private isFormValid(): boolean {
    const validName =
      this.name.trim().length >= 3 &&
      this.name.trim().length <= 255;

    const validPhone =
      /^01[0125][0-9]{8}$/.test(this.phone.trim());

    const validPassword =
      this.password.length >= 6;

    const validPasswordConfirmation =
      this.passwordConfirmation.length > 0 &&
      this.password === this.passwordConfirmation;

    const validArea =
      this.areaId !== null;

    const validCategory =
      this.role === 'customer' ||
      this.categoryId !== null;

    return (
      validName &&
      validPhone &&
      validPassword &&
      validPasswordConfirmation &&
      validArea &&
      validCategory
    );
  }
}

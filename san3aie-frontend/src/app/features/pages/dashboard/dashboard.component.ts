import { Component } from '@angular/core';
import { AuthService } from '../../../shared/services/auth.service';
import { User } from '../../../shared/models/user.model';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  templateUrl: './dashboard.component.html'
})
export class DashboardComponent {
  user: User | null = null;

  constructor(private authService: AuthService) {
    this.user = this.authService.getCurrentUser();
  }
}

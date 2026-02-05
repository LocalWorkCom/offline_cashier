import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ProductsService } from '../../services/products.service';

@Component({
  selector: 'app-dish-management',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './dish-management.component.html',
  styleUrls: ['./dish-management.component.css']
})
export class DishManagementComponent implements OnInit {
  categories: any[] = [];
  selectedCategoryId: number | null = null;
  dishes: any[] = [];
  loading = false;
  dishesLoading = false;
  successMessage: string = '';
  errorMessage: string = '';

  constructor(private productsService: ProductsService) {}

  ngOnInit(): void {
    this.loadCategories();
  }

  loadCategories(): void {
    this.loading = true;
    this.productsService.getMenuCategoriesAll().subscribe({
      next: (res: any) => {
        if (res.status && res.data) {
          this.categories = res.data;
        }
        this.loading = false;
      },
      error: (err) => {
        console.error('Error loading categories', err);
        this.loading = false;
      }
    });
  }

  onCategoryChange(): void {
    if (!this.selectedCategoryId) {
      this.dishes = [];
      return;
    }
    this.loadDishes();
  }

  loadDishes(): void {
    if (!this.selectedCategoryId) return;
    this.dishesLoading = true;
    this.productsService.getMenuDishesAll(this.selectedCategoryId).subscribe({
      next: (res: any) => {
        if (res.status && res.data) {
          this.dishes = res.data.dishes || [];
        }
        this.dishesLoading = false;
      },
      error: (err) => {
        console.error('Error loading dishes', err);
        this.dishesLoading = false;
      }
    });
  }

  toggleStatus(dish: any): void {
    // Note: dish.is_active is already toggled by the checkbox [(ngModel)]
    this.productsService.toggleDishStatus(dish.id, dish.is_active).subscribe({
      next: (res: any) => {
        // Success
        this.showSuccess('تم تحديث حالة المنتج بنجاح');
      },
      error: (err) => {
        console.error('Error toggling status', err);
        // Revert if error
        dish.is_active = !dish.is_active;
        this.showError('حدث خطأ أثناء تحديث الحالة');
      }
    });
  }

  showSuccess(msg: string): void {
    this.successMessage = msg;
    setTimeout(() => this.successMessage = '', 3000);
  }

  showError(msg: string): void {
    this.errorMessage = msg;
    setTimeout(() => this.errorMessage = '', 3000);
  }
}

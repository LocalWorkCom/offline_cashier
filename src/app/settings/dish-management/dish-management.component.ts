import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ProductsService } from '../../services/products.service';
import { Subject, debounceTime, distinctUntilChanged } from 'rxjs';

@Component({
  selector: 'app-dish-management',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './dish-management.component.html',
  styleUrls: ['./dish-management.component.css']
})
export class DishManagementComponent implements OnInit, OnDestroy {
  categories: any[] = [];
  dishes: any[] = [];
  filteredDishes: any[] = [];
  loading = false;
  dishesLoading = false;
  successMessage: string = '';
  errorMessage: string = '';

  // Search and Filters
  searchTerm: string = '';
  statusFilter: string = 'all'; // 'all', 'active', 'inactive'
  selectedCategoryId: any = null;

  private searchSubject = new Subject<string>();

  constructor(private productsService: ProductsService) {}

  ngOnInit(): void {
    this.loadCategories();
    // Do not load dishes initialy - satisfy user requirement
    // this.loadDishes();

    // Setup search debouncing
    this.searchSubject.pipe(
      debounceTime(2000),
      distinctUntilChanged()
    ).subscribe(() => {
      this.loadDishes();
    });
  }

  ngOnDestroy(): void {
    this.searchSubject.complete();
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

  loadDishes(): void {
    if (!this.selectedCategoryId) {
      this.dishes = [];
      this.filteredDishes = [];
      return;
    }

    this.dishesLoading = true;

    this.productsService.getMenuDishesAll(
      this.selectedCategoryId,
      this.searchTerm,
      this.statusFilter
    ).subscribe({
      next: (res: any) => {
        if (res.status && res.data) {
          this.dishes = res.data.dishes || []; // Using non-paginated data structure
          this.filteredDishes = [...this.dishes];
        } else {
          this.dishes = [];
          this.filteredDishes = [];
        }
        this.dishesLoading = false;
      },
      error: (err) => {
        console.error('Error loading dishes', err);
        this.dishesLoading = false;
      }
    });
  }

  onCategoryChange(): void {
    this.loadDishes();
  }

  onFilterChange(): void {
    if (this.searchTerm.length > 0 && this.searchTerm.length < 2) return;
    this.searchSubject.next(this.searchTerm);
  }

  onStatusChange(): void {
    this.loadDishes();
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

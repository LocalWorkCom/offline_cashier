import { CommonModule } from '@angular/common';
import { Component, DoCheck, Input, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { RouterLink, RouterLinkActive } from '@angular/router';
import { ProductCardComponent } from '../product-card/product-card.component';
import { ProductsService } from '../services/products.service';
import { OfferCardComponent } from '../offer-card/offer-card.component';
import { FormsModule } from '@angular/forms';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { NewDishService } from '../services/pusher/newDish';
import { ShowLoaderUntilPageLoadedDirective } from '../core/directives/show-loader-until-page-loaded.directive';
import { finalize } from 'rxjs';

@Component({
  selector: 'app-categories',
  imports: [
    CommonModule,
    ProductCardComponent,
    FormsModule,
    ShowLoaderUntilPageLoadedDirective
  ],
  templateUrl: './categories.component.html',
  styleUrls: ['./categories.component.css'],
})
export class CategoriesComponent implements OnInit,OnDestroy {
  products: any;
  offers: any;
  categories: any[] = [];
  selectedCategory: any = null;
  filteredOrders: any[] = [];
  filterCategories: any[] = [];
  searchOrderCategoreis: string | number | any = '';
  selectedCategoryProducts: any[] = [];
  searchOrderNumber: string | number | any = '';
  isAllLoading:boolean=true;
  @Input() item: any;
  @Input() offer: any;
  @ViewChild('closebutton') closebutton: any;
  constructor(private productsRequestService: ProductsService, private modalService: NgbModal,
    private newDish:NewDishService) {}
 

  ngOnInit(): void {
    this.fetchMenuData();
    this.listenTonewDish()
    
  }
  // ngDoCheck(){
  //   this.fetchMenuData();
  // }
  ngOnChanges(): void {
    this.fetchMenuData();
  }

  fetchMenuData(): void {
    this.isAllLoading=false;
    this.productsRequestService.getMenuDishes() .pipe(
    finalize(() => {
      this.isAllLoading=true 
    })
  ).subscribe((response: any) => {
      if (response && response.status && response.data) { 
        this.categories = response.data;
        this.filterCategories = [...this.categories]; // Update filtered categories
        this.filteredOrders = []; // Clear previous orders
        if (this.categories.length > 0) {
          this.onCategorySelect(this.categories[0]); // Select first category by default
        }
      } else {
        console.error("Invalid response format", response);
      }
    });
    
  }
  
  
  onCategorySelect(category: any): void {
      if (!category || !Array.isArray(category.dishes)) {
      this.selectedCategoryProducts = [];
      return;
    }
  
    this.selectedCategory = category;
    this.selectedCategoryProducts = category.dishes
      .filter((d: { dish: any }) => d && d.dish) // Ensure dish exists
      .map((d: { dish: any; sizes: any; addon_categories: any }) => ({
        ...d.dish,
        sizes: Array.isArray(d.sizes) ? d.sizes : [],
        addon_categories: Array.isArray(d.addon_categories) ? d.addon_categories : [],
      }))
      .filter((dish: any) => this.isDishActive(dish)); // ✅ Remove inactive dishes
  
    this.filteredOrders = [...this.selectedCategoryProducts];
    this.filterCategories = [...this.categories];
    this.closebutton.nativeElement.click();
  }
  
  isDishActive(dish: any): boolean {
    return dish.is_active !== false; // Adjust based on API response field
  }
  

  filterOrders() {
    const searchId = Number(this.searchOrderNumber);
  
    let filtered = [...this.selectedCategoryProducts].filter((dish) =>
      this.isDishActive(dish) // ✅ Remove inactive dishes
    );
  
    if (this.searchOrderNumber && !isNaN(searchId)) {
      filtered = filtered.filter((order) => order.id === searchId);
    } else if (this.searchOrderNumber) {
      filtered = filtered.filter((order) =>
        order.name.toLowerCase().includes(this.searchOrderNumber.toLowerCase())
      );
    }
  
    this.filteredOrders = filtered;
  }
  

  
  filterOrderCategories() {
    const searchId = Number(this.searchOrderCategoreis);

    if (this.searchOrderCategoreis && !isNaN(searchId)) {
      this.filterCategories = this.categories.filter(
        (order) => order.id === searchId
      );
    } else if (this.searchOrderCategoreis) {
      this.filterCategories = this.categories.filter((order) =>
        order.name.toLowerCase().includes(this.searchOrderCategoreis.toLowerCase())
      );
    } else {
      this.filterCategories = [...this.categories];
    }
  
  }

  recieveFromProduct(id: any): void {
    this.products = this.products.filter(
      (product: { id: any }) => product.id !== id
    );
  }
  
  clearSelection(): void {
    this.selectedCategory = null;
    this.filteredOrders = [];
  }

  trackById(index: number, product: any): any {
    return product.id;
  }
    listenTonewDish() {
  this.newDish.listenToNewdish();

  this.newDish.dishAdded$.subscribe((newDishWrapper) => {
    const newDish = newDishWrapper.dish;
    console.log('New dish received:', newDish);

    const targetCategoryId = newDish.branch_menu_category_id;
    const targetCategory = this.categories.find((cat: any) => cat.id === targetCategoryId);

    if (targetCategory) {
      // Push the dish into the right category
      targetCategory.dishes.unshift({
        dish: newDish,
        sizes: [],
        addon_categories: []
      });

      // If user is currently viewing that category, update the view
      if (this.selectedCategory && this.selectedCategory.id === targetCategoryId) {
        this.selectedCategoryProducts = targetCategory.dishes
          .filter((d: any) => d && d.dish)
          .map((d: any) => ({
            ...d.dish,
            sizes: Array.isArray(d.sizes) ? d.sizes : [],
            addon_categories: Array.isArray(d.addon_categories) ? d.addon_categories : [],
          }))
          .filter((dish: any) => this.isDishActive(dish));

        this.filteredOrders = [...this.selectedCategoryProducts];
      }

      console.log('Dish inserted into category:', targetCategory.name);
    } else {
      console.warn('Category not found for new dish:', newDish);
    }
  });
}
 ngOnDestroy(): void {
    this.newDish.stopListening();
  }
}

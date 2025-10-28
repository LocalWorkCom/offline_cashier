import { CommonModule } from '@angular/common';
import { Component, DoCheck, Input, OnDestroy, OnInit, ViewChild, ChangeDetectionStrategy } from '@angular/core';
import { RouterLink, RouterLinkActive } from '@angular/router';
import { ProductCardComponent } from '../product-card/product-card.component';
import { ProductsService } from '../services/products.service';
import { OfferCardComponent } from '../offer-card/offer-card.component';
import { FormsModule } from '@angular/forms';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { NewDishService } from '../services/pusher/newDish';
import { ShowLoaderUntilPageLoadedDirective } from '../core/directives/show-loader-until-page-loaded.directive';
import { finalize } from 'rxjs';
import { IndexeddbService } from '../services/indexeddb.service';
import { ChangeDetectorRef } from '@angular/core';

@Component({
  selector: 'app-newcategories',
  imports: [
    CommonModule,
    ProductCardComponent,
    FormsModule,
    ShowLoaderUntilPageLoadedDirective
  ],
  templateUrl: './newcategories.component.html',
  styleUrls: ['./newcategories.component.css'],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class NewcategoriesComponent implements OnInit, OnDestroy {
  products: any;
  offers: any;
  categories: any[] = [];
  selectedCategory: any = null;
  filteredOrders: any[] = [];
  filterCategories: any[] = [];
  searchOrderCategoreis: string | number | any = '';
  selectedCategoryProducts: any[] = [];
  searchOrderNumber: string | number | any = '';
  isAllLoading: boolean = true;
  isOnline: boolean = navigator.onLine;
  usingOfflineData: boolean = false;
  @Input() item: any;
  @Input() offer: any;
  @ViewChild('closebutton') closebutton: any;
  constructor(private productsRequestService: ProductsService, private modalService: NgbModal,
    private newDish: NewDishService, private dbService: IndexeddbService,
    private cdr: ChangeDetectorRef) { }

  ngOnInit(): void {

    console.log('old category component');
    //start dalia
    // Add event listeners for online/offline status
    window.addEventListener('online', this.handleOnlineStatus.bind(this));
    window.addEventListener('offline', this.handleOnlineStatus.bind(this));

    this.dbService.init()
    .then(async () => {
      await this.loadFromIndexedDB();
      if (navigator.onLine) {
         this.fetchMenuData();
      }
    })
    .catch(error => {
      console.error('Error initializing IndexedDB:', error);
      // this.loading = true;
    })
    //end dalia

    this.listenTonewDish()
  }
  // ngDoCheck(){
  //   this.fetchMenuData();
  // }
  ngOnChanges(): void {
    this.fetchMenuData();
  }

  // start dalia
  // fetchMenuData(): void {
  //   this.isAllLoading = false;
  //   this.productsRequestService.getMenuDishes().pipe(
  //     finalize(() => {
  //       this.isAllLoading = true
  //     })
  //   ).subscribe((response: any) => {
  //     if (response && response.status && response.data) {
  //       this.categories = response.data;
  //       this.filterCategories = [...this.categories]; // Update filtered categories
  //       this.filteredOrders = []; // Clear previous orders
  //       if (this.categories.length > 0) {
  //         this.onCategorySelect(this.categories[0]); // Select first category by default
  //       }
  //     } else {
  //       console.error("Invalid response format", response);
  //     }
  //   });

  // }

  fetchMenuData() {
    this.isAllLoading = false;
    if (this.isOnline) {
      this.fetchFromAPI();
    } else {
      this.loadFromIndexedDB();
    }
  }

    private fetchFromAPI() {
    this.productsRequestService.getMenuDishes().pipe(
      finalize(() => {
        this.isAllLoading = true;
      })
    ).subscribe((response: any) => {
      if (response && response.status && response.data) {
        this.categories = response.data;
        this.processCategories();

        // Save to IndexedDB for offline use (non-blocking)
        this.dbService.saveData('categories', this.categories)
          .catch(error => console.error('Error saving to IndexedDB:', error));

        this.usingOfflineData = false;
      } else {
        console.error("Invalid response format", response);
        // Fallback to offline data if API returns invalid response
        this.loadFromIndexedDB();
      }
    }, (error) => {
      console.error('API fetch failed, trying offline data:', error);
      this.loadFromIndexedDB();
    });
  }

  private loadFromIndexedDB() {
    this.dbService.getAll('categories')
      .then(categories => {
        if (categories && categories.length > 0) {
          this.categories = categories;
          this.processCategories();
          this.usingOfflineData = true;
          console.log('Loaded from offline storage');
        } else {
          this.categories = [];
          this.selectedCategory = null;
          this.filteredOrders = [];
          this.usingOfflineData = false;
        }
        this.isAllLoading = true;
        this.cdr.detectChanges();
      })
      .catch(error => {
        console.error('Error loading from IndexedDB:', error);
        this.isAllLoading = true;
        this.cdr.detectChanges();
      });
  }

  private processCategories() {
    this.filterCategories = [...this.categories];
    this.filteredOrders = [];

    if (this.categories.length > 0) {
      // Try to maintain current selection or select first category
      const currentCategoryId = this.selectedCategory?.id;
      const categoryToSelect = currentCategoryId
        ? this.categories.find(cat => cat.id === currentCategoryId)
        : this.categories[0];

      if (categoryToSelect) {
        this.onCategorySelect(categoryToSelect);
      } else {
        this.onCategorySelect(this.categories[0]);
      }
    }
    this.cdr.markForCheck();
  }

  private handleOnlineStatus() {
    this.isOnline = navigator.onLine;
    // Optional: automatically refresh data when coming back online
    if (this.isOnline && this.usingOfflineData) {
      this.fetchMenuData();
    }
    this.cdr.detectChanges();
  }
  //end dalia


  onCategorySelect(category: any): void {
    if (!category || !Array.isArray(category.dishes)) {
      this.selectedCategoryProducts = [];
      this.cdr.markForCheck();
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
    this.cdr.markForCheck();
    if (this.closebutton) {
      this.closebutton.nativeElement.click();
    }
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

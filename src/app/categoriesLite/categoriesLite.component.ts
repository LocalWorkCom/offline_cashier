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
import { IndexeddbService } from '../services/indexeddb.service';
import { ChangeDetectorRef } from '@angular/core';

@Component({
  selector: 'app-categoriesLite',
  imports: [
    CommonModule,
    ProductCardComponent,
    FormsModule,
    ShowLoaderUntilPageLoadedDirective
  ],
  templateUrl: './categoriesLite.component.html',
  styleUrls: ['./categoriesLite.component.css'],
})
export class CategoriesLiteComponent implements OnInit, OnDestroy {
  private readonly CATEGORIES_CACHE_KEY = 'menuCategoriesLiteCache';
  private readonly CATEGORY_DISHES_CACHE_PREFIX = 'menuDishesLiteCache_';

  products: any;
  offers: any;
  categories: any[] = [];
  categoriesLite: any[] = [];
  selectedCategory: any = null;
  filteredOrders: any[] = [];
  filterCategories: any[] = [];
  searchOrderCategoreis: string | number | any = '';
  selectedCategoryProducts: any[] = [];
  searchOrderNumber: string | number | any = '';
  isAllLoading: boolean = true;
  isCategoryLoading: boolean = false;
  isOnline: boolean = navigator.onLine;
  usingOfflineData: boolean = false;
  errorMessage: string = '';
  @Input() item: any;
  @Input() offer: any;
  @ViewChild('closebutton') closebutton: any;
  constructor(private productsRequestService: ProductsService, private modalService: NgbModal,
    private newDish: NewDishService, private dbService: IndexeddbService,
    private cdr: ChangeDetectorRef) { }

  ngOnInit(): void {
    //start dalia
    // Add event listeners for online/offline status
    window.addEventListener('online', this.handleOnlineStatus.bind(this));
    window.addEventListener('offline', this.handleOnlineStatus.bind(this));


    //end dalia
    // const hasCachedCategories = this.loadCategoriesFromCache();
    // this.fetchMenuData(hasCachedCategories);
    this.fetchMenuData();
    this.listenTonewDish()
  }
  // ngDoCheck(){
  //   this.fetchMenuData();
  // }
  ngOnChanges(): void {
    this.fetchMenuData(this.categories.length > 0);
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

  fetchMenuData(hasCachedData: boolean = false) {
    if (!this.isOnline) {
      this.errorMessage = 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ';
      // this.loadFromIndexedDB();
      return;
    }

    // if (!hasCachedData) {
    //   this.isAllLoading = false;
    // }

    this.fetchFromAPILite();
  }

  private loadCategoriesFromCache(): boolean {
    try {
      const cachedValue = localStorage.getItem(this.CATEGORIES_CACHE_KEY);
      if (!cachedValue) {
        return false;
      }

      const cachedCategories = JSON.parse(cachedValue);
      if (Array.isArray(cachedCategories) && cachedCategories.length > 0) {
        this.categories = cachedCategories;
        this.isAllLoading = true;
        this.processCategories(true);
        return true;
      }
    } catch (error) {
      console.error('Failed to load categories cache', error);
    }
    return false;
  }

  private saveCategoriesToCache(categories: any[]): void {
    try {
      localStorage.setItem(this.CATEGORIES_CACHE_KEY, JSON.stringify(categories));
    } catch (error) {
      console.error('Failed to save categories cache', error);
    }
  }

  private getCachedCategoryDishes(categoryId: number | string): any[] | null {
    try {
      const cachedValue = localStorage.getItem(`${this.CATEGORY_DISHES_CACHE_PREFIX}${categoryId}`);
      if (!cachedValue) {
        return null;
      }

      const cachedDishes = JSON.parse(cachedValue);
      return Array.isArray(cachedDishes) ? cachedDishes : null;
    } catch (error) {
      console.error(`Failed to load cached dishes for category ${categoryId}`, error);
      return null;
    }
  }

  private saveCategoryDishesToCache(categoryId: number | string, dishes: any[]): void {
    try {
      localStorage.setItem(
        `${this.CATEGORY_DISHES_CACHE_PREFIX}${categoryId}`,
        JSON.stringify(dishes)
      );
    } catch (error) {
      console.error(`Failed to cache dishes for category ${categoryId}`, error);
    }
  }

    private fetchFromAPI() {
    this.errorMessage = '';
    this.productsRequestService.getMenuDishes().pipe(
      finalize(() => {
        this.isAllLoading = true;
      })
    ).subscribe((response: any) => {
      if (response && response.status && response.data) {
        this.categories = response.data;
        this.processCategories(true);

        this.usingOfflineData = false;
        this.errorMessage = '';
      } else {
        console.error("Invalid response format", response);
        // this.errorMessage = 'Failed to fetch data from server. Please try again.';
      this.errorMessage = 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ';

      }
    }, (error) => {
      console.error('API fetch failed, trying offline data:', error);
      // this.errorMessage = 'Failed to fetch data from server. Please try again.';
      this.errorMessage = 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ';

    });
  }


  private fetchFromAPILite() {
    this.errorMessage = '';
    this.productsRequestService.getMenuCategoriesLite().pipe(
      finalize(() => {
        this.isAllLoading = true;
      })
    ).subscribe((response: any) => {
      if (response && response.status && Array.isArray(response.data)) {
        this.categories = response.data;
        this.saveCategoriesToCache(this.categories);
        this.processCategories();
        this.usingOfflineData = false;
        this.errorMessage = '';
      } else {
        console.error("Invalid response format", response);
        this.errorMessage = 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ';
      }
    }, (error) => {
      console.error('API fetch failed, trying offline data:', error);
      this.errorMessage = 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ';
    });
  }
  private loadFromIndexedDB(): Promise<boolean> {
    this.errorMessage = '';
    return this.dbService.getAll('categories')
      .then(categories => {
        if (categories && categories.length > 0) {
          this.categories = categories;
          this.processCategories(true);
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
        return !!(categories && categories.length > 0);
      })
      .catch(error => {
        console.error('Error loading from IndexedDB:', error);
        this.isAllLoading = true;
        this.cdr.detectChanges();
        return false;
      });
  }

  private processCategories(preferCachedDishes: boolean = false) {
    this.filterCategories = [...this.categories];
    this.filteredOrders = [];

    if (this.categories.length > 0) {
      const currentCategoryId = this.selectedCategory?.id;
      const categoryToSelect = currentCategoryId
        ? this.categories.find(cat => cat.id === currentCategoryId)
        : this.categories[0];

      if (categoryToSelect) {
        this.onCategorySelect(categoryToSelect, true, preferCachedDishes);
      } else {
        this.onCategorySelect(this.categories[0], true, preferCachedDishes);
      }
    }
  }

  private handleOnlineStatus() {
    this.isOnline = navigator.onLine;
    // Optional: automatically refresh data when coming back online
    if (this.isOnline && this.usingOfflineData) {
      this.fetchMenuData(this.categories.length > 0);
    }
    this.cdr.detectChanges();
  }
  //end dalia


  onCategorySelect(category: any, skipModalClose: boolean = false, preferCachedDishes: boolean = false): void {
    if (!category) {
      this.selectedCategory = null;
      this.selectedCategoryProducts = [];
      this.filteredOrders = [];
      return;
    }

    this.selectedCategory = category;

    let appliedFromCache = false;

    if (preferCachedDishes) {
      const cachedDishes = this.getCachedCategoryDishes(category.id);
      if (cachedDishes && cachedDishes.length > 0) {
        this.applyCategoryDishes(category, cachedDishes, skipModalClose, true);
        appliedFromCache = true;
      }
    }

    if (!appliedFromCache && Array.isArray(category.dishes) && category.dishes.length > 0) {
      this.applyCategoryDishes(category, category.dishes, skipModalClose, true);
      appliedFromCache = true;
    }

    if (!appliedFromCache) {
      this.selectedCategoryProducts = [];
      this.filteredOrders = [];
    }

    this.fetchCategoryDishes(category, skipModalClose);
  }

  private fetchCategoryDishes(category: any, skipModalClose: boolean): void {
    if (!category?.id || !this.isOnline) {
      return;
    }

    const cachedDishes = this.getCachedCategoryDishes(category.id);
    if (cachedDishes && cachedDishes.length > 0) {
      this.applyCategoryDishes(category, cachedDishes, skipModalClose, true);
    }

    this.isCategoryLoading = true;
    this.productsRequestService.getMenuDishesLite(category.id).pipe(
      finalize(() => {
        this.isCategoryLoading = false;
        this.cdr.detectChanges();
      })
    ).subscribe((response: any) => {
      if (response && response.status) {
        const data = response.data;
        const dishesPayload = Array.isArray(data)
          ? data
          : Array.isArray(data?.dishes)
            ? data.dishes
            : Array.isArray(data?.items)
              ? data.items
              : [];

        this.dbService.saveData('categories', data);
        // this.dbService.saveData('category', category);

        if (Array.isArray(dishesPayload)) {
          this.applyCategoryDishes(category, dishesPayload, skipModalClose);
          return;
        }
      }

      console.error('Invalid dishes response for category', category, response);
      this.errorMessage = 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ';
    }, (error) => {
      console.error(`Failed to fetch dishes for category ${category.id}`, error);
      this.errorMessage = 'فشل فى الاتصال . يرجى المحاوله مرة اخرى ';
    });
  }

  private applyCategoryDishes(category: any, dishesPayload: any[], skipModalClose: boolean, fromCache: boolean = false): void {
    const normalizedDishes = this.normalizeDishesPayload(dishesPayload);

    this.selectedCategoryProducts = normalizedDishes;
    this.filteredOrders = [...this.selectedCategoryProducts];
    this.filterCategories = [...this.categories];
    category.dishes = normalizedDishes;

    if (!fromCache) {
      this.saveCategoryDishesToCache(category.id, normalizedDishes);
    }

    if (!skipModalClose && this.closebutton?.nativeElement) {
      this.closebutton.nativeElement.click();
    }

    this.errorMessage = '';
  }

  private normalizeDishesPayload(dishesPayload: any[]): any[] {
    return (Array.isArray(dishesPayload) ? dishesPayload : [])
      .map((entry: any) => {
        if (entry && entry.dish) {
          return {
            ...entry.dish,
            sizes: Array.isArray(entry.sizes) ? entry.sizes : [],
            addon_categories: Array.isArray(entry.addon_categories) ? entry.addon_categories : [],
          };
        }
        return {
          ...entry,
          sizes: Array.isArray(entry?.sizes) ? entry.sizes : [],
          addon_categories: Array.isArray(entry?.addon_categories) ? entry.addon_categories : [],
        };
      })
      .filter((dish: any) => this.isDishActive(dish));
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

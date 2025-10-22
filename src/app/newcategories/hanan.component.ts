import { CommonModule } from '@angular/common';
import { Component, DoCheck, Input, OnDestroy, OnInit, ViewChild, HostListener } from '@angular/core';
import { RouterLink, RouterLinkActive } from '@angular/router';
import { ProductCardComponent } from '../product-card/product-card.component';
import { ProductsService } from '../services/products.service';
import { OfferCardComponent } from '../offer-card/offer-card.component';
import { FormsModule } from '@angular/forms';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { NewDishService } from '../services/pusher/newDish';
import { ShowLoaderUntilPageLoadedDirective } from '../core/directives/show-loader-until-page-loaded.directive';
// import { finalize } from 'rxjs';
import { IndexeddbService } from '../services/indexeddb.service';
import { ChangeDetectorRef } from '@angular/core';
// HANAN infinite scroll
import { BehaviorSubject, Subject } from 'rxjs';
import { finalize, debounceTime, takeUntil, take } from 'rxjs/operators';


@Component({
  selector: 'app-newcategories',
  // imports: [
  //   CommonModule,
  //   ProductCardComponent,
  //   FormsModule,
  //   ShowLoaderUntilPageLoadedDirective
  // ],
  // templateUrl: './newcategories.component.html',
  // styleUrls: ['./newcategories.component.css'],
})
export class hananComponent implements OnInit, OnDestroy {
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

  // hanan infinite scroll
  currentPage: number = 1;
  perPage: number = 10;
  hasMorePages: boolean = true;
  isLoadingMore: boolean = false;
  allCategories: any[] = []; // Store all loaded categories
  private scrollSubject = new Subject<void>();

  @Input() item: any;
  @Input() offer: any;
  @ViewChild('closebutton') closebutton: any;
  constructor(private productsRequestService: ProductsService, private modalService: NgbModal,
    private newDish: NewDishService, private dbService: IndexeddbService,
    private cdr: ChangeDetectorRef) {
    // hanan infinite scroll
    // ✅ SETUP INFINITE SCROLL DEBOUNCE
    this.scrollSubject.pipe(debounceTime(300)).subscribe(() => {
      this.loadMoreCategories();
    });
  }
  private performanceLog(message: string) {
  const time = performance.now();
  console.log(`⏱️ ${time.toFixed(2)}ms: ${message}`);
}

  ngOnInit(): void {
    //start dalia
    // Add event listeners for online/offline status
    window.addEventListener('online', this.handleOnlineStatus.bind(this));
    window.addEventListener('offline', this.handleOnlineStatus.bind(this));

   this.performanceLog('Component started');
  this.dbService.init();
  this.performanceLog('DB initialized');
    //end dalia
    // this.fetchMenuData();
        this.fetchMenuDataUltraFast();
  this.listenTonewDish();
  this.performanceLog('All init completed');
  }

  // hanan infinite scroll

  // ✅ INFINITE SCROLL HANDLER
  @HostListener('window:scroll', ['$event'])
  onWindowScroll(): void {
    if (this.isLoadingMore || !this.hasMorePages) return;

    const scrollPosition = window.innerHeight + window.scrollY;
    const documentHeight = document.documentElement.scrollHeight;
    const threshold = 500; // Load when 500px from bottom

    if (scrollPosition >= documentHeight - threshold) {
      this.scrollSubject.next();
    }
  }
  // ✅ LOAD MORE CATEGORIES
  private loadMoreCategories(): void {
    if (this.isLoadingMore || !this.hasMorePages || !this.isOnline) return;

    this.isLoadingMore = true;
    this.currentPage++;

    this.productsRequestService.getMenuDishesE(this.currentPage, this.perPage)
      .pipe(finalize(() => {
        this.isLoadingMore = false;
        this.cdr.detectChanges();
      }))
      .subscribe({
        next: (response: any) => {
          if (response && response.status && response.data) {
            const newCategories = response.data.categories || [];
            const pagination = response.data.pagination;

            // ✅ ADD NEW CATEGORIES TO EXISTING ONES
            this.allCategories = [...this.allCategories, ...newCategories];
            this.categories = this.allCategories;

            // ✅ UPDATE PAGINATION STATUS
            this.hasMorePages = pagination.current_page < pagination.last_page;

            this.processCategories();

            // ✅ SAVE TO INDEXEDDB
            this.dbService.saveData('categories', this.allCategories)
              .catch(error => console.error('Error saving to IndexedDB:', error));
          }
        },
        error: (error) => {
          console.error('Error loading more categories:', error);
          this.currentPage--; // Revert page on error
        }
      });
  }
  // ngDoCheck(){
  //   this.fetchMenuData();
  // }
  ngOnChanges(): void {
    this.fetchMenuData();
  }

   // ✅ ULTRA FAST FETCH - NO INFINITE SCROLL INITIALLY
  fetchMenuDataUltraFast(): void {
  this.isAllLoading = false;

  const startTime = performance.now(); // ✅ استخدام performance API
  console.log('🚀 Starting ULTRA FAST menu fetch...');

  this.productsRequestService.getMenuUltraFast()
    .pipe(
      take(1),
      finalize(() => {
        const endTime = performance.now();
        console.log(`✅ Total load time: ${(endTime - startTime).toFixed(2)}ms`);

        this.isAllLoading = true;
        this.cdr.detectChanges();
      })
    )
    .subscribe({
      next: (response: any) => {
        const receiveTime = performance.now();
        console.log(`📦 API response received: ${(receiveTime - startTime).toFixed(2)}ms`);
        this.handleUltraFastResponse(response);
      },
      error: (error) => this.handleApiError(error)
    });
}

private handleUltraFastResponse(response: any): void {
    if (response?.status && response.data) {
      const categories = response.data.categories || [];
      console.log(`✅ ULTRA FAST: Loaded ${categories.length} categories`);

      this.categories = categories;
      this.allCategories = categories;

      this.processCategories();
    this.preloadCategoryImages();

      // Save to IndexedDB
      setTimeout(() => {
        this.dbService.saveData('categories', categories)
          .catch(err => console.error('IndexedDB error:', err));
      }, 0);

      this.usingOfflineData = false;
    } else {
      this.loadFromIndexedDB();
    }
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

  // fetchMenuData() {
  //   this.isAllLoading = false;
  //   if (this.isOnline) {
  //     this.fetchFromAPI();
  //   } else {
  //     this.loadFromIndexedDB();
  //   }
  // }
  // hanan infinite scroll
  // ✅ MODIFIED FETCH MENU DATA FOR INITIAL LOAD
  fetchMenuData(): void {
    if (this.isLoadingMore) return;

    this.isAllLoading = false;
    this.currentPage = 1;
    this.hasMorePages = true;
    this.allCategories = [];

    console.log('🚀 Starting menu data fetch...');

    this.productsRequestService.getMenuDishesE(this.currentPage, this.perPage)
      .pipe(
        take(1),
        finalize(() => {
          this.isAllLoading = true;
          this.cdr.detectChanges();
          console.timeEnd('🕒 Menu Load Time');
        })
      )
      .subscribe({
        next: (response: any) => this.handleApiResponse(response),
        error: (error) => this.handleApiError(error)
      });
  }

  private handleApiError(error: any): void {
    console.error('API fetch failed, trying offline data:', error);
    this.loadFromIndexedDB();
  }
  private handleApiResponse(response: any): void {
    if (response?.status && response.data) {
      console.log(`✅ Loaded ${response.data.categories?.length || 0} categories`);

      this.allCategories = response.data.categories || [];
      this.categories = this.allCategories;

      const pagination = response.data.pagination;
      this.hasMorePages = pagination.current_page < pagination.last_page;

      this.processCategories();

      // ✅ NON-BLOCKING SAVE
      setTimeout(() => {
        this.dbService.saveData('categories', this.allCategories)
          .then(() => console.log('💾 Saved to IndexedDB'))
          .catch(err => console.error('IndexedDB error:', err));
      }, 0);

      this.usingOfflineData = false;
    } else {
      console.warn('⚠️ Invalid API response, falling back to offline data');
      this.loadFromIndexedDB();
    }
  }

  // private fetchFromAPI() {
  //   this.productsRequestService.getMenuDishesE().pipe(
  //     finalize(() => {
  //       this.isAllLoading = true;
  //     })
  //   ).subscribe((response: any) => {
  //     if (response && response.status && response.data) {
  //       this.categories = response.data;
  //       this.processCategories();

  //       // Save to IndexedDB for offline use (non-blocking)
  //       this.dbService.saveData('categories', this.categories)
  //         .catch(error => console.error('Error saving to IndexedDB:', error));

  //       this.usingOfflineData = false;
  //     } else {
  //       console.error("Invalid response format", response);
  //       // Fallback to offline data if API returns invalid response
  //       this.loadFromIndexedDB();
  //     }
  //   }, (error) => {
  //     console.error('API fetch failed, trying offline data:', error);
  //     this.loadFromIndexedDB();
  //   });
  // }
  private fetchFromAPI(): void {
    this.productsRequestService.getMenuDishesE(this.currentPage, this.perPage)
      .pipe(finalize(() => {
        this.isAllLoading = true;
      }))
      .subscribe((response: any) => {
        if (response && response.status && response.data) {
          this.allCategories = response.data.categories || [];
          this.categories = this.allCategories;

          const pagination = response.data.pagination;
          this.hasMorePages = pagination.current_page < pagination.last_page;

          this.processCategories();

          // Save to IndexedDB
          this.dbService.saveData('categories', this.allCategories)
            .catch(error => console.error('Error saving to IndexedDB:', error));

          this.usingOfflineData = false;
        } else {
          console.error("Invalid response format", response);
          this.loadFromIndexedDB();
        }
      }, (error) => {
        console.error('API fetch failed, trying offline data:', error);
        this.loadFromIndexedDB();
      });
  }

  // private loadFromIndexedDB() {
  //   this.dbService.getAll('categories')
  //     .then(categories => {
  //       if (categories && categories.length > 0) {
  //         this.categories = categories;
  //         this.processCategories();
  //         this.usingOfflineData = true;
  //         console.log('Loaded from offline storage');
  //       } else {
  //         this.categories = [];
  //         this.selectedCategory = null;
  //         this.filteredOrders = [];
  //         this.usingOfflineData = false;
  //       }
  //       this.isAllLoading = true;
  //       this.cdr.detectChanges();
  //     })
  //     .catch(error => {
  //       console.error('Error loading from IndexedDB:', error);
  //       this.isAllLoading = true;
  //       this.cdr.detectChanges();
  //     });
  // }
  // hanan infinite scroll

  private loadFromIndexedDB(): void {
    this.dbService.getAll('categories')
      .then(categories => {
        if (categories && categories.length > 0) {
          this.allCategories = categories;
          this.categories = this.allCategories;
          this.processCategories();
          this.usingOfflineData = true;
          this.hasMorePages = false; // No pagination in offline mode
          console.log('Loaded from offline storage');
        } else {
          this.allCategories = [];
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


  // private processCategories() {
  //   this.filterCategories = [...this.categories];
  //   this.filteredOrders = [];

  //   if (this.categories.length > 0) {
  //     // Try to maintain current selection or select first category
  //     const currentCategoryId = this.selectedCategory?.id;
  //     const categoryToSelect = currentCategoryId
  //       ? this.categories.find(cat => cat.id === currentCategoryId)
  //       : this.categories[0];

  //     if (categoryToSelect) {
  //       this.onCategorySelect(categoryToSelect);
  //     } else {
  //       this.onCategorySelect(this.categories[0]);
  //     }
  //   }
  // }

  // hanan infinite scroll

  private processCategories(): void {
    this.filterCategories = [...this.categories];

    if (this.categories.length > 0 && !this.selectedCategory) {
      this.onCategorySelect(this.categories[0]);
    }

    this.cdr.detectChanges();
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


  // onCategorySelect(category: any): void {
  //   if (!category || !Array.isArray(category.dishes)) {
  //     this.selectedCategoryProducts = [];
  //     return;
  //   }

  //   this.selectedCategory = category;
  //   this.selectedCategoryProducts = category.dishes
  //     .filter((d: { dish: any }) => d && d.dish) // Ensure dish exists
  //     .map((d: { dish: any; sizes: any; addon_categories: any }) => ({
  //       ...d.dish,
  //       sizes: Array.isArray(d.sizes) ? d.sizes : [],
  //       addon_categories: Array.isArray(d.addon_categories) ? d.addon_categories : [],
  //     }))
  //     .filter((dish: any) => this.isDishActive(dish)); // ✅ Remove inactive dishes

  //   this.filteredOrders = [...this.selectedCategoryProducts];
  //   this.filterCategories = [...this.categories];
  //   this.closebutton.nativeElement.click();
  // }

  // hanan infinite scroll

  // ✅ REST OF YOUR EXISTING METHODS REMAIN THE SAME
  onCategorySelect(category: any): void {
    if (!category || this.selectedCategory?.id === category.id) return;

    console.log(`🎯 Selecting category: ${category.name}`);
    this.selectedCategory = category;

    const startTime = performance.now();

    this.selectedCategoryProducts = (category.dishes || [])
      .filter((dish: any) => dish?.is_active !== false)
      .map((dish: any) => ({
        ...dish,
        sizes: dish.sizes || [],
        addon_categories: dish.addon_categories || []
      }));

    this.filteredOrders = [...this.selectedCategoryProducts];

    const endTime = performance.now();
    console.log(`⚡ Category processing took: ${(endTime - startTime).toFixed(2)}ms`);

    if (this.closebutton?.nativeElement) {
      this.closebutton.nativeElement.click();
    }
  }



  isDishActive(dish: any): boolean {
    return dish.is_active !== false; // Adjust based on API response field
  }


  // filterOrders() {
  //   const searchId = Number(this.searchOrderNumber);

  //   let filtered = [...this.selectedCategoryProducts].filter((dish) =>
  //     this.isDishActive(dish) // ✅ Remove inactive dishes
  //   );

  //   if (this.searchOrderNumber && !isNaN(searchId)) {
  //     filtered = filtered.filter((order) => order.id === searchId);
  //   } else if (this.searchOrderNumber) {
  //     filtered = filtered.filter((order) =>
  //       order.name.toLowerCase().includes(this.searchOrderNumber.toLowerCase())
  //     );
  //   }

  //   this.filteredOrders = filtered;
  // }
  // hanan infinite scroll
  filterOrders() {
    const searchTerm = this.searchOrderNumber?.toString().toLowerCase() || '';

    if (!searchTerm) {
      this.filteredOrders = [...this.selectedCategoryProducts];
      return;
    }

    const startTime = performance.now();
    const searchId = Number(searchTerm);

    this.filteredOrders = this.selectedCategoryProducts.filter(dish => {
      if (!isNaN(searchId)) {
        return dish.id === searchId;
      }
      return dish.name?.toLowerCase().includes(searchTerm);
    });

    const endTime = performance.now();
    console.log(`🔍 Filtering took: ${(endTime - startTime).toFixed(2)}ms`);
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
  preloadCategoryImages() {
  // ✅ Preload only first 3 category images
  const categoriesToPreload = this.categories.slice(0, 3);

  categoriesToPreload.forEach(category => {
    if (category.image_path) {
      const img = new Image();
      img.src = category.image_path;
    }
  });
}
  ngOnDestroy(): void {
    this.newDish.stopListening();
  }
}

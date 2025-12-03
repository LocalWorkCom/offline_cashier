import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable, BehaviorSubject, throwError, interval, Subject, forkJoin, of } from 'rxjs';
import { switchMap, map, catchError } from 'rxjs/operators';
import { baseUrl } from '../environment';
import { IndexeddbService } from './indexeddb.service';

@Injectable({
  providedIn: 'root'
})
export class ProductsService {
  private apiUrl = `${baseUrl}api`;

  private cart: any[] = [];
  categories: any[] = [];

  private productSubject = new BehaviorSubject<any>(null);
  product$ = this.productSubject.asObservable();

  private menuDishesSubject = new BehaviorSubject<any[]>([]);
  menuDishes$ = this.menuDishesSubject.asObservable();

  private cartSubject = new BehaviorSubject<any[]>([]);
  cart$ = this.cartSubject.asObservable();

  private totalPrice: number = 0;

  constructor(private http: HttpClient, private db: IndexeddbService) {
    this.loadCart(); // Load cart data when service is initialized
    // this.startDishPolling(); // Start polling for dish updates
  }

  setProduct(product: any) {
    this.productSubject.next(product);
  }

  private buildAuthContext(): { headers: HttpHeaders; branchId: string } | null {
    const token = localStorage.getItem('authToken');
    const branchId = localStorage.getItem('branch_id');

    if (!token) {
      console.error('No auth token found!');
      return null;
    }

    if (!branchId) {
      console.error('No branch ID found!');
      return null;
    }

    const headers = new HttpHeaders({
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json'
    });

    return { headers, branchId };
  }

  getMenuDishes(): Observable<any> {
    const authContext = this.buildAuthContext();

    if (!authContext) {
      return throwError(() => new Error('Authentication context missing.'));
    }

    const url = `${this.apiUrl}/menu-dishes?branchId=${authContext.branchId}`;

    return this.http.get<any[]>(url, { headers: authContext.headers });
  }

  getMenuCategoriesLite(): Observable<any> {
    const authContext = this.buildAuthContext();

    if (!authContext) {
      return throwError(() => new Error('Authentication context missing.'));
    }

    const url = `${this.apiUrl}/menu-categories-lite?branchId=${authContext.branchId}`;

    return this.http.get<any>(url, { headers: authContext.headers });
  }

  getMenuDishesLite(categoryId: number | string): Observable<any> {
    const authContext = this.buildAuthContext();

    if (!authContext) {
      return throwError(() => new Error('Authentication context missing.'));
    }

    const url = `${this.apiUrl}/menu-dishes-lite?categoryId=${categoryId}&branchId=${authContext.branchId}`;

    return this.http.get<any>(url, { headers: authContext.headers });
  }

  getMenuDataLite(): Observable<any> {
    return this.getMenuCategoriesLite().pipe(
      switchMap((categoriesResponse: any) => {
        if (!categoriesResponse?.status || !Array.isArray(categoriesResponse.data)) {
          return throwError(() => new Error('Invalid categories response format.'));
        }

        const categories = categoriesResponse.data;
        const status = categoriesResponse.status;

        if (categories.length === 0) {
          return of({ status, data: [] });
        }

        const categoriesWithDishes$ = categories.map((category: any) =>
          this.getMenuDishesLite(category.id).pipe(
            map((dishesResponse: any) => ({
              ...category,
              dishes: dishesResponse?.data ?? []
            })),
            catchError((error) => {
              console.error(`Failed to fetch dishes for category ${category.id}`, error);
              return of({
                ...category,
                dishes: []
              });
            })
          )
        );

        return forkJoin(categoriesWithDishes$).pipe(map((data) => ({ status, data })));
      })
    );
  }

  fetchMenuDishes(): void {
    this.getMenuDishes().subscribe(
      (data) => {
        this.menuDishesSubject.next(data.filter((dish: { isActive: any; }) => dish.isActive)); // Only active dishes
      },
      (error) => {
        console.error("Error fetching dishes:", error);
      }
    );
  }

  // startDishPolling(): void {
  //   interval(2000) // Poll every 10 seconds
  //     .pipe(switchMap(() => this.getMenuDishes()))
  //     .subscribe((data) => {
  //       this.menuDishesSubject.next(data.filter((dish: { isActive: any; }) => dish.isActive)); // Keep only active dishes
  //     });
  // }

  // loadCart(): void {
  //   try {
  //     const storedCart = localStorage.getItem('cart');
  //     this.cart = storedCart ? JSON.parse(storedCart) : [];
  //     this.cartSubject.next(this.cart);
  //     this.calculateTotal();
  //   } catch (error) {
  //     console.error("Error loading cart from localStorage", error);
  //     this.cart = [];
  //   }
  // }
  loadCart(): void {
    try {
      // ✅ حدد المفتاح حسب نوع الطلب
      const isHoldOrder = !!localStorage.getItem('currentOrderId');
      const cartKey = isHoldOrder ? 'holdCart' : 'cart';

      const storedCart = localStorage.getItem(cartKey);
      this.cart = storedCart ? JSON.parse(storedCart) : [];
      this.cartSubject.next(this.cart);
      this.calculateTotal();
    } catch (error) {
      console.error("Error loading cart from localStorage", error);
      this.cart = [];
    }
  }

  saveCart(): void {
    try {
      localStorage.setItem('cart', JSON.stringify(this.cart));
      this.cartSubject.next(this.cart);
    } catch (error) {
      console.error("Error saving cart to localStorage", error);
    }
  }

  getCart() {
    return this.cartSubject.getValue(); // Fix: Return the current cart array
  }


  // addToCart(product: any): void {
  //   let cart = JSON.parse(localStorage.getItem('cart') || '[]');

  //   const existingIndex = cart.findIndex((p: any) =>
  //     p.id === product.id &&
  //     p.selectedSize?.id === product.selectedSize?.id &&
  //     JSON.stringify(p.selectedAddons) === JSON.stringify(product.selectedAddons) &&
  //     p.note === product.note
  //   );

  //   if (existingIndex !== -1) {
  //     cart[existingIndex].quantity += product.quantity;
  //   } else {
  //     cart.push(product);
  //   }

  //   localStorage.setItem('cart', JSON.stringify(cart));
  //   this.cartSubject.next(cart);
  // }
  addToCart(product: any): void {
    let cart: any[] = [];
    try {
      cart = JSON.parse(localStorage.getItem('cart') || '[]');
      if (!Array.isArray(cart)) cart = [];
    } catch {
      cart = [];
    }
    // ⭐️ أضيفي category_id إلى الطبق إذا لم يكن موجوداً
    if (product.dish && !product.dish.category_id) {
      // يمكنك البحث عن category_id من source مختلف
      product.dish.category_id = product.dish.branch_menu_category_id ||
        product.category_id ||
        product.dish.category?.id;
    }

    const existingIndex = cart.findIndex((p: any) =>
      p.dish?.id === product.dish?.id &&
      p.selectedSize?.id === product.selectedSize?.id &&
      this.addonsAreEqual(p.selectedAddons || [], product.selectedAddons || []) &&
      p.note === product.note
    );

    if (existingIndex !== -1) {
      cart[existingIndex].quantity += product.quantity;
      console.log(product)
    } else {
      cart.push(product);
      console.log(product)

    }

    localStorage.setItem('cart', JSON.stringify(cart));
    this.cartSubject.next(cart);
    console.log('Product structure:', {
      dish: product.dish,
      hasBranchMenuCategoryId: !!product.dish?.branch_menu_category_id,
      hasCategoryId: !!product.dish?.category_id,
      hasCategory: !!product.dish?.category
    });
  }
  getCategoryIdForDish(dishId: number): Observable<number | null> {
    return new Observable(observer => {
      // أولاً: بحث في categories الموجودة في الذاكرة
      if (this.categories && this.categories.length > 0) {
        for (const category of this.categories) {
          if (category.dishes && Array.isArray(category.dishes)) {
            // البحث في بنية dishes (قد يكون {dish: {...}} أو مباشرة)
            for (const dishEntry of category.dishes) {
              const dish = dishEntry.dish || dishEntry;
              if (dish.id === dishId) {
                observer.next(category.id);
                observer.complete();
                return;
              }
            }
          }
        }
      }

      // ثانياً: جلب بيانات جديدة إذا لزم الأمر
      this.getMenuDishes().subscribe({
        next: (response: any) => {
          if (response && response.data) {
            this.categories = response.data;
            for (const category of this.categories) {
              if (category.dishes && Array.isArray(category.dishes)) {
                for (const dishEntry of category.dishes) {
                  const dish = dishEntry.dish || dishEntry;
                  if (dish.id === dishId) {
                    observer.next(category.id);
                    observer.complete();
                    return;
                  }
                }
              }
            }
          }
          observer.next(null);
          observer.complete();
        },
        error: () => {
          observer.next(null);
          observer.complete();
        }
      });
    });
  }
  addToHoldCart(product: any): void {
    let cart: any[] = [];
    try {
      cart = JSON.parse(localStorage.getItem('holdCart') || '[]');
      if (!Array.isArray(cart)) cart = [];
    } catch {
      cart = [];
    }

    const existingIndex = cart.findIndex((p: any) =>
      p.dish?.id === product.dish?.id &&
      p.selectedSize?.id === product.selectedSize?.id &&
      this.addonsAreEqual(p.selectedAddons || [], product.selectedAddons || []) &&
      p.note === product.note
    );

    if (existingIndex !== -1) {
      cart[existingIndex].quantity += product.quantity;
    } else {
      cart.push(product);
    }

    localStorage.setItem('holdCart', JSON.stringify(cart));
    this.cartSubject.next(cart);
  }
  private addonsAreEqual(a: any[], b: any[]): boolean {
    if (a.length !== b.length) return false;

    const sortById = (arr: any[]) => [...arr].sort((x, y) => x.id - y.id);
    const sortedA = sortById(a);
    const sortedB = sortById(b);

    return sortedA.every((addon, index) =>
      addon.id === sortedB[index].id &&
      addon.name === sortedB[index].name &&
      addon.price === sortedB[index].price
    );
  }

  removeFromCart(productId: number, sizeId?: number): void {
    this.cart = this.cart.filter(item => !this.isMatchingItem(item, productId, sizeId));
    this.saveCart();
  }

  clearCart(): void {
    this.cart = [];
    localStorage.removeItem('cart');
    this.saveCart();
  }

  private isMatchingItem(item: any, productId: number, sizeId?: number): boolean {
    return item.id === productId && (!sizeId || item.selectedSize?.id === sizeId);
  }

  updateItemTotalPrice(item: any): void {
    item.totalPrice = item.quantity * item.price;
  }

  updateTotalPrices(): void {
    this.cart.forEach(item => {
      this.updateItemTotalPrice(item);
    });
    this.saveCart();
  }

  calculateTotal(): void {
    this.totalPrice = this.cart.reduce((total, item) => total + item.price * item.quantity, 0);
  }

  getTotalPrice(): number {
    return this.totalPrice;
  }

  applyDiscount(discount: number): void {
    this.totalPrice -= discount;
  }
  private savedOrdersSubject = new BehaviorSubject<any[]>(this.getSavedOrdersFromStorage());
  public savedOrders$ = this.savedOrdersSubject.asObservable();

  public getSavedOrdersFromStorage(): any[] {
    const savedOrders = localStorage.getItem('savedOrders');
    if (savedOrders) {
      if (this.savedOrdersSubject) {
        this.savedOrdersSubject.next(JSON.parse(savedOrders))
      };
    }
    return savedOrders ? JSON.parse(savedOrders) : [];
  }

  updateSavedOrders(newOrders: any[]): void {
    localStorage.setItem('savedOrders', JSON.stringify(newOrders));
    this.savedOrdersSubject.next(newOrders); // ✅ Notifies all subscribers
  }
  destroyCart() {
    console.warn('🚨 destroyCart CALLED');
    this.cartSubject.next([]);
  }




  fetchAndSave(): Observable<any> {
    return new Observable(observer => {
      this.getMenuDishes().subscribe({
        next: async (response: any) => {
          if (response && response.status && response.data) {
            this.categories = response.data;


            // Save to IndexedDB for offline use (non-blocking)
            this.db.saveData('categories', this.categories)
              .catch(error => console.error('Error saving to IndexedDB:', error));
          } else {
            console.error("Invalid response format", response);
            // Fallback to offline data if API returns invalid response

          }
          observer.next(response);
          observer.complete();
        },
        error: (err) => {
          console.error('❌ Failed to fetch pils', err);
          observer.error(err);
        }
      });
    });
  }



}

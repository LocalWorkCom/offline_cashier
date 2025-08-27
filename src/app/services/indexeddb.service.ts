// // // // // // import { Injectable } from '@angular/core';

// // // // // // @Injectable({
// // // // // //   providedIn: 'root'
// // // // // // })
// // // // // // export class IndexeddbService {

// // // // // //   constructor() { }
// // // // // // }


// // // // // import { Injectable } from '@angular/core';
// // // // // import { openDB, DBSchema, IDBPDatabase } from 'idb';

// // // // // interface MyDB extends DBSchema {
// // // // //   cart: {
// // // // //     key: number;
// // // // //     value: { id: number; name: string; qty: number; price: number };
// // // // //   };
// // // // //   orders: {
// // // // //     key: number;
// // // // //     value: { id: number; items: any[]; status: string };
// // // // //   };
// // // // // }

// // // // // @Injectable({
// // // // //   providedIn: 'root'
// // // // // })
// // // // // export class IndexeddbService {
// // // // //   private db!: IDBPDatabase<MyDB>;

// // // // //   async init() {
// // // // //     this.db = await openDB<MyDB>('OfflineCashierDB', 1, {
// // // // //       upgrade(db) {
// // // // //         if (!db.objectStoreNames.contains('cart')) {
// // // // //           db.createObjectStore('cart', { keyPath: 'id' });
// // // // //         }
// // // // //         if (!db.objectStoreNames.contains('orders')) {
// // // // //           db.createObjectStore('orders', { keyPath: 'id' });
// // // // //         }
// // // // //       }
// // // // //     });
// // // // //   }

// // // // //   async addToCart(item: any) {
// // // // //     return this.db.put('cart', item);
// // // // //   }

// // // // //   async getCart() {
// // // // //     return this.db.getAll('cart');
// // // // //   }

// // // // //   async clearCart() {
// // // // //     return this.db.clear('cart');
// // // // //   }

// // // // //   async saveOrder(order: any) {
// // // // //     return this.db.put('orders', order);
// // // // //   }

// // // // //   async getOrders() {
// // // // //     return this.db.getAll('orders');
// // // // //   }
// // // // // }
// // // // import { Injectable } from '@angular/core';
// // // // import { openDB, IDBPDatabase } from 'idb';

// // // // @Injectable({
// // // //   providedIn: 'root',
// // // // })
// // // // export class IndexeddbService {
// // // //   private db!: IDBPDatabase;

// // // //   async init() {
// // // //     this.db = await openDB('CashierDB', 1, {
// // // //       upgrade(db) {
// // // //         // عرف stores الأساسية هنا
// // // //         if (!db.objectStoreNames.contains('sideDetails')) {
// // // //           db.createObjectStore('sideDetails', { keyPath: 'id' });
// // // //         }
// // // //         if (!db.objectStoreNames.contains('orders')) {
// // // //           db.createObjectStore('orders', { keyPath: 'id' });
// // // //         }
// // // //       },
// // // //     });
// // // //   }

// // // //   private async ensureStore(storeName: string) {
// // // //     if (!this.db.objectStoreNames.contains(storeName)) {
// // // //       this.db.close();
// // // //       this.db = await openDB('CashierDB', this.db.version + 1, {
// // // //         upgrade(upgradeDb) {
// // // //           if (!upgradeDb.objectStoreNames.contains(storeName)) {
// // // //             upgradeDb.createObjectStore(storeName, { keyPath: 'id' });
// // // //           }
// // // //         },
// // // //       });
// // // //     }
// // // //   }

// // // //   async saveData(storeName: string, data: any) {
// // // //     await this.ensureStore(storeName);
// // // //     const tx = this.db.transaction(storeName, 'readwrite');
// // // //     const store = tx.objectStore(storeName);

// // // //     if (Array.isArray(data)) {
// // // //       for (const item of data) {
// // // //         await store.put(item);
// // // //       }
// // // //     } else {
// // // //       await store.put(data);
// // // //     }

// // // //     await tx.done;
// // // //   }

// // // //   async getAll(storeName: string) {
// // // //     await this.ensureStore(storeName);
// // // //     return await this.db.getAll(storeName);
// // // //   }

// // // //   async getById(storeName: string, id: any) {
// // // //     await this.ensureStore(storeName);
// // // //     return await this.db.get(storeName, id);
// // // //   }

// // // //   async deleteById(storeName: string, id: any) {
// // // //     await this.ensureStore(storeName);
// // // //     return await this.db.delete(storeName, id);
// // // //   }

// // // //   async clearStore(storeName: string) {
// // // //     await this.ensureStore(storeName);
// // // //     return await this.db.clear(storeName);
// // // //   }


// // // // }


// // // import { Injectable } from '@angular/core';
// // // import { openDB, IDBPDatabase } from 'idb';

// // // @Injectable({
// // //   providedIn: 'root',
// // // })
// // // export class IndexeddbService {
// // //   private db!: IDBPDatabase;

// // //   // ✅ In-memory cache
// // //   private cache: { [storeName: string]: any[] } = {};

// // //   async init() {
// // //     this.db = await openDB('CashierDB', 1, {
// // //       upgrade(db) {
// // //         if (!db.objectStoreNames.contains('sideDetails')) {
// // //           db.createObjectStore('sideDetails', { keyPath: 'id' });
// // //         }
// // //         if (!db.objectStoreNames.contains('orders')) {
// // //           db.createObjectStore('orders', { keyPath: 'id' });
// // //         }
// // //         if (!db.objectStoreNames.contains('categories')) {
// // //           db.createObjectStore('categories', { keyPath: 'id' } );
// // //         }
// // //       },
// // //     });
// // //   }

// // //   // Save data to DB + cache
// // //   async saveData(storeName: string, data: any) {
// // //     this.cache[storeName] = Array.isArray(data) ? data : [data];

// // //     const tx = this.db.transaction(storeName, 'readwrite');
// // //     const store = tx.objectStore(storeName);

// // //     if (Array.isArray(data)) {
// // //       for (const item of data) {
// // //         await store.put(item);
// // //       }
// // //     } else {
// // //       await store.put(data);
// // //     }

// // //     await tx.done;
// // //   }

// // //   // Get all data from cache first, fallback to IndexedDB
// // //   async getAll(storeName: string) {
// // //     if (this.cache[storeName]) {
// // //       return this.cache[storeName];
// // //     }
// // //     const data = await this.db.getAll(storeName);
// // //     this.cache[storeName] = data;
// // //     return data;
// // //   }

// // //   async clearStore(storeName: string) {
// // //     this.cache[storeName] = [];
// // //     const tx = this.db.transaction(storeName, 'readwrite');
// // //     await tx.objectStore(storeName).clear();
// // //     await tx.done;
// // //   }
// // // }


// // import { Injectable } from '@angular/core';

// // @Injectable({
// //   providedIn: 'root'
// // })
// // export class IndexeddbService {
// //   private db!: IDBDatabase;

// //   constructor() {}

// //   init(): Promise<void> {
// //     return new Promise((resolve, reject) => {
// //       const request = indexedDB.open('MyDB', 1);

// //       request.onupgradeneeded = (event: any) => {
// //         this.db = event.target.result;
// //         if (!this.db.objectStoreNames.contains('categories')) {
// //           this.db.createObjectStore('categories', { keyPath: 'id' });
// //         }
// //       };

// //       request.onsuccess = (event: any) => {
// //         this.db = event.target.result;
// //         resolve();
// //       };

// //       request.onerror = (event) => {
// //         console.error('IndexedDB init failed', event);
// //         reject(event);
// //       };
// //     });
// //   }

// //   getAll(storeName: string): Promise<any[]> {
// //     return new Promise((resolve, reject) => {
// //       const tx = this.db.transaction(storeName, 'readonly');
// //       const store = tx.objectStore(storeName);
// //       const request = store.getAll();

// //       request.onsuccess = () => resolve(request.result);
// //       request.onerror = (e) => reject(e);
// //     });
// //   }

// //   saveData(storeName: string, data: any[]): Promise<void> {
// //     return new Promise((resolve, reject) => {
// //       const tx = this.db.transaction(storeName, 'readwrite');
// //       const store = tx.objectStore(storeName);

// //       data.forEach(item => store.put(item));

// //       tx.oncomplete = () => resolve();
// //       tx.onerror = (e) => reject(e);
// //     });
// //   }

// //   clearStore(storeName: string): Promise<void> {
// //     return new Promise((resolve, reject) => {
// //       const tx = this.db.transaction(storeName, 'readwrite');
// //       const store = tx.objectStore(storeName);
// //       const request = store.clear();

// //       request.onsuccess = () => resolve();
// //       request.onerror = (e) => reject(e);
// //     });
// //   }
// // }


// import { Injectable } from '@angular/core';

// @Injectable({
//   providedIn: 'root'
// })
// export class IndexeddbService {
//   private db!: IDBDatabase;
//   private isInitialized = false;
//   private initPromise!: Promise<void>;

//   constructor() {
//     this.init();
//   }

//   init(): Promise<void> {
//     if (this.initPromise) return this.initPromise;

//     this.initPromise = new Promise((resolve, reject) => {
//       const request = indexedDB.open('MyDB', 4); // Incremented version

//       request.onupgradeneeded = (event: any) => {
//         this.db = event.target.result;

//         // Create categories store if it doesn't exist
//         if (!this.db.objectStoreNames.contains('categories')) {
//           this.db.createObjectStore('categories', { keyPath: 'id' });
//         }

//         // Create lastSync store for tracking sync status
//         if (!this.db.objectStoreNames.contains('lastSync')) {
//           this.db.createObjectStore('lastSync', { keyPath: 'storeName' });
//         }


//         if (!this.db.objectStoreNames.contains('cart')) {
//           this.db.createObjectStore('cart', {
//             keyPath: 'cartItemId', // Unique ID for each cart item
//             autoIncrement: true
//           });
//         }
//       };

//       request.onsuccess = (event: any) => {
//         this.db = event.target.result;
//         this.isInitialized = true;
//         resolve();
//       };

//       request.onerror = (event) => {
//         console.error('IndexedDB init failed', event);
//         reject(event);
//       };
//     });

//     return this.initPromise;
//   }

//   // Get all data from a store
//   getAll(storeName: string): Promise<any[]> {
//     return this.ensureInit().then(() => {
//       return new Promise((resolve, reject) => {
//         const tx = this.db.transaction(storeName, 'readonly');
//         const store = tx.objectStore(storeName);
//         const request = store.getAll();

//         request.onsuccess = () => resolve(request.result);
//         request.onerror = (e) => reject(e);
//       });
//     });
//   }

//   // Save data to a store
//   saveData(storeName: string, data: any[]): Promise<void> {
//     return this.ensureInit().then(() => {
//       return new Promise((resolve, reject) => {
//         const tx = this.db.transaction(storeName, 'readwrite');
//         const store = tx.objectStore(storeName);

//         // Clear existing data first
//         store.clear().onsuccess = () => {
//           data.forEach(item => store.put(item));

//           tx.oncomplete = () => resolve();
//           tx.onerror = (e) => reject(e);
//         };
//       });
//     });
//   }

//   // Get single item by key
//   getByKey(storeName: string, key: any): Promise<any> {
//     return this.ensureInit().then(() => {
//       return new Promise((resolve, reject) => {
//         const tx = this.db.transaction(storeName, 'readonly');
//         const store = tx.objectStore(storeName);
//         const request = store.get(key);

//         request.onsuccess = () => resolve(request.result);
//         request.onerror = (e) => reject(e);
//       });
//     });
//   }

//     // Add item to cart
//   addToCart(cartItem: any): Promise<number> {
//     return this.ensureInit().then(() => {
//       return new Promise((resolve, reject) => {
//         const tx = this.db.transaction('cart', 'readwrite');
//         const store = tx.objectStore('cart');
//         const request = store.add(cartItem);

//         request.onsuccess = () => resolve(request.result as number);
//         request.onerror = (e) => reject(e);
//       });
//     });
//   }
//    // Get all cart items
//   getCartItems(): Promise<any[]> {
//     return this.getAll('cart');
//   }

//     // Update cart item
//   updateCartItem(cartItemId: number, updates: any): Promise<void> {
//     return this.ensureInit().then(() => {
//       return new Promise((resolve, reject) => {
//         const tx = this.db.transaction('cart', 'readwrite');
//         const store = tx.objectStore('cart');
//         const request = store.get(cartItemId);

//         request.onsuccess = () => {
//           const item = request.result;
//           if (item) {
//             const updatedItem = { ...item, ...updates };
//             store.put(updatedItem);
//             tx.oncomplete = () => resolve();
//             tx.onerror = (e) => reject(e);
//           } else {
//             reject(new Error('Cart item not found'));
//           }
//         };
//         request.onerror = (e) => reject(e);
//       });
//     });
//   }

//     // Remove item from cart
//   removeFromCart(cartItemId: number): Promise<void> {
//     return this.ensureInit().then(() => {
//       return new Promise((resolve, reject) => {
//         const tx = this.db.transaction('cart', 'readwrite');
//         const store = tx.objectStore('cart');
//         const request = store.delete(cartItemId);

//         request.onsuccess = () => resolve();
//         request.onerror = (e) => reject(e);
//       });
//     });
//   }
//   // Clear entire cart
//   clearCart(): Promise<void> {
//     return this.ensureInit().then(() => {
//       return new Promise((resolve, reject) => {
//         const tx = this.db.transaction('cart', 'readwrite');
//         const store = tx.objectStore('cart');
//         const request = store.clear();

//         request.onsuccess = () => resolve();
//         request.onerror = (e) => reject(e);
//       });
//     });
//   }

//     // Get cart item count
//   getCartItemCount(): Promise<number> {
//     return this.ensureInit().then(() => {
//       return new Promise((resolve, reject) => {
//         const tx = this.db.transaction('cart', 'readonly');
//         const store = tx.objectStore('cart');
//         const request = store.count();

//         request.onsuccess = () => resolve(request.result);
//         request.onerror = (e) => reject(e);
//       });
//     });
//   }


//   // Check if database is initialized
//   private ensureInit(): Promise<void> {
//     if (this.isInitialized) {
//       return Promise.resolve();
//     }
//     return this.init();
//   }

//   // Check if we have offline data
//   hasOfflineData(storeName: string): Promise<boolean> {
//     return this.getAll(storeName).then(data => data.length > 0);
//   }
// }



import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class IndexeddbService {
  private db!: IDBDatabase;
  private isInitialized = false;
  private initPromise!: Promise<void>;

  constructor() {
    this.init();
  }

  init(): Promise<void> {
    if (this.initPromise) return this.initPromise;

    this.initPromise = new Promise((resolve, reject) => {
      const request = indexedDB.open('MyDB', 10); // Incremented version to 4

      request.onupgradeneeded = (event: any) => {
        this.db = event.target.result;

        // Create categories store
        if (!this.db.objectStoreNames.contains('categories')) {
          this.db.createObjectStore('categories', { keyPath: 'id' });
        }

        if (!this.db.objectStoreNames.contains('tables')) {
          this.db.createObjectStore('tables', { keyPath: 'id' });
        }

        if (!this.db.objectStoreNames.contains('selectedTable')) {
          this.db.createObjectStore('selectedTable', { keyPath: 'id' });
        }

        // Create lastSync store
        if (!this.db.objectStoreNames.contains('lastSync')) {
          this.db.createObjectStore('lastSync', { keyPath: 'storeName' });
        }

        // Create cart store
        if (!this.db.objectStoreNames.contains('cart')) {
          this.db.createObjectStore('cart', {
            keyPath: 'cartItemId',
            autoIncrement: true
          });
        }

        // Create pendingOperations store for offline operations
        if (!this.db.objectStoreNames.contains('pendingOperations')) {
          this.db.createObjectStore('pendingOperations', {
            keyPath: 'id',
            autoIncrement: true
          });
        }
      };

      request.onsuccess = (event: any) => {
        this.db = event.target.result;
        this.isInitialized = true;
        resolve();
      };

      request.onerror = (event) => {
        console.error('IndexedDB init failed', event);
        reject(event);
      };
    });

    return this.initPromise;
  }

  // Add item to cart
  addToCart(cartItem: any): Promise<number> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('cart', 'readwrite');
        const store = tx.objectStore('cart');

        // Add timestamp and online status
        const itemWithMetadata = {
          ...cartItem,
          addedAt: new Date().toISOString(),
          isSynced: navigator.onLine,
          lastUpdated: new Date().toISOString()
        };

        const request = store.add(itemWithMetadata);

        request.onsuccess = () => resolve(request.result as number);
         // Reload the page
    window.location.reload();
        request.onerror = (e) => reject(e);
      });
    });
  }

  // Get all cart items
  getCartItems(): Promise<any[]> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('cart', 'readonly');
        const store = tx.objectStore('cart');
        const request = store.getAll();

        request.onsuccess = () => resolve(request.result);
        request.onerror = (e) => reject(e);
      });
    });
  }

  // Get cart item count
  getCartItemCount(): Promise<number> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('cart', 'readonly');
        const store = tx.objectStore('cart');
        const request = store.count();

        request.onsuccess = () => resolve(request.result);
        request.onerror = (e) => reject(e);
      });
    });
  }

  // Remove item from cart
  removeFromCart(cartItemId: number): Promise<void> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('cart', 'readwrite');
        const store = tx.objectStore('cart');
        const request = store.delete(cartItemId);

        request.onsuccess = () => resolve();
        request.onerror = (e) => reject(e);
      });
    });
  }

  // Clear entire cart
  clearCart(): Promise<void> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('cart', 'readwrite');
        const store = tx.objectStore('cart');
        const request = store.clear();

        request.onsuccess = () => resolve();
        request.onerror = (e) => reject(e);
      });
    });
  }

  // Mark cart items as synced
  markCartItemsAsSynced(): Promise<void> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('cart', 'readwrite');
        const store = tx.objectStore('cart');
        const request = store.getAll();

        request.onsuccess = () => {
          const items = request.result;
          items.forEach(item => {
            if (!item.isSynced) {
              item.isSynced = true;
              store.put(item);
            }
          });
          tx.oncomplete = () => resolve();
        };
        request.onerror = (e) => reject(e);
      });
    });
  }

  // Get unsynced cart items
  getUnsyncedCartItems(): Promise<any[]> {
    return this.getCartItems().then(items =>
      items.filter(item => !item.isSynced)
    );
  }

  // Check if database is initialized
  private ensureInit(): Promise<void> {
    if (this.isInitialized) {
      return Promise.resolve();
    }
    return this.init();
  }

  // Other existing methods...
  getAll(storeName: string): Promise<any[]> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction(storeName, 'readonly');
        const store = tx.objectStore(storeName);
        const request = store.getAll();

        request.onsuccess = () => resolve(request.result);
        request.onerror = (e) => reject(e);
      });
    });
  }

  saveData(storeName: string, data: any[]): Promise<void> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction(storeName, 'readwrite');
        const store = tx.objectStore(storeName);

        store.clear().onsuccess = () => {
          data.forEach(item => store.put(item));

          tx.oncomplete = () => resolve();
          tx.onerror = (e) => reject(e);
        };
      });
    });
  }
}

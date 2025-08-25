// // // // import { Injectable } from '@angular/core';

// // // // @Injectable({
// // // //   providedIn: 'root'
// // // // })
// // // // export class IndexeddbService {

// // // //   constructor() { }
// // // // }


// // // import { Injectable } from '@angular/core';
// // // import { openDB, DBSchema, IDBPDatabase } from 'idb';

// // // interface MyDB extends DBSchema {
// // //   cart: {
// // //     key: number;
// // //     value: { id: number; name: string; qty: number; price: number };
// // //   };
// // //   orders: {
// // //     key: number;
// // //     value: { id: number; items: any[]; status: string };
// // //   };
// // // }

// // // @Injectable({
// // //   providedIn: 'root'
// // // })
// // // export class IndexeddbService {
// // //   private db!: IDBPDatabase<MyDB>;

// // //   async init() {
// // //     this.db = await openDB<MyDB>('OfflineCashierDB', 1, {
// // //       upgrade(db) {
// // //         if (!db.objectStoreNames.contains('cart')) {
// // //           db.createObjectStore('cart', { keyPath: 'id' });
// // //         }
// // //         if (!db.objectStoreNames.contains('orders')) {
// // //           db.createObjectStore('orders', { keyPath: 'id' });
// // //         }
// // //       }
// // //     });
// // //   }

// // //   async addToCart(item: any) {
// // //     return this.db.put('cart', item);
// // //   }

// // //   async getCart() {
// // //     return this.db.getAll('cart');
// // //   }

// // //   async clearCart() {
// // //     return this.db.clear('cart');
// // //   }

// // //   async saveOrder(order: any) {
// // //     return this.db.put('orders', order);
// // //   }

// // //   async getOrders() {
// // //     return this.db.getAll('orders');
// // //   }
// // // }
// // import { Injectable } from '@angular/core';
// // import { openDB, IDBPDatabase } from 'idb';

// // @Injectable({
// //   providedIn: 'root',
// // })
// // export class IndexeddbService {
// //   private db!: IDBPDatabase;

// //   async init() {
// //     this.db = await openDB('CashierDB', 1, {
// //       upgrade(db) {
// //         // عرف stores الأساسية هنا
// //         if (!db.objectStoreNames.contains('sideDetails')) {
// //           db.createObjectStore('sideDetails', { keyPath: 'id' });
// //         }
// //         if (!db.objectStoreNames.contains('orders')) {
// //           db.createObjectStore('orders', { keyPath: 'id' });
// //         }
// //       },
// //     });
// //   }

// //   private async ensureStore(storeName: string) {
// //     if (!this.db.objectStoreNames.contains(storeName)) {
// //       this.db.close();
// //       this.db = await openDB('CashierDB', this.db.version + 1, {
// //         upgrade(upgradeDb) {
// //           if (!upgradeDb.objectStoreNames.contains(storeName)) {
// //             upgradeDb.createObjectStore(storeName, { keyPath: 'id' });
// //           }
// //         },
// //       });
// //     }
// //   }

// //   async saveData(storeName: string, data: any) {
// //     await this.ensureStore(storeName);
// //     const tx = this.db.transaction(storeName, 'readwrite');
// //     const store = tx.objectStore(storeName);

// //     if (Array.isArray(data)) {
// //       for (const item of data) {
// //         await store.put(item);
// //       }
// //     } else {
// //       await store.put(data);
// //     }

// //     await tx.done;
// //   }

// //   async getAll(storeName: string) {
// //     await this.ensureStore(storeName);
// //     return await this.db.getAll(storeName);
// //   }

// //   async getById(storeName: string, id: any) {
// //     await this.ensureStore(storeName);
// //     return await this.db.get(storeName, id);
// //   }

// //   async deleteById(storeName: string, id: any) {
// //     await this.ensureStore(storeName);
// //     return await this.db.delete(storeName, id);
// //   }

// //   async clearStore(storeName: string) {
// //     await this.ensureStore(storeName);
// //     return await this.db.clear(storeName);
// //   }


// // }


// import { Injectable } from '@angular/core';
// import { openDB, IDBPDatabase } from 'idb';

// @Injectable({
//   providedIn: 'root',
// })
// export class IndexeddbService {
//   private db!: IDBPDatabase;

//   // ✅ In-memory cache
//   private cache: { [storeName: string]: any[] } = {};

//   async init() {
//     this.db = await openDB('CashierDB', 1, {
//       upgrade(db) {
//         if (!db.objectStoreNames.contains('sideDetails')) {
//           db.createObjectStore('sideDetails', { keyPath: 'id' });
//         }
//         if (!db.objectStoreNames.contains('orders')) {
//           db.createObjectStore('orders', { keyPath: 'id' });
//         }
//         if (!db.objectStoreNames.contains('categories')) {
//           db.createObjectStore('categories', { keyPath: 'id' } );
//         }
//       },
//     });
//   }

//   // Save data to DB + cache
//   async saveData(storeName: string, data: any) {
//     this.cache[storeName] = Array.isArray(data) ? data : [data];

//     const tx = this.db.transaction(storeName, 'readwrite');
//     const store = tx.objectStore(storeName);

//     if (Array.isArray(data)) {
//       for (const item of data) {
//         await store.put(item);
//       }
//     } else {
//       await store.put(data);
//     }

//     await tx.done;
//   }

//   // Get all data from cache first, fallback to IndexedDB
//   async getAll(storeName: string) {
//     if (this.cache[storeName]) {
//       return this.cache[storeName];
//     }
//     const data = await this.db.getAll(storeName);
//     this.cache[storeName] = data;
//     return data;
//   }

//   async clearStore(storeName: string) {
//     this.cache[storeName] = [];
//     const tx = this.db.transaction(storeName, 'readwrite');
//     await tx.objectStore(storeName).clear();
//     await tx.done;
//   }
// }


import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class IndexeddbService {
  private db!: IDBDatabase;

  constructor() {}

  init(): Promise<void> {
    return new Promise((resolve, reject) => {
      const request = indexedDB.open('MyDB', 1);

      request.onupgradeneeded = (event: any) => {
        this.db = event.target.result;
        if (!this.db.objectStoreNames.contains('categories')) {
          this.db.createObjectStore('categories', { keyPath: 'id' });
        }
      };

      request.onsuccess = (event: any) => {
        this.db = event.target.result;
        resolve();
      };

      request.onerror = (event) => {
        console.error('IndexedDB init failed', event);
        reject(event);
      };
    });
  }

  getAll(storeName: string): Promise<any[]> {
    return new Promise((resolve, reject) => {
      const tx = this.db.transaction(storeName, 'readonly');
      const store = tx.objectStore(storeName);
      const request = store.getAll();

      request.onsuccess = () => resolve(request.result);
      request.onerror = (e) => reject(e);
    });
  }

  saveData(storeName: string, data: any[]): Promise<void> {
    return new Promise((resolve, reject) => {
      const tx = this.db.transaction(storeName, 'readwrite');
      const store = tx.objectStore(storeName);

      data.forEach(item => store.put(item));

      tx.oncomplete = () => resolve();
      tx.onerror = (e) => reject(e);
    });
  }

  clearStore(storeName: string): Promise<void> {
    return new Promise((resolve, reject) => {
      const tx = this.db.transaction(storeName, 'readwrite');
      const store = tx.objectStore(storeName);
      const request = store.clear();

      request.onsuccess = () => resolve();
      request.onerror = (e) => reject(e);
    });
  }
}

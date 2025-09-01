
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

      const request = indexedDB.open('MyDB', 150); // Incremented version to 4

      request.onupgradeneeded = (event: any) => {
        this.db = event.target.result;

        // Create categories store
        if (!this.db.objectStoreNames.contains('categories')) {
          this.db.createObjectStore('categories', { keyPath: 'id' });
        }

        // Modify pills store to use autoIncrement instead of keyPath
        if (this.db.objectStoreNames.contains('pillDetails')) {
          this.db.deleteObjectStore('pillDetails');
        }
        this.db.createObjectStore('pillDetails', { keyPath: 'id' });

        // Create or modify pills store
        // if (this.db.objectStoreNames.contains('pills')) {
        //   this.db.deleteObjectStore('pills');
        // }
        // this.db.createObjectStore('pills', { keyPath: 'invoice_id' });
         if (!(this.db.objectStoreNames.contains('pills'))) {
           const store = this.db.createObjectStore('pills', { keyPath: 'id', autoIncrement: true });
           store.createIndex('invoice_id', 'invoice_id', { unique: false });
          }

        // Create nextOrderNumber store
        if (!this.db.objectStoreNames.contains('nextOrderNumber')) {
          this.db.createObjectStore('nextOrderNumber', { keyPath: 'id' });
        }

        if (!this.db.objectStoreNames.contains('tables')) {
          this.db.createObjectStore('tables', { keyPath: 'id' });
        }

        if (!this.db.objectStoreNames.contains('selectedTable')) {
          this.db.createObjectStore('selectedTable', { keyPath: 'id' });
        }
        if (!this.db.objectStoreNames.contains('selectedOrderType')) {
          this.db.createObjectStore('selectedOrderType', { keyPath: 'id' });
        }

        if (!this.db.objectStoreNames.contains('branch')) {
          this.db.createObjectStore('branch', { keyPath: 'id' });
        }

        if (!this.db.objectStoreNames.contains('branch_id')) {
          this.db.createObjectStore('branch_id', { keyPath: 'id' });
        }

        // Create lastSync store
        if (!this.db.objectStoreNames.contains('lastSync')) {
          this.db.createObjectStore('lastSync', { keyPath: 'storeName' });
        }

        // Create hotels store
        if (!this.db.objectStoreNames.contains('hotels')) {
          this.db.createObjectStore('hotels', { keyPath: 'id' });
        }

        // Create areas store
        if (!this.db.objectStoreNames.contains('areas')) {
          this.db.createObjectStore('areas', { keyPath: 'id' });
        }

        // Create countries store
        if (!this.db.objectStoreNames.contains('countries')) {
          this.db.createObjectStore('countries', { keyPath: 'code' });
        }

        // Create formData store for delivery form data
        if (!this.db.objectStoreNames.contains('formData')) {
          this.db.createObjectStore('formData', { keyPath: 'id', autoIncrement: true });
        }

        // Create clientInfo store for client information
        if (!this.db.objectStoreNames.contains('clientInfo')) {
          this.db.createObjectStore('clientInfo', { keyPath: 'id', autoIncrement: true });
        }

        if (!this.db.objectStoreNames.contains('orders')) {
          this.db.createObjectStore('orders', { keyPath: 'order_details.order_id' });
        }

        // Create cart store
        if (!this.db.objectStoreNames.contains('cart')) {
          this.db.createObjectStore('cart', {
            keyPath: 'cartItemId',
            autoIncrement: true
          });
        }


        // Create form_delivery store
        if (!this.db.objectStoreNames.contains('form_delivery')) {
          this.db.createObjectStore('form_delivery', { keyPath: 'id' });
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

  // Save form data to IndexedDB
  saveFormData(formData: any): Promise<number> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('formData', 'readwrite');
        const store = tx.objectStore('formData');

        // Add timestamp and online status
        const formDataWithMetadata = {
          ...formData,
          savedAt: new Date().toISOString(),
          isSynced: navigator.onLine
        };

        const request = store.add(formDataWithMetadata);

        request.onsuccess = () => resolve(request.result as number);
        request.onerror = (e) => reject(e);
      });
    });
  }

  // Get all form data
  getFormData(): Promise<any[]> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('formData', 'readonly');
        const store = tx.objectStore('formData');
        const request = store.getAll();

        request.onsuccess = () => resolve(request.result);
        request.onerror = (e) => reject(e);
      });
    });
  }

  // Clear form data
  clearFormData(): Promise<void> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('formData', 'readwrite');
        const store = tx.objectStore('formData');
        const request = store.clear();

        request.onsuccess = () => resolve();
        request.onerror = (e) => reject(e);
      });
    });
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



  // Save orders to IndexedDB
  saveOrders(orders: any[]): Promise<void> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('orders', 'readwrite');
        const store = tx.objectStore('orders');

        // Clear existing orders
        store.clear().onsuccess = () => {
          // Add all new orders
          orders.forEach(order => {
            const orderWithMetadata = {
              ...order,
              savedAt: new Date().toISOString(),
              isSynced: navigator.onLine
            };
            store.put(orderWithMetadata);
          });

          tx.oncomplete = () => resolve();
          tx.onerror = (e) => reject(e);
        };
      });
    });
  }

  // Get orders from IndexedDB
  getOrders(): Promise<any[]> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('orders', 'readonly');
        const store = tx.objectStore('orders');
        const request = store.getAll();

        request.onsuccess = () => resolve(request.result);
        request.onerror = (e) => reject(e);
      });
    });
  }

  // Get last sync time for orders
  getOrdersLastSync(): Promise<number> {
    return this.getLastSync('orders');
  }

  // Set last sync time for orders
  setOrdersLastSync(timestamp: number): Promise<void> {
    return this.setLastSync('orders', timestamp);
  }



  // Get last sync time for a specific store
  getLastSync(storeName: string): Promise<number> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('lastSync', 'readonly');
        const store = tx.objectStore('lastSync');
        const request = store.get(storeName);

        request.onsuccess = () => {
          resolve(request.result ? request.result.timestamp : 0);
        };
        request.onerror = (e) => reject(e);
      });
    });
  }

  // Set last sync time for a specific store
  setLastSync(storeName: string, timestamp: number): Promise<void> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('lastSync', 'readwrite');
        const store = tx.objectStore('lastSync');
        const request = store.put({ storeName, timestamp });

        request.onsuccess = () => resolve();
        request.onerror = (e) => reject(e);
      });
    });
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
  getData(storeName: string, key: any): Promise<any> {
    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction([storeName], 'readonly');
      const store = transaction.objectStore(storeName);
      const request = store.get(key);

      request.onsuccess = () => resolve(request.result);
      request.onerror = () => reject(request.error);
    });
  }
  // saveData(storeName: string, data: any[]): Promise<void> {
  //   return this.ensureInit().then(() => {
  //     return new Promise((resolve, reject) => {
  //       const tx = this.db.transaction(storeName, 'readwrite');
  //       const store = tx.objectStore(storeName);

  //       store.clear().onsuccess = () => {
  //         data.forEach(item => store.put(item));

  //         tx.oncomplete = () => resolve();
  //         tx.onerror = (e) => reject(e);
  //       };
  //     });
  //   });
  // }

  // In IndexeddbService, modify the saveData method to handle missing key paths
  // In indexeddb.service.ts

  private hasKeyPath(item: any, keyPath: string | string[]): boolean {
    if (typeof keyPath === 'string') {
      return item.hasOwnProperty(keyPath);
    } else if (Array.isArray(keyPath)) {
      return keyPath.every(path => item.hasOwnProperty(path));
    }
    return false;
  }

  private setKeyPath(item: any, keyPath: string | string[], value: any): void {
    if (typeof keyPath === 'string') {
      item[keyPath] = value;
    } else if (Array.isArray(keyPath)) {
      // For compound keys, we need to handle this differently
      // For simplicity, we'll just set the first key path
      if (keyPath.length > 0) {
        item[keyPath[0]] = value;
      }
    }
  }
  saveData(storeName: string, data: any, key?: any): Promise<void> {
    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction([storeName], 'readwrite');
      const store = transaction.objectStore(storeName);
      const keyPath = store.keyPath;

      // Handle the case where a custom key is provided
      if (key !== undefined) {
        // Create a new object with the key as a property
        const itemToSave = { ...data, id: key };
        const request = store.put(itemToSave);

        request.onsuccess = () => resolve();
        request.onerror = () => reject(request.error);
      } else {
        // Handle array of data
        if (Array.isArray(data)) {
          const promises = data.map(item => {
            return new Promise<void>((res, rej) => {
              // Check if the item has the required key path
              if (keyPath && !this.hasKeyPath(item, keyPath)) {
                // Generate a temporary key if missing
                this.setKeyPath(item, keyPath, `temp_${Date.now()}_${Math.random()}`);
              }
              const request = store.put(item);
              request.onsuccess = () => res();
              request.onerror = () => rej(request.error);
            });
          });

          Promise.all(promises)
            .then(() => resolve())
            .catch(error => reject(error));
        } else {
          // Handle single object
          // Check if the data has the required key path
          if (keyPath && !this.hasKeyPath(data, keyPath)) {
            // Generate a temporary key if missing
            this.setKeyPath(data, keyPath, `temp_${Date.now()}_${Math.random()}`);
          }
          const request = store.put(data);
          request.onsuccess = () => resolve();
          request.onerror = () => reject(request.error);
        }
      }
    });
  }


  // Save client info to IndexedDB
  saveClientInfo(clientInfo: any): Promise<number> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('clientInfo', 'readwrite');
        const store = tx.objectStore('clientInfo');

        // Add timestamp
        const clientInfoWithMetadata = {
          ...clientInfo,
          savedAt: new Date().toISOString()
        };

        const request = store.add(clientInfoWithMetadata);

        request.onsuccess = () => resolve(request.result as number);
        request.onerror = (e) => reject(e);
      });
    });
  }

  // Get client info from IndexedDB
  getClientInfo(): Promise<any[]> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('clientInfo', 'readonly');
        const store = tx.objectStore('clientInfo');
        const request = store.getAll();

        request.onsuccess = () => resolve(request.result);
        request.onerror = (e) => reject(e);
      });
    });
  }

  // Get latest client info
  getLatestClientInfo(): Promise<any> {
    return this.getClientInfo().then(clientInfoArray => {
      if (clientInfoArray && clientInfoArray.length > 0) {
        // Sort by savedAt descending and return the latest
        return clientInfoArray.sort((a, b) =>
          new Date(b.savedAt).getTime() - new Date(a.savedAt).getTime()
        )[0];
      }
      return null;
    });
  }

  // Clear client info
  clearClientInfo(): Promise<void> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('clientInfo', 'readwrite');
        const store = tx.objectStore('clientInfo');
        const request = store.clear();

        request.onsuccess = () => resolve();
        request.onerror = (e) => reject(e);
      });
    });
  }

  // Get the last order number from IndexedDB
  getLastOrderNumberFromDB(): Promise<string> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('orders', 'readonly');
        const store = tx.objectStore('orders');
        const request = store.openCursor(null, 'prev'); // Get last item

        request.onsuccess = () => {
          const cursor = request.result;
          if (cursor) {
            const orderNumber = cursor.value.order_details?.order_number || '#CS-1000';
            resolve(orderNumber);
          } else {
            resolve('#CS-1000'); // Default starting point
          }
        };
        request.onerror = (e) => reject(e);
      });
    });
  }

  // Extract numeric part from order number and increment
  extractAndIncrementOrderNumber(orderNumber: string): { current: string, next: string } {
    if (!orderNumber) {
      return { current: '#CS-1000', next: '#CS-1001' };
    }

    // Extract numeric part (e.g., #CS-31163 → 31163)
    const numericMatch = orderNumber.match(/\d+/);
    const prefixMatch = orderNumber.match(/^[^\d]+/);

    const numericPart = numericMatch ? parseInt(numericMatch[0], 10) : 1000;
    const prefix = prefixMatch ? prefixMatch[0] : '#CS-';

    const nextNumeric = numericPart + 1;

    return {
      current: orderNumber,
      next: `${prefix}${nextNumeric}`
    };
  }

  // Save the next order number
  saveNextOrderNumber(nextOrderNumber: string): Promise<void> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('nextOrderNumber', 'readwrite');
        const store = tx.objectStore('nextOrderNumber');
        const data = {
          id: 'nextOrderNumber',
          value: nextOrderNumber,
          updatedAt: new Date().toISOString()
        };
        const request = store.put(data);

        request.onsuccess = () => resolve();
        request.onerror = (e) => reject(e);
      });
    });
  }

  // Get stored next order number
  getStoredNextOrderNumber(): Promise<string> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('nextOrderNumber', 'readonly');
        const store = tx.objectStore('nextOrderNumber');
        const request = store.get('nextOrderNumber');

        request.onsuccess = () => {
          resolve(request.result ? request.result.value : '#CS-1001');
        };
        request.onerror = (e) => reject(e);
      });
    });
  }

  // Complete process: get last order, extract number, increment, and save
  processAndStoreNextOrderNumber(): Promise<{ current: string, next: string }> {
    return this.getLastOrderNumberFromDB().then(lastOrderNumber => {
      // Extract and increment the order number
      const result = this.extractAndIncrementOrderNumber(lastOrderNumber);

      console.log('Extracted from:', lastOrderNumber);
      console.log('Current:', result.current);
      console.log('Next:', result.next);

      // Save the next order number
      return this.saveNextOrderNumber(result.next).then(() => result);
    });
  }


  // Get order by ID from IndexedDB
  getOrderById(orderId: number): Promise<any> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('orders', 'readonly');
        const store = tx.objectStore('orders');

        // Convert orderId to number if it's a string
        const numericOrderId = typeof orderId === 'string' ? parseInt(orderId, 10) : orderId;

        const request = store.get(numericOrderId);

        request.onsuccess = () => {
          resolve(request.result ? request.result : null);
        };
        request.onerror = (e) => reject(e);
      });
    });
  }

  // Save single order to IndexedDB
  saveOrder(order: any): Promise<void> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('orders', 'readwrite');
        const store = tx.objectStore('orders');

        const orderWithMetadata = {
          ...order,
          savedAt: new Date().toISOString(),
          isSynced: navigator.onLine
        };

        const request = store.put(orderWithMetadata);

        request.onsuccess = () => resolve();
        request.onerror = (e) => reject(e);
      });
    });
  }
  deleteOrder(orderId: string): Promise<void> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('orders', 'readwrite');
        const store = tx.objectStore('orders');
        const request = store.delete(orderId);

        request.onsuccess = () => resolve();
        request.onerror = (e) => reject(e);
      });
    });
  }
  // Save app setting to IndexedDB
   saveAppSetting(key: string, value: any): Promise<void> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('appSettings', 'readwrite');
        const store = tx.objectStore('appSettings');
        const data = {
          id: key,
          value: value,
          updatedAt: new Date().toISOString()
        };
        const request = store.put(data);

        request.onsuccess = () => resolve();
        request.onerror = (e) => reject(e);
      });
    });
  }

  // Get app setting from IndexedDB
   getAppSetting(key: string): Promise<any> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('appSettings', 'readonly');
        const store = tx.objectStore('appSettings');
        const request = store.get(key);

        request.onsuccess = () => {
          resolve(request.result ? request.result.value : null);
        };
        request.onerror = (e) => reject(e);
      });
    });
  }

  // Remove app setting from IndexedDB
   removeAppSetting(key: string): Promise<void> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('appSettings', 'readwrite');
        const store = tx.objectStore('appSettings');
        const request = store.delete(key);

        request.onsuccess = () => resolve();
        request.onerror = (e) => reject(e);
      });
    });
  }


   removeItem(storeName: string, key: any): Promise<void> {
  return this.ensureInit().then(() => {
    return new Promise((resolve, reject) => {
      const tx = this.db.transaction(storeName, 'readwrite');
      const store = tx.objectStore(storeName);
      const request = store.delete(key);

      request.onsuccess = () => {
        console.log(`✅ Item with key ${key} removed from ${storeName}`);
        resolve();
      };
      request.onerror = (e) => {
        console.error(`❌ Error removing item from ${storeName}:`, e);
        reject(e);
      };
    });
  });
}

  // 🔹 Get pill by invoice_id (using index)
  async getPillByInvoiceId(invoiceId: string | number): Promise<any> {
    await this.ensureInit();

    return new Promise((resolve, reject) => {
      const tx = this.db!.transaction('pills', 'readonly');
      const store = tx.objectStore('pills');
      const index = store.index('invoice_id'); // ✅ use index
      const request = index.get(Number(invoiceId));

      request.onsuccess = () => resolve(request.result ?? null);
      request.onerror = (e) => reject(e);
    });
  }

}

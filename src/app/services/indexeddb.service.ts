import { Injectable } from '@angular/core';
import { table } from 'console';

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

      const request = indexedDB.open('MyDB', 151); // Incremented version to 4

      request.onupgradeneeded = (event: any) => {
        this.db = event.target.result;

        // Create categories store
        if (!this.db.objectStoreNames.contains('categories')) {
          this.db.createObjectStore('categories', { keyPath: 'id' });
        }

        if (!this.db.objectStoreNames.contains('getCurrentBalance')) {
          this.db.createObjectStore('getCurrentBalance', { keyPath: 'id' });
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
          // this.db.createObjectStore('tables', { keyPath: 'id' });
          const tableStore = this.db.createObjectStore('tables', { keyPath: 'id', autoIncrement: true });
          tableStore.createIndex('table_number', 'table_number', { unique: false });
        }

        if (!this.db.objectStoreNames.contains('selectedTable')) {
          this.db.createObjectStore('selectedTable', { keyPath: 'id' });
        }
        if (!this.db.objectStoreNames.contains('selectedOrderType')) {
          this.db.createObjectStore('selectedOrderType', { keyPath: 'id', autoIncrement: true });
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

        if (!this.db.objectStoreNames.contains('countries')) {
          this.db.createObjectStore('countries', { autoIncrement: true });
        }


        // Create form_delivery store
        if (!this.db.objectStoreNames.contains('form_delivery')) {
          this.db.createObjectStore('form_delivery', { keyPath: 'id' });
        }

        if (!this.db.objectStoreNames.contains('availabletables')) {
          this.db.createObjectStore('availabletables', { keyPath: 'id' });
        }

        // Create pendingOperations store for offline operations
        if (!this.db.objectStoreNames.contains('pendingOperations')) {
          const pendingStore = this.db.createObjectStore('pendingOperations', {
            keyPath: 'id',
            autoIncrement: true
          });
          pendingStore.createIndex('type', 'type', { unique: false });
        } else {
          // Ensure index exists if store already exists
          const pendingStore = this.db.transaction('pendingOperations', 'readwrite').objectStore('pendingOperations');
          if (!pendingStore.indexNames.contains('type')) {
            pendingStore.createIndex('type', 'type', { unique: false });
          }
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


  // indexeddb.service.ts
  async getLastFormData(): Promise<any | null> {
    return new Promise((resolve, reject) => {
      const tx = this.db.transaction('formData', 'readonly'); // make sure you have 'form_data' store
      const store = tx.objectStore('formData');
      const request = store.getAll();

      request.onsuccess = () => {
        const all = request.result || [];
        if (all.length > 0) {
          resolve(all[all.length - 1]); // ✅ last item
        } else {
          resolve(null);
        }
      };

      request.onerror = () => reject(request.error);
    });
  }


  async getLastSelectedOrdertype(): Promise<any | null> {
    return new Promise((resolve, reject) => {
      const tx = this.db.transaction('selectedOrderType', 'readonly'); // make sure you have 'form_data' store
      const store = tx.objectStore('selectedOrderType');
      const request = store.getAll();

      request.onsuccess = () => {
        const all = request.result || [];
        if (all.length > 0) {
          resolve(all[all.length - 1]); // ✅ last item
        } else {
          resolve(null);
        }
      };

      request.onerror = () => reject(request.error);
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
  // Update cart item (for quantity changes)
  updateCartItem(cartItem: any): Promise<void> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('cart', 'readwrite');
        const store = tx.objectStore('cart');

        // Update lastUpdated timestamp
        cartItem.lastUpdated = new Date().toISOString();

        const request = store.put(cartItem);

        request.onsuccess = () => resolve();
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

  // Clear pills store
  clearPills(): Promise<void> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('pills', 'readwrite');
        const store = tx.objectStore('pills');
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

  // Get orders from IndexedDB - Optimized for performance
  getOrders(): Promise<any[]> {
    // Skip ensureInit if already initialized for faster access
    if (this.isInitialized && this.db) {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('orders', 'readonly');
        const store = tx.objectStore('orders');
        const request = store.getAll();

        request.onsuccess = () => {
          const result = request.result;
          // Only reverse if we have items (optimization)
          if (result && result.length > 0) {
            resolve(result.reverse());
          } else {
            resolve([]);
          }
        };
        request.onerror = (e) => reject(e);
      });
    }

    // Fallback to ensureInit if not initialized
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('orders', 'readonly');
        const store = tx.objectStore('orders');
        const request = store.getAll();

        request.onsuccess = () => {
          const result = request.result;
          if (result && result.length > 0) {
            resolve(result.reverse());
          } else {
            resolve([]);
          }
        };
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
      const tx = this.db.transaction([storeName], 'readonly');
      const store = tx.objectStore(storeName);
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


  getOrderById(orderIdOrRunId: number | string): Promise<any> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('orders', 'readonly');
        const store = tx.objectStore('orders');

        // حاول تفسر القيمة كرقم (لو هي string)
        const numericId = typeof orderIdOrRunId === 'string' ? parseInt(orderIdOrRunId, 10) : orderIdOrRunId;

        const request = store.get(numericId);

        request.onsuccess = () => {
          if (request.result) {
            // ✅ لقينا order بالـ id
            resolve(request.result);
          } else {
            // ❌ مش موجود → نجرب ندور بالـ runId
            const allOrders = store.getAll();
            allOrders.onsuccess = () => {
              const found = allOrders.result.find((o: any) => o.runId == orderIdOrRunId);
              resolve(found || null);
            };
            allOrders.onerror = (e) => reject(e);
          }
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

    // console.log("offline-invoiceId", invoiceId);
    return new Promise((resolve, reject) => {
      const tx = this.db!.transaction('pills', 'readonly');
      const store = tx.objectStore('pills');
      const index = store.index('invoice_id'); // ✅ use index
      const request = index.get(Number(invoiceId));

      request.onsuccess = () => resolve(request.result ?? null);
      request.onerror = (e) => reject(e);
    });
  }

  // indexeddb.service.ts
  async getAreaById(areaId: number): Promise<any | null> {
    return new Promise((resolve, reject) => {
      const tx = this.db.transaction('areas', 'readonly');
      const store = tx.objectStore('areas');
      const request = store.get(areaId);

      request.onsuccess = () => {
        console.log("🔍 getAreaById result:", request.result);
        resolve(request.result || null);
      };

      request.onerror = () => {
        console.error("❌ getAreaById error:", request.error);
        reject(request.error);
      };
    });
  }


  async savePendingOrder(orderData: any): Promise<void> {
    try {
      console.log("🟢 Saving pending order:", orderData);
      await this.ensureInit();

      const formData = orderData.delivery_info;
      let delivery_fees = orderData.delivery_fees || 0;

      let tranaction: Array<{
        date: string;
        is_refund: number;
        paid: number;
        payment_method: string;
        payment_status: string;
        refund: number;
      }> | undefined;
      // 🟢 Get delivery fees from area if available
      // if (formData) {
      //   //  console.log("dd");
      //   // const area = await this.getAreaById(Number(formData.area_id));
        // delivery_fees = orderData.delivery_fees || 0;
      // }
      if (orderData.payment_method_menu_integration == "cash + credit") {
        tranaction = [{
          date: new Date().toISOString().split("T")[0],
          is_refund: 0,
          paid: orderData.cash_amount,
          payment_method: "cash",
          payment_status: orderData.payment_status || "unpaid",
          refund: 0,
        },
        {
          date: new Date().toISOString().split("T")[0],
          is_refund: 0,
          paid: orderData.credit_amount,
          payment_method: "credit",
          payment_status: orderData.payment_status || "unpaid",
          refund: 0,

        }]

      }

      return new Promise((resolve, reject) => {

        const tx = this.db.transaction(["orders", "pills"], "readwrite");

        const ordersStore = tx.objectStore("orders");

        const pillsStore = tx.objectStore("pills");

        // 🆔 Generate unique order ID
        const orderId = orderData.orderId || Date.now();

        // 🧮 Calculate total item count
        const count_item =
          orderData.items?.reduce(
            (sum: number, item: any) => sum + (item.quantity ?? 0),
            0
          ) ?? 0;


        // 🧾 Build order summary once and reuse it
        const buildOrderSummary = () => {
          const subtotal_price_before_coupon = orderData.type != "talabat" ? orderData.items.reduce(
            (sum: number, item: any) => sum + item.dish_price * item.quantity,
            0
          ) : orderData.items.reduce(
            (sum: number, item: any) => sum + item.dish_price,
            0
          );


          const coupon_value = orderData.coupon_value || 0;
          const subtotal_price = subtotal_price_before_coupon - coupon_value;

          let service_percentage = 0;
          let service_fees = 0;
          let delivery_fees_value = 0;

          if (orderData.type === "dine-in") {
            service_percentage = 12;
            service_fees = (subtotal_price * service_percentage) / 100;
          } else if (orderData.type == "Delivery") {
            delivery_fees_value = Number(delivery_fees) || 0;
          }

          const tax_percentage = 14;
          const tax_value =
            ((subtotal_price + service_fees) * tax_percentage) / 100;

          // const total_price =
          //   subtotal_price + service_fees + tax_value + Number(delivery_fees);

          const total_price = orderData.bill_amount;
          return {
            coupon_code: orderData.coupon_code || null,
            coupon_id: orderData.coupon_id || null,
            coupon_title: orderData.coupon_title || null,
            coupon_type: orderData.coupon_type || "fixed",
            coupon_value,
            delivery_fees: delivery_fees_value,
            order_notes: orderData.note || "",
            order_number: orderId,
            service_fees,
            service_percentage,
            subtotal_price,
            subtotal_price_before_coupon,
            tax_application: true,
            tax_apply: true,
            tax_percentage,
            tax_value,
            total_price,

          };
        };




        const summary = buildOrderSummary();
        const currency_symbol = orderData.items[0]?.currency_symbol || "ج.م";

        // جلب وتنسيق بيانات الفرع
        const rawBranchData = JSON.parse(localStorage.getItem("branchData") || "{}");
        const branchData = {
          branch_name: rawBranchData.name_ar || rawBranchData.name || "الفرع الرئيسي",
          branch_phone: "01242542154", // أو من مصدر آخر
          branch_address: rawBranchData.address_ar || rawBranchData.address || "العنوان الرئيسي",
          floor_name: "الطابق الأرضي"
        };

        console.log("orderData.payment_method", orderData.payment_method);



        // 🟢 Order object
        const orderWithMetadata: any = {
          formdata_delivery: formData,
          formdata_delivery_area_id: formData ? formData.area_id : null,
          delivery_fees_amount: delivery_fees,
          edit_invoice: false,

          order_details: {
            order_id: orderData.order_id ?? orderId, // ⚠️ REQUIRED for IndexedDB keyPath
            order_number: orderId,
            table_number: orderData.table_number || orderData.table_id,
            order_type: orderData.type || "dine-in",
            type: orderData.type || "dine-in",
            hasCoupon: !!orderData.coupon_code,
            client_name: orderData.client_name || "",
            client_phone: orderData.client_phone || "",
            status: "pending",
            order_items_count: count_item,
            cashier_machine_id: orderData.cashier_machine_id,
            branch_id: orderData.branch_id || null,
            address_id: orderData.address_id || null,
            payment_method: orderData.payment_method || "cash",
            payment_status: orderData.payment_status || "unpaid",
            cash_amount: orderData.cash_amount || 0,
            credit_amount: orderData.credit_amount || 0,
            note: orderData.note || "",
            created_at: new Date().toISOString(),
            updated_at: new Date().toISOString(),
          },



          details_order: {
            currency_symbol,
            order_type: orderData.type || "dine-in",
            type: orderData.type || "dine-in",
            status: "pending",
            transactions: tranaction || [
              {
                date: new Date().toISOString().split("T")[0],
                is_refund: 0,
                paid: summary.total_price,
                payment_method: orderData.payment_method || "cash",
                payment_status: orderData.payment_status || "unpaid",
                refund: 0,
              },
            ],
            order_details: orderData.items.map((item: any, idx: number) => ({
              // addons: item.addon_categories,
              order_detail_id: idx + 1,
              dish_id: item.dish_id,
              dish_name: item.dish_name,
              size: item.sizeName || null,
              quantity: item.quantity,
              note: item.note || "",
              addons: item.selectedAddons
                ? item.selectedAddons.map((addon: any) => addon.name)
                : [],
              coupon_id: item.coupon_id || null,
              coupon_title: item.coupon_title || null,
              coupon_value: item.coupon_value || 0,
              total_dish_price: item.dish_price * item.quantity,
              total_dish_price_coupon_applied:
                item.dish_price * item.quantity - (item.coupon_value || 0),
            })),
            order_summary: summary,
          },

          order_items: orderData.items.map((item: any) => ({
            addon_categories: item.addon_categories,
            currency_symbol,
            dish_id: item.dish_id,
            dish_name: item.dish_name,
            dish_price: item.dish_price,
            quantity: item.quantity,
            final_price: item.finalPrice,
            note: item.note || "",
            addons: item.selectedAddons || [],
            sizeId: item.sizeId,
            size: item.sizeName || "",
            size_name: item.sizeName || "",
            total_dish_price: item.finalPrice == 0 ? item.dish_price * item.quantity : item.finalPrice,
            dish_status: "pending",
          })),

          total_price: summary.total_price,
          currency_symbol,

          // 💰 Tips Section
          change_amount: orderData.change_amount || 0,
          tips_aption: orderData.tips_aption ?? "no_tip",
          tip_amount: orderData.tip_amount ?? 0,
          tip_specific_amount: orderData.tip_specific_amount ?? 0,
          payment_amount: orderData.payment_amount ?? 0,
          bill_amount: orderData.bill_amount ?? 0,
          total_with_tip: orderData.total_with_tip ?? 0,
          returned_amount: orderData.returned_amount ?? 0,
          menu_integration: orderData.type === 'talabat' ? true : false,
          payment_status_menu_integration: orderData.payment_status,
          payment_method_menu_integration: orderData.payment_method,

          // ⚙️ Metadata
          isOffline: true,
          isSynced: false,
          status: "pending",
          savedAt: new Date().toISOString(),
          createdAt: new Date().toISOString(),
        };
        console.log("orderWithMetadata", orderWithMetadata);
        // 🧾 Save to IndexedDB (orders)
        const orderRequest = ordersStore.put(orderWithMetadata);

        orderRequest.onsuccess = () => {
          console.log("✅ Order saved to IndexedDB with ID:", orderId);

          // 🧾 Save invoice in pills store
          const newInvoice = {
            id: `temp_${Date.now()}_${Math.random()}`,
            invoice_id: orderId,
            order_id: orderId,
            order_number: orderId,
            invoice_number: `INV-OFF-${orderId}`,
            invoice_type: "invoice",
            order_items_count: count_item,
            invoice_print_status: "hold",
            order_type: orderData.type || "dine-in",
            type: orderData.type || "dine-in",
            order_status: "pending",
            // tracking-status: "pending",
            order_time: 30,
            payment_status: orderData.payment_status || "unpaid",
            currency_symbol,
            print_count: 0,
            table_number: orderData.table_number,

            invoice_details: [
              {
                address_details: formData || null,
                currency_symbol,
                delivery_name: null,

                branch_details: {
                  branch_id: orderData.branch_id || null,
                  branch_name: branchData.branch_name,
                  branch_phone: branchData.branch_phone,
                  branch_address: branchData.branch_address,
                  floor_name: branchData.floor_name,
                  floor_partition_name:
                    orderData.floor_partition_name || "test",
                  invoice_number: `INV-OFF-${orderId}`,
                  order_number: orderId,
                  table_id: orderData.table_id || null,
                  table_number :orderData.table_number,
                  created_at: new Date().toISOString(),
                },

                cashier_info: {
                  first_name: orderData.cashier_first_name || "test",
                  last_name: orderData.cashier_last_name || "test",
                  email: orderData.cashier_email || "test",
                  phone_number: orderData.cashier_phone || "test",
                  employee_code: orderData.cashier_code || "test",
                },

                invoice_summary: summary,
                is_refund: false,

                orderDetails: orderData.items.map((item: any, idx: number) => ({
                  order_detail_id: idx + 1,
                  dish_id: item.dish_id,
                  dish_name: item.dish_name,
                  size: item.size || null,
                  quantity: item.quantity,
                  note: item.note || "",
                  addons: item.selectedAddons || [],
                  coupon_id: item.coupon_id || null,
                  coupon_title: item.coupon_title || null,
                  coupon_value: item.coupon_value || 0,
                  total_dish_price: item.dish_price * item.quantity,
                  total_dish_price_coupon_applied:
                    item.dish_price * item.quantity - (item.coupon_value || 0),
                })),

                order_status: "pending",
                order_type: orderData.type || "dine-in",
                type: orderData.type || "dine-in",
                transactions: [
                  {
                    date: new Date().toISOString().split("T")[0],
                    is_refund: 0,
                    paid: summary.total_price,
                    payment_method: orderData.payment_method || "cash",
                    payment_status: orderData.payment_status || "unpaid",
                    refund: 0,
                  },
                ],
              },
            ],

            invoice_tips: [
              {
                payment_method:orderData.payment_method || "cash",
                change_amount: orderData.change_amount || 0,
                tips_aption: orderData.tips_aption ?? "tip_the_change",
                tip_amount: orderData.tip_amount ?? 0,
                tip_specific_amount: orderData.tip_specific_amount ?? 0,
                payment_amount: orderData.payment_amount ?? 0,
                bill_amount: orderData.bill_amount ?? 0,
                total_with_tip: orderData.total_with_tip ?? 0,
                returned_amount: orderData.returned_amount ?? 0,
              },
            ],

            isOffline: true,
            isSynced: false,
            status: "pending",
            saved_at: new Date().toISOString(),
            created_at: new Date().toISOString(),
          };

          const pillRequest = pillsStore.put(newInvoice);

          pillRequest.onsuccess = () => {
            console.log("✅ Invoice saved in pills store:", newInvoice);
            resolve();
          };

          pillRequest.onerror = (err) => {
            console.error("❌ Error saving invoice:", err);
            reject(err);
          };
        };

        orderRequest.onerror = (e) => {
          console.error("❌ Error saving order to IndexedDB:", e);
          reject(e);
        };
      });
    } catch (error) {
      console.error("❌ Error in savePendingOrder:", error);
      throw error;
    }
  }




  // async savePendingOrder(orderData: any): Promise<void> {
  //   try {
  //     console.log("dorder_offline", orderData);
  //     await this.ensureInit();
  //     const formData = await this.getLastFormData();


  //     let delivery_fees = 0;
  //     if (formData) {
  //       console.log("formData.area_id", formData.area_id);
  //       const area = await this.getAreaById(Number(formData.area_id));
  //       delivery_fees = area ? parseFloat(area.delivery_fees) : 0;
  //     }

  //     return new Promise((resolve, reject) => {
  //       const tx = this.db.transaction("orders", "readwrite");
  //       const store = tx.objectStore("orders");

  //       // Generate a unique ID if orderId is null
  //       const orderId = orderData.orderId || Date.now();

  //       // 🟢 Order Summary Function
  //       const buildOrderSummary = () => {
  //         const subtotal_price_before_coupon = orderData.items.reduce(
  //           (sum: number, item: any) =>
  //             sum + (item.finalPrice * item.quantity),
  //           0
  //         );

  //         const coupon_value = orderData.coupon_value || 0;
  //         const subtotal_price = subtotal_price_before_coupon - coupon_value;

  //         let service_percentage = 0;
  //         let service_fees = 0;
  //         let delivery_fees_value = 0;

  //         // تحديد الرسوم حسب نوع الطلب
  //         if (orderData.type === "dine-in") {
  //           service_percentage = 12;
  //           service_fees = (subtotal_price * service_percentage) / 100;
  //         } else if (orderData.type === "delivery") {
  //           delivery_fees_value = Number(delivery_fees) || 0;
  //         }

  //         // ضريبة ثابتة 14%
  //         const tax_percentage = 14;
  //         const tax_value = ((subtotal_price + service_fees) * tax_percentage) / 100;

  //         const total_price =
  //           subtotal_price + service_fees + tax_value + Number(delivery_fees);

  //         return {
  //           coupon_code: null,
  //           coupon_id: orderData.coupon_id || null,
  //           coupon_title: orderData.coupon_title || null,
  //           coupon_type: orderData.coupon_type || "fixed",
  //           coupon_value: 0,
  //           delivery_fees: delivery_fees_value,
  //           order_notes: orderData.note || "",
  //           order_number: Date.now(),
  //           service_fees,
  //           service_percentage,
  //           subtotal_price,
  //           subtotal_price_before_coupon,
  //           tax_application: true,
  //           tax_apply: true,
  //           tax_percentage,
  //           tax_value,
  //           total_price,
  //         };
  //       };

  //       // Build order object
  //       const orderWithMetadata: any = {
  //         formdata_delivery: formData,
  //         formdata_delivery_area_id: formData ? formData.area_id : null,
  //         delivery_fees_amount: delivery_fees,

  //         // Main order details
  //         order_details: {
  //           order_id: orderId,
  //           order_type: orderData.type || "dine-in",
  //           hasCoupon: !!orderData.coupon_code,
  //           client_name: orderData.client_name || "",
  //           client_phone: orderData.client_phone || "",
  //           status: "pending",
  //           cashier_machine_id: orderData.cashier_machine_id,
  //           order_number: orderId,
  //           branch_id: orderData.branch_id || null,
  //           table_id: orderData.table_id || null,
  //           address_id: orderData.address_id || null,
  //           payment_method: orderData.payment_method || "cash",
  //           payment_status: orderData.payment_status || "unpaid",
  //           cash_amount: orderData.cash_amount || 0,
  //           credit_amount: orderData.credit_amount || 0,
  //           note: orderData.note || "",
  //           created_at: new Date().toISOString(),
  //           updated_at: new Date().toISOString(),
  //         },

  //         // details_order (API-like structure)
  //         details_order: {
  //           currency_symbol: orderData.items[0]?.currency_symbol || "ج.م",
  //           order_type: orderData.type || "dine-in",
  //           status: "pending",

  //           // Transactions
  //           transactions: [
  //             {
  //               date: new Date().toISOString().split("T")[0],
  //               is_refund: 0,
  //               paid: buildOrderSummary().total_price || 0,
  //               payment_method: orderData.payment_method || "cash",
  //               payment_status: orderData.payment_status || "unpaid",
  //               refund: 0,
  //             },
  //           ],

  //           // Order details (line items)
  //           order_details: orderData.items.map((item: any, idx: number) => ({
  //             order_detail_id: idx + 1, // temporary offline ID
  //             dish_id: item.dish_id,
  //             dish_name: item.dish_name,
  //             size: item.size || null,
  //             quantity: item.quantity,
  //             note: item.note || "",
  //             addons: item.selectedAddons || [],
  //             coupon_id: item.coupon_id || null,
  //             coupon_title: item.coupon_title || null,
  //             coupon_value: item.coupon_value || 0,
  //             total_dish_price: item.finalPrice * item.quantity,
  //             total_dish_price_coupon_applied:
  //               item.finalPrice * item.quantity - (item.coupon_value || 0),
  //           })),

  //           // Order summary
  //           order_summary: buildOrderSummary(),
  //         },

  //         // Flattened order items
  //         order_items: orderData.items.map((item: any) => ({
  //           addon_categories: item.addon_categories,
  //           currency_symbol: item.currency_symbol,
  //           dish_id: item.dish_id,
  //           dish_name: item.dish_name,
  //           dish_price: item.dish_price,
  //           quantity: item.quantity,
  //           final_price: item.finalPrice,
  //           note: item.note || "",
  //           addons: item.selectedAddons || [],
  //           sizeId: item.sizeId,
  //           size: item.size || "",
  //           size_name: item.sizeName || "",
  //           total_dish_price: item.dish_price,
  //           dish_status: "pending",
  //         })),

  //         // Summary info
  //         total_price: buildOrderSummary().total_price, // ✅ من order_summary
  //         currency_symbol: orderData.items[0]?.currency_symbol || "ج.م",

  //             // dalia start tips
  //             // tip_amount: this.tipAmount || 0,
  //             change_amount: orderData.change_amount || 0,
  //             // tips_aption : this.selectedTipType ?? "tip_the_change" ,                  //'tip_the_change', 'tip_specific_amount','no_tip'
  //             tips_aption : orderData.tips_aption ?? "tip_the_change" ,                  //'tip_the_change', 'tip_specific_amount','no_tip'

  //             tip_amount:orderData.tip_amount ?? 0,
  //             tip_specific_amount:orderData.tip_specific_amount ?? 0,
  //             payment_amount :orderData.payment_amount ?? 0,
  //             bill_amount :  orderData.bill_amount ?? 0,
  //             total_with_tip: orderData.total_with_tip ?? 0,
  //             returned_amount:orderData.returned_amount ?? 0,
  //               // dalia end tips


  //         // Metadata
  //         isOffline: true,
  //         isSynced: false,
  //         status: "pending",
  //         savedAt: new Date().toISOString(),
  //         createdAt: new Date().toISOString(),
  //       };

  //       console.log("Saving to IndexedDB:", orderWithMetadata);

  //       const request = store.put(orderWithMetadata);

  //       // request.onsuccess = () => {
  //       //   console.log("Successfully saved to IndexedDB with ID:", orderId);
  //       //   resolve();
  //       // };

  //       request.onsuccess = () => {
  //         console.log("✅ Order saved to IndexedDB with ID:", orderId);
  //         const branchData = JSON.parse(localStorage.getItem("branchData") || "{}");


  //         // ✅ Save Invoice (Pill) in pills store
  //         const pillsTx = this.db.transaction("pills", "readwrite");
  //         const pillsStore = pillsTx.objectStore("pills");


  //         const newInvoice = {
  //           id: `temp_${Date.now()}_${Math.random()}`,
  //           invoice_id: orderId,
  //           order_id: orderId,
  //           order_number: orderId,
  //           invoice_number: `INV-OFF-${orderId}`,
  //           invoice_type: "invoice",
  //           order_items_count: orderData.items.length,
  //           invoice_print_status: "hold",
  //           order_type: orderData.type || "dine-in",
  //           order_status: "pending",
  //           order_time: 30,
  //           payment_status: orderData.payment_status || "unpaid",
  //           currency_symbol: orderData.items[0]?.currency_symbol || "ج.م",
  //           print_count: 0,
  //           table_number: orderData.table_id || null,
  //           // ✅ خليها Array زي الـ API
  //           invoice_details: [
  //             {
  //               address_details: formData || null,
  //               currency_symbol: orderData.items[0]?.currency_symbol || "ج.م",
  //               delivery_name: null,

  //               branch_details: {
  //                 branch_id: orderData.branch_id || null,
  //                 branch_name: branchData.branch_name || "test",
  //                 branch_phone: branchData.branch_phone || "test",
  //                 branch_address: branchData.branch_address || "test",
  //                 floor_name: branchData.floor_name || "test",
  //                 floor_partition_name: orderData.floor_partition_name || "test",
  //                 invoice_number: `INV-OFF-${orderId}`,
  //                 order_number: orderId,
  //                 table_id: orderData.table_id || null,
  //                 created_at: new Date().toISOString(),
  //               },

  //               cashier_info: {
  //                 first_name: orderData.cashier_first_name || "test",
  //                 last_name: orderData.cashier_last_name || "test",
  //                 email: orderData.cashier_email || "test",
  //                 phone_number: orderData.cashier_phone || "test",
  //                 employee_code: orderData.cashier_code || "test",
  //               },

  //               invoice_summary: buildOrderSummary(),
  //               is_refund: false,

  //               orderDetails: orderData.items.map((item: any, idx: number) => ({
  //                 order_detail_id: idx + 1, // temporary offline ID
  //                 dish_id: item.dish_id,
  //                 dish_name: item.dish_name,
  //                 size: item.size || null,
  //                 quantity: item.quantity,
  //                 note: item.note || "",
  //                 addons: item.selectedAddons || [],
  //                 coupon_id: item.coupon_id || null,
  //                 coupon_title: item.coupon_title || null,
  //                 coupon_value: item.coupon_value || 0,
  //                 total_dish_price: item.finalPrice * item.quantity,
  //                 total_dish_price_coupon_applied:
  //                   item.finalPrice * item.quantity - (item.coupon_value || 0),
  //               })),

  //               order_status: "pending",
  //               order_type: orderData.type || "dine-in",
  //               original_invoice_id: null,
  //               original_invoice_number: null,
  //               print_count: 0,
  //               return_type: null,

  //               transactions: [
  //                 {
  //                   date: new Date().toISOString().split("T")[0],
  //                   is_refund: 0,
  //                   paid: buildOrderSummary().total_price || 0,
  //                   payment_method: orderData.payment_method || "cash",
  //                   payment_status: orderData.payment_status || "unpaid",
  //                   refund: 0,
  //                 },
  //               ],
  //             },
  //           ],

  //           invoice_tips : [
  //           {
  //             change_amount: orderData.change_amount|| 0,
  //             tips_aption: orderData.tips_aption ?? "tip_the_change", // 'tip_the_change', 'tip_specific_amount', 'no_tip'
  //             tip_amount: orderData.tip_amount ?? 0,
  //             tip_specific_amount:orderData.tip_specific_amount ?? 0,
  //             payment_amount: orderData.payment_amount ?? 0,
  //             bill_amount: orderData.bill_amount ?? 0,
  //             total_with_tip: orderData.total_with_tip ?? 0,
  //             returned_amount: orderData.returned_amount ?? 0 ,
  //           },
  //         ],


  //           isOffline: true,
  //           isSynced: false,
  //           status: "pending",
  //           saved_at: new Date().toISOString(),
  //           created_at: new Date().toISOString(),
  //         };

  //         const pillRequest = pillsStore.put(newInvoice);

  //         pillRequest.onsuccess = () => {
  //           console.log("✅ Invoice saved in pills store:", newInvoice);
  //           resolve();
  //         };

  //         pillRequest.onerror = (err) => {
  //           console.error("❌ Error saving invoice:", err);
  //           reject(err);
  //         };
  //       };


  //       request.onerror = (e) => {
  //         console.error("Error saving to IndexedDB:", e);
  //         reject(e);
  //       };
  //     });
  //   } catch (error) {
  //     console.error("Error in savePendingOrder:", error);
  //     throw error;
  //   }
  // }

  // Generic update function for any store
  async updateGeneric(storeName: string, id: number | string, updates: any): Promise<any> {
    await this.ensureInit();

    return new Promise((resolve, reject) => {
      const tx = this.db.transaction(storeName, "readwrite");
      const store = tx.objectStore(storeName);

      const request = store.get(id);

      request.onsuccess = (event: any) => {
        const existingRecord = event.target.result;

        if (!existingRecord) {
          console.warn(`⚠️ Record with ID ${id} not found in ${storeName}`);
          return reject(`Record ${id} not found`);
        }

        // 📝 اعمل merge بين القديم والجديد
        const updatedRecord = {
          ...existingRecord,
          ...updates,
          isUpdatedOffline: true,
          isSynced: false,
          updatedAt: new Date().toISOString(),
        };

        const updateRequest = store.put(updatedRecord);

        updateRequest.onsuccess = () => {
          console.log(`✅ Record updated in ${storeName}:`, updatedRecord);
          resolve(updatedRecord);
        };

        updateRequest.onerror = (err) => {
          console.error(`❌ Error updating record in ${storeName}:`, err);
          reject(err);
        };
      };

      request.onerror = (err) => {
        console.error(`❌ Error reading record in ${storeName}:`, err);
        reject(err);
      };
    });
  }



  // 🔹 Save or update selected table
  async saveOrUpdateSelectedTable(table: any): Promise<void> {
    await this.ensureInit();

    return new Promise((resolve, reject) => {
      const tx = this.db.transaction('selectedTable', 'readwrite');
      const store = tx.objectStore('selectedTable');

      // clear old selected table (only one should exist)
      const clearRequest = store.clear();

      clearRequest.onsuccess = () => {
        const request = store.put({
          ...table,
          savedAt: new Date().toISOString()
        });

        request.onsuccess = () => {
          console.log('✅ Selected table updated:', table);
          resolve();
        };

        request.onerror = (e) => {
          console.error('❌ Error saving selected table:', e);
          reject(e);
        };
      };

      clearRequest.onerror = (e) => reject(e);
    });
  }

  // 🔹 Get the currently selected table
  async getSelectedTable(): Promise<any | null> {
    await this.ensureInit();

    return new Promise((resolve, reject) => {
      const tx = this.db.transaction('selectedTable', 'readonly');
      const store = tx.objectStore('selectedTable');
      const request = store.getAll();

      request.onsuccess = () => {
        resolve(request.result && request.result.length > 0 ? request.result[0] : null);
      };

      request.onerror = (e) => reject(e);
    });
  }


  // Update a single table by id
  // async updateTableStatus(tableId: number, newStatus: number): Promise<void> {
  //   await this.ensureInit();

  //   return new Promise((resolve, reject) => {
  //     const tx = this.db.transaction('tables', 'readwrite');
  //     const store = tx.objectStore('tables');

  //     const getReq = store.get(tableId);
  //     getReq.onsuccess = () => {
  //       const table = getReq.result;
  //       if (!table) {
  //         reject(`Table ${tableId} not found`);
  //         return;
  //       }

  //       table.status = newStatus;
  //       const putReq = store.put(table);

  //       putReq.onsuccess = () => resolve();
  //       putReq.onerror = (e) => reject(e);
  //     };
  //     getReq.onerror = (e) => reject(e);
  //   });
  // }

  async updateTableStatus(identifier: number, newStatus: number): Promise<void> {
    await this.ensureInit();

    return new Promise((resolve, reject) => {
      const tx = this.db.transaction('tables', 'readwrite');
      const store = tx.objectStore('tables');

      // حاول الأول بالـ id
      const getById = store.get(identifier);

      getById.onsuccess = () => {
        let table = getById.result;

        if (table) {
          // ✅ لو لقاها بالـ id
          table.status = newStatus;
          const putReq = store.put(table);
          putReq.onsuccess = () => resolve();
          putReq.onerror = (e) => reject(e);
        } else {
          // ❌ مش لقاها بالـ id → نجرب بالـ table_number
          const index = store.index('table_number');
          const getByTableNumber = index.get(identifier);

          getByTableNumber.onsuccess = () => {
            const tableByNumber = getByTableNumber.result;
            if (!tableByNumber) {
              reject(`Table with ID or table_number ${identifier} not found`);
              return;
            }

            tableByNumber.status = newStatus;
            const putReq = store.put(tableByNumber);
            putReq.onsuccess = () => resolve();
            putReq.onerror = (e) => reject(e);
          };

          getByTableNumber.onerror = (e) => reject(e);
        }
      };

      getById.onerror = (e) => reject(e);
    });
  }




  deleteFromIndexedDB(storeName: string, key?: any): void {
    const dbName = 'MyDB'; // replace with your DB name
    const request = indexedDB.open(dbName);

    request.onsuccess = (event: any) => {
      const db = event.target.result;
      const tx = db.transaction(storeName, 'readwrite');
      const store = tx.objectStore(storeName);

      if (key !== undefined) {
        // ✅ Delete specific record by key
        store.delete(key);
        console.log(`Deleted record with key "${key}" from "${storeName}"`);
      } else {
        // ✅ Clear all records in store
        store.clear();
        console.log(`Cleared all data from store "${storeName}"`);
      }

      tx.oncomplete = () => {
        db.close();
      };
    };

    request.onerror = () => {
      console.error(`Error opening DB "${dbName}"`);
    };
  }
  //   async updatePill(updatedPill: any): Promise<void> {
  //   await this.ensureInit();
  //   return new Promise(async (resolve, reject) => {
  //     try {
  //       const tx = this.db!.transaction("pills", "readwrite");
  //       const store = tx.objectStore("pills");

  //       // ✅ put يضيف جديد أو يحدث الموجود تلقائي
  //       const request = store.put(updatedPill);

  //       request.onsuccess = () => {
  //         // console.log("✅ Pill added/updated in IndexedDB:", updatedPill);
  //         resolve();
  //       };

  //       request.onerror = (err) => {
  //         console.error("❌ Error updating/adding pill:", err);
  //         reject(err);
  //       };
  //     } catch (error) {
  //       reject(error);
  //     }
  //   });
  // }



  // async updatePill(updatedPill: any): Promise<void> {
  //   await this.ensureInit()
  //   return new Promise(async (resolve, reject) => {
  //     try {

  //       const tx = this.db.transaction("pills", "readwrite");
  //       const store = tx.objectStore("pills");

  //       const request = store.put(updatedPill);

  //       request.onsuccess = () => {
  //         console.log("✅ Pill updated in IndexedDB:", updatedPill);
  //         resolve();
  //       };

  //       request.onerror = (err) => {
  //         console.error("❌ Error updating pill:", err);
  //         reject(err);
  //       };
  //     } catch (error) {
  //       reject(error);
  //     }
  //   });
  // }

  // db.service.ts
  async getOfflineUpdatedPills(): Promise<any[]> {
    await this.ensureInit();
    return new Promise((resolve, reject) => {
      const tx = this.db!.transaction("pills", "readonly");
      const store = tx.objectStore("pills");
      const request = store.getAll();

      request.onsuccess = () => {
        const offlinePills = request.result.filter((pill: any) => pill.isUpdatedOffline === true);
        resolve(offlinePills);
      };
      request.onerror = (e) => reject(e);
    });
  }


  async updatePill(updatedPill: any): Promise<void> {
    await this.ensureInit();
    return new Promise((resolve, reject) => {
      try {
        const tx = this.db!.transaction("pills", "readwrite");
        const store = tx.objectStore("pills");

        // ✅ put: يضيف أو يحدث بناءً على المفتاح الأساسي (id أو invoice_number)
        const request = store.put(updatedPill);

        request.onsuccess = () => {
          // console.log("✅ Pill added/updated in IndexedDB:", updatedPill.invoice_number);
          resolve();
        };

        request.onerror = (err) => {
          console.error("❌ Error updating/adding pill:", err);
          reject(err);
        };
      } catch (error) {
        console.error("❌ Exception while updating pill:", error);
        reject(error);
      }
    });
  }

  async updateOrderById(orderId: number, updatedOrder: any): Promise<void> {
    return new Promise((resolve, reject) => {
      const tx = this.db.transaction("orders", "readwrite");
      const store = tx.objectStore("orders");

      // لازم الـ orderId يكون هو الـ key بتاع الـ object
      updatedOrder.id = orderId;

      const request = store.put(updatedOrder);

      request.onsuccess = () => {
        console.log("✅ Order updated in IndexedDB:", updatedOrder);
        resolve();
      };

      request.onerror = (event) => {
        console.error("❌ Error updating order:", (event.target as any).error);
        reject((event.target as any).error);
      };
    });
  }

  // Save pending invoice update to IndexedDB
  async savePendingInvoiceUpdate(invoiceUpdateData: any): Promise<number> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('pendingOperations', 'readwrite');
        const store = tx.objectStore('pendingOperations');

        const pendingUpdate = {
          ...invoiceUpdateData,
          type_operation: 'invoiceUpdate',
          'edit_invoice': true,
          savedAt: new Date().toISOString(),
          isSynced: false
        };

        const request = store.add(pendingUpdate);

        request.onsuccess = () => {
          console.log('✅ Pending invoice update saved to IndexedDB:', request.result);
          resolve(request.result as number);
        };
        request.onerror = (e) => {
          console.error('❌ Error saving pending invoice update:', e);
          reject(e);
        };
      });
    });
  }

  // Get all pending invoice updates
  async getPendingInvoiceUpdates(): Promise<any[]> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('pendingOperations', 'readonly');
        const store = tx.objectStore('pendingOperations');

        const request = store.getAll();

        request.onsuccess = () => {
          const pendingUpdates = request.result.filter((item: any) =>
            item.type_operation === 'invoiceUpdate' && !item.isSynced
          );
          resolve(pendingUpdates);
        };
        request.onerror = (e) => {
          console.error('❌ Error getting pending invoice updates:', e);
          reject(e);
        };
      });
    });
  }

  // Mark pending invoice update as synced
  async markPendingInvoiceUpdateAsSynced(id: number): Promise<void> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('pendingOperations', 'readwrite');
        const store = tx.objectStore('pendingOperations');
        const request = store.get(id);

        request.onsuccess = () => {
          const item = request.result;
          if (item) {
            item.isSynced = true;
            store.put(item);
          }
          resolve();
        };
        request.onerror = (e) => {
          console.error('❌ Error marking pending invoice update as synced:', e);
          reject(e);
        };
      });
    });
  }

  // Delete synced pending invoice update
  async deleteSyncedPendingInvoiceUpdate(id: number): Promise<void> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('pendingOperations', 'readwrite');
        const store = tx.objectStore('pendingOperations');
        const request = store.delete(id);

        request.onsuccess = () => resolve();
        request.onerror = (e) => {
          console.error('❌ Error deleting synced pending invoice update:', e);
          reject(e);
        };
      });
    });
  }

  // Save pending order (raw orderData for API sync)
  async savePendingOrderForSync(orderData: any): Promise<number> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('pendingOperations', 'readwrite');
        const store = tx.objectStore('pendingOperations');

        // Get order_id from orderData (could be order_id, orderId, or id)
        const orderId = orderData.order_id || orderData.orderId || orderData.id;

        // First, search for existing order with same order_id
        const getAllRequest = store.getAll();

        getAllRequest.onsuccess = () => {
          const allPending = getAllRequest.result;

          // Find existing order with matching order_id and type_operation
          // Check multiple possible locations for order_id
          const existingOrder = allPending.find((item: any) => {
            if (item.type_operation !== 'orderPlacement') return false;

            // Check various possible order_id fields
            const itemOrderId = item.order_id || item.orderId || item.id;
            return itemOrderId === orderId ||
              itemOrderId === String(orderId) ||
              String(itemOrderId) === String(orderId);
          });

          let pendingOrder: any;
          let request: IDBRequest;

          if (existingOrder) {
            // Update existing order - merge with existing data to preserve all fields
            pendingOrder = {
              ...existingOrder, // Keep ALL existing data (items, branch_id, bill_amount, etc.)
              id: existingOrder.id, // Keep the existing id
              type_operation: 'orderPlacement', // Ensure type is correct
              savedAt: existingOrder.savedAt, // Keep original savedAt
              isSynced: false // Reset sync status
            };

            // Update order_id/orderId if provided (but don't overwrite if they exist)
            if (orderData.order_id) pendingOrder.order_id = orderData.order_id;
            if (orderData.orderId) pendingOrder.orderId = orderData.orderId;

            // If tip data is provided, update tip-related fields
            if (orderData.tip && typeof orderData.tip === 'object') {
              pendingOrder.tip_amount = orderData.tip.tip_amount !== undefined ? orderData.tip.tip_amount : existingOrder.tip_amount;
              pendingOrder.tip_specific_amount = orderData.tip.tip_specific_amount !== undefined ? orderData.tip.tip_specific_amount : existingOrder.tip_specific_amount;
              pendingOrder.payment_amount = orderData.tip.payment_amount !== undefined ? orderData.tip.payment_amount : existingOrder.payment_amount;
              pendingOrder.bill_amount = orderData.tip.bill_amount !== undefined ? orderData.tip.bill_amount : existingOrder.bill_amount;
              pendingOrder.total_with_tip = orderData.tip.total_with_tip !== undefined ? orderData.tip.total_with_tip : existingOrder.total_with_tip;
              pendingOrder.returned_amount = orderData.tip.returned_amount !== undefined ? orderData.tip.returned_amount : existingOrder.returned_amount;
              pendingOrder.change_amount = orderData.tip.change_amount !== undefined ? orderData.tip.change_amount : existingOrder.change_amount;
              pendingOrder.tips_aption = orderData.tip.tips_aption !== undefined ? orderData.tip.tips_aption : existingOrder.tips_aption;
              // Keep tip object reference if needed
              pendingOrder.tip = orderData.tip;


              pendingOrder.payment_status_menu_integration = "paid";
            }

            // Update payment_status to "paid" when editing invoice
            if (orderData.edit_invoice === true) {
              pendingOrder.payment_status = "paid";
              pendingOrder.cash_amount = orderData.cash_amount !== undefined ? orderData.cash_amount : existingOrder.cash_amount;
              pendingOrder.credit_amount = orderData.credit_amount !== undefined ? orderData.credit_amount : existingOrder.credit_amount;
            }

            // Update edit_invoice if explicitly set
            if (orderData.edit_invoice !== undefined) {
              pendingOrder.edit_invoice = orderData.edit_invoice;
            }

            request = store.put(pendingOrder);
            console.log('🔄 Updating existing pending order with id:', existingOrder.id, 'orderId:', orderId);
          } else {
            // Create new order
            pendingOrder = {
              ...orderData,
              type_operation: 'orderPlacement',
              'edit_invoice': orderData.edit_invoice !== undefined ? orderData.edit_invoice : false,
              savedAt: new Date().toISOString(),
              isSynced: false
            };
            request = store.add(pendingOrder);
            console.log('➕ Creating new pending order for orderId:', orderId);
          }

          request.onsuccess = () => {
            const resultId = existingOrder ? existingOrder.id : (request.result as number);
            console.log('✅ Pending order saved to IndexedDB for sync:', resultId);
            resolve(resultId);
          };

          request.onerror = (e) => {
            console.error('❌ Error saving pending order:', e);
            reject(e);
          };
        };

        getAllRequest.onerror = (e) => {
          console.error('❌ Error getting pending orders:', e);
          reject(e);
        };
      });
    });
  }

  // Get all pending orders
  async getPendingOrders(): Promise<any[]> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('pendingOperations', 'readonly');
        const store = tx.objectStore('pendingOperations');

        const request = store.getAll();

        request.onsuccess = () => {
          const pendingOrders = request.result.filter((item: any) =>
            item.type_operation === 'orderPlacement' && !item.isSynced
          );
          resolve(pendingOrders);
        };
        request.onerror = (e) => {
          console.error('❌ Error getting pending orders:', e);
          reject(e);
        };
      });
    });
  }

  // Mark pending order as synced
  async markPendingOrderAsSynced(id: number): Promise<void> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('pendingOperations', 'readwrite');
        const store = tx.objectStore('pendingOperations');
        const request = store.get(id);

        request.onsuccess = () => {
          const item = request.result;
          if (item) {
            item.isSynced = true;
            store.put(item);
          }
          resolve();
        };
        request.onerror = (e) => {
          console.error('❌ Error marking pending order as synced:', e);
          reject(e);
        };
      });
    });
  }

  // Delete synced pending order
  async deleteSyncedPendingOrder(id: number): Promise<void> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('pendingOperations', 'readwrite');
        const store = tx.objectStore('pendingOperations');
        const request = store.delete(id);

        request.onsuccess = () => resolve();
        request.onerror = (e) => {
          console.error('❌ Error deleting synced pending order:', e);
          reject(e);
        };
      });
    });
  }
  // حفظ بيانات الفورم للتوصيل
  async saveDeliveryFormData(formData: any): Promise<number> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('formData', 'readwrite');
        const store = tx.objectStore('formData');

        const formDataWithMetadata = {
          ...formData,
          type: 'deliveryForm',
          savedAt: new Date().toISOString(),
          isSynced: navigator.onLine
        };

        const request = store.add(formDataWithMetadata);

        request.onsuccess = () => resolve(request.result as number);
        request.onerror = (e) => reject(e);
      });
    });
  }

  // حفظ العناوين المؤقتة
  async savePendingAddress(addressData: any): Promise<number> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('pendingOperations', 'readwrite');
        const store = tx.objectStore('pendingOperations');

        const addressWithMetadata = {
          ...addressData,
          type_operation: 'addressCreation',
          savedAt: new Date().toISOString(),
          isSynced: false
        };

        const request = store.add(addressWithMetadata);

        request.onsuccess = () => {
          console.log('✅ Pending address saved to IndexedDB:', request.result);
          resolve(request.result as number);
        };
        request.onerror = (e) => {
          console.error('❌ Error saving pending address:', e);
          reject(e);
        };
      });
    });
  }

  // جلب العناوين المؤقتة
  async getPendingAddresses(): Promise<any[]> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('pendingOperations', 'readonly');
        const store = tx.objectStore('pendingOperations');
        const request = store.getAll();

        request.onsuccess = () => {
          const pendingAddresses = request.result.filter((item: any) =>
            item.type_operation === 'addressCreation' && !item.isSynced
          );
          resolve(pendingAddresses);
        };
        request.onerror = (e) => {
          console.error('❌ Error getting pending addresses:', e);
          reject(e);
        };
      });
    });
  }

  // حذف عنوان مؤقت بعد المزامنة
  async deletePendingAddress(id: number): Promise<void> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('pendingOperations', 'readwrite');
        const store = tx.objectStore('pendingOperations');
        const request = store.delete(id);

        request.onsuccess = () => resolve();
        request.onerror = (e) => {
          console.error('❌ Error deleting pending address:', e);
          reject(e);
        };
      });
    });
  }

  // مزامنة العناوين المؤقتة
  async syncPendingAddresses(): Promise<void> {
    if (!navigator.onLine) {
      console.log('📴 Offline - skipping address sync');
      return;
    }

    try {
      const pendingAddresses = await this.getPendingAddresses();

      if (pendingAddresses.length === 0) {
        console.log('✅ No pending addresses to sync');
        return;
      }

      console.log(`🔄 Syncing ${pendingAddresses.length} pending address(es)...`);

      for (const address of pendingAddresses) {
        try {
          // هنا يمكنك إضافة استدعاء API لإرسال العنوان
          // await this.addressService.submitAddress(address).toPromise();

          console.log('✅ Successfully synced address:', address);

          // حذف من IndexedDB بعد المزامنة الناجحة
          await this.deletePendingAddress(address.id);

        } catch (error) {
          console.error('❌ Error syncing address:', error);
          // نستمر مع العناوين الأخرى حتى لو فشل أحدها
        }
      }

      console.log('✅ Finished syncing all pending addresses');

    } catch (error) {
      console.error('❌ Error in syncPendingAddresses:', error);
    }
  }

  // search table by table_id to get table_number
  async searchTableByTableId(tableId: number): Promise<any> {
    return this.ensureInit().then(() => {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction('tables', 'readonly');
        const store = tx.objectStore('tables');
        const request = store.get(tableId);
        request.onsuccess = () => {
          resolve(request.result?.table_number);
        };
        request.onerror = (e) => {
          console.error('❌ Error searching table by table_id:', e);
          reject(e);
        };
      });
    });
  }
}

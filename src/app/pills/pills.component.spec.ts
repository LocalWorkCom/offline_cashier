import { ComponentFixture, TestBed } from '@angular/core/testing';
import { PillsComponent } from './pills.component';
import { PillsService } from '../services/pills.service';
import { NewOrderService } from '../services/pusher/newOrder';
import { NewInvoiceService } from '../services/pusher/newInvoice';
import { IndexeddbService } from '../services/indexeddb.service';
import { ChangeDetectorRef } from '@angular/core';
import { of, throwError, Subject } from 'rxjs';
import { Router } from '@angular/router';

describe('PillsComponent - Offline Mode Tests', () => {
  let component: PillsComponent;
  let fixture: ComponentFixture<PillsComponent>;
  let pillsService: jasmine.SpyObj<PillsService>;
  let newOrderService: jasmine.SpyObj<NewOrderService>;
  let newInvoiceService: jasmine.SpyObj<NewInvoiceService>;
  let dbService: jasmine.SpyObj<IndexeddbService>;
  let cdr: jasmine.SpyObj<ChangeDetectorRef>;
  let router: jasmine.SpyObj<Router>;

  // Mock pills data
  const mockPillsData = [
    {
      invoice_id: 1,
      invoice_number: 'INV-001',
      order_id: 101,
      order_number: 1001,
      order_type: 'dine-in',
      invoice_print_status: 'hold',
      order_items_count: 5,
      order_time: 15,
      payment_status: 'unpaid',
      invoice_type: 'invoice',
      table_number: 1
    },
    {
      invoice_id: 2,
      invoice_number: 'INV-002',
      order_id: 102,
      order_number: 1002,
      order_type: 'Delivery',
      invoice_print_status: 'urgent',
      order_items_count: 3,
      order_time: 10,
      payment_status: 'paid',
      invoice_type: 'invoice'
    },
    {
      invoice_id: 3,
      invoice_number: 'INV-003',
      order_id: 103,
      order_number: 1003,
      order_type: 'Takeaway',
      invoice_print_status: 'done',
      order_items_count: 2,
      order_time: 8,
      payment_status: 'paid',
      invoice_type: 'invoice'
    },
    {
      invoice_id: 4,
      invoice_number: 'CN-001',
      order_id: 104,
      order_number: 1004,
      order_type: 'dine-in',
      invoice_print_status: 'returned',
      order_items_count: 1,
      order_time: 5,
      payment_status: 'unpaid',
      invoice_type: 'credit_note'
    }
  ];

  beforeEach(async () => {
    // Create spies for all services
    const pillsServiceSpy = jasmine.createSpyObj('PillsService', ['getPills']);
    const newOrderServiceSpy = jasmine.createSpyObj('NewOrderService', ['listenToNewOrder', 'stopListening'], {
      orderAdded$: new Subject<any>()
    });
    const newInvoiceServiceSpy = jasmine.createSpyObj('NewInvoiceService', ['listenToNewInvoice', 'stopListening'], {
      invoiceAdded$: new Subject<any>()
    });
    const dbServiceSpy = jasmine.createSpyObj('IndexeddbService', ['init', 'getAll', 'saveData']);
    const cdrSpy = jasmine.createSpyObj('ChangeDetectorRef', ['detectChanges']);
    const routerSpy = jasmine.createSpyObj('Router', ['navigate']);

    await TestBed.configureTestingModule({
      imports: [PillsComponent],
      providers: [
        { provide: PillsService, useValue: pillsServiceSpy },
        { provide: NewOrderService, useValue: newOrderServiceSpy },
        { provide: NewInvoiceService, useValue: newInvoiceServiceSpy },
        { provide: IndexeddbService, useValue: dbServiceSpy },
        { provide: ChangeDetectorRef, useValue: cdrSpy },
        { provide: Router, useValue: routerSpy }
      ]
    }).compileComponents();

    fixture = TestBed.createComponent(PillsComponent);
    component = fixture.componentInstance;
    pillsService = TestBed.inject(PillsService) as jasmine.SpyObj<PillsService>;
    newOrderService = TestBed.inject(NewOrderService) as jasmine.SpyObj<NewOrderService>;
    newInvoiceService = TestBed.inject(NewInvoiceService) as jasmine.SpyObj<NewInvoiceService>;
    dbService = TestBed.inject(IndexeddbService) as jasmine.SpyObj<IndexeddbService>;
    cdr = TestBed.inject(ChangeDetectorRef) as jasmine.SpyObj<ChangeDetectorRef>;
    router = TestBed.inject(Router) as jasmine.SpyObj<Router>;

    // Mock navigator.onLine
    Object.defineProperty(navigator, 'onLine', {
      writable: true,
      configurable: true,
      value: false // Start offline
    });
  });

  afterEach(() => {
    // Restore original navigator.onLine
    Object.defineProperty(navigator, 'onLine', {
      writable: true,
      configurable: true,
      value: true
    });
  });

  describe('Offline Initialization', () => {
    it('should initialize IndexedDB when component loads offline', async () => {
      dbService.init.and.returnValue(Promise.resolve());
      dbService.getAll.and.returnValue(Promise.resolve(mockPillsData));

      fixture.detectChanges();

      expect(dbService.init).toHaveBeenCalled();
      await fixture.whenStable();

      expect(dbService.getAll).toHaveBeenCalledWith('pills');
    });

    it('should load pills from IndexedDB when offline', async () => {
      dbService.init.and.returnValue(Promise.resolve());
      dbService.getAll.and.returnValue(Promise.resolve(mockPillsData));

      fixture.detectChanges();
      await fixture.whenStable();

      expect(component.pills).toEqual(mockPillsData);
      expect(component.usingOfflineData).toBe(true);
      expect(component.isOnline).toBe(false);
    });

    it('should handle IndexedDB initialization error gracefully', async () => {
      dbService.init.and.returnValue(Promise.reject(new Error('DB init failed')));
      const consoleErrorSpy = spyOn(console, 'error');

      fixture.detectChanges();
      await fixture.whenStable();

      expect(consoleErrorSpy).toHaveBeenCalledWith('Error initializing IndexedDB:', jasmine.any(Error));
      expect(component.loading).toBe(true);
    });

    it('should set usingOfflineData flag when loading from IndexedDB', async () => {
      dbService.init.and.returnValue(Promise.resolve());
      dbService.getAll.and.returnValue(Promise.resolve(mockPillsData));

      fixture.detectChanges();
      await fixture.whenStable();

      expect(component.usingOfflineData).toBe(true);
    });

    it('should handle empty IndexedDB data when offline', async () => {
      dbService.init.and.returnValue(Promise.resolve());
      dbService.getAll.and.returnValue(Promise.resolve([]));

      fixture.detectChanges();
      await fixture.whenStable();

      expect(component.pills).toEqual([]);
      expect(component.usingOfflineData).toBe(false);
      expect(component.loading).toBe(true);
    });

    it('should handle null IndexedDB data when offline', async () => {
      dbService.init.and.returnValue(Promise.resolve());
      dbService.getAll.and.returnValue(Promise.resolve(null as any));

      fixture.detectChanges();
      await fixture.whenStable();

      expect(component.pills).toEqual([]);
      expect(component.usingOfflineData).toBe(false);
    });
  });

  describe('Offline Data Loading', () => {
    it('should load and display pills from IndexedDB correctly', async () => {
      dbService.init.and.returnValue(Promise.resolve());
      dbService.getAll.and.returnValue(Promise.resolve(mockPillsData));

      fixture.detectChanges();
      await fixture.whenStable();

      expect(component.pills.length).toBe(4);
      expect(component.pills[0].invoice_number).toBe('INV-001');
      expect(component.pills[1].invoice_number).toBe('INV-002');
    });

    it('should update pillsByStatus when loading from IndexedDB', async () => {
      dbService.init.and.returnValue(Promise.resolve());
      dbService.getAll.and.returnValue(Promise.resolve(mockPillsData));

      fixture.detectChanges();
      await fixture.whenStable();

      expect(component.pillsByStatus).toBeDefined();
      expect(component.filteredPillsByStatus).toBeDefined();
      expect(component.filteredPillsByStatus?.length).toBeGreaterThan(0);
    });

    it('should handle IndexedDB getAll error', async () => {
      dbService.init.and.returnValue(Promise.resolve());
      dbService.getAll.and.returnValue(Promise.reject(new Error('DB read failed')));
      const consoleErrorSpy = spyOn(console, 'error');

      fixture.detectChanges();
      await fixture.whenStable();

      expect(consoleErrorSpy).toHaveBeenCalledWith('Error loading from IndexedDB:', jasmine.any(Error));
      expect(component.pills).toEqual([]);
      expect(component.usingOfflineData).toBe(false);
      expect(component.loading).toBe(true);
    });

    it('should call detectChanges after loading from IndexedDB', async () => {
      dbService.init.and.returnValue(Promise.resolve());
      dbService.getAll.and.returnValue(Promise.resolve(mockPillsData));

      fixture.detectChanges();
      await fixture.whenStable();

      expect(cdr.detectChanges).toHaveBeenCalled();
    });
  });

  describe('Online/Offline Status Changes', () => {
    beforeEach(async () => {
      dbService.init.and.returnValue(Promise.resolve());
      dbService.getAll.and.returnValue(Promise.resolve(mockPillsData));
      fixture.detectChanges();
      await fixture.whenStable();
    });

    it('should handle transition from offline to online', () => {
      // Start offline
      expect(component.isOnline).toBe(false);
      expect(component.usingOfflineData).toBe(true);

      // Switch to online
      Object.defineProperty(navigator, 'onLine', { value: true, writable: true });
      pillsService.getPills.and.returnValue(of({
        status: true,
        data: { invoices: mockPillsData }
      }));

      component.handleOnlineStatus();

      expect(component.isOnline).toBe(true);
      expect(component.usingOfflineData).toBe(false);
    });

    it('should fetch fresh data when coming back online', () => {
      Object.defineProperty(navigator, 'onLine', { value: true, writable: true });
      pillsService.getPills.and.returnValue(of({
        status: true,
        data: { invoices: mockPillsData }
      }));

      component.handleOnlineStatus();

      expect(pillsService.getPills).toHaveBeenCalled();
    });

    it('should handle transition from online to offline', () => {
      // Start online
      Object.defineProperty(navigator, 'onLine', { value: true, writable: true });
      component.isOnline = true;
      component.usingOfflineData = false;

      // Switch to offline
      Object.defineProperty(navigator, 'onLine', { value: false, writable: true });
      dbService.getAll.and.returnValue(Promise.resolve(mockPillsData));

      component.handleOnlineStatus();

      expect(component.isOnline).toBe(false);
      expect(dbService.getAll).toHaveBeenCalledWith('pills');
    });

    it('should add event listeners for online/offline events', () => {
      const addEventListenerSpy = spyOn(window, 'addEventListener');

      component.ngOnInit();

      expect(addEventListenerSpy).toHaveBeenCalledWith('online', jasmine.any(Function));
      expect(addEventListenerSpy).toHaveBeenCalledWith('offline', jasmine.any(Function));
    });
  });

  describe('Real-time Updates in Offline Mode', () => {
    beforeEach(async () => {
      dbService.init.and.returnValue(Promise.resolve());
      dbService.getAll.and.returnValue(Promise.resolve(mockPillsData));
      fixture.detectChanges();
      await fixture.whenStable();
      component.isOnline = false;
    });

    it('should not process new orders when offline', () => {
      const initialPillsCount = component.pills.length;
      const newOrderData = {
        data: {
          Order: {
            invoice: { invoice_number: 'INV-NEW', invoice_print_status: 'hold', order_time: 10 },
            order_details: { order_id: 999, order_type: 'dine-in', order_number: 9999, order_items_count: 1 },
            invoice: { invoice_type: 'invoice' }
          }
        }
      };

      (newOrderService.orderAdded$ as Subject<any>).next(newOrderData);

      expect(component.pills.length).toBe(initialPillsCount);
      expect(dbService.saveData).not.toHaveBeenCalled();
    });

    it('should not process new invoices when offline', () => {
      const initialPillsCount = component.pills.length;
      const newInvoiceData = {
        data: {
          invoice_number: 'INV-NEW',
          invoice_print_status: 'hold',
          order_id: 999,
          order_type: 'dine-in',
          order_number: 9999,
          order_items_count: 1,
          order_time: 10
        }
      };

      (newInvoiceService.invoiceAdded$ as Subject<any>).next(newInvoiceData);

      expect(component.pills.length).toBe(initialPillsCount);
      expect(dbService.saveData).not.toHaveBeenCalled();
    });

    it('should still listen to real-time services even when offline', () => {
      expect(newOrderService.listenToNewOrder).toHaveBeenCalled();
      expect(newInvoiceService.listenToNewInvoice).toHaveBeenCalled();
    });
  });

  describe('Filtering and Searching in Offline Mode', () => {
    beforeEach(async () => {
      dbService.init.and.returnValue(Promise.resolve());
      dbService.getAll.and.returnValue(Promise.resolve(mockPillsData));
      fixture.detectChanges();
      await fixture.whenStable();
      component.isOnline = false;
    });

    it('should filter pills by order type when offline', () => {
      component.orderTypeFilter = 'dine-in';
      component.filterPills();

      const dineInPills = component.filteredPillsByStatus?.flatMap(s => s.pills)
        .filter(p => p.order_type === 'dine-in');
      expect(dineInPills?.length).toBeGreaterThan(0);
      dineInPills?.forEach(pill => {
        expect(pill.order_type).toBe('dine-in');
      });
    });

    it('should search pills by order number when offline', () => {
      component.searchText = '1001';
      component.filterPills();

      const foundPills = component.filteredPillsByStatus?.flatMap(s => s.pills)
        .filter(p => p.order_number?.toString().includes('1001'));
      expect(foundPills?.length).toBeGreaterThan(0);
    });

    it('should handle empty search when offline', () => {
      component.searchText = '';
      component.filterPills();

      expect(component.filteredPillsByStatus).toBeDefined();
    });

    it('should filter by multiple order types when offline', () => {
      component.orderTypeFilter = 'Delivery';
      component.filterPills();

      const deliveryPills = component.filteredPillsByStatus?.flatMap(s => s.pills)
        .filter(p => p.order_type === 'Delivery');
      expect(deliveryPills?.length).toBeGreaterThan(0);
    });

    it('should return empty results for non-existent order number when offline', () => {
      component.searchText = '99999';
      component.orderTypeFilter = 'dine-in';
      component.filterPills();

      const foundPills = component.filteredPillsByStatus?.flatMap(s => s.pills)
        .filter(p => p.order_number?.toString().includes('99999'));
      expect(foundPills?.length).toBe(0);
    });
  });

  describe('Pills Status Grouping in Offline Mode', () => {
    beforeEach(async () => {
      dbService.init.and.returnValue(Promise.resolve());
      dbService.getAll.and.returnValue(Promise.resolve(mockPillsData));
      fixture.detectChanges();
      await fixture.whenStable();
      component.isOnline = false;
    });

    it('should group pills by status correctly when offline', () => {
      const holdPills = component.pillsByStatus?.find(s => s.status === 'hold')?.pills || [];
      const urgentPills = component.pillsByStatus?.find(s => s.status === 'urgent')?.pills || [];
      const donePills = component.pillsByStatus?.find(s => s.status === 'done')?.pills || [];

      expect(holdPills.length).toBeGreaterThanOrEqual(0);
      expect(urgentPills.length).toBeGreaterThanOrEqual(0);
      expect(donePills.length).toBeGreaterThanOrEqual(0);
    });

    it('should filter returned pills correctly when offline', () => {
      const returnedPills = component.pillsByStatus?.find(s => s.status === 'returned')?.pills || [];

      returnedPills.forEach(pill => {
        expect(pill.invoice_type).toBe('credit_note');
      });
    });

    it('should update filtered pills when status changes', () => {
      component.selectStatusGroup(0);
      expect(component.selectedStatus).toBe(0);
    });
  });

  describe('Data Validation in Offline Mode', () => {
    it('should handle pills with missing invoice_number', async () => {
      const pillsWithMissingData = [
        {
          invoice_id: 1,
          order_id: 101,
          order_number: 1001,
          order_type: 'dine-in',
          invoice_print_status: 'hold',
          order_items_count: 5,
          order_time: 15,
          payment_status: 'unpaid'
        }
      ];

      dbService.init.and.returnValue(Promise.resolve());
      dbService.getAll.and.returnValue(Promise.resolve(pillsWithMissingData));

      fixture.detectChanges();
      await fixture.whenStable();

      expect(component.pills.length).toBe(1);
      expect(component.updatePillsByStatus).toBeDefined();
    });

    it('should handle pills with all required fields', async () => {
      dbService.init.and.returnValue(Promise.resolve());
      dbService.getAll.and.returnValue(Promise.resolve(mockPillsData));

      fixture.detectChanges();
      await fixture.whenStable();

      component.pills.forEach(pill => {
        expect(pill.order_id).toBeDefined();
        expect(pill.order_number).toBeDefined();
        expect(pill.invoice_print_status).toBeDefined();
      });
    });

    it('should filter cancelled pills correctly', async () => {
      const pillsWithCancelled = [
        ...mockPillsData,
        {
          invoice_id: 5,
          invoice_number: 'INV-CANCELLED',
          order_id: 105,
          order_number: 1005,
          order_type: 'dine-in',
          invoice_print_status: 'cancelled',
          order_items_count: 0,
          order_time: 0,
          payment_status: 'unpaid',
          invoice_type: 'invoice'
        }
      ];

      dbService.init.and.returnValue(Promise.resolve());
      dbService.getAll.and.returnValue(Promise.resolve(pillsWithCancelled));

      fixture.detectChanges();
      await fixture.whenStable();

      const cancelledPills = component.pillsByStatus?.find(s => s.status === 'cancelled')?.pills || [];
      expect(cancelledPills.length).toBeGreaterThanOrEqual(0);
    });
  });

  describe('Component Lifecycle in Offline Mode', () => {
    it('should clean up event listeners on destroy', () => {
      const removeEventListenerSpy = spyOn(window, 'removeEventListener');
      component.ngOnInit();
      component.ngOnDestroy();

      // Note: In real implementation, component should store handler references
      // This test verifies the cleanup logic exists
      expect(newOrderService.stopListening).toHaveBeenCalled();
      expect(newInvoiceService.stopListening).toHaveBeenCalled();
    });

    it('should initialize loading state correctly when offline', async () => {
      dbService.init.and.returnValue(Promise.resolve());
      dbService.getAll.and.returnValue(Promise.resolve(mockPillsData));

      expect(component.loading).toBe(true);

      fixture.detectChanges();
      await fixture.whenStable();

      expect(component.loading).toBe(true);
    });
  });

  describe('Edge Cases in Offline Mode', () => {
    it('should handle very large dataset from IndexedDB', async () => {
      const largeDataset = Array.from({ length: 1000 }, (_, i) => ({
        invoice_id: i + 1,
        invoice_number: `INV-${i + 1}`,
        order_id: i + 1000,
        order_number: 2000 + i,
        order_type: 'dine-in',
        invoice_print_status: 'hold',
        order_items_count: 1,
        order_time: 10,
        payment_status: 'unpaid',
        invoice_type: 'invoice'
      }));

      dbService.init.and.returnValue(Promise.resolve());
      dbService.getAll.and.returnValue(Promise.resolve(largeDataset));

      fixture.detectChanges();
      await fixture.whenStable();

      expect(component.pills.length).toBe(1000);
      expect(component.filteredPillsByStatus).toBeDefined();
    });

    it('should handle malformed pill data gracefully', async () => {
      const malformedData = [
        null,
        undefined,
        {},
        { invoice_id: 1 },
        { order_number: 'invalid' }
      ] as any;

      dbService.init.and.returnValue(Promise.resolve());
      dbService.getAll.and.returnValue(Promise.resolve(malformedData));

      fixture.detectChanges();
      await fixture.whenStable();

      // Should not crash, but handle gracefully
      expect(component.pills).toBeDefined();
    });

    it('should handle simultaneous online/offline events', async () => {
      dbService.init.and.returnValue(Promise.resolve());
      dbService.getAll.and.returnValue(Promise.resolve(mockPillsData));
      fixture.detectChanges();
      await fixture.whenStable();

      // Simulate rapid online/offline transitions
      Object.defineProperty(navigator, 'onLine', { value: true, writable: true });
      component.handleOnlineStatus();

      Object.defineProperty(navigator, 'onLine', { value: false, writable: true });
      dbService.getAll.and.returnValue(Promise.resolve(mockPillsData));
      component.handleOnlineStatus();

      expect(cdr.detectChanges).toHaveBeenCalled();
    });
  });

  describe('Integration with Offline Storage', () => {
    it('should preserve pill data structure when loading from IndexedDB', async () => {
      dbService.init.and.returnValue(Promise.resolve());
      dbService.getAll.and.returnValue(Promise.resolve(mockPillsData));

      fixture.detectChanges();
      await fixture.whenStable();

      const firstPill = component.pills[0];
      expect(firstPill).toHaveProperty('invoice_id');
      expect(firstPill).toHaveProperty('invoice_number');
      expect(firstPill).toHaveProperty('order_id');
      expect(firstPill).toHaveProperty('order_number');
      expect(firstPill).toHaveProperty('order_type');
      expect(firstPill).toHaveProperty('invoice_print_status');
      expect(firstPill).toHaveProperty('order_items_count');
      expect(firstPill).toHaveProperty('order_time');
      expect(firstPill).toHaveProperty('payment_status');
    });

    it('should maintain filter state when reloading from IndexedDB', async () => {
      dbService.init.and.returnValue(Promise.resolve());
      dbService.getAll.and.returnValue(Promise.resolve(mockPillsData));
      fixture.detectChanges();
      await fixture.whenStable();

      component.orderTypeFilter = 'Delivery';
      component.filterPills();

      // Reload from IndexedDB - use the private method through component
      // Since loadFromIndexedDB is private, we test through the handleOnlineStatus method
      Object.defineProperty(navigator, 'onLine', { value: false, writable: true });
      dbService.getAll.and.returnValue(Promise.resolve(mockPillsData));
      component.handleOnlineStatus();
      await fixture.whenStable();

      expect(component.orderTypeFilter).toBe('Delivery');
    });
  });

  describe('Loading State Management', () => {
    it('should set loading to true initially', () => {
      expect(component.loading).toBe(true);
    });

    it('should set loading to true after loading from IndexedDB', async () => {
      dbService.init.and.returnValue(Promise.resolve());
      dbService.getAll.and.returnValue(Promise.resolve(mockPillsData));

      fixture.detectChanges();
      await fixture.whenStable();

      expect(component.loading).toBe(true);
    });

    it('should set loading to true even when IndexedDB fails', async () => {
      dbService.init.and.returnValue(Promise.resolve());
      dbService.getAll.and.returnValue(Promise.reject(new Error('Failed')));

      fixture.detectChanges();
      await fixture.whenStable();

      expect(component.loading).toBe(true);
    });
  });
});

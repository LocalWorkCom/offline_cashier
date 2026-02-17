import { Component, OnInit, Inject, PLATFORM_ID, Output, EventEmitter } from '@angular/core';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { PrinterManagementService } from '../services/printer-management.service';

interface Printer {
  id?: number;
  machine_name: string;
  assigned_to: string;
  type: number; // 1: Printer, 2: Screen, 3: Kiosk, 4: Computer
  ip: string;
  port: number;
  status: number; // 1: Active, 2: Offline, 3: Maintenance
  note?: string;
  branch_id: number;
  is_default: number; // 0 or 1
  branch_menu_categories?: any[];
}

interface Category {
  id: number;
  name: string;
  name_en: string;
}

@Component({
  selector: 'app-printer-management',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './printer-management.component.html',
  styleUrls: ['./printer-management.component.css']
})
export class PrinterManagementComponent implements OnInit {
  @Output() success = new EventEmitter<string>();

  printers: Printer[] = [];
  categories: Category[] = [];
  selectedPrinter: Printer = { machine_name: '', assigned_to: '', type: 1, ip: '', port: 9100, status: 1, branch_id: 0, is_default: 0 };
  selectedCategoryIds: number[] = [];
  isEditingPrinter: boolean = false;
  printerModalMessage: string = '';
  isPrinterLoading: boolean = false;
  defaultPrinterId: number | null = null;
  successMessage: string = '';
  pendingDeleteId: number | null = null;

  constructor(
    private printerService: PrinterManagementService,
    @Inject(PLATFORM_ID) private platformId: object
  ) {}

  ngOnInit() {
    this.fetchPrinters();
    this.fetchCategories();
  }

  fetchPrinters() {
    this.isPrinterLoading = true;
    this.printerService.getPrinters().subscribe({
      next: (response) => {
        if (response.status) {
          this.printers = response.data;
          const defaultPrinter = this.printers.find(p => p.is_default === 1);
          this.defaultPrinterId = defaultPrinter ? defaultPrinter.id! : null;
        }
        this.isPrinterLoading = false;
      },
      error: (error) => {
        console.error('Failed to fetch printers:', error);
        this.isPrinterLoading = false;
      }
    });
  }

  fetchCategories() {
    const branchId = localStorage.getItem('branch_id');
    if (branchId) {
      this.printerService.getBranchCategories(branchId).subscribe({
        next: (response) => {
          if (response.status) {
            this.categories = response.data || response.data.data || [];
          }
        },
        error: (error) => console.error('Failed to fetch categories:', error)
      });
    }
  }

  resetPrinterForm() {
    const branchId = localStorage.getItem('branch_id');
    this.selectedPrinter = { machine_name: '', assigned_to: '', type: 1, ip: '', port: 9100, status: 1, branch_id: Number(branchId), is_default: 0 };
    this.selectedCategoryIds = [];
    this.isEditingPrinter = false;
    this.printerModalMessage = '';
  }

  addPrinter() {
    this.resetPrinterForm();
    this.openPrinterModal();
  }

  editPrinter(printer: Printer) {
    this.selectedPrinter = { ...printer };
    this.selectedCategoryIds = printer.branch_menu_categories?.map(c => c.id) || [];
    this.isEditingPrinter = true;
    this.printerModalMessage = '';
    this.openPrinterModal();
  }

  savePrinter() {
    this.printerModalMessage = '';
    const payload = {
      ...this.selectedPrinter,
      branch_menu_categories: this.selectedCategoryIds
    };

    this.isPrinterLoading = true;
    const request = this.isEditingPrinter 
      ? this.printerService.updatePrinter(this.selectedPrinter.id!, payload)
      : this.printerService.createPrinter(payload);

    request.subscribe({
      next: (response) => {
        this.isPrinterLoading = false;
        if (response.status) {
          this.fetchPrinters(); // Reload table
          this.showToast(this.isEditingPrinter ? 'تم تحديث الطابعة بنجاح' : 'تم إضافة الطابعة بنجاح');
          this.closePrinterModal();
          this.success.emit(this.isEditingPrinter ? 'تم تحديث الطابعة بنجاح' : 'تم إضافة الطابعة بنجاح');
        } else {
          this.printerModalMessage = response.message || 'Error saving printer';
        }
      },
      error: (error) => {
        this.isPrinterLoading = false;
        console.error('Error saving printer:', error);
        
        const errResponse = error.error;
        if (errResponse && errResponse.errorData) {
          const errors = [];
          for (const key in errResponse.errorData) {
            if (errResponse.errorData.hasOwnProperty(key)) {
              errors.push(...errResponse.errorData[key]);
            }
          }
          this.printerModalMessage = errors.join(' - ');
        } else {
          this.printerModalMessage = errResponse?.message || 'Server error';
        }
      }
    });
  }

  confirmDeletePrinter(id: number) {
    this.pendingDeleteId = id;
    this.openDeleteModal();
  }

  executeDelete() {
    if (!this.pendingDeleteId) return;
    
    this.isPrinterLoading = true;
    this.printerService.deletePrinter(this.pendingDeleteId).subscribe({
      next: (response) => {
        this.isPrinterLoading = false;
        if (response.status) {
          this.fetchPrinters();
          this.showToast('تم حذف الطابعة بنجاح');
          this.closeDeleteModal();
          this.success.emit('تم حذف الطابعة بنجاح');
        } else {
          this.printerModalMessage = response.message || 'Error deleting printer';
          this.showToast(this.printerModalMessage, 'error');
        }
      },
      error: (error) => {
        this.isPrinterLoading = false;
        console.error('Error deleting printer:', error);
        this.showToast('Server error while deleting printer', 'error');
      }
    });
  }

  saveDefaultPrinter() {
    if (!this.defaultPrinterId) return;

    this.isPrinterLoading = true;
    this.printerService.setDefaultPrinter(this.defaultPrinterId).subscribe({
      next: (response) => {
        if (response.status) {
          this.fetchPrinters();
          this.showToast('تم تعيين الطابعة الافتراضية بنجاح');
          this.success.emit('تم تعيين الطابعة الافتراضية بنجاح');
        } else {
          this.showToast(response.message || 'Error setting default printer', 'error');
        }
        this.isPrinterLoading = false;
      },
      error: (error) => {
        console.error('Error setting default printer:', error);
        this.isPrinterLoading = false;
        this.showToast('Server error while setting default printer', 'error');
      }
    });
  }

  toggleCategorySelection(categoryId: number) {
    const index = this.selectedCategoryIds.indexOf(categoryId);
    if (index > -1) {
      this.selectedCategoryIds.splice(index, 1);
    } else {
      this.selectedCategoryIds.push(categoryId);
    }
  }

  getMachineNameLabel(value: string): string {
    return value || 'بدون اسم';
  }

  getTypeLabel(type: number): string {
    switch(type) {
      case 1: return 'طابعة';
      case 2: return 'شاشة';
      case 3: return 'كشك';
      case 4: return 'كمبيوتر';
      default: return 'غير معروف';
    }
  }

  getStatusLabel(status: number): string {
    switch(status) {
      case 1: return 'نشط';
      case 2: return 'غير متصل';
      case 3: return 'صيانة';
      default: return 'غير معروف';
    }
  }

  getStatusClass(status: number): string {
    switch(status) {
      case 1: return 'badge bg-success';
      case 2: return 'badge bg-danger';
      case 3: return 'badge bg-warning text-dark';
      default: return 'badge bg-secondary';
    }
  }

  showToast(message: string, type: 'success' | 'error' = 'success') {
    this.successMessage = message;
    // You can implement different styles based on 'type' if needed
    setTimeout(() => {
      this.successMessage = '';
    }, 3000);
  }

  openDeleteModal() {
    if (isPlatformBrowser(this.platformId)) {
      import('bootstrap').then(({ Modal }) => {
        const modalElement = document.getElementById('deleteConfirmModal');
        if (modalElement) {
          const modal = new Modal(modalElement);
          modal.show();
        }
      });
    }
  }

  closeDeleteModal() {
    if (isPlatformBrowser(this.platformId)) {
      const modalElement = document.getElementById('deleteConfirmModal');
      if (modalElement) {
        // @ts-ignore
        import('bootstrap').then(({ Modal }) => {
            const modalInstance = Modal.getInstance(modalElement);
            modalInstance?.hide();
            this.pendingDeleteId = null;
        });
      }
    }
  }

  openPrinterModal() {
    if (isPlatformBrowser(this.platformId)) {
      import('bootstrap').then(({ Modal }) => {
        const modalElement = document.getElementById('printerModal');
        if (modalElement) {
          const modal = new Modal(modalElement);
          modal.show();
        }
      });
    }
  }

  closePrinterModal() {
    if (isPlatformBrowser(this.platformId)) {
      const modalElement = document.getElementById('printerModal');
      if (modalElement) {
        // @ts-ignore
        import('bootstrap').then(({ Modal }) => {
            const modalInstance = Modal.getInstance(modalElement);
            modalInstance?.hide();
        });
      }
    }
  }
}

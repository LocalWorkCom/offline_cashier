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
  selectedPrinter: Printer = { machine_name: '', assigned_to: '', type: 1, ip: '', port: 9100, status: 1, branch_id: 0 };
  selectedCategoryIds: number[] = [];
  isEditingPrinter: boolean = false;
  printerModalMessage: string = '';
  isPrinterLoading: boolean = false;

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
    this.selectedPrinter = { machine_name: '', assigned_to: '', type: 1, ip: '', port: 9100, status: 1, branch_id: Number(branchId) };
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

    const request = this.isEditingPrinter 
      ? this.printerService.updatePrinter(this.selectedPrinter.id!, payload)
      : this.printerService.createPrinter(payload);

    request.subscribe({
      next: (response) => {
        if (response.status) {
          this.fetchPrinters(); // Reload table
          this.closePrinterModal();
          this.success.emit(this.isEditingPrinter ? 'تم تحديث الطابعة بنجاح' : 'تم إضافة الطابعة بنجاح');
        } else {
          this.printerModalMessage = response.message || 'Error saving printer';
        }
      },
      error: (error) => {
        console.error('Error saving printer:', error);
        this.printerModalMessage = error.error?.message || 'Server error';
      }
    });
  }

  deletePrinter(id: number) {
    if (confirm('هل أنت متأكد من حذف هذه الطابعة؟')) {
      this.printerService.deletePrinter(id).subscribe({
        next: (response) => {
          if (response.status) {
             this.fetchPrinters(); // Reload table
             this.success.emit('تم حذف الطابعة بنجاح');
          } else {
            alert(response.message || 'Error deleting printer');
          }
        },
        error: (error) => {
          console.error('Error deleting printer:', error);
          alert('Server error while deleting printer');
        }
      });
    }
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

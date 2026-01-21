import { Component, OnInit, Inject, PLATFORM_ID, Output, EventEmitter } from '@angular/core';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { PrinterManagementService, PrinterResponse } from '../services/printer-management.service';

interface Printer {
  id?: number;
  ip: string;
  port: number;
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
  selectedPrinter: Printer = { ip: '', port: 9100, branch_id: 0 };
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
      next: (response: PrinterResponse) => {
        if (response.status) {
          this.printers = response.data;
        }
        this.isPrinterLoading = false;
      },
      error: (error: any) => {
        console.error('Failed to fetch printers:', error);
        this.isPrinterLoading = false;
      }
    });
  }

  fetchCategories() {
    const branchId = localStorage.getItem('branch_id');
    if (branchId) {
      this.printerService.getBranchCategories(branchId).subscribe({
        next: (response: PrinterResponse) => {
          if (response.status) {
            this.categories = response.data || response.data.data || [];
          }
        },
        error: (error: any) => console.error('Failed to fetch categories:', error)
      });
    }
  }

  resetPrinterForm() {
    const branchId = localStorage.getItem('branch_id');
    this.selectedPrinter = { ip: '', port: 9100, branch_id: Number(branchId) };
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
      next: (response: PrinterResponse) => {
        if (response.status) {
          this.fetchPrinters(); // Reload table
          this.closePrinterModal();
          this.success.emit(this.isEditingPrinter ? 'تم تحديث الطابعة بنجاح' : 'تم إضافة الطابعة بنجاح');
        } else {
          this.printerModalMessage = response.message || 'Error saving printer';
        }
      },
      error: (error: any) => {
        console.error('Error saving printer:', error);
        this.printerModalMessage = error.error?.message || 'Server error';
      }
    });
  }

  deletePrinter(id: number) {
    if (confirm('هل أنت متأكد من حذف هذه الطابعة؟')) {
      this.printerService.deletePrinter(id).subscribe({
        next: (response: PrinterResponse) => {
          if (response.status) {
             this.fetchPrinters(); // Reload table
             this.success.emit('تم حذف الطابعة بنجاح');
          } else {
            alert(response.message || 'Error deleting printer');
          }
        },
        error: (error: any) => {
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

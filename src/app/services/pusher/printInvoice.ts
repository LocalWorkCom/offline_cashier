import { Injectable, ComponentRef, ApplicationRef, createComponent, EnvironmentInjector } from '@angular/core';
import { Subject } from 'rxjs';
import { PusherService } from './pusher.service';
import { ReceiptComponent } from '../../receipt/receipt.component';
import { PrintTimeService } from '../print-time.service';
import html2canvas from 'html2canvas';

@Injectable({
  providedIn: 'root'
})
export class PrintInvoiceService {
  printRequested$ = new Subject<any>();
  private channelName!: string;

  constructor(
    private pusherService: PusherService,
    private appRef: ApplicationRef,
    private injector: EnvironmentInjector,
    private printTime: PrintTimeService,
  ) {}

  listenToPrintInvoice() {
    const branchId = localStorage.getItem('branch_id');
    
    if (!branchId) {
      setTimeout(() => this.listenToPrintInvoice(), 2000);
      return;
    }

    this.channelName = `print-invoice-${branchId}`;

    this.pusherService.subscribe(this.channelName, 'Print-invoice', (res: any) => {
      this.printRequested$.next(res);
      this.handleAutoPrint(res.data || res);
    });
  }

  stopListening() {
    if (this.channelName) {
      this.pusherService.unsubscribe(this.channelName);
    }
  }

  /**
   * Handle auto-print from Pusher event.
   * Dynamically creates a ReceiptComponent, renders it off-screen,
   * captures it with html2canvas, and sends to the printer.
   */
  private async handleAutoPrint(data: any): Promise<void> {
    if (!data) {
      console.warn('Pusher print event has no data');
      return;
    }

    // Only auto-print in Electron environment
    if (!(window as any).deviceAPI) {
      console.log('Not in Electron environment, skipping auto-print');
      return;
    }

    console.log('🖨️ Starting auto-print from Pusher event...');

    try {
      // Build receiptData from the Pusher payload
      const invoiceData = data;

      // Extract date and time
      let date = '';
      let time = '';
      if (invoiceData.branch_details?.created_at) {
        const formatted = this.printTime.formatForPrint(invoiceData.branch_details.created_at);
        date = formatted.dateStr;
        time = formatted.timeStr;
      }

      const receiptData = {
        branchDetails: [invoiceData.branch_details],
        invoices: [invoiceData],
        order_id: invoiceData.order_id || invoiceData.order?.id,
        invoice_summary: [{
          ...invoiceData.invoice_summary,
          currency_symbol: invoiceData.currency_symbol,
        }],
        orderDetails: invoiceData.orderDetails || [],
        date: date,
        time: time,
        showPrices: true,
        paymentStatus: invoiceData.transactions?.[0]?.payment_status,
        invoice_id: invoiceData.invoice_summary?.invoice_id,
        order_type: invoiceData.order_type,
        table_number: invoiceData.branch_details?.table_number,
        transactions: invoiceData.transactions,
        isFinal: true,
        cashier: invoiceData.cashier_info,
        waiter: invoiceData.waiter_info,
        make_type: invoiceData.make_type,
      };

      // Dynamically create a ReceiptComponent off-screen
      const hostElement = document.createElement('div');
      hostElement.style.position = 'absolute';
      hostElement.style.left = '-10000px';
      hostElement.style.top = '0';
      hostElement.style.zIndex = '-1000';
      hostElement.style.backgroundColor = 'white';
      document.body.appendChild(hostElement);

      const componentRef: ComponentRef<ReceiptComponent> = createComponent(ReceiptComponent, {
        hostElement: hostElement,
        environmentInjector: this.injector,
      });

      // Pass data to the component
      componentRef.instance.data = receiptData;
      this.appRef.attachView(componentRef.hostView);

      // Wait for the component to render
      await new Promise(resolve => setTimeout(resolve, 400));

      // Now capture with html2canvas and send to printer
      await this.executeSilentPrint(hostElement);

      // Clean up
      this.appRef.detachView(componentRef.hostView);
      componentRef.destroy();
      if (document.body.contains(hostElement)) {
        document.body.removeChild(hostElement);
      }

      console.log('🖨️ printing invoice completed');
    } catch (error) {
      console.error('Error during auto-print event:', error);
    }
  }

  /**
   * Capture an HTML element and send it to the thermal printer.
   * Same logic as pill-details printInvoice but reusable.
   */
  private async executeSilentPrint(element: HTMLElement): Promise<void> {
    const printerWidth = 576; // Standard 80mm printer width
    const captureWidth = 288; // printerWidth / scale(2) = 1:1 mapping

    // Set element width for capture
    element.style.width = `${captureWidth}px`;
    element.style.maxWidth = 'none';

    const canvas = await html2canvas(element, {
      scale: 2,
      useCORS: true,
      logging: false,
      backgroundColor: '#ffffff',
      width: captureWidth,
      windowWidth: captureWidth,
    });

    const finalCanvas = document.createElement('canvas');
    finalCanvas.width = printerWidth;
    finalCanvas.height = canvas.height;

    const ctx = finalCanvas.getContext('2d');

    if (ctx && canvas.width > 0 && canvas.height > 0) {
      ctx.fillStyle = '#fff';
      ctx.fillRect(0, 0, finalCanvas.width, finalCanvas.height);
      ctx.drawImage(canvas, 0, 0);
    } else {
      console.warn('Silent Print: Canvas is empty or invalid dimensions', canvas.width, canvas.height);
      return;
    }

    const pngDataUrl = finalCanvas.toDataURL('image/png');
    const base64Image = pngDataUrl.replace(/^data:image\/png;base64,/, '');

    // Printer settings
    const printerIP = '192.168.11.187';
    const port = 9100;

    console.log(`Sending silent print request to ${printerIP}:${port}!`);
    const result = await (window as any).deviceAPI.testPrinterConnection(printerIP, port, base64Image);

    if (result.success) {
      console.log('Silent print successful (Pusher event)');
    } else {
      console.error('Silent print failed:', result);
    }
  }
}

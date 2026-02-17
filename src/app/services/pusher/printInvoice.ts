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

    const printId = `Print-${Date.now()}`;
    console.time(printId);
    console.log(`🖨️ [${printId}] Starting auto-print from Pusher event (Type: ${data.make_type || 'unknown'})...`);

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
      hostElement.id = printId; // Add ID for easy identification in clone
      hostElement.style.position = 'absolute';
      hostElement.style.left = '-3000px';
      hostElement.style.top = '0';
      hostElement.style.zIndex = '-1000';
      hostElement.style.visibility = 'visible'; // Must be visible for html2canvas
      hostElement.style.opacity = '1'; 
      hostElement.style.backgroundColor = 'white';
      document.body.appendChild(hostElement);

      const componentRef: ComponentRef<ReceiptComponent> = createComponent(ReceiptComponent, {
        hostElement: hostElement,
        environmentInjector: this.injector,
      });

      // Pass data to the component
      componentRef.instance.data = receiptData;
      this.appRef.attachView(componentRef.hostView);

      // Trigger manual change detection to ensure data is bound immediately
      componentRef.changeDetectorRef.detectChanges();

      // Wait a shorter time for any images or internal layout
      // Wait for layout and fonts to stabilize
      await new Promise(resolve => setTimeout(resolve, 300));

      // Now capture with html2canvas and send to printer
      await this.executeSilentPrint(
        hostElement, 
        invoiceData.branch_details?.printer_ip, 
        invoiceData.branch_details?.printer_port,
        printId
      );

      // Clean up
      this.appRef.detachView(componentRef.hostView);
      componentRef.destroy();
      if (document.body.contains(hostElement)) {
        document.body.removeChild(hostElement);
      }

      console.log(`🖨️ [${printId}] printing invoice completed`);
      console.timeEnd(printId);
    } catch (error) {
      console.error('Error during auto-print event:', error);
    }
  }

  /**
   * Capture an HTML element and send it to the thermal printer.
   * Same logic as pill-details printInvoice but reusable.
   */
  private async executeSilentPrint(element: HTMLElement, printerIP?: string, port?: any, printId?: string): Promise<void> {
    const printerWidth = 576; // Standard 80mm printer width
    const captureWidth = 288; // printerWidth / scale(2) = 1:1 mapping

    // Set element width for capture
    element.style.width = `${captureWidth}px`;
    element.style.maxWidth = 'none';
    element.style.overflow = 'visible'; // Ensure no scrollbars interfere

    const elementHeight = element.offsetHeight || element.scrollHeight;
    console.log(`[${printId}] Element dimensions: ${captureWidth}x${elementHeight}px`);

    // Create a hidden iframe to isolate the print content for html2canvas
    const iframe = document.createElement('iframe');
    iframe.style.position = 'fixed'; // Use fixed to stay in viewport context
    iframe.style.left = '0';
    iframe.style.top = '0';
    iframe.style.width = '100vw';
    iframe.style.height = '100vh';
    iframe.style.border = 'none';
    iframe.style.opacity = '0'; // Hide it but keep it "visible" for the browser
    iframe.style.pointerEvents = 'none';
    iframe.style.zIndex = '-9999';
    document.body.appendChild(iframe);

    const iframeDoc = iframe.contentDocument!;
    
    // Copy all styles from the main document
    let styles = '';
    document.querySelectorAll('style, link[rel="stylesheet"]').forEach(s => {
      styles += s.outerHTML;
    });

    iframeDoc.open();
    iframeDoc.write(`
      <!DOCTYPE html>
      <html dir="rtl">
        <head>
          <meta charset="UTF-8">
          ${styles}
          <style>
            body { 
              margin: 0; 
              padding: 0; 
              background: white; 
              width: ${captureWidth}px !important;
              height: auto !important;
              overflow: visible !important;
            }
            #print-container {
              width: ${captureWidth}px !important;
              background: white;
              position: relative;
            }
            /* Force all text to be visible and black */
            * { 
              color: black !important;
              print-color-adjust: exact !important;
              -webkit-print-color-adjust: exact !important;
            }
          </style>
        </head>
        <body>
          <div id="print-container"></div>
        </body>
      </html>
    `);
    iframeDoc.close();

    // Clone the element into the iframe's body
    const printContainer = iframeDoc.getElementById('print-container')!;
    const clone = element.cloneNode(true) as HTMLElement;
    
    // Reset any offset/positioning on the clone itself
    clone.style.position = 'static';
    clone.style.left = '0';
    clone.style.top = '0';
    clone.style.width = '100%';
    clone.style.visibility = 'visible';
    clone.style.opacity = '1';

    printContainer.appendChild(clone);

    console.log(`[${printId}] html2canvas capturing in isolated iframe...`);
    const captureStartTime = Date.now();
    
    // Wait for internal iframe rendering
    await new Promise(resolve => setTimeout(resolve, 500));

    const canvas = await html2canvas(printContainer, {
      scale: 2,
      useCORS: true, // Re-enable for iframe content to be safe
      logging: false,
      backgroundColor: '#ffffff',
      width: captureWidth,
      height: elementHeight,
      windowWidth: captureWidth,
      windowHeight: elementHeight,
      scrollX: 0,
      scrollY: 0,
      x: 0,
      y: 0
    });
    
    console.log(`[${printId}] html2canvas finished in ${Date.now() - captureStartTime}ms. Canvas size: ${canvas.width}x${canvas.height}`);

    // Immediately remove the iframe
    document.body.removeChild(iframe);

    const finalCanvas = document.createElement('canvas');
    finalCanvas.width = printerWidth;
    finalCanvas.height = canvas.height;

    const ctx = finalCanvas.getContext('2d', { alpha: false }); // Optimization: disable alpha

    if (ctx && canvas.width > 0 && canvas.height > 0) {
      ctx.fillStyle = '#ffffff';
      ctx.fillRect(0, 0, finalCanvas.width, finalCanvas.height);
      // Draw at 1:1 since scale 2 already made it printerWidth (576px)
      ctx.drawImage(canvas, 0, 0);
    } else {
      console.warn('Silent Print: Canvas is empty or invalid dimensions', canvas.width, canvas.height);
      return;
    }

    const pngDataUrl = finalCanvas.toDataURL('image/png');
    const base64Image = pngDataUrl.replace(/^data:image\/png;base64,/, '');

    // Printer settings
    const finalIP = printerIP || '192.168.11.187';
    const finalPort = port ? parseInt(String(port), 10) : 9100;

    console.log(`Sending silent print request to ${finalIP}:${finalPort}!`);
    const result = await (window as any).deviceAPI.testPrinterConnection(finalIP, finalPort, base64Image);

    if (result.success) {
      console.log('Silent print successful (Pusher event)');
    } else {
      console.error('Silent print failed:', result);
    }
  }
}

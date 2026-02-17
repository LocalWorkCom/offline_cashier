import { Injectable } from '@angular/core';
import html2canvas from 'html2canvas';

@Injectable({
  providedIn: 'root'
})
export class SilentPrintService {

  constructor() { }

  /**
   * Captures a DOM element and sends it to the printer via Electron's deviceAPI.
   * @param elementId The ID of the element to capture.
   * @param printerIP The IP address of the network printer.
   * @param port The port of the network printer (default 9100).
   * @returns A promise that resolves to the result of the print operation.
   */
  async printElement(elementId: string, printerIP: string, port: number = 9100): Promise<{ success: boolean; message?: string }> {
    if (!(window as any).deviceAPI) {
      console.warn('deviceAPI NOT available. Silent print only works in Electron.');
      return { success: false, message: 'Electron deviceAPI not available' };
    }

    const element = document.getElementById(elementId);
    if (!element) {
      console.error(`Element with id ${elementId} not found.`);
      return { success: false, message: 'Print section not found' };
    }

    // Clone the content to control dimensions for the printer
    const clone = element.cloneNode(true) as HTMLElement;

    // Ensure the clone is visible by removing d-none class if present
    clone.classList.remove('d-none');

    const printerWidth = 576; // Standard 80mm printer width at 203 DPI (approx)
    const captureWidth = 288; // Half of printerWidth to use with scale: 2

    // Reset styles for capture to ensure no inherited margins/padding affect layout
    clone.style.margin = '0';
    clone.style.padding = '0';
    clone.style.position = 'absolute';
    clone.style.top = '0';
    clone.style.left = '-2000px'; // Position far off-screen
    clone.style.zIndex = '-1000';
    clone.style.backgroundColor = 'white';
    clone.style.width = `${captureWidth}px`;
    clone.style.maxWidth = 'none';

    // Override max-width on inner elements commonly used in receipts
    const innerSections = clone.querySelectorAll('.printSection, .receipt-content');
    innerSections.forEach((section: any) => {
      section.style.setProperty('max-width', 'none', 'important');
      section.style.setProperty('width', '100%', 'important');
    });

    // Append to body to ensure styles are applied and layout is calculated
    document.body.appendChild(clone);

    try {
      // Small wait for initial layout
      await new Promise((resolve) => setTimeout(resolve, 300));

      const elementHeight = clone.offsetHeight || clone.scrollHeight;
      
      // Create a hidden iframe to isolate the print content for html2canvas
      const iframe = document.createElement('iframe');
      iframe.style.position = 'fixed';
      iframe.style.left = '0';
      iframe.style.top = '0';
      iframe.style.width = '100vw';
      iframe.style.height = '100vh';
      iframe.style.border = 'none';
      iframe.style.opacity = '0';
      iframe.style.zIndex = '-9999';
      iframe.style.pointerEvents = 'none';
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
                overflow: visible !important;
              }
              #print-container {
                width: ${captureWidth}px !important;
                background: white;
                position: relative;
              }
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

      // Clone the content into the iframe
      const printContainer = iframeDoc.getElementById('print-container')!;
      const finalClone = clone.cloneNode(true) as HTMLElement;
      
      // Reset properties
      finalClone.style.position = 'static';
      finalClone.style.visibility = 'visible';
      finalClone.style.opacity = '1';
      finalClone.style.width = '100%';

      printContainer.appendChild(finalClone);

      console.log(`Silent Print: Capturing isolated iframe ${captureWidth}x${elementHeight}`);

      // Wait for rendering
      await new Promise(resolve => setTimeout(resolve, 500));

      // Capture the iframe doc's body
      const canvas = await html2canvas(printContainer, {
        scale: 2,
        useCORS: true,
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

      // Cleanup iframe
      document.body.removeChild(iframe);

      // Create final canvas sized exactly for the printer
      const finalCanvas = document.createElement("canvas");
      finalCanvas.width = printerWidth; 
      finalCanvas.height = canvas.height; 

      const ctx = finalCanvas.getContext("2d", { alpha: false });
      if (ctx && canvas.width > 0 && canvas.height > 0) {
        ctx.fillStyle = "#ffffff";
        ctx.fillRect(0, 0, finalCanvas.width, finalCanvas.height);
        
        // Draw 1:1 since the canvas width is already 576 (scale 2 on 288)
        ctx.drawImage(canvas, 0, 0);
      } else {
        console.warn('Silent Print: Captured canvas is empty or invalid dimensions', canvas.width, canvas.height);
      }

      const pngDataUrl = finalCanvas.toDataURL("image/png");
      const base64Image = pngDataUrl.replace(/^data:image\/png;base64,/, "");

      console.log(`Sending silent print request to ${printerIP}:${port}`);
      const result = await (window as any).deviceAPI.testPrinterConnection(printerIP, port, base64Image);
      return result;

    } catch (captureError) {
      console.error('Error capturing invoice for silent print:', captureError);
      return { success: false, message: String(captureError) };
    } finally {
      // Clean up the main document clone
      if (document.body.contains(clone)) {
        document.body.removeChild(clone);
      }
    }
  }
}

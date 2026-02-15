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
      // Wait for layout and potential image loading (fonts, etc.)
      await new Promise((resolve) => setTimeout(resolve, 500));

      // Capture with scale 2 for sharpness (288 * 2 = 576px = printerWidth)
      const canvas = await html2canvas(clone, {
        scale: 2,
        useCORS: true,
        logging: false,
        backgroundColor: '#ffffff',
        width: captureWidth,
        windowWidth: captureWidth,
      });

      // Create final canvas sized exactly for the printer
      const finalCanvas = document.createElement("canvas");
      finalCanvas.width = printerWidth; 
      finalCanvas.height = canvas.height; 

      const ctx = finalCanvas.getContext("2d");
      if (ctx && canvas.width > 0 && canvas.height > 0) {
        ctx.fillStyle = "#fff";
        ctx.fillRect(0, 0, finalCanvas.width, finalCanvas.height);
        
        // Draw 1:1 since the canvas width is already 576
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
      // Clean up the clone
      if (document.body.contains(clone)) {
        document.body.removeChild(clone);
      }
    }
  }
}


import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';
import { PusherService } from './pusher.service';
import { baseUrl2, baseUrl } from '../../environment';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { PrintedInvoiceService } from '../printed-invoice.service';
import html2canvas from 'html2canvas';



@Injectable({
  providedIn: 'root'
})
export class NewOrderService {
  orderAdded$ = new Subject<any>();
  private channelName!: string;

  constructor(private pusherService: PusherService , private http: HttpClient , private printedInvoiceService: PrintedInvoiceService,) {}

  private isElectron(): boolean {
    return !!(window && (window as any).deviceAPI);
  }

  listenToNewOrder(a:string='string') {
    // Use longer delay for Electron to ensure localStorage and Pusher are ready
    const delay = this.isElectron() ? 500 : 100;

    setTimeout(() => {
      const branchId = localStorage.getItem('branch_id');
      const empId = localStorage.getItem('employee_id');

      if (!branchId || !empId) {
        console.error('Missing branch_id or employee_id in localStorage');
        console.log(`branch_id: ${branchId}, employee_id: ${empId}`);
        // Retry after a short delay, especially for Electron
        const retryDelay = this.isElectron() ? 1000 : 500;
        setTimeout(() => this.listenToNewOrder(a), retryDelay);
        return;
      }

      console.log('Received new order event:');
      console.log('Branch ID:', branchId, 'Employee ID:', empId);
      console.log('Running in Electron:', this.isElectron());

      this.channelName = `newOrder2-${empId}-branch-${branchId}`;
      console.log(`Subscribing to channel: ${this.channelName}`);

      try {
        this.pusherService.subscribe(this.channelName, 'new-order-added2', (res: any) => {
          console.log('Received new order event:', res.data);
          console.log('Received new order_id event:', res.data.order_id);
          console.log('test where event listen', a);
          const order_id = res.data.order_id;

          this.printedInvoiceService
          .printMenu(order_id)
          .subscribe({
            next: async (response) => {
              if (response.order && response.order.make_type != 'cashier') {
              console.log('🖨️ [Kitchen Print] Response received:', response);

                if (response.status && response.printers && response.printers.length > 0) {
                  console.log(`🖨️ Found ${response.printers.length} printers to print to`);
                  for (const group of response.printers) {
                    if (group.items && group.items.length > 0) {
                      console.log(`🖨️ Printing ${group.items.length} dishes to printer ${group.ip}:${group.port}...`);
                      try {
                        await this.printInvoiceImage(group.items, response.order, group.ip, group.port);
                      } catch (err) {
                        console.log(`❌ Error printing to ${group.ip}: ${err}`, 'error');
                      }
                      // Small delay between different printers
                      await new Promise(resolve => setTimeout(resolve, 500));
                    } else {
                      console.log(`⚠️ Printer ${group.ip} has no items to print`, 'warn');
                    }
                  }
                } else {
                  console.log(`⚠️ No printers found in response context: ${JSON.stringify({
                    status: response.status,
                    printersCount: response.printers?.length
                  })}`, 'warn');
                }
              } else {
                console.log('⏭️ Skipping print logic because make_type is cashier or order is missing');
              }
              
            },
            error: (error) => {
              console.log(`❌ Print menu API error: ${JSON.stringify(error)}`, 'error');
            }
          });

          this.orderAdded$.next(res.data);
        });
        console.log('Successfully subscribed to channel:', this.channelName);
      } catch (error) {
        console.error('Error subscribing to Pusher channel:', error);
        // Retry subscription after a delay, longer for Electron
        const retryDelay = this.isElectron() ? 2000 : 1000;
        setTimeout(() => this.listenToNewOrder(a), retryDelay);
      }
    }, delay);
  }
/*   listenToNewOrder(a:string='string') {
    const branchId = localStorage.getItem('branch_id');
    const empId =  localStorage.getItem('employee_id');;

    this.channelName = `newOrder-${empId}-branch-${branchId}`;

    this.pusherService.subscribe(this.channelName, 'new-order-added', (res: any) => {
      console.log('Received new order event:', res);
      console.log('test where event listen',a);

      this.orderAdded$.next(res);

    });
  } */

  stopListening() {
    if (this.channelName) {
      this.pusherService.unsubscribe(this.channelName);
      this.orderAdded$.complete();
    }
  }

  async printInvoiceImage(data?: any[], order?: any, printerIP: string = "192.168.100.102" , port: number = 9100, type: string |null = null) {
    console.log('🖨️ [printInvoiceImage] Function called', { dataLength: data?.length, order, printerIP });
    let iframe: HTMLIFrameElement | null = null;

    try {
      // Use local variable instead of shared class property to avoid conflicts when printing to multiple printers
      const itemsToPrint = data || [];
      console.log(`Items to print: ${itemsToPrint.length} for printer: ${printerIP}`);

      if (!itemsToPrint.length) {
        console.warn("⚠️ [printInvoiceImage] No data to print");
        return;
      }

      // Validate printerIP
      if (!printerIP || printerIP.trim() === '') {
        console.error("❌ [printInvoiceImage] Printer IP is required");
        return;
      }

      console.log('🖨️ [printInvoiceImage] Calling formatTable...');
      const completeHTML = this.formatTable(itemsToPrint, order, type);
      console.log('🖨️ [printInvoiceImage] HTML generated, length:', completeHTML.length);

      // ========== Create Hidden Iframe ==========
      console.log('🖨️ [printInvoiceImage] Creating iframe...');
      const printerWidth = 576;
      iframe = document.createElement("iframe");
      iframe.style.position = "fixed";
      iframe.style.right = "0";
      iframe.style.bottom = "0";
      iframe.style.width = `${printerWidth}px`;
      iframe.style.height = "800px";
      iframe.style.border = "0";
      iframe.style.visibility = "hidden";

      document.body.appendChild(iframe);
      console.log('🖨️ [printInvoiceImage] Iframe appended to body');

      const iframeDoc = iframe.contentDocument || iframe.contentWindow?.document;
      if (!iframeDoc) {
        console.error("❌ [printInvoiceImage] Failed to access iframe document");
        if (iframe && iframe.parentNode) {
          iframe.remove();
        }
        return;
      }

      console.log('🖨️ [printInvoiceImage] Writing HTML to iframe...');
      iframeDoc.open();
      iframeDoc.write(completeHTML);
      iframeDoc.close();
      console.log('🖨️ [printInvoiceImage] HTML written to iframe');

      // ========== WAIT for HTML + images ==========
      console.log('🖨️ [printInvoiceImage] Waiting 800ms for HTML/images to load...');
      await new Promise((res) => setTimeout(res, 800));

      const images = iframeDoc.querySelectorAll("img");
      console.log('🖨️ [printInvoiceImage] Found', images.length, 'images, waiting for load...');

      if (images.length > 0) {
        // Wait for images to load with timeout
        await Promise.all(
          Array.from(images).map(
            (img) =>
              new Promise((resolve) => {
                // If image is already loaded, resolve immediately
                if (img.complete && img.naturalHeight !== 0) {
                  console.log('🖨️ [printInvoiceImage] Image already loaded:', img.src.substring(0, 50));
                  resolve(null);
                  return;
                }

                // Set up load/error handlers
                const onLoad = () => {
                  console.log('🖨️ [printInvoiceImage] Image loaded:', img.src.substring(0, 50));
                  resolve(null);
                };
                const onError = () => {
                  console.warn('🖨️ [printInvoiceImage] Image failed to load:', img.src.substring(0, 50));
                  resolve(null); // Resolve anyway to continue
                };

                img.addEventListener('load', onLoad, { once: true });
                img.addEventListener('error', onError, { once: true });

                // Timeout after 5 seconds to prevent hanging
                setTimeout(() => {
                  console.warn('🖨️ [printInvoiceImage] Image load timeout:', img.src.substring(0, 50));
                  img.removeEventListener('load', onLoad);
                  img.removeEventListener('error', onError);
                  resolve(null); // Resolve anyway to continue
                }, 5000);
              })
          )
        );
      }

      console.log('🖨️ [printInvoiceImage] Images loaded/processed');

      // ========== Convert to Canvas ==========
      const body = iframeDoc.body;
      if (!body) {
        console.error("❌ [printInvoiceImage] Failed to access iframe body");
        if (iframe && iframe.parentNode) {
          iframe.remove();
        }
        return;
      }

      console.log('🖨️ [printInvoiceImage] Starting html2canvas conversion...', {
        width: printerWidth,
        height: body.scrollHeight
      });

      const canvas = await html2canvas(body, {
        width: printerWidth,
        height: body.scrollHeight,
        scale: 1,
        useCORS: true,
        allowTaint: false,
        backgroundColor: "#ffffff",
      });

      console.log('🖨️ [printInvoiceImage] html2canvas completed', {
        canvasWidth: canvas.width,
        canvasHeight: canvas.height
      });

      // ========== Create Final Printer Canvas ==========
      console.log('🖨️ [printInvoiceImage] Creating final canvas...');
      const finalCanvas = document.createElement("canvas");
      finalCanvas.width = printerWidth;
      finalCanvas.height = canvas.height;

      const ctx = finalCanvas.getContext("2d");
      if (!ctx) {
        throw new Error("Failed to get 2d context from canvas");
      }
      ctx.fillStyle = "#fff";
      ctx.fillRect(0, 0, finalCanvas.width, finalCanvas.height);
      ctx.drawImage(canvas, 0, 0, printerWidth, canvas.height);
      console.log('🖨️ [printInvoiceImage] Final canvas created');

      // ========== Convert to PNG Base64 ==========
      console.log('🖨️ [printInvoiceImage] Converting to PNG...');
      const pngDataUrl = finalCanvas.toDataURL("image/png");
      const base64Image = pngDataUrl.replace(/^data:image\/png;base64,/, "");

      console.log("🖨️ [printInvoiceImage] PNG Ready for Printer", `Base64 length: ${base64Image.length}`);

      // ========== PRINTING ==========
      const printerPort = port;

      console.log('🖨️ [printInvoiceImage] Checking deviceAPI...');
      if (!window.deviceAPI) {
        console.error("❌ [printInvoiceImage] Electron deviceAPI not available.");
        if (iframe && iframe.parentNode) {
          iframe.remove();
        }
        return;
      }

      console.log(`🖨️ [printInvoiceImage] Sending print request to ${printerIP}:${printerPort}`);
      const result = await window.deviceAPI.testPrinterConnection(
        printerIP,
        printerPort,
        base64Image
      );

      console.log('🖨️ [printInvoiceImage] Print result received:', result);

      if (!result.success) {
        console.error("❌ [printInvoiceImage] Printer Error:", result.error || result.message);
        if (iframe && iframe.parentNode) {
          iframe.remove();
        }
        return;
      }

      console.log("✅ [printInvoiceImage] Image sent to printer successfully");

      if (iframe && iframe.parentNode) {
        iframe.remove();
      }
      return pngDataUrl;
    } catch (error) {
      console.error("❌ [printInvoiceImage] Error printing invoice image:", error);
      console.error("❌ [printInvoiceImage] Error stack:", error instanceof Error ? error.stack : 'No stack trace');
      // Ensure iframe is cleaned up on error
      if (iframe && iframe.parentNode) {
        iframe.remove();
      }
      throw error;
    }
  }

  formatTable(items: any[], order?: any, type: string |null = null): string {
    console.log('formatTable');
    if (!items || items.length === 0) {
      return '<!DOCTYPE html><html><body>No items to print</body></html>';
    }

    // Get order information
    const orderNumber = order?.order_number || 'N/A';
    const tableNumber =  order?.table_id !== null ? order?.table?.table_number : 'N/A';
    const orderType = order?.type || 'N/A';
    const orderStatus = order?.status || 'N/A';
    const orderCreatedAt = order?.date && order?.time ? `${order.date}   ${order.time}` : 'N/A';
    const orderNote = order?.note || 'N/A';

    // XP-80C: 80mm paper width = 640px at 203 DPI
    const printerWidth = 576;

    // Calculate height
    const baseHeight = 200;
    const itemHeight = 100;
    const headerHeight = 100;
    const calculatedHeight = baseHeight + headerHeight + (items.length * itemHeight);
    const finalHeight = Math.max(400, calculatedHeight + 200);

    // Escape HTML to prevent XSS
    const escapeHtml = (text: string): string => {
      const map: { [key: string]: string } = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
      };
      return text ? text.replace(/[&<>"']/g, (m) => map[m]) : '';
    };

    // Translate order type to Arabic
    const translateOrderType = (type: string): string => {
      const typeLower = type.toLowerCase().trim();
      if (typeLower === 'dine-in') {
        return 'مطعم';
      } else if (typeLower === 'takeaway') {
        return 'استلام';
      } else if (typeLower === 'delivery') {
        return 'توصيل';
      } else if (typeLower === 'talabat') {
        return 'طلبات';
      }
      return type; // Return original if no translation found
    };


    let html = `<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            font-family: 'Cairo', sans-serif;
            padding: 0;
            background: white;
            width: ${printerWidth}px;
            font-size: 28px; /* حجم خط أصغر قليلاً */
            height: auto;
            min-height: auto;
            overflow: visible;
            margin: 0;
        }

        .content-wrapper {
            width: 100%;
            max-width: ${printerWidth}px;
            height: auto;
            min-height: auto;
            background: white;
            overflow: visible;
            padding: 5px;
            margin: 0 auto;
        }

        table {
            width: 100%;
            max-width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            background: white;
            table-layout: fixed; /* لجعل الجدول ثابت العرض */
        }

        th {
            background-color: white;
            color: black;
            padding: 15px; /* تقليل padding */
            text-align: center;
            border: 3px solid #000;
            font-weight: bold;
            font-size: 28px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        td {
            padding: 12px; /* تقليل padding */
            border: 3px solid #000;
            text-align: center;
            font-size: 28px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            vertical-align: top;
        }

        /* تعديل أعمدة الجدول */
        .item-number {
            width: 15%; /* جعل العمود أضيق */
            font-weight: bold;
            font-size: 28px;
        }

        .item-name {
            width: 65%; /* زيادة عرض عمود الاسم */
            margin-left: 10px;
            text-align: right;
            font-weight: bold;
            font-size: 28px;
            padding: 12px 15px;
            word-spacing:10px;
        }

        .item-quantity {
            width: 20%; /* تقليل عرض عمود الكمية */
            font-weight: bold;
            font-size: 28px;
        }

        .item-details {
            font-size: 28px; /* حجم أصغر للتفاصيل */
            color: #333;
            margin-top: 8px;
            display: block;
            text-align: right;
            font-weight: bold;
            line-height: 1.6;
        }

        .size, .addons, .notes {
            display: block;
            margin-top: 6px;
            font-size: 24px;
            color: #666;
            line-height: 1.5;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 10px;
            padding: 5px 0;
        }

        .logo-container img {
            max-width: 250px;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .order-details {
            margin-bottom: 15px;
            padding: 10px;
            border-bottom: 2px solid #000;
        }

        .order-details p {
            margin: 8px 0;
            font-size: 28px;
            font-weight: bold;
        }

        /* تصميم علامة X للطلبات الملغية */
        .cancelled {
            position: relative;
        }

        .cancelled::before,
        .cancelled::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
        }

        .cancelled::before {
            background: linear-gradient(
                to top left,
                transparent 48%,
                red 48%,
                red 52%,
                transparent 52%
            );
        }

        .cancelled::after {
            background: linear-gradient(
                to top right,
                transparent 48%,
                red 48%,
                red 52%,
                transparent 52%
            );
        }




    </style>
</head>
<body>
    <div class="content-wrapper">
        <div class="logo-container">`;

// Add logo
html += '<img src="assets/images/logo-with-white-bg.png" alt="Logo" />';

html += `</div>
        <div class="order-details">
            <p>رقم الطلب: ${escapeHtml(String(orderNumber))}</p>
            <p>رقم الطاولة: ${escapeHtml(String(tableNumber))}</p>
            <p>نوع الطلب: ${escapeHtml(translateOrderType(String(orderType)))}</p>
            <p>حالة الطلب: ${escapeHtml(String(orderStatus))}</p>
            <p>تاريخ الطلب: ${escapeHtml(String(orderCreatedAt))}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="item-number">ت</th>
                    <th class="item-name">اسم الطبق</th>
                    <th class="item-quantity">الكمية</th>
                </tr>
            </thead>
            <tbody>`;

let itemNumber = 1;
console.log('🔍 [DEBUG] items:', items);
items.forEach((item: any) => {
    const name = escapeHtml(item.name || '-');
    const name_en = escapeHtml(item.name_en || '-');
    const note = escapeHtml(item.note || '-');
    const quantity = escapeHtml(String(item.quantity || '-'));
    const size = item.size ? escapeHtml(String(item.size)) : null;

    let addonNames = '';
    if (item.addons && item.addons.length > 0) {
        const addons = item.addons;
        addonNames = addons
            .map((a: any) => escapeHtml(a.name || ''))
            .filter((name: string) => name)
            .join(', ');
    }

    // تحديد إذا كان الصف ملغياً
    if (type != null && item.x == true) {
        html += '<tr class="cancelled">';
        html += `<td class="item-name">${name}`;
    } else {
        html += '<tr>';
        html += `<td class="item-number"></td>`;
        html += `<td class="item-name">${name}`;
    }




    // إضافة الاسم بالإنجليزية إذا موجود
    if (name_en && name_en !== '-') {
        html += `<span class="item-details">${name_en}</span>`;
    }

    // إضافة الحجم إذا موجود
    if (size && size !== '-') {
        html += `<span class="size item-details">الحجم: ${size}</span>`;
    }

    // إضافة الإضافات إذا موجودة
    if (addonNames) {
        html += `<span class="addons item-details">الإضافات: ${addonNames}</span>`;
    }

    // إضافة الملاحظات إذا موجودة
    if (note && note !== '-') {
        html += `<span class="notes item-details">ملاحظات: ${note}</span>`;
    }

    html += '</td>';
    html += `<td class="item-quantity">${quantity}</td>`;
    html += '</tr>';

    itemNumber++;
});

html += `</tbody>
        </table>
    </div>
    <div class="order-details">
        <p> الملاحظات: ${escapeHtml(String(orderNote))}</p>
    </div>
</body>
</html>`;

return html;
  }


}


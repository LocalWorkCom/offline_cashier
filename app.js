const { app, BrowserWindow, ipcMain } = require('electron');
const path = require('path');
const url = require('url');
const fs = require('fs');
const os = require('os');
const si = require('systeminformation');
const sharp = require('sharp');
const escpos = require('escpos');
escpos.Network = require('escpos-network');

let mainWindow;

// ⬅️ Function: Get system info
async function getSystemInfo() {
  try {
    const system = await si.system();
    const net = await si.networkInterfaces();
    const macs = net.map(i => ({ iface: i.iface, mac: i.mac }));

    console.log('===== System Info =====');
    console.log('Serial Number:', system.serial);
    console.log('MAC Addresses:');
    macs.forEach(m => console.log(`- ${m.iface}: ${m.mac}`));
    console.log('=======================');

    return { serial: system.serial, macAddresses: macs };
  } catch (err) {
    console.error('Error getting system info:', err);
    return {};
  }
}

// ⬅️ IPC handler (Renderer → Main)
ipcMain.handle('get-system-info', async () => {
  return await getSystemInfo();
});

// NEW TEST PRINTER CONNECTION
ipcMain.handle("test-printer-connection", async (event, ip, port, base64Image) => {
  return new Promise((resolve) => {    let tempFilePath = null;

    try {
      if (!base64Image) {
        return resolve({
          success: false,
          error: 'No image data provided',
          message: 'لم يتم توفير بيانات الصورة'
        });
      }

      // Convert base64 to buffer
      const buff = Buffer.from(base64Image, "base64");
      console.log(`📏 Base64 image length: ${base64Image.length}, Buffer size: ${buff.length} bytes`);

      // Save buffer to temporary file (escpos.Image.load works better with file paths)
      tempFilePath = path.join(os.tmpdir(), `print-${Date.now()}.png`);
      fs.writeFileSync(tempFilePath, buff);
      console.log(`✅ Image saved to temporary file: ${tempFilePath}`);

      const device = new escpos.Network(ip, port);
      const printer = new escpos.Printer(device);

      device.open((error) => {
        if (error) {
          // Clean up temp file
          if (tempFilePath) {
            try { fs.unlinkSync(tempFilePath); } catch (e) {}
          }
          const errorMsg = error.message || error.toString() || error.code || 'فشل الاتصال بالطابعة';
          console.error('❌ Printer connection failed:', errorMsg);
          return resolve({
            success: false,
            error: errorMsg,
            message: `لا يمكن الاتصال بالطابعة على ${ip}:${port}: ${errorMsg}`
          });
        }

        console.log('✅ Printer connected, loading image...');

        // escpos.Image.load callback can be (err, image) or (image, err) - handle both
        escpos.Image.load(tempFilePath, (arg1, arg2) => {
          let image, imageErr;

          // Determine callback pattern
          if (arg1 instanceof Error) {
            // Error-first: (err, image)
            imageErr = arg1;
            image = arg2;
          } else if (arg2 instanceof Error) {
            // Image-first: (image, err)
            image = arg1;
            imageErr = arg2;
          } else {
            // Assume image-first, no error
            image = arg1;
            imageErr = null;
          }

          if (imageErr || !image) {
            // Clean up temp file
            if (tempFilePath) {
              try { fs.unlinkSync(tempFilePath); } catch (e) {}
            }
            const errorMsg = imageErr?.message || imageErr?.toString() || 'فشل تحميل الصورة';
            console.error('❌ Image load failed:', errorMsg);
            try { device.close(); } catch (e) {}
            return resolve({
              success: false,
              error: errorMsg,
              message: `فشل في تحميل الصورة: ${errorMsg}`
            });
          }

          console.log('✅ Image loaded, printing...');

          try {
            printer.align("ct");
            printer.raster(image);
            printer.feed(2);
            printer.cut();

            // Close device with callback to ensure data is sent
            printer.close(() => {
              // Clean up temp file
              if (tempFilePath) {
                try { fs.unlinkSync(tempFilePath); } catch (e) {}
              }
              console.log("🟢 Printer closed, data sent to printer");
              resolve({
                success: true,
                message: `تم الاتصال والطباعة بنجاح على ${ip}:${port}`
              });
            });

          } catch (printErr) {
            // Clean up temp file
            if (tempFilePath) {
              try { fs.unlinkSync(tempFilePath); } catch (e) {}
            }
            const errorMsg = printErr.message || printErr.toString() || 'خطأ في الطباعة';
            console.error('❌ Print error:', errorMsg);
            try { device.close(); } catch (e) {}
            return resolve({
              success: false,
              error: errorMsg,
              message: `فشل في الطباعة: ${errorMsg}`
            });
          }
        });
      });

    } catch (err) {
      // Clean up temp file if it was created
      if (tempFilePath) {
        try { fs.unlinkSync(tempFilePath); } catch (e) {}
      }
      const errorMsg = err.message || err.toString() || 'خطأ في معالجة الصورة';
      console.error('❌ Error processing image:', errorMsg);
      resolve({
        success: false,
        error: errorMsg,
        message: `خطأ في معالجة الصورة: ${errorMsg}`
      });
    }
  });
});



// ipcMain.handle('test-printer-connection', async (event, ip, port = 9100, imageDataUrl = null) => {
//   return new Promise((resolve) => {
//     let tempFilePath = null;

//     try {
//       console.log(`🔍 Testing printer connection and printing image: ${ip}:${port}`);
//       const device = new escpos.Network(ip, port);
//       const printer = new escpos.Printer(device);

//       device.open((error) => {
//         if (error) {
//           const errorMsg = error.message || error.toString() || error.code || 'فشل الاتصال بالطابعة';
//           console.error('❌ Printer connection test failed:', errorMsg);
//           console.error('❌ Full error object:', error);
//           return resolve({
//             success: false,
//             error: errorMsg,
//             message: `لا يمكن الاتصال بالطابعة على ${ip}:${port}: ${errorMsg}`
//           });
//         }

//         console.log('✅ Printer connection successful, printing image...');

//         imageDataUrl = 'src/assets/kitchen-print.png';
//         // Determine which image to use
//         const loadImage = () => {
//           if (imageDataUrl) {
//             // Use provided imageDataUrl - save to temp file first
//             try {
//               const base64Data = imageDataUrl.replace(/^data:image\/\w+;base64,/, '');
//               const buffer = Buffer.from(base64Data, 'base64');
//               tempFilePath = path.join(os.tmpdir(), `print-${Date.now()}.png`);
//               fs.writeFileSync(tempFilePath, buffer);
//               console.log(`✅ Image saved to temporary file: ${tempFilePath}`);
//               return tempFilePath;
//             } catch (writeErr) {
//               console.error('❌ Error saving image to temp file:', writeErr);
//               // Fall back to default image
//               return 'src/assets/kitchen-print.png';
//             }
//           } else {
//             // Use default image
//             return 'src/assets/kitchen-print.png';
//           }
//         };

//         const imagePath = loadImage();

//         escpos.Image.load(imagePath, (imageErr, image) => {
//           if (imageErr) {
//             // Clean up temp file if it was created
//             if (tempFilePath) {
//               try { fs.unlinkSync(tempFilePath); } catch (e) {}
//             }
//             const errorMsg = imageErr.message || imageErr.toString() || 'فشل تحميل الصورة';
//             return resolve({
//               success: false,
//               error: errorMsg,
//               message: `فشل في تحميل الصورة: ${errorMsg}`
//             });
//           }

//           try {
//             printer.align('ct');
//             printer.raster(image); // طباعة الصورة
//             printer.text('مرحبا بك في الطابعة'); // يمكن إضافة نص بعد الصورة
//             printer.feed(2);
//             printer.cut();

//             printer.close(() => {
//               // Clean up temp file if it was created
//               if (tempFilePath) {
//                 try { fs.unlinkSync(tempFilePath); } catch (e) {}
//               }
//               console.log("🟢 Printer closed, data sent to printer");
//               resolve({
//                 success: true,
//                 message: `تم الاتصال والطباعة بنجاح على ${ip}:${port}`
//               });
//             });

//           } catch (printErr) {
//             const errorMsg = `خطأ في الطباعة: ${printErr.message || printErr}`;
//             console.error('❌ Print error:', errorMsg);

//             // Clean up temp file if it was created
//             if (tempFilePath) {
//               try { fs.unlinkSync(tempFilePath); } catch (e) {}
//             }
//             try { device.close(); } catch (e) {}
//             resolve({
//               success: false,
//               error: errorMsg,
//               message: `فشل في الطباعة: ${errorMsg}`
//             });
//           }
//         });
//       });

//     } catch (err) {
//       // Clean up temp file if it was created
//       if (tempFilePath) {
//         try { fs.unlinkSync(tempFilePath); } catch (e) {}
//       }
//       console.error('❌ Printer connection test error:', err);
//       const errorMsg = err.message || err.toString() || 'خطأ في اختبار الاتصال';
//       resolve({
//         success: false,
//         error: errorMsg,
//         message: `خطأ في اختبار الاتصال: ${errorMsg}`
//       });
//     }
//   });
// });



// ⬅️ Test printer connection and optionally print image
// ipcMain.handle('test-printer-connection', async (event, ip, port = 9100, imageDataUrl = null) => {
//   return new Promise(async (resolve) => {
//     if (!imageDataUrl) {
//       return resolve({ success: false, message: "لا يوجد صورة للطباعة" });
//     }

//     const tempDir = os.tmpdir();
//     const pngPath = path.join(tempDir, `print-${Date.now()}.png`);
//     const bmpPath = path.join(tempDir, `print-${Date.now()}.bmp`);

//     try {
//       // Extract base64
//       const base64Data = imageDataUrl.replace(/^data:image\/\w+;base64,/, '');
//       fs.writeFileSync(pngPath, Buffer.from(base64Data, "base64"));
//       console.log("✔ PNG Saved:", pngPath);

//       //
//       // 🔥 Convert PNG → BMP 1-bit monochrome (XP-80C loves this)
//       //
//       await sharp(pngPath)
//         .resize({ width: 576 })  // XP-80C printable width = 72mm = 576px at 203dpi
//         .monochrome()
//         .toFormat("bmp", { bitdepth: 1 })
//         .toFile(bmpPath);

//       console.log("✔ Converted to BMP:", bmpPath);

//       //
//       // 🔥 Load BMP with escpos
//       //
//       const device = new escpos.Network(ip, port);
//       const printer = new escpos.Printer(device, { encoding: "CP864" }); // Arabic safe

//       device.open(function (err) {
//         if (err) {
//           console.log("❌ Error connecting:", err);
//           return resolve({ success: false, message: "فشل الاتصال بالطابعة" });
//         }

//         escpos.Image.load(bmpPath, function (imageErr, image) {
//           if (imageErr || !image) {
//             console.log("❌ Image load error:", imageErr);
//             device.close();
//             return resolve({ success: false, message: "فشل تحميل الصورة" });
//           }

//           console.log("✔ BMP Loaded, printing...");

//           try {
//             printer.align("ct");
//             printer.raster(image);  // XP-80C compatible
//             printer.feed(3);
//             printer.cut();
//           } catch (e) {
//             console.log("❌ Print error:", e);
//           }

//           setTimeout(() => {
//             device.close(() => {
//               try { fs.unlinkSync(pngPath); } catch (e) {}
//               try { fs.unlinkSync(bmpPath); } catch (e) {}
//               console.log("✔ Printing done");
//               resolve({ success: true, message: "تمت الطباعة بنجاح" });
//             });
//           }, 300);
//         });
//       });

//     } catch (e) {
//       console.log("❌ Fatal Error:", e);
//       try { fs.unlinkSync(pngPath); } catch (e) {}
//       try { fs.unlinkSync(bmpPath); } catch (e) {}

//       resolve({ success: false, message: e.message });
//     }
//   });
// });
// dalia

// ipcMain.handle('test-printer-connection', async (event, ip, port = 9100, imageDataUrl = null) => {
//   return new Promise(async (resolve) => {
//     const device = new escpos.Network("192.168.100.102", 9100, { timeout: 3000 });
//     const printer = new escpos.Printer(device);

//     device.open(async (err) => {
//       if (err) return resolve({ success: false, message: 'لا يمكن الاتصال بالطابعة' });

//       if (imageDataUrl) {
//         let tempFilePath = null;
//         try {
//           // Decode base64
//           const base64 = imageDataUrl.replace(/^data:image\/\w+;base64,/, '');
//           const buffer = Buffer.from(base64, 'base64');

//           console.log('🔄 Processing image for XP-80C printer...');
//           console.log('📏 Original buffer size:', buffer.length, 'bytes');

//           // Convert to monochrome PNG suitable for ESC/POS
//           // XP-80C works well with PNG format
//           tempFilePath = path.join(os.tmpdir(), `print-${Date.now()}.png`);

//           await sharp(buffer)
//             .resize({ width: 384, withoutEnlargement: true }) // XP-80C typical width (384px)
//             .flatten({ background: '#FFFFFF' }) // White background
//             .greyscale() // Convert to grayscale first
//             .normalize() // Normalize contrast
//             .threshold(128) // Convert to black and white
//             .png() // PNG format (escpos.Image.load supports PNG)
//             .toFile(tempFilePath);

//           console.log('✅ Image processed and saved to:', tempFilePath);

//           // Verify file exists
//           if (!fs.existsSync(tempFilePath)) {
//             throw new Error('Failed to create temporary image file');
//           }

//           const stats = fs.statSync(tempFilePath);
//           console.log('📏 Processed file size:', stats.size, 'bytes');

//           // Load image via escpos.Image.load
//           // escpos.Image.load callback pattern: (image, err) or just (image)
//           escpos.Image.load(tempFilePath, function (arg1, arg2) {
//             let image, imageErr;

//             // Determine callback pattern - check if arg1 is Error or Image
//             if (arg1 instanceof Error) {
//               // Error-first: (err, image)
//               imageErr = arg1;
//               image = arg2;
//             } else if (arg2 instanceof Error) {
//               // Image-first: (image, err)
//               image = arg1;
//               imageErr = arg2;
//             } else if (arg1 && (arg1.pixels || arg1.data || arg1.width || arg1.constructor?.name?.includes('Image'))) {
//               // arg1 is Image object, arg2 might be error or undefined
//               image = arg1;
//               imageErr = arg2 instanceof Error ? arg2 : null;
//             } else {
//               // Unknown pattern, assume arg1 is image
//               image = arg1;
//               imageErr = null;
//             }

//             // Check for error
//             if (imageErr) {
//               console.error('❌ Cannot load image:', imageErr);
//               // Clean up temp file
//               try { fs.unlinkSync(tempFilePath); } catch(e){}
//               device.close();
//               return resolve({ success: false, message: 'فشل تحميل الصورة: ' + imageErr.message });
//             }

//             // Check if image is valid
//             if (!image) {
//               console.error('❌ Image is null or undefined');
//               // Clean up temp file
//               try { fs.unlinkSync(tempFilePath); } catch(e){}
//               device.close();
//               return resolve({ success: false, message: 'فشل تحميل الصورة: الصورة فارغة' });
//             }

//             // Verify it's a valid escpos.Image object
//             const isImageValid = image && (
//               image.constructor?.name === 'Image' ||
//               image.constructor?.name?.includes('Image') ||
//               image.width !== undefined ||
//               image.height !== undefined ||
//               image.pixels !== undefined ||
//               image.data !== undefined
//             );

//             if (!isImageValid) {
//               console.error('❌ Invalid image object - missing required properties');
//               console.error('Image object:', Object.keys(image || {}));
//               // Clean up temp file
//               try { fs.unlinkSync(tempFilePath); } catch(e){}
//               device.close();
//               return resolve({ success: false, message: 'فشل تحميل الصورة: كائن الصورة غير صالح' });
//             }

//             console.log('✅ Image loaded successfully');
//             console.log('📏 Image type:', typeof image);
//             console.log('📏 Image constructor:', image.constructor?.name);
//             console.log('📏 Image has pixels:', !!image.pixels);
//             console.log('📏 Image has data:', !!image.data);

//             try {
//               // Execute print commands
//               printer.align('ct');

//               // Try different image printing methods
//               // Some printers work better with image() or bitImage() instead of raster()
//               let imageSent = false;

//               if (typeof printer.bitImage === 'function'){
//                 console.log('📸 Using bitImage() method');
//                 printer.bitImage(image, 's8');
//                 imageSent = true;
//               } else if (typeof printer.image === 'function') {
//                 // printer.bitImage(image, 's8');
//                 console.log('📸 Using image() method');
//                 printer.image(image);
//                 imageSent = true;
//               } else if (typeof printer.raster === 'function') {
//                 console.log('📸 Using raster() method');
//                 printer.raster(image);
//                 imageSent = true;
//               } else {
//                 console.warn('⚠️ No image printing method available');
//                 console.warn('Available printer methods:', Object.keys(printer).filter(k => typeof printer[k] === 'function'));
//               }

//               if (!imageSent) {
//                 throw new Error('لا توجد طريقة متاحة لطباعة الصورة');
//               }

//               printer.feed(3);
//               printer.cut();

//               // Flush data to ensure it's sent
//               if (typeof device.flush === 'function') {
//                 console.log('🔄 Flushing printer buffer...');
//                 device.flush();
//               }

//               // Wait before closing to ensure data is sent to network printer
//               setTimeout(() => {
//                 console.log('⏳ Closing device connection...');
//                 device.close(function (closeErr) {
//                   // Delete temp file
//                   try { fs.unlinkSync(tempFilePath); } catch(e){}

//                   if (closeErr) {
//                     console.warn('⚠️ Error closing device:', closeErr);
//                   } else {
//                     console.log('✅ Device closed successfully');
//                   }

//                   console.log('🟢 Image print done');
//                   resolve({ success: true, message: 'تم طباعة الصورة بنجاح' });
//                 });
//               }, 1500); // Increased delay for network printer
//             } catch (printErr) {
//               console.error('❌ Print error:', printErr);
//               // Clean up temp file
//               try { fs.unlinkSync(tempFilePath); } catch(e){}
//               device.close();
//               resolve({ success: false, message: 'فشل في الطباعة: ' + printErr.message });
//             }
//           });
//         } catch (convErr) {
//           console.error('❌ Image processing error:', convErr);
//           console.error('❌ Error stack:', convErr.stack);

//           // Clean up temp file if it was created
//           if (tempFilePath) {
//             try {
//               if (fs.existsSync(tempFilePath)) {
//                 fs.unlinkSync(tempFilePath);
//               }
//             } catch(e) {
//               console.warn('⚠️ Could not delete temp file:', e);
//             }
//           }

//           device.close();
//           resolve({
//             success: false,
//             message: 'خطأ أثناء تجهيز الصورة للطباعة: ' + (convErr.message || convErr.toString())
//           });
//         }
//         return;
//       }

//       // Print default text if no image
//       printer.align('ct');
//       printer.text('Hello Printer');
//       printer.feed(2);
//       printer.cut();

//       setTimeout(() => {
//         device.close(function (closeErr) {
//           if (closeErr) {
//             console.warn('⚠️ Error closing device:', closeErr);
//           }
//           resolve({ success: true, message: 'تم طباعة نص تجريبي' });
//         });
//       }, 200);
//     });
//   });
// });

//dalia


// ipcMain.handle('test-printer-connection', async (event, ip, port = 9100, imageDataUrl = null) => {
//   return new Promise((resolve) => {
//     let tempFilePath = null;

//     try {
//       if (imageDataUrl) {
//         console.log(`🔍 Testing printer connection and printing image: ${ip}:${port}`);
//       } else {
//         console.log(`🔍 Testing printer connection and printing "hello": ${ip}:${port}`);
//       }

//       const device = new escpos.Network(ip, port);
//       const printer = new escpos.Printer(device);

//       device.open(function (error) {
//         if (error) {
//           console.error('❌ Printer connection test failed:', error.message);
//           resolve({
//             success: false,
//             error: error.message || 'فشل الاتصال بالطابعة',
//             message: `لا يمكن الاتصال بالطابعة على ${ip}:${port}`
//           });
//           return;
//         }

//         console.log('✅ Printer connection successful');

//         // If image is provided, print image; otherwise print "hello"
//         if (imageDataUrl) {
//           // Print image
//           try {
//             // Extract base64 data
//             const base64Data = imageDataUrl.replace(/^data:image\/\w+;base64,/, '');
//             const buffer = Buffer.from(base64Data, 'base64');

//             // Create temporary file
//             const tempDir = os.tmpdir();
//             tempFilePath = path.join(tempDir, `print-image-${Date.now()}.png`);
//             fs.writeFileSync(tempFilePath, buffer);
//             console.log(`✅ Image saved to temporary file: ${tempFilePath}`);

//             // Load and print image
//             // escpos.Image.load(tempFilePath, function (arg1, arg2) {
//             //   let image, err;

//             //   // Determine callback pattern
//             //   if (arg1 instanceof Error || (typeof arg1 === 'object' && arg1 !== null && !arg1.constructor?.name?.includes('Image'))) {
//             //     err = arg1;
//             //     image = arg2;
//             //   } else {
//             //     image = arg1;
//             //     err = arg2;
//             //   }

//             //   if (err || !image) {
//             //     // Clean up temp file
//             //     try { fs.unlinkSync(tempFilePath); } catch (e) {}
//             //     device.close();
//             //     resolve({
//             //       success: false,
//             //       error: err?.message || 'فشل في تحميل الصورة',
//             //       message: 'فشل في طباعة الصورة'
//             //     });
//             //     return;
//             //   }

//             //   console.log('✅ Image loaded, printing...');

//             //   // Print image
//             //   printer.align('ct');
//             //   if (typeof printer.bitImage === 'function') {
//             //     printer.bitImage(image, 's8');
//             //   } else if (typeof printer.image === 'function') {
//             //     printer.image(image);
//             //   }
//             //   printer.feed(1);
//             //   printer.feed(2);
//             //   printer.cut();

//             //   setTimeout(() => {
//             //     device.close(function (closeErr) {
//             //       // Clean up temp file
//             //       try { fs.unlinkSync(tempFilePath); } catch (e) {}
//             //       if (closeErr) {
//             //         console.warn('⚠️ Error closing connection:', closeErr.message);
//             //       }
//             //       console.log('✅ Image print completed successfully');
//             //       resolve({
//             //         success: true,
//             //         message: `تم الاتصال بالطابعة وطباعة الصورة بنجاح على ${ip}:${port}`
//             //       });
//             //     });
//             //   }, 200);
//             // });
//             escpos.Image.load(tempFilePath, function (arg1, arg2) {
//               let image, err;

//               if (arg1 instanceof Error) {
//                 err = arg1;
//                 image = arg2;
//               } else {
//                 image = arg1;
//                 err = arg2;
//               }

//               if (err || !image) {
//                 try { fs.unlinkSync(tempFilePath); } catch (e) {}
//                 device.close();
//                 resolve({
//                   success: false,
//                   error: err?.message || 'فشل تحميل الصورة',
//                   message: 'فشل في طباعة الصورة'
//                 });
//                 return;
//               }

//               console.log('🖼 Image loaded');

//               try {
//                 printer.align('ct');
//                 printer.raster(image);     // <<< الحل السحري
//                 printer.feed(2);
//                 printer.cut();
//               } catch (e) {
//                 console.log("❌ Printer raster error:", e);
//               }

//               setTimeout(() => {
//                 device.close(() => {
//                   try { fs.unlinkSync(tempFilePath); } catch (e) {}
//                   resolve({ success: true, message: "تم الطباعة بنجاح" });
//                 });
//               }, 300);
//             });

//           } catch (imgErr) {
//             // Clean up temp file
//             if (tempFilePath) {
//               try { fs.unlinkSync(tempFilePath); } catch (e) {}
//             }
//             device.close();
//             resolve({
//               success: false,
//               error: imgErr.message || 'خطأ في معالجة الصورة',
//               message: 'فشل في طباعة الصورة'
//             });
//           }
//         } else {
//           // Print "hello" text
//           try {
//             printer.align('ct');
//             printer.text('hello');
//             printer.feed(2);
//             printer.cut();

//             setTimeout(() => {
//               device.close(function (closeErr) {
//                 if (closeErr) {
//                   console.warn('⚠️ Error closing connection:', closeErr.message);
//                 } else {
//                   console.log('✅ Device closed successfully');
//                 }
//                 console.log('✅ Print "hello" completed successfully');
//                 resolve({
//                   success: true,
//                   message: `تم الاتصال بالطابعة وطباعة "hello" بنجاح على ${ip}:${port}`
//                 });
//               });
//             }, 100);
//           } catch (printErr) {
//             const errorMsg = `خطأ في الطباعة: ${printErr.message || printErr}`;
//             console.error('❌ Print error:', errorMsg);
//             try {
//               device.close();
//             } catch (closeErr) {
//               console.error('Error closing device:', closeErr);
//             }
//             resolve({
//               success: false,
//               error: errorMsg,
//               message: `فشل في طباعة "hello": ${errorMsg}`
//             });
//           }
//         }
//       });
//     } catch (err) {
//       // Clean up temp file if exists
//       if (tempFilePath) {
//         try { fs.unlinkSync(tempFilePath); } catch (e) {}
//       }
//       console.error('❌ Printer connection test error:', err);
//       resolve({
//         success: false,
//         error: err.message || 'خطأ في اختبار الاتصال',
//         message: `خطأ في اختبار الاتصال: ${err.message}`
//       });
//     }
//   });
// });

// ⬅️ Print text to network printer
// ipcMain.handle('print-to-network', async (event, text, ip, port = 9100) => {
//   return new Promise((resolve) => {
//     try {
//       console.log(`🖨️ Print text request: ip=${ip}, port=${port}, text="${text}"`);

//       const device = new escpos.Network(ip, port);
//       const printer = new escpos.Printer(device);

//       device.open(function (error) {
//         if (error) {
//           const errorMsg = `فشل الاتصال بالطابعة: ${error.message || error}`;
//           console.error('❌ Device open error:', errorMsg);
//           resolve({ success: false, error: errorMsg });
//           return;
//         }

//         console.log('✅ Connected to printer, printing text...');

//         try {
//           // Print text with alignment and formatting
//           // In escpos v3, we need to use the callback pattern or execute commands synchronously
//           printer.align('ct');
//           printer.text(text);
//           printer.feed(2);
//           printer.cut();

//           // Close device to flush and send all commands
//           device.close(function (closeErr) {
//             if (closeErr) {
//               console.error('⚠️ Error closing device:', closeErr);
//             } else {
//               console.log('✅ Device closed successfully');
//             }
//             console.log('✅ Text print completed successfully');
//             resolve({ success: true, bytesSent: text.length });
//           });
//         } catch (err) {
//           const errorMsg = `خطأ في معالجة النص: ${err.message || err}`;
//           console.error('❌ Text processing error:', errorMsg);
//           try {
//             device.close();
//           } catch (closeErr) {
//             console.error('Error closing device:', closeErr);
//           }
//           resolve({ success: false, error: errorMsg });
//         }
//       });
//     } catch (err) {
//       const errorMsg = `خطأ عام في الطباعة: ${err.message || err}`;
//       console.error('❌ Error in print-to-network:', errorMsg);
//       resolve({ success: false, error: errorMsg });
//     }
//   });
// });

// استلام طلب الطباعة من Angular (printImage - legacy)
// ipcMain.handle('printImage', async (event, base64, ip, port = 9100) => {
//   return new Promise((resolve, reject) => {
//     try {
//       const device = new escpos.Network(ip, port);
//       const printer = new escpos.Printer(device);

//       // تحويل Base64 إلى Buffer
//       const buffer = Buffer.from(base64.replace(/^data:image\/png;base64,/, ''), 'base64');

//       device.open(function () {
//         escpos.Image.load(buffer, function (image) {
//           printer.align('ct').image(image).then(() => {
//             printer.cut().close();
//             resolve({ success: true });
//           }).catch(err => {
//             console.error(err);
//             reject({ success: false, error: err.message });
//           });
//         });
//       });
//     } catch (err) {
//       console.error(err);
//       reject({ success: false, error: err.message });
//     }
//   });
// });

// استلام طلب الطباعة من Angular (print-image-to-network - new)
// ipcMain.handle('print-image-to-network', async (event, imageDataUrl, ip, port = 9100) => {
//   return new Promise((resolve, reject) => {
//     let tempFilePath = null; // Declare at function scope for cleanup

//     try {
//       console.log(`🖨️ Print image request: ip=${ip}, port=${port}`);

//       if (!imageDataUrl) {
//         const errorMsg = 'لم يتم توفير بيانات الصورة';
//         console.error('❌', errorMsg);
//         resolve({ success: false, error: errorMsg });
//         return;
//       }

//       // تحويل Base64 إلى Buffer (مع أو بدون data URL prefix)
//       let base64Data;
//       try {
//         base64Data = imageDataUrl.replace(/^data:image\/\w+;base64,/, '');
//         if (!base64Data || base64Data.length === 0) {
//           throw new Error('بيانات الصورة فارغة');
//         }
//       } catch (err) {
//         const errorMsg = `خطأ في تحويل بيانات الصورة: ${err.message || err}`;
//         console.error('❌', errorMsg);
//         resolve({ success: false, error: errorMsg });
//         return;
//       }

//       let buffer;
//       try {
//         buffer = Buffer.from(base64Data, 'base64');
//         if (!buffer || buffer.length === 0) {
//           throw new Error('فشل في إنشاء buffer من بيانات الصورة');
//         }
//         console.log(`✅ Image buffer created: ${buffer.length} bytes`);

//         // Detect image format from buffer
//         const isPNG = buffer[0] === 0x89 && buffer[1] === 0x50 && buffer[2] === 0x4E && buffer[3] === 0x47;
//         const isJPEG = buffer[0] === 0xFF && buffer[1] === 0xD8 && buffer[2] === 0xFF;
//         const imageFormat = isPNG ? 'png' : (isJPEG ? 'jpg' : 'png');
//         console.log(`📷 Detected image format: ${imageFormat}`);
//       } catch (err) {
//         const errorMsg = `خطأ في إنشاء buffer: ${err.message || err}`;
//         console.error('❌', errorMsg);
//         resolve({ success: false, error: errorMsg });
//         return;
//       }

//       // Create temporary file path
//       const tempDir = os.tmpdir();
//       tempFilePath = path.join(tempDir, `print-image-${Date.now()}.png`);

//       // Write buffer to temporary file
//       try {
//         fs.writeFileSync(tempFilePath, buffer);
//         console.log(`✅ Image saved to temporary file: ${tempFilePath}`);
//       } catch (writeErr) {
//         const errorMsg = `فشل في حفظ الصورة في ملف مؤقت: ${writeErr.message || writeErr}`;
//         console.error('❌', errorMsg);
//         resolve({ success: false, error: errorMsg });
//         return;
//       }

//       const device = new escpos.Network(ip, port);
//       const printer = new escpos.Printer(device);

//       device.open(function (error) {
//         if (error) {
//           // Clean up temp file
//           try { fs.unlinkSync(tempFilePath); } catch (e) { }
//           const errorMsg = `فشل الاتصال بالطابعة: ${error.message || error}`;
//           console.error('❌ Device open error:', errorMsg);
//           resolve({ success: false, error: errorMsg });
//           return;
//         }

//         console.log('✅ Connected to printer, loading image from file...');

//         try {
//           // escpos.Image.load can accept file path - this is more reliable than buffer
//           escpos.Image.load(tempFilePath, function (arg1, arg2) {
//             let image, err;

//             // Determine callback pattern: error-first (err, image) or image-first (image, err)
//             if (arg1 instanceof Error || (typeof arg1 === 'object' && arg1 !== null && !arg1.constructor?.name?.includes('Image'))) {
//               // Error-first pattern: (err, image)
//               err = arg1;
//               image = arg2;
//             } else {
//               // Image-first pattern: (image, err)
//               image = arg1;
//               err = arg2;
//             }

//             // Handle error
//             if (err) {
//               // Clean up temp file
//               try { fs.unlinkSync(tempFilePath); } catch (e) { }
//               const errorMsg = `خطأ في تحميل الصورة: ${err.message || err}`;
//               console.error('❌ Image load error:', errorMsg);
//               device.close();
//               resolve({ success: false, error: errorMsg });
//               return;
//             }

//             // Check if image is valid
//             if (!image) {
//               // Clean up temp file
//               try { fs.unlinkSync(tempFilePath); } catch (e) { }
//               const errorMsg = 'فشل في تحميل الصورة (الصورة غير صالحة)';
//               console.error('❌', errorMsg);
//               device.close();
//               resolve({ success: false, error: errorMsg });
//               return;
//             }

//             console.log('✅ Image loaded, type:', typeof image);
//             console.log('✅ Image constructor:', image.constructor?.name);
//             console.log('✅ Image keys:', Object.keys(image || {}));

//             // Verify image is a valid escpos.Image instance
//             // In escpos v3, Image might have different structure
//             const isImageValid = image && (
//               image.constructor?.name === 'Image' ||
//               image.constructor?.name?.includes('Image') ||
//               typeof image.toBitmap === 'function' ||
//               typeof image.toRaster === 'function' ||
//               image.width !== undefined ||
//               image.height !== undefined
//             );

//             if (!isImageValid) {
//               // Clean up temp file
//               try { fs.unlinkSync(tempFilePath); } catch (e) { }
//               const errorMsg = 'الصورة المحملة ليست من نوع escpos.Image صالح';
//               console.error('❌', errorMsg, 'Image object:', image);
//               device.close();
//               resolve({ success: false, error: errorMsg });
//               return;
//             }

//             console.log('✅ Image loaded, printing...');

//             // Try to print using bitImage first (matching PHP: $printer->bitImage($logo))
//             // If bitImage is not available, use image() method
//             // Chain all commands together like the old code does
//             let printChain;

//             if (typeof printer.bitImage === 'function') {
//               console.log('📸 Using bitImage method');
//               // Chain: align -> bitImage -> feed -> feed -> cut -> close
//               printChain = printer
//                 .align('ct')
//                 .bitImage(image, 's8')
//                 .feed(1)
//                 .feed(2)
//                 .cut();
//             } else if (typeof printer.image === 'function') {
//               console.log('📸 Using image method');
//               // Chain: align -> image -> feed -> feed -> cut -> close
//               printChain = printer
//                 .align('ct')
//                 .image(image)
//                 .feed(1)
//                 .feed(2)
//                 .cut();
//             } else {
//               // Clean up temp file
//               try { fs.unlinkSync(tempFilePath); } catch (e) { }
//               device.close();
//               resolve({ success: false, error: 'لا توجد طريقة متاحة لطباعة الصورة' });
//               return;
//             }

//             // Execute the chain and handle promise
//             printChain.then(() => {
//               console.log('✅ Print commands executed, closing device...');

//               // Close device to flush and send all commands
//               device.close(function (closeErr) {
//                 if (closeErr) {
//                   console.error('⚠️ Error closing device:', closeErr);
//                 } else {
//                   console.log('✅ Device closed successfully');
//                 }

//                 // Clean up temp file after successful print
//                 try {
//                   fs.unlinkSync(tempFilePath);
//                   console.log('✅ Temporary file cleaned up');
//                 } catch (unlinkErr) {
//                   console.warn('⚠️ Failed to delete temp file:', unlinkErr.message);
//                 }

//                 console.log('✅ Print completed successfully');
//                 resolve({ success: true });
//               });
//             }).catch(err => {
//               // Clean up temp file on error
//               try { fs.unlinkSync(tempFilePath); } catch (e) { }
//               const errorMsg = `خطأ في الطباعة: ${err.message || err}`;
//               console.error('❌ Print error:', errorMsg);
//               console.error('❌ Error details:', err);
//               try {
//                 device.close();
//               } catch (closeErr) {
//                 console.error('Error closing device:', closeErr);
//               }
//               resolve({ success: false, error: errorMsg });
//             });
//           });
//         } catch (err) {
//           // Clean up temp file on error
//           try { fs.unlinkSync(tempFilePath); } catch (e) { }
//           const errorMsg = `خطأ في معالجة الصورة: ${err.message || err}`;
//           console.error('❌ Image processing error:', errorMsg);
//           try {
//             device.close();
//           } catch (closeErr) {
//             console.error('Error closing device:', closeErr);
//           }
//           resolve({ success: false, error: errorMsg });
//         }
//       });
//     } catch (err) {
//       // Clean up temp file if it exists
//       if (tempFilePath) {
//         try { fs.unlinkSync(tempFilePath); } catch (e) { }
//       }
//       const errorMsg = `خطأ عام في الطباعة: ${err.message || err}`;
//       console.error('❌ Error in print-image-to-network:', errorMsg);
//       resolve({ success: false, error: errorMsg });
//     }
//   });
// });


// ⬅️ IPC handler for silent printing to specific printer
// ipcMain.handle('print-to-printer', async (event, { htmlContent, printerName, silent = true, ...options }) => {
//   try {
//     // Create a hidden window for printing
//     const printWindow = new BrowserWindow({
//       show: false,
//       width: 800,
//       height: 600,
//       webPreferences: {
//         nodeIntegration: true,
//         contextIsolation: false
//       }
//     });

//     // Load HTML content
//     await printWindow.loadURL(`data:text/html;charset=utf-8,${encodeURIComponent(htmlContent)}`);

//     // Wait for content to load
//     await new Promise(resolve => setTimeout(resolve, 500));

//     // Print silently to specific printer (Electron will use the printer name directly)
//     return new Promise((resolve) => {
//       printWindow.webContents.print({
//         silent: silent,
//         printBackground: true,
//         deviceName: printerName,
//         ...options
//       }, (success, failureReason) => {
//         if (success) {
//           console.log(`✅ Successfully printed to ${printerName}`);
//         } else {
//           console.error(`❌ Print failed on ${printerName}:`, failureReason);
//         }

//         // Close the hidden window after printing
//         setTimeout(() => {
//           printWindow.close();
//         }, 1000);

//         resolve({
//           success: success,
//           error: success ? null : failureReason
//         });
//       });
//     });
//   } catch (error) {
//     console.error('❌ Error in print-to-printer:', error);
//     return { success: false, error: error.message };
//   }
// });

// ⬅️ Create BrowserWindow
async function createWindow() {
  const fullPath = path.join(__dirname, 'dist/cashier/index.html');
  console.log('📂 Full path to index.html:', fullPath);

  // Get preload script path - works in both dev and production
  const preloadPath = path.join(__dirname, 'preload.js');
  console.log('📂 Preload script path:', preloadPath);

  // Verify preload file exists
  if (!fs.existsSync(preloadPath)) {
    console.error('❌ Preload script not found at:', preloadPath);
  } else {
    console.log('✅ Preload script found');
  }

  mainWindow = new BrowserWindow({
    width: 1500,
    height: 1400,
    webPreferences: {
      preload: preloadPath,
      contextIsolation: true,
      nodeIntegration: true,
    }
  });

  // ✅ Build safe file:// URL
  const indexUrl = url.format({
    pathname: fullPath,
    protocol: 'file:',
    slashes: true,
  });

  console.log('📂 Loading:', indexUrl);
  await mainWindow.loadURL(indexUrl);

  // mainWindow.webContents.on('did-finish-load', () => {
  //   console.log('✅ Loaded:', mainWindow.webContents.getURL());
  // });

  mainWindow.webContents.on('did-fail-load', () => {
    console.log('⚠️ Reload failed, forcing index.html');
    const fullPath = path.join(__dirname, 'dist/cashier/index.html');
    const indexUrl = url.format({
      pathname: fullPath,
      protocol: 'file:',
      slashes: true,
    });
    mainWindow.loadURL(indexUrl + '#/home');
  });


  mainWindow.webContents.on('did-navigate', (event, url) => {
    console.log('📂 Navigated to:', url);
  });

  mainWindow.webContents.on('did-navigate-in-page', (event, url) => {
    console.log('📂 In-page navigation:', url);
  });

  // Clear cache every time
  await mainWindow.webContents.session.clearCache();
  console.log('✅ Cache cleared');

  // mainWindow.webContents.openDevTools();

  mainWindow.on('closed', () => {
    mainWindow = null;
  });
}

// ⬅️ Logout function
function logoutUser() {
  if (mainWindow) {
    mainWindow.webContents.executeJavaScript(`
      localStorage.clear();
      sessionStorage.clear();
      location.reload();
    `);
  }
}

// ⬅️ App lifecycle
app.on('ready', async () => {
  await createWindow();

  // Optional: auto logout test
  setTimeout(() => {
    console.log('Logging out user...');
    logoutUser();
  }, 1000);
});

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') app.quit();
});

app.on('activate', () => {
  if (!mainWindow) createWindow();
});



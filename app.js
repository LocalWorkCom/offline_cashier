const { app, BrowserWindow, ipcMain } = require('electron');
const path = require('path');
const url = require('url');
const si = require('systeminformation');
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

// استلام طلب الطباعة من Angular (printImage - legacy)
ipcMain.handle('printImage', async (event, base64, ip, port = 9100) => {
  return new Promise((resolve, reject) => {
    try {
      const device = new escpos.Network(ip, port);
      const printer = new escpos.Printer(device);

      // تحويل Base64 إلى Buffer
      const buffer = Buffer.from(base64.replace(/^data:image\/png;base64,/, ''), 'base64');

      device.open(function () {
        escpos.Image.load(buffer, function (image) {
          printer.align('ct').image(image).then(() => {
            printer.cut().close();
            resolve({ success: true });
          }).catch(err => {
            console.error(err);
            reject({ success: false, error: err.message });
          });
        });
      });
    } catch (err) {
      console.error(err);
      reject({ success: false, error: err.message });
    }
  });
});

// استلام طلب الطباعة من Angular (print-image-to-network - new)
ipcMain.handle('print-image-to-network', async (event, imageDataUrl, ip, port = 9100) => {
  return new Promise((resolve, reject) => {
    try {
      console.log(`🖨️ Print image request: ip=${ip}, port=${port}`);

      if (!imageDataUrl) {
        const errorMsg = 'لم يتم توفير بيانات الصورة';
        console.error('❌', errorMsg);
        resolve({ success: false, error: errorMsg });
        return;
      }

      // تحويل Base64 إلى Buffer (مع أو بدون data URL prefix)
      let base64Data;
      try {
        base64Data = imageDataUrl.replace(/^data:image\/\w+;base64,/, '');
        if (!base64Data || base64Data.length === 0) {
          throw new Error('بيانات الصورة فارغة');
        }
      } catch (err) {
        const errorMsg = `خطأ في تحويل بيانات الصورة: ${err.message || err}`;
        console.error('❌', errorMsg);
        resolve({ success: false, error: errorMsg });
        return;
      }

      let buffer;
      try {
        buffer = Buffer.from(base64Data, 'base64');
        if (!buffer || buffer.length === 0) {
          throw new Error('فشل في إنشاء buffer من بيانات الصورة');
        }
        console.log(`✅ Image buffer created: ${buffer.length} bytes`);
      } catch (err) {
        const errorMsg = `خطأ في إنشاء buffer: ${err.message || err}`;
        console.error('❌', errorMsg);
        resolve({ success: false, error: errorMsg });
        return;
      }

      const device = new escpos.Network(ip, port);
      const printer = new escpos.Printer(device);

      device.open(function (error) {
        if (error) {
          const errorMsg = `فشل الاتصال بالطابعة: ${error.message || error}`;
          console.error('❌ Device open error:', errorMsg);
          resolve({ success: false, error: errorMsg });
          return;
        }

        console.log('✅ Connected to printer, loading image...');

        try {
          escpos.Image.load(buffer, function (image, err) {
            if (err) {
              const errorMsg = `خطأ في تحميل الصورة: ${err.message || err}`;
              console.error('❌ Image load error:', errorMsg);
              device.close();
              resolve({ success: false, error: errorMsg });
              return;
            }

            if (!image) {
              const errorMsg = 'فشل في تحميل الصورة (الصورة غير صالحة)';
              console.error('❌', errorMsg);
              device.close();
              resolve({ success: false, error: errorMsg });
              return;
            }

            console.log('✅ Image loaded, printing...');
            printer.align('ct').image(image).then(() => {
              console.log('✅ Image printed, cutting...');
              printer.cut();
              device.close();
              console.log('✅ Print completed successfully');
              resolve({ success: true });
            }).catch(err => {
              const errorMsg = `خطأ في الطباعة: ${err.message || err}`;
              console.error('❌ Print error:', errorMsg);
              try {
                device.close();
              } catch (closeErr) {
                console.error('Error closing device:', closeErr);
              }
              resolve({ success: false, error: errorMsg });
            });
          });
        } catch (err) {
          const errorMsg = `خطأ في معالجة الصورة: ${err.message || err}`;
          console.error('❌ Image processing error:', errorMsg);
          try {
            device.close();
          } catch (closeErr) {
            console.error('Error closing device:', closeErr);
          }
          resolve({ success: false, error: errorMsg });
        }
      });
    } catch (err) {
      const errorMsg = `خطأ عام في الطباعة: ${err.message || err}`;
      console.error('❌ Error in print-image-to-network:', errorMsg);
      resolve({ success: false, error: errorMsg });
    }
  });
});

// ⬅️ Create BrowserWindow
async function createWindow() {
  const fullPath = path.join(__dirname, 'dist/cashier/index.html');
  console.log('📂 Full path to index.html:', fullPath);

  mainWindow = new BrowserWindow({
    width: 1500,
    height: 1400,
    webPreferences: {
      preload: path.join(__dirname, 'preload.js'),
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

  mainWindow.webContents.openDevTools();

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



const { app, BrowserWindow, ipcMain } = require('electron');
const path = require('path');
const url = require('url');
const si = require('systeminformation');

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



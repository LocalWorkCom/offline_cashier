// import { contextBridge, ipcRenderer } from 'electron';
const { contextBridge, ipcRenderer } = require('electron');

contextBridge.exposeInMainWorld('deviceAPI', {
  getSystemInfo: async () => {
    return await ipcRenderer.invoke('get-system-info');
  },
  printToNetwork: async (text, ip, port) => {
    return await ipcRenderer.invoke('print-to-network', text, ip, port);
  },
  printImageToNetwork: async (imageData, ip, port) => {
    return await ipcRenderer.invoke('print-image-to-network', imageData, ip, port);
  },
  testPrinterConnection: async (ip, port) => {
    return await ipcRenderer.invoke('test-printer-connection', ip, port);
  }
});





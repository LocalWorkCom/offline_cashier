// import { contextBridge, ipcRenderer } from 'electron';
const { contextBridge, ipcRenderer } = require('electron');

contextBridge.exposeInMainWorld('deviceAPI', {
  getSystemInfo: async () => {
    return await ipcRenderer.invoke('get-system-info');
  }

});





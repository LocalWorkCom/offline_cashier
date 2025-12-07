interface DeviceAPI {
  getSystemInfo: () => Promise<any>;
  printToNetwork: (text: string, ip: string, port?: number) => Promise<{ success: boolean; bytesSent?: number; error?: string }>;
  printImageToNetwork: (imageData: string, ip: string, port?: number) => Promise<{ success: boolean; error?: string }>;
  testPrinterConnection: (ip: string, port?: number) => Promise<{ success: boolean; error?: string; message?: string }>;
}

declare global {
  interface Window {
    deviceAPI?: DeviceAPI;
  }
}

export {};


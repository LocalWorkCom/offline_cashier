import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class SysteminfoService {

  // constructor() { }
  async getSystemInfo(): Promise<any> {
    try {
      const info = await (window as any).deviceAPI.getSystemInfo();
      console.log("✅ System info:", info);

      localStorage.setItem('systemInfo', JSON.stringify(info));
      return info;
    } catch (err) {
      console.error("❌ Failed to get system info:", err);
      return null;
    }
  }

}

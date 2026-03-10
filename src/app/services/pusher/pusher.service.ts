import { Injectable } from '@angular/core';
import { environment } from '../../environment';

@Injectable({
  providedIn: 'root',
})
export class PusherService {
  private socket: WebSocket | null = null;
  private subscriptions: Map<string, ((data: any) => void)[]> = new Map();
  private isConnected = false;

  constructor() {}

  connect(): void {
    if (this.socket && (this.socket.readyState === WebSocket.OPEN || this.socket.readyState === WebSocket.CONNECTING)) {
      return;
    }

    // Connect to the Electron WebSocket Server
    const url = `${environment.wsUrl}?key=${environment.pusher.key}`;
    this.socket = new WebSocket(url);

    this.socket.onopen = () => {
      console.log('✅ Connected to Electron WebSocket Hub');
      this.isConnected = true;
    };

    this.socket.onmessage = (event) => {
      try {
        const payload = JSON.parse(event.data);
        const { channel, event: eventName, data } = payload;

        if (channel && eventName) {
          const key = `${channel}:${eventName}`;
          const callbacks = this.subscriptions.get(key);
          if (callbacks) {
            console.log(`[Socket] Event received: ${key}`, data);
            callbacks.forEach(cb => cb(data));
          }
        } else if (payload.type === 'system') {
          console.log('[Socket System]', payload.message);
        }
      } catch (e) {
        console.warn('[Socket] Non-JSON message received:', event.data);
      }
    };

    this.socket.onclose = () => {
      console.log('❌ Disconnected from Electron WebSocket Hub. Retrying in 3s...');
      this.isConnected = false;
      setTimeout(() => this.connect(), 3000);
    };

    this.socket.onerror = (error) => {
      console.error('[Socket] Error:', error);
    };
  }

  subscribe(
    channelName: string,
    eventName: string,
    callback: (data: any) => void
  ): void {
    const key = `${channelName}:${eventName}`;
    if (!this.subscriptions.has(key)) {
      this.subscriptions.set(key, []);
    }
    this.subscriptions.get(key)?.push(callback);
    
    console.log(`[Socket] Subscribed locally to: ${key}`);

    // Notify the Electron server so you can see it in the terminal logs
    if (this.socket && this.socket.readyState === WebSocket.OPEN) {
      this.socket.send(JSON.stringify({
        type: 'subscribe',
        channel: channelName,
        event: eventName
      }));
    }
  }


  subscribeToPrivateChannel(
    channelName: string,
    eventName: string,
    endPoint: string,
    callback: (data: any) => void
  ) {
    // For local Electron WebSocket, we treat private channels same as public for now
    // In a full implementation, you'd send an auth request to Laravel first
    this.subscribe(channelName, eventName, callback);
  }

  unsubscribe(channelName: string): void {
    // Remove all subscriptions for this channel
    for (const key of this.subscriptions.keys()) {
      if (key.startsWith(`${channelName}:`)) {
        this.subscriptions.delete(key);
      }
    }
    console.log(`[Socket] Unsubscribed from channel: ${channelName}`);
  }
}

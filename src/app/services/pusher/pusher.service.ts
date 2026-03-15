import { Injectable } from '@angular/core';
import { environment } from '../../environment';
import { io, Socket } from 'socket.io-client';

@Injectable({
  providedIn: 'root',
})
export class PusherService {
  private socket: Socket | null = null;
  private subscriptions: Map<string, ((data: any) => void)[]> = new Map();
  private isConnected = false;

  constructor() {}

  connect(): void {
    if (this.socket && this.socket.connected) {
      return;
    }

    // Connect to the Electron Socket.io Server
    // Socket.io automatically appends /socket.io/ and handles the handshake
    const url = environment.wsUrl.replace('ws://', 'http://').replace('wss://', 'https://');
    
    this.socket = io(url, {
      transports: ['polling', 'websocket'],
      withCredentials: true,
      query: {
        key: environment.pusher.key
      }
    });

    this.socket.on('connect', () => {
      console.log('✅ Connected to Electron Socket.io Hub');
      this.isConnected = true;
    });

    this.socket.on('disconnect', () => {
      console.log('❌ Disconnected from Electron Socket.io Hub');
      this.isConnected = false;
    });

    this.socket.on('connect_error', (error) => {
      console.error('[Socket] Connection Error:', error);
    });

    // Handle incoming events
    // We listen to the catch-all or specific channel:event pattern
    // Based on app.js: io.emit(`${channel}:${event}`, data);
    this.socket.onAny((path, data) => {
       // path usually looks like "channel:event"
       const callbacks = this.subscriptions.get(path);
       if (callbacks) {
         console.log(`[Socket] Event received: ${path}`, data);
         callbacks.forEach(cb => cb(data));
       }
    });
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

    // Notify the Electron server
    if (this.socket && this.socket.connected) {
      this.socket.emit('subscribe', {
        channel: channelName,
        event: eventName
      });
    }
  }

  subscribeToPrivateChannel(
    channelName: string,
    eventName: string,
    endPoint: string,
    callback: (data: any) => void
  ) {
    this.subscribe(channelName, eventName, callback);
  }

  unsubscribe(channelName: string): void {
    for (const key of this.subscriptions.keys()) {
      if (key.startsWith(`${channelName}:`)) {
        this.subscriptions.delete(key);
      }
    }
    console.log(`[Socket] Unsubscribed from channel: ${channelName}`);
  }
}

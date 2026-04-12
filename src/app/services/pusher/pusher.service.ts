import { Injectable } from '@angular/core';
import Pusher from 'pusher-js';
import { io, Socket } from 'socket.io-client';
import { baseUrl, environment, baseUrl2 } from '../../environment';

export interface RealtimeProvider {
  connect(): void;
  subscribe(channelName: string, eventName: string, callback: (data: any) => void): void;
  subscribeToPrivateChannel(
    channelName: string,
    eventName: string,
    endPoint: string,
    callback: (data: any) => void
  ): void;
  unsubscribe(channelName: string): void;
}

class PusherProvider implements RealtimeProvider {
  private pusher: Pusher | null = null;
  private token: string = localStorage.getItem('access_token') || '';

  constructor() {
    Pusher.logToConsole = true;
  }

  connect(): void {
    if (this.pusher) return;
    this.pusher = new Pusher(environment.pusher.key, {
      cluster: environment.pusher.cluster,
      authEndpoint: `${baseUrl2}/broadcasting/auth`,
      auth: {
        headers: {
          Authorization: `Bearer ${this.token}`,
          Accept: 'application/json',
          'Content-Type': 'application/json',
        },
      },
    });
  }

  subscribe(channelName: string, eventName: string, callback: (data: any) => void): void {
    if (!this.pusher) {
      this.connect();
    }
    const channel = this.pusher!.subscribe(channelName);
    channel.bind(eventName, callback);
  }

  subscribeToPrivateChannel(
    channelName: string,
    eventName: string,
    endPoint: string,
    callback: (data: any) => void
  ): void {
    if (!this.pusher) {
      this.connect();
    }

    // Manual authentication as per old code
    fetch(`${baseUrl2}${endPoint}`, {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        socket_id: this.pusher?.connection.socket_id,
        channel_name: channelName,
      }),
    })
      .then((response) => response.json())
      .then((authData) => {
        if (!this.pusher) return;
        const channel = this.pusher.subscribe(channelName);
        channel.bind(eventName, callback);
      })
      .catch((error) => {
        console.error('Error authenticating private channel:', error);
      });
  }

  unsubscribe(channelName: string): void {
    if (this.pusher) {
      this.pusher.unsubscribe(channelName);
    }
  }
}

class SocketProvider implements RealtimeProvider {
  private socket: Socket | null = null;
  private subscriptions: Map<string, ((data: any) => void)[]> = new Map();

  connect(): void {
    // Bug fix: also guard against a socket that is currently connecting (not yet connected)
    if (this.socket) return;

    const wsUrl = (environment as any).wsUrl || 'ws://127.0.0.1:8081';
    const url = wsUrl.replace('ws://', 'http://').replace('wss://', 'https://');

    this.socket = io(url, {
      transports: ['polling', 'websocket'],
      withCredentials: true,
      query: {
        key: environment.pusher.key,
      },
    });

    this.socket.on('connect', () => {
      console.log('✅ Connected to Socket.io Hub');
      // Resubscribe all existing subscriptions on reconnect
      this.subscriptions.forEach((callbacks, path) => {
        // Bug fix: split on first ':' only to handle event names that contain ':'
        const separatorIndex = path.indexOf(':');
        const channel = path.substring(0, separatorIndex);
        const event = path.substring(separatorIndex + 1);
        this.socket?.emit('subscribe', { channel, event });
      });
    });

    this.socket.onAny((eventName, data) => {
      // app.js emits events as "channel:event" strings, which matches our subscription keys exactly
      const callbacks = this.subscriptions.get(eventName);
      if (callbacks) {
        callbacks.forEach((cb) => cb(data));
      }
    });

    this.socket.on('connect_error', (error) => {
      console.error('[Socket] Connection Error:', error);
    });
  }

  subscribe(channelName: string, eventName: string, callback: (data: any) => void): void {
    const key = `${channelName}:${eventName}`;
    if (!this.subscriptions.has(key)) {
      this.subscriptions.set(key, []);
    }
    this.subscriptions.get(key)?.push(callback);

    if (this.socket && this.socket.connected) {
      this.socket.emit('subscribe', {
        channel: channelName,
        event: eventName,
      });
    } else {
      this.connect();
    }
  }

  subscribeToPrivateChannel(
    channelName: string,
    eventName: string,
    endPoint: string,
    callback: (data: any) => void
  ): void {
    // For local socket.io, we might not need separate private auth endpoints 
    // depending on implementation, but we'll treat it like a regular subscribe for now.
    this.subscribe(channelName, eventName, callback);
  }

  unsubscribe(channelName: string): void {
    for (const key of this.subscriptions.keys()) {
      if (key.startsWith(`${channelName}:`)) {
        this.subscriptions.delete(key);
      }
    }
    if (this.socket && this.socket.connected) {
      this.socket.emit('unsubscribe', channelName);
    }
  }
}

@Injectable({
  providedIn: 'root',
})
export class PusherService {
  private provider: RealtimeProvider;

  constructor() {
    const env = environment as any;
    const type = env.broadcast_type || 'pusher';
    console.log('[PusherService] Environment loaded:', JSON.stringify({
      production: env.production,
      broadcast_type: env.broadcast_type,
      hasPusher: !!env.pusher,
      hasWsUrl: !!env.wsUrl
    }));
    console.log(`[PusherService] Initializing as: ${type}`);
    
    if (type === 'socket') {
      console.log('socket driver loaded');
      this.provider = new SocketProvider();
    } else {
      console.log('pusher driver loaded');
      this.provider = new PusherProvider();
    }
  }

  connect(): void {
    this.provider.connect();
  }

  subscribe(channelName: string, eventName: string, callback: (data: any) => void): void {
    this.provider.subscribe(channelName, eventName, callback);
  }

  subscribeToPrivateChannel(
    channelName: string,
    eventName: string,
    endPoint: string,
    callback: (data: any) => void
  ): void {
    this.provider.subscribeToPrivateChannel(channelName, eventName, endPoint, callback);
  }

  unsubscribe(channelName: string): void {
    this.provider.unsubscribe(channelName);
  }
}

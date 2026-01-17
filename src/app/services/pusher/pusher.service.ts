import { Injectable } from '@angular/core';
import Pusher from 'pusher-js';
import Echo from 'laravel-echo';
import { baseUrl, environment } from '../../environment';
import { BasicsConstance } from '../../constants';

// Make Pusher available globally for Laravel Echo
(window as any).Pusher = Pusher;

@Injectable({
  providedIn: 'root',
})
export class PusherService {
  private echo: Echo<any> | null = null;
  private token: string = localStorage.getItem('access_token') || '';

  constructor() {
    // Enable logging for debugging
    Pusher.logToConsole = true;
  }

  connect(): void {
    this.echo = new Echo({
      broadcaster: 'reverb',
      key: environment.reverb.key,
      wsHost: environment.reverb.wsHost,
      wsPort: environment.reverb.wsPort,
      wssPort: environment.reverb.wssPort,
      forceTLS: environment.reverb.forceTLS,
      enabledTransports: environment.reverb.enabledTransports as any,
      authEndpoint: `${baseUrl}broadcasting/auth`,
      auth: {
        headers: {
          Authorization: `Bearer ${this.token}`,
          Accept: 'application/json',
          'Content-Type': 'application/json',
        },
      },
    });
  }

  subscribe(
    channelName: string,
    eventName: string,
    callback: (data: any) => void
  ): void {
    if (!this.echo) {
      throw new Error(
        'Echo connection not initialized. Call connect() first.'
      );
    }

    this.echo.channel(channelName).listen(eventName, callback);
  }

  subscribeToPrivateChannel(channelName: string, eventName: string, endPoint: string,
    callback: (data: any) => void) {

    if (!this.echo) {
      throw new Error(
        'Echo connection not initialized. Call connect() first from private listen.'
      );
    }

    this.echo.channel(channelName).listen(eventName, callback);
  }

  unsubscribe(channelName: string): void {
    if (this.echo) {
      this.echo.leaveChannel(channelName);
    }
  }
}

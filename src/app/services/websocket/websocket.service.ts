import { Injectable } from '@angular/core';
import { io, Socket } from 'socket.io-client';
import { Observable } from 'rxjs';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { environment2 } from '../../environment';

(window as any).Pusher = Pusher;
@Injectable({
  providedIn: 'root',
})
export class WebsocketService {
  private socket!: Socket;

  public echo: Echo<any>;

  constructor() {
    this.echo = new Echo({
      broadcaster: 'pusher',
      key: environment2.pusher.key,
      cluster: environment2.pusher.cluster,
      wsHost: environment2.pusher.wsHost,
      wsPort: environment2.pusher.wsPort,
      forceTLS: false, // إذا السيرفر غير HTTPS
      enabledTransports: ['ws', 'wss'],
    });
  }

  connect(url: string) {
    this.socket = io(url, {
      transports: ['websocket'],
    });

    this.socket.on('connect', () => console.log('Connected to server'));
  }

  joinChannel(channelName: string) {
    this.socket.emit('joinChannel', channelName);
  }

  sendMessageToChannel(channelName: string, message: string) {
    this.socket.emit('channelMessage', { channel: channelName, message });
  }

listenToChannel(
    channelName: string,
    eventName: string,
    callback: (data: any) => void,
    isPrivate: boolean = false
  ) {
    console.warn(`Listening to channel: ${channelName}, event: ${eventName}`);

    const channel = isPrivate
      ? this.echo.private(channelName)
      : this.echo.channel(channelName);

    channel.listen(eventName, (data: any) => {
      console.log('🎉 Event received raw data:', data);
      callback(data);
    });
  }

}

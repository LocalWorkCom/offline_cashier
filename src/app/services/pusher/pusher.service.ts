import { Injectable } from '@angular/core';
import Pusher from 'pusher-js';
import { baseUrl, environment } from '../../environment';
import { BasicsConstance } from '../../constants';

@Injectable({
  providedIn: 'root',
})
export class PusherService {
  private pusher: Pusher | null = null;
  private token: string = localStorage.getItem('access_token') || '';

  constructor() {
    Pusher.logToConsole = true;
  }

  connect(): void {
    this.pusher = new Pusher(environment.pusher.key, {
      cluster: environment.pusher.cluster,
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
 
  // connect(): void {
  //   this.pusher = new Pusher('77f608d73899bd256cfa', {
  //     cluster: 'mt1',
  //     forceTLS: true,
  //     authEndpoint:`${baseUrl}broadcasting/auth`,
  //     auth: {
  //       headers: {
  //         token: `Bearer ${this.token}`,
  //           Lang: localStorage.getItem(BasicsConstance.LANG)||BasicsConstance.DefaultLang,
  //       }
  //     }
  //   });
  // }

  subscribe(
    channelName: string,
    eventName: string,
    callback: (data: any) => void
  ): void {
    if (!this.pusher) {
      throw new Error(
        'Pusher connection not initialized. Call connect() first.'
      );
    }

    const channel = this.pusher.subscribe(channelName);
    channel.bind(eventName, callback);
  }

  subscribeToPrivateChannel(channelName: string,eventName:string,endPoint:string,
    callback: (data: any) => void) {

    // Make an authentication request to your server for the private channel
    fetch(`${baseUrl}${endPoint}`, {
      method: 'POST',credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        socket_id: this.pusher?.connection.socket_id,
        channel_name: channelName
      })
    })
      .then(response => response.json())
      .then(authData => {
         if (!this.pusher) {
      throw new Error(
        'Pusher connection not initialized. Call connect() first from private listen   .'
      );
    }
        const channel = this.pusher.subscribe(channelName);
        channel.bind(eventName,callback);
      })
      .catch(error => {
        console.error('Error authenticating private channel:', error);
      });
  }
  unsubscribe(channelName: string): void {
    if (this.pusher) {
      this.pusher.unsubscribe(channelName);
    }
  }
}

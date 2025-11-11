import { Component, OnInit, OnDestroy } from '@angular/core';
import { Subscription } from 'rxjs';
import { WebsocketService } from '../../services/websocket/websocket.service';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';


@Component({
  selector: 'app-test',
  imports: [FormsModule,CommonModule],
  templateUrl: './test.component.html',
  styleUrl: './test.component.css'
})
export class TestComponent implements OnInit, OnDestroy{
    messages: string[] = [];
  newMessage = '';
  private sub!: Subscription;
  channel = `newOrder2-9-branch-7`; // Example channel
orders:any      // اسم القناة
  eventName = 'new-order-added2';           // اسم الحدث من Laravel
  isPrivateChannel = true;   
  constructor(private socketService: WebsocketService) {}

 /*  ngOnInit() {
    this.socketService.connect('http://localhost:3000');

    // Join a channel
    this.socketService.joinChannel(this.channel);

    // Listen for channel messages
    this.sub = this.socketService.listenChannel(this.channel).subscribe((msg) => {
      this.messages.push(msg);
    });
  }
 */

  ngOnInit() {
    console.log('🔗 Subscribing to channel:', this.channel);

    this.socketService.listenToChannel(
      this.channel,
      this.eventName,
      (event: any) => {
        console.log('✅ New order event received:', event);
        console.log('Order ID:', event.order_id);
        console.log('Full object:', JSON.stringify(event, null, 2));

        this.orders.push(event);
        console.log('Updated orders array:', this.orders);
      },
      this.isPrivateChannel
    );
  }


  ngOnDestroy() {
    this.sub.unsubscribe();
  }
}

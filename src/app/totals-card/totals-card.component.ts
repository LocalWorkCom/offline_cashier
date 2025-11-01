import { CommonModule } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { Component, OnInit } from '@angular/core';
import { baseUrl } from '../environment';
import { totalBalance } from '../services/pusher/totalBalance';

@Component({
  selector: 'app-totals-card',
  imports: [CommonModule],
  templateUrl: './totals-card.component.html',
  styleUrl: './totals-card.component.css'
})
export class TotalsCardComponent implements OnInit{

constructor(private http: HttpClient,private totalBalance:totalBalance) {}
paymentSummary: { name: string,value: number }[] = [];
errorMsg!:string;

totals:number=32523
ngOnInit(): void {
  this.getTotalMoney();
  this.listenToTotal()
}
listenToTotal(){
this.totalBalance.listenToBalance();
this.totalBalance.totalChange$.subscribe((balance)=>{
  console.log('balance',balance,'must change format ');
  this.paymentSummary=[...balance.data]
  
})
} 
getTotalMoney() {
  const shiftData = JSON.parse(localStorage.getItem('shiftData')!);

const body = {
  cashier_machine_id: localStorage.getItem('cashier_machine_id'),
  employee_schedule_id: localStorage.getItem('employee_schedule_id'),
  shift_start: shiftData?.shift_start || null,  
  shift_end: shiftData?.shift_end || null,     
};

  this.http.post<any>(`${baseUrl}api/cashier/get-current-balance`, body).subscribe({
    next: (res) => {
      this.paymentSummary = res.data; 
      if(res.status==false){
          this.errorMsg=res.message;
          alert(this.errorMsg)
          
      }
    },
    error: (err) => {
      console.error('Failed to fetch total money:', err);
    }
  });
}
}

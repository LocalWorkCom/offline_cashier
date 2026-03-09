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
/** When true, monetary amounts are visible; when false, only labels are shown. Default hidden on load/refresh. */
showAmounts = false;
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
           // localStorage.setItem('paymentSummary', JSON.stringify(this.paymentSummary));
          localStorage.setItem('totalcash', res.data[0].value);
          localStorage.setItem('totalvisa', res.data[1].value);
           // localStorage.setItem('total', res.data[2].value);
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

toggleAmounts(): void {
  this.showAmounts = !this.showAmounts;
}
}

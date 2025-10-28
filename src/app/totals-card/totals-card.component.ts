import { CommonModule } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { Component, OnInit, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { baseUrl } from '../environment';
import { totalBalance } from '../services/pusher/totalBalance';
import { IndexeddbService } from '../services/indexeddb.service';

@Component({
  selector: 'app-totals-card',
  imports: [CommonModule],
  templateUrl: './totals-card.component.html',
  styleUrl: './totals-card.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class TotalsCardComponent implements OnInit{

constructor(
  private http: HttpClient,
  private dbService: IndexeddbService,
  private totalBalance: totalBalance,
  private cdr: ChangeDetectorRef
) {}
paymentSummary: { name: string,value: number }[] = [];
errorMsg!:string;

totals:number=32523
ngOnInit(): void {
  // Defer API call to next tick for faster initial render
  setTimeout(() => {
    this.getTotalMoney();
    this.listenToTotal();
  }, 0);
}
listenToTotal(){
this.totalBalance.listenToBalance();
this.totalBalance.totalChange$.subscribe((balance)=>{
  console.log('balance',balance,'must change format ');
  this.paymentSummary=[...balance.data]
  this.cdr.markForCheck();
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
     this.dbService.saveData('getCurrentBalance',res.data);

      if(res.status==false){
          this.errorMsg=res.message;
          alert(this.errorMsg)
      }
      this.cdr.markForCheck();
    },
    error: (err) => {
      console.error('Failed to fetch total money:', err);
      this.cdr.markForCheck();
    }
  });
}
}

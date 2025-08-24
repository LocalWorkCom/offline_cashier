import { Component, EventEmitter, Input, Output } from '@angular/core';
import { ToastModule } from 'primeng/toast';
import { ConfirmDialogModule } from 'primeng/confirmdialog';
import { ButtonModule } from 'primeng/button';
import { ConfirmationService, ConfirmEventType } from 'primeng/api';

@Component({
  selector: 'app-confirm-dialog',
  imports: [ToastModule, ConfirmDialogModule, ButtonModule],
  templateUrl: './confirm-dialog.component.html',
  styleUrl: './confirm-dialog.component.css',
  providers: [ConfirmationService],
})
export class ConfirmDialogComponent {
  @Input() display: boolean = false;
  @Input() header: string = '';
  @Input() message: string = '';
  @Input() acceptLabel: string = 'Yes';
  @Input() rejectLabel: string = 'No';
  @Output() actionResult = new EventEmitter<boolean>();

  constructor(private confirmationService: ConfirmationService) {}
   
  confirm(acceptLabel: string = 'نعم', rejectLabel: string = 'لا') {
    this.confirmationService.confirm({
      header: this.header,
      message: this.message,
      acceptLabel,
      rejectLabel,

      accept: () => {
        this.actionResult.emit(true);
      },
      reject: (type:ConfirmEventType) => {  
         if (type === ConfirmEventType.REJECT) { 
          this.actionResult.emit(false);
        }
      }, 
    });
  }
}

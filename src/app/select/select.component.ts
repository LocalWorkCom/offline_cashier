import { CommonModule } from '@angular/common';
import { Component, EventEmitter, Input, OnChanges, OnInit, Output, SimpleChanges } from '@angular/core';
import { FormControl, ReactiveFormsModule } from '@angular/forms';
import { DropdownModule } from 'primeng/dropdown';
import { SelectModule } from 'primeng/select';

 @Component({
  selector: 'app-select',
  standalone: true,
  templateUrl: './select.component.html',
  styleUrl: './select.component.css',
  imports: [SelectModule,ReactiveFormsModule,CommonModule]
})
export class SelectComponent  implements OnInit ,OnChanges{
  @Input() options: {name:string,id:number,countryFlag?:string,countryCode?:string}[] = [];
  @Input() placeholder!:string;
  @Input() name:string='';
  @Input() title!:string;
  @Input() width:string='239px';
  @Input() control:FormControl =new FormControl();




  ngOnInit() {
    if(!this.placeholder)
   this.options[0]?this.placeholder= 'يرجى اختيار    '+this.title:'';
  }


@Input() titleDropdown:string='';
@Output() optionSelected = new EventEmitter<any>();
@Input() addall=true;
filter:boolean=false;
ngOnChanges(changes: SimpleChanges): void {

 if(changes['options']){
  if(this.options&&this.options.length>4){
    this.filter=true
  }

}
}

getFlagEmoji(countryCode: string): string {
  const codePoints = countryCode
    .toUpperCase()
    .split('')
    .map(char =>  127397 + char.charCodeAt(0));
  return String.fromCodePoint(...codePoints);
}

}

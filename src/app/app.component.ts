import { Component, inject, OnInit } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { HomeComponent } from './home/home.component';
// import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { NavbarComponent } from './navbar/navbar.component';
import { SidebarComponent } from './sidebar/sidebar.component';
import { RouterModule } from '@angular/router';
import { MainLayoutComponent } from './main-layout/main-layout.component';
import { AuthLayoutComponent } from './auth-layout/auth-layout.component';
import { PusherService } from './services/pusher/pusher.service';
import { TranslationService } from './core/i18n';
import { ConfirmDialog } from "primeng/confirmdialog";
import { SyncService } from './services/sync.service';

// import {LoginComponent} from "./login/login.component";

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet, CommonModule, RouterModule],
  templateUrl: './app.component.html',
  styleUrl: './app.component.css',
})
export class AppComponent implements OnInit {
  title = 'cashier';
  dir!: 'ltr' | 'rtl';

  private pusher = inject(PusherService);
  private translate = inject(TranslationService);
    constructor(private syncService: SyncService) {}

  ngOnInit(): void {
    this.dir = this.translate.getHtmlDirection();
    document.body.dir = this.dir;

    this.pusher.connect();


     // ✅ دايماً اسمع على رجوع الإنترنت
    window.addEventListener('online', () => {
      console.log("🌐 Back online globally, syncing all data...");
      this.syncService.runAllSyncFunctions();
    });
  }
}

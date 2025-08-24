import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class ModalStateService {
  private isModalOpenValue = false;

  constructor() {
    // Initialize from session storage if available
    if (typeof sessionStorage !== 'undefined') {
      this.isModalOpenValue = sessionStorage.getItem('welcomeModalOpen') === 'true';
    }
  }

  isModalOpen(): boolean {
    return this.isModalOpenValue;
  }

  setModalOpen(isOpen: boolean): void {
    this.isModalOpenValue = isOpen;
    if (typeof sessionStorage !== 'undefined') {
      if (isOpen) {
        sessionStorage.setItem('welcomeModalOpen', 'true');
      } else {
        sessionStorage.removeItem('welcomeModalOpen');
      }
    }
  }
}
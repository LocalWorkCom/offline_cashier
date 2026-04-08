import { Component, OnInit, Inject, PLATFORM_ID } from '@angular/core';
import { ProfileService } from '../services/profile.service';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { isPlatformBrowser } from '@angular/common';
import { finalize } from 'rxjs';
import { ShowLoaderUntilPageLoadedDirective } from '../core/directives/show-loader-until-page-loaded.directive';
import { PrinterManagementComponent } from '../printer-management/printer-management.component';
import { DishManagementComponent } from './dish-management/dish-management.component';
import { PaymentDeviceManagementComponent } from '../payment-device-management/payment-device-management.component';

interface Country {
  code: string;
  flag: string;
}
interface PhoneValidationRule {
  length: number; 
  pattern?: RegExp; 
}

const phoneValidationRules: { [key: string]: PhoneValidationRule } = {
  '+20': { length: 11, pattern: /^01[0-9]{9}$/ }, // Egypt (Must start with 01)
  '+966': { length: 9, pattern: /^5[0-9]{8}$/ }, // Saudi Arabia (Must start with 5)
  '+971': { length: 9, pattern: /^5[0-9]{8}$/ }, // UAE (Must start with 5)
  '+212': { length: 9, pattern: /^[5-7][0-9]{8}$/ }, // Morocco (Starts with 5, 6, or 7)
  '+44': { length: 10, pattern: /^[1-9][0-9]{9}$/ }, // UK
  '+81': { length: 10, pattern: /^[7-9][0-9]{9}$/ }, // Japan
  '+39': { length: 10, pattern: /^[3][0-9]{9}$/ }, // Italy (Starts with 3)
  '+49': { length: 10, pattern: /^[1-9][0-9]{9}$/ }, // Germany
  '+91': { length: 10, pattern: /^[6-9][0-9]{9}$/ }, // India (Starts with 6-9)
  '+1': { length: 10, pattern: /^[2-9][0-9]{9}$/ }, // USA & Canada (Cannot start with 0 or 1)
  '+82': { length: 9, pattern: /^[1][0-9]{8}$/ }, // South Korea (Starts with 1)
  '+55': { length: 11, pattern: /^[1-9][0-9]{10}$/ }, // Brazil
  '+86': { length: 11, pattern: /^[1][0-9]{10}$/ }, // China (Starts with 1)
  '+7': { length: 10, pattern: /^[1-9][0-9]{9}$/ }, // Russia & Kazakhstan
  '+965': { length: 8, pattern: /^[5-9][0-9]{7}$/ }, // Kuwait (Starts with 5-9)
  '+33': { length: 9, pattern: /^[1-9][0-9]{8}$/ }, // France
  '+61': { length: 9, pattern: /^[2-478][0-9]{8}$/ }, // Australia (Landline & Mobile)
  '+52': { length: 10, pattern: /^[1-9][0-9]{9}$/ }, // Mexico
};


@Component({
  selector: 'app-settings',
  standalone: true,
  imports: [CommonModule, FormsModule, ShowLoaderUntilPageLoadedDirective, PrinterManagementComponent, DishManagementComponent, PaymentDeviceManagementComponent],
  templateUrl: './settings.component.html',
  styleUrls: ['./settings.component.css'],
})
export class SettingsComponent implements OnInit {
fullName: string = '';
  phoneNumber: string | null = null;
  countryCode: string | null = null;
  selectedCountry: Country | null = null;
  countryList: Country[] = [];
  filteredCountries: Country[] = []; // New array for filtering
  searchTerm: string = ''; // Store the input search value
  errorMessage: string = '';
  successMessage: string = '';
  dropdownOpen: boolean = false;
  imageUrl: string | null = null; 
  successModalMessage: string = 'تم تحديث البيانات بنجاح!';

  constructor(
    private profileService: ProfileService,
    @Inject(PLATFORM_ID) private platformId: object
  ) {}

  ngOnInit() {
       
    if (isPlatformBrowser(this.platformId)) {
     
      this.imageUrl = localStorage.getItem('imageUrl');
    }
  
    const token = localStorage.getItem('authToken');
    if (!token) {
      console.error('User is not authenticated. Redirecting to login...');
      window.location.href = '/login';
      return;
    }

    const storedCountry = localStorage.getItem('selectedCountry');
    if (storedCountry) {
      this.selectedCountry = JSON.parse(storedCountry);
      if (this.selectedCountry) {
        this.countryCode = this.selectedCountry.code;
      }
    }

    this.fetchUserProfile();

    this.profileService.getCountries().subscribe({
      next: (countries) => {
        console.log('Fetched countries:', countries);
        this.countryList = countries;
        this.filteredCountries = countries; // Initialize with all countries
      },
      error: (error) => {
        console.error('Failed to fetch countries:', error);
      },
    });
  }
loading:boolean=true;
fetchUserProfile() {
  this.loading=false
  this.profileService.getUserProfile().pipe(
    finalize(()=>this.loading=true)
  ).subscribe({
    next: (profile) => {
      this.fullName = profile.fullName; // This will now get the full_name directly
      this.phoneNumber = profile.phone_number;
      this.countryCode = profile.country_code;
      this.selectedCountry = {
        code: profile.country_code,
        flag: this.getCountryFlag(profile.country_code),
      };

      // Update full name in ProfileService
      this.profileService.setFullName(profile.fullName);
    },
    error: (error) => {
      console.error('Failed to fetch user profile:', error);
      this.errorMessage = 'Failed to load user data.';
    },
  });
}
  

  getCountryFlag(code: string): string {
    const country = this.countryList.find((c) => c.code === code);
    return country ? country.flag : 'assets/images/default-flag.png'; // Default flag if not found
  }

  filterCountries() {
    const term = this.searchTerm.toLowerCase();
    this.filteredCountries = this.countryList.filter(country =>
      country.code.toLowerCase().includes(term)
    );
  }

  toggleDropdown() {
    this.dropdownOpen = !this.dropdownOpen;
    if (!this.dropdownOpen) {
      this.searchTerm = ''; // Reset search input when closing
      this.filteredCountries = [...this.countryList]; // Reset list
    }
  }

  selectCountry(country: Country) {
    console.log('Selected country code:', country.code);
    this.selectedCountry = country;
    this.countryCode = country.code;
    localStorage.setItem('selectedCountry', JSON.stringify(country));
    this.dropdownOpen = false;
  }

  handlePrinterSuccess(message: string) {
    this.successMessage = message;
    setTimeout(() => {
      this.successMessage = '';
    }, 4000);
  }

  showSuccessModal() {
    if (isPlatformBrowser(this.platformId)) {
      import('bootstrap').then(({ Modal }) => {
        const modalElement = document.getElementById('successModal');
        if (modalElement) {
          const successModal = new Modal(modalElement);
          successModal.show();
          setTimeout(() => successModal.hide(), 3000);
        }
      });
    }
  }

  // saveProfile() {
  //   this.errorMessage = ''; 
  //   this.successMessage = '';
  
  //   if (!this.fullName) {
  //     this.errorMessage = 'يرجى إدخال الاسم الاول و الاخير.'; 
  //     return;
  //   }
  
  //   if (!this.phoneNumber) {
  //     this.errorMessage = 'رقم الهاتف مطلوب.'; 
  //     return;
  //   }
  
  //   if (!this.countryCode) {
  //     this.errorMessage = 'كود الدولة مطلوب.'; 
  //     return;
  //   }
  
  //   const nameParts = this.fullName.trim().split(' ');
  //   const firstName = nameParts[0] || '';
  //   const lastName = nameParts.slice(1).join(' ') || '';
  
  //   if (!firstName || !lastName) {
  //     this.errorMessage = 'يرجى إدخال الاسم الأول والأخير.'; 
  //     return;
  //   }
  
  //   const updatedData = {
  //     first_name: firstName,
  //     last_name: lastName,
  //     country_code: this.countryCode,
  //     phone_number: this.phoneNumber,
  //   };
  
  //   console.log('🔼 Sending data to API:', updatedData);
  
  //   this.profileService.updateUserProfile(updatedData).subscribe({
  //     next: (response) => {
  //       console.log('🔽 API Response:', response);
        
  //       if (response.status) {
  //         this.successMessage = response.message;
          
  //         // Show Success Modal
  //         if (isPlatformBrowser(this.platformId)) {
  //           import('bootstrap').then(({ Modal }) => {
  //             const modalElement = document.getElementById('successModal');
  //             if (modalElement) {
  //               const successModal = new Modal(modalElement);
  //               successModal.show();
  
  //               setTimeout(() => {
  //                 successModal.hide();
  //               }, 3000);
  //             }
  //           });
  //         }
  //       } else {
  //         this.errorMessage = response.message || 'حدث خطأ أثناء تحديث البيانات.';
  //       }
  //     },
  //     error: (error) => {
  //       console.error('❌ API Error:', error);
  
  //       if (error.error && error.error.errorData) {
  //         const errorData = error.error.errorData;
  //         const messages = [];
  
  //         if (errorData.first_name) messages.push(errorData.first_name[0]);
  //         if (errorData.last_name) messages.push(errorData.last_name[0]);
  //         if (errorData.country_code) messages.push(errorData.country_code[0]);
  //         if (errorData.phone_number) messages.push(errorData.phone_number[0]);
  
  //         this.errorMessage = messages.join(' ');
  //       } else {
  //         this.errorMessage = 'فشل تحديث الملف الشخصي. يرجى المحاولة لاحقًا.';
  //       }
  //     },
  //   });
  // }
  
saveProfile() {
  this.errorMessage = ''; 
  this.successMessage = '';

  // Remove all frontend validation for fullName
  if (!this.phoneNumber) {
    this.errorMessage = 'رقم الهاتف مطلوب.'; 
    return;
  }

  if (!this.countryCode) {
    this.errorMessage = 'كود الدولة مطلوب.'; 
    return;
  }

  // Validate phone number format and length based on country code
  const validationRule = phoneValidationRules[this.countryCode];

  if (validationRule) {
    const phonePattern = validationRule.pattern;
    if (phonePattern && !phonePattern.test(this.phoneNumber)) {
      this.errorMessage = `رقم الهاتف غير صحيح بناءً على رمز الدولة ${this.countryCode}.`;
      return;
    }
  }

  // Prepare data to send - use full_name directly
  const updatedData = {
    full_name: this.fullName, // Send the full name directly
    country_code: this.countryCode,
    phone_number: this.phoneNumber,
  };

  console.log('🔼 Sending data to API:', updatedData);

  this.profileService.updateUserProfile(updatedData).subscribe({
    next: (response) => {
      console.log('🔽 API Response:', response);
      
      if (response.status) {
        this.successMessage = response.message;
        this.profileService.setFullName(this.fullName); // Update the full name
        this.handlePrinterSuccess(response.message || 'تم تحديث البيانات بنجاح!');
        if (isPlatformBrowser(this.platformId)) {
          this.imageUrl = localStorage.getItem('imageUrl');
        }
      } else {
        if (response.errorData) {
          const errorMessages = Object.values(response.errorData)
            .flat()
            .join(' ');
      
          this.errorMessage = errorMessages || 'حدث خطأ أثناء تحديث البيانات.';
        } else {
          this.errorMessage = 'حدث خطأ أثناء تحديث البيانات.';
        }
      }
    },
    error: (error) => {
      console.error('❌ API Error:', error);
      if (error.error && error.error.errorData) {
        const errorData = error.error.errorData;
        const messages = [];

        if (errorData.full_name) messages.push(errorData.full_name[0]);
        if (errorData.country_code) messages.push(errorData.country_code[0]);
        if (errorData.phone_number) messages.push(errorData.phone_number[0]);

        this.errorMessage = messages.join(' ');
      } else {
        this.errorMessage = 'فشل تحديث الملف الشخصي. يرجى المحاولة لاحقًا.';
      }
    },
  });
}
  
}

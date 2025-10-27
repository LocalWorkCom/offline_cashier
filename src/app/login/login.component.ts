import { Component, inject, OnInit } from '@angular/core';
import { NgxCountriesDropdownModule } from 'ngx-countries-dropdown';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AuthService } from '../services/auth.service';
import { Router } from '@angular/router';
import { TranslateModule, TranslateService } from '@ngx-translate/core';
import { SysteminfoService } from '../services/systeminfo.service';

interface Country {
  code: string;
  flag: string;
}

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [
    TranslateModule,
    NgxCountriesDropdownModule,
    CommonModule,
    FormsModule,
  ],
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css'],
})
export class LoginComponent implements OnInit {
  translate = inject(TranslateService);
  selectedCountry: Country = { code: '+20', flag: "assets/images/egypt.png" };
  storedSystemInfo = localStorage.getItem('systemInfo');

  dropdownOpen = false;
  loginData = {
    email_or_phone: '',
    password: '',
  };
  errorMessage: string = '';
  countryList: Country[] = [];
  filteredCountries: Country[] = []; // List for filtered countries
  searchTerm: string = ''; // Search term for filtering
  isPasswordVisible: boolean = false;
  EmailOrPhone: boolean = true
  passwordError!: string
  constructor(public authService: AuthService, private router: Router, private systemService: SysteminfoService) {
    /*     this.translate.get('login.selectCountry').subscribe((res: string) => {
          console.log('rsssssss',res)
          this.selectedCountry = {
            code: res || 'Select Country',
            flag: '',
          };
        }); */
  }

  // ngOnInit() {
  //   this.fetchCountries();
  // }
  systemInfo: any;


  async ngOnInit() {
    this.fetchCountries();
    this.systemInfo = await this.systemService.getSystemInfo();
    console.log("System info loaded in login:", this.systemInfo);
  }
  fetchCountries() {
    this.authService.getCountries().subscribe({
      next: (response) => {
        if (response.data && Array.isArray(response.data)) {
          this.countryList = response.data.map(
            (country: { phone_code: string; image: string }) => ({
              code: country.phone_code,
              flag: country.image,
            })
          );
          const allowedCountryCodes: string[] = ['+20', '+962', '+964', '+212', '+963', '+965', '+966'];
          this.filteredCountries = [...this.countryList]; // Initialize filteredCountries
          this.filteredCountries = this.filteredCountries.filter((country: any) =>
            allowedCountryCodes.includes(country.code.replace(/\s+/g, '').replace(' ', '').replace('ـ', '').replace('–', ''))
          );

        } else {
          this.errorMessage = 'No country data found in the response.';
        }
      },
      error: () => {
        this.errorMessage = 'Failed to load country data.';
      },
    });
  }

  toggleDropdown() {
    this.dropdownOpen = !this.dropdownOpen;
  }

  selectCountry(country: Country) {
    this.selectedCountry = country;
    this.dropdownOpen = false;
    this.searchTerm = ''; // Clear search term after selection
    this.filteredCountries = [...this.countryList]; // Reset filtered list
    console.log('Selected country:', this.selectedCountry);
  }

  filterCountries() {
    this.filteredCountries = this.countryList.filter((country) =>
      country.code.toLowerCase().includes(this.searchTerm.toLowerCase())
    );
  }

  togglePasswordVisibility() {
    this.isPasswordVisible = !this.isPasswordVisible;
  }

  // onLogin() {
  //   sessionStorage.setItem('allowed', 'true');
  //   const isEmailInput = this.isEmail(this.loginData.email_or_phone);

  //   // Validation for empty fields
  //   if (!this.loginData.email_or_phone) {
  //     this.errorMessage = isEmailInput
  //       ? 'أدخل البريد الإلكتروني'
  //       : 'أدخل رقم الهاتف';
  //     return;
  //   }

  //   if (!this.loginData.password) {
  //     this.errorMessage = 'أدخل كلمة المرور';
  //     return;
  //   }

  //   // Only require country code if it's not an email
  //   if ( !isEmailInput && !this.selectedCountry.code) {
  //     this.errorMessage = 'أختر الدولة';
  //     return;
  //   }

  //   const loginPayload = {
  //     // Only include country_code if it's not an email
  //     ...(!isEmailInput && { country_code: this.selectedCountry.code }),
  //     email_or_phone: this.loginData.email_or_phone,
  //     password: this.loginData.password,
  //   };

  // this.authService.login(loginPayload).subscribe({
  // next: (response) => {
  //   if (response.status && response.data.access_token) {
  //     // Ensure balance status is properly set
  //     if (response.data.is_open_balance !== undefined) {
  //       this.authService.setOpenBalanceStatus(response.data.is_open_balance);
  //     }

  //     this.authService.setUsername(this.loginData.email_or_phone);
  //     this.router.navigate(['/home']);
  //   }
  // },
  //     error: (error) => {
  //       console.error('Login Failed:', error);
  //       this.errorMessage =
  //         error.error?.message ||
  //         error.message ||
  //         'فشل تسجيل الدخول، تحقق من البيانات المدخلة';
  //     },
  //   });
  // }

  onLogin() {
    sessionStorage.setItem('allowed', 'true');
    console.log(this.loginData);

    const isEmailInput = this.isEmail(this.loginData.email_or_phone);
    console.log(isEmailInput);


    if (!this.loginData.email_or_phone) {
      this.errorMessage = isEmailInput
        ? 'أدخل البريد الإلكتروني'
        : 'أدخل رقم الهاتف';
      return;
    }

    if (!this.loginData.password) {
      this.errorMessage = 'أدخل كلمة المرور';
      return;
    }

    // ✅ جيب الماك من localStorage
    let macAddress = null;
    const storedSystemInfo = localStorage.getItem('systemInfo');
    if (storedSystemInfo) {
      const systemInfo = JSON.parse(storedSystemInfo);
      const validMac = systemInfo.macAddresses.find((m: any) => m.mac !== "00:00:00:00:00:00");
      macAddress = validMac ? validMac.mac : null;
    }
    // const loginPayload = {
    //   ...(!isEmailInput && { country_code: this.selectedCountry.code }),
    //   email_or_phone: this.loginData.email_or_phone,
    //   password: this.loginData.password,
    // };
    // if (!isEmailInput && !this.selectedCountry.code) {
    //   this.errorMessage = 'أختر الدولة';
    //   return;
    // }

    const loginPayload = {
      ...(isEmailInput ? {} : { country_code: '+20' }),
      email_or_phone: this.loginData.email_or_phone,
      password: this.loginData.password,
      ...(macAddress ? { mac: macAddress } : {}) 



    };


    this.authService.login(loginPayload).subscribe({
      next: (response) => {
        console.log(response, "alllllllaaaaaaaaaaaaaaaaaaaaaaaaaa");

        if (response.status && response.data.access_token) {
          console.log('toqa');
          this.authService.setUsername(this.loginData.email_or_phone);
          if (response.data.is_open_balance !== undefined) {
            this.authService.setOpenBalanceStatus(response.data.is_open_balance);
            if (response.data.is_open_balance && response.data.opened_balance_id) {
              this.authService.setOpenedBalanceId(response.data.opened_balance_id);
            }
          }
          this.router.navigate(['/home']);
        } else {
          this.passwordError = response?.errorData?.credential

          setTimeout(() => {
            this.passwordError = '';
          }, 2000);
          console.log("alaaa");

        }
      },
      error: (error) => {
        console.error('Login Failed:', error);

        const defaultMessage = 'فشل تسجيل الدخول، تحقق من البيانات المدخلة';

        const errorData = error?.error?.errorData;
        if (errorData) {
          const firstKey = Object.keys(errorData)[0];
          const firstMessage = Array.isArray(errorData[firstKey]) ? errorData[firstKey][0] : null;

          this.errorMessage = firstMessage || error?.error?.message || defaultMessage;
        } else {
          this.errorMessage = error?.error?.message || defaultMessage;
        }
      }

    });
  }
  isEmail(input: any): boolean {
    // Simple email regex pattern
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailPattern.test(input);
  }

  onInputChange() {
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (emailPattern.test(this.loginData.email_or_phone)) {
      this.EmailOrPhone = true;
    } else {
      this.EmailOrPhone = false
    }
  }
}

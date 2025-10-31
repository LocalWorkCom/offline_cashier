import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import { NgxCountriesDropdownModule } from 'ngx-countries-dropdown';
import { CommonModule, Location } from '@angular/common';
import {
  AbstractControl,
  FormBuilder,
  FormControl,
  FormGroup,
  FormsModule,
  ReactiveFormsModule,
  ValidationErrors,
  ValidatorFn,
  Validators,
} from '@angular/forms';
import { AddAddressService } from '../services/add-address.service';
import { AuthService } from '../services/auth.service';
import { Country } from '../services/profile.service';
import { Router, RouterModule } from '@angular/router';
import { HttpClient } from '@angular/common/http';
type PropertyType = 'apartment' | 'villa' | 'office' | 'hotel';
import { NgbDropdownModule } from '@ng-bootstrap/ng-bootstrap';
import { PhoneCheckService } from '../services/phoneCheck';
import { SelectComponent } from '../select/select.component';
import { ConfirmDialogComponent } from '../shared/ui/component/confirm-dialog/confirm-dialog.component';
import { finalize } from 'rxjs';
import { ChangeDetectorRef } from '@angular/core';
import { baseUrl } from '../environment';
// start hanan
import { IndexeddbService } from '../services/indexeddb.service';
// end hanan

@Component({
  selector: 'app-delivery-details',
  standalone: true,
  imports: [
    NgxCountriesDropdownModule,
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    RouterModule,
    NgbDropdownModule,
    SelectComponent,
    ConfirmDialogComponent,
  ],
  templateUrl: './delivery-details.component.html',
  styleUrls: ['./delivery-details.component.css'],
})
export class DeliveryDetailsComponent implements OnInit {
  form!: FormGroup;
  // selectedCountry!: Country ;
  selectedCountry: Country = {
    code: '+20',
    flag: 'assets/images/egypt.png',
    phoneLength: 11,
  };
  selectedWhatsappCountry: Country = {
    code: '+20',
    flag: 'assets/images/egypt.png',
    phoneLength: 11,
  };

  dropdownOpen = false;
  selectedProperty: any | '' = 'apartment';
  submitted = false;
  filteredCountries: Country[] = [];
  countryList: Country[] = [];
  previousProperty: string | undefined;
  showWhatsappInput: boolean = true;
  useSameNumberForWhatsapp: boolean = true;
  // start hanan
  private formDataId: number | null = null;
  // end hanan

  whatsappCountryCode: string = this.selectedWhatsappCountry.code;
  whatsappPhone: any;

  selectedAddress = JSON.parse(localStorage.getItem('selected_address')!);
  userStoredAddress: { name: string; id: number; delivery_fees: string }[] =
    this.selectedAddress ? [this.selectedAddress] : [];
  @ViewChild('confirmDialog') confirmationDialog!: ConfirmDialogComponent;
  userId!: number;
  searchTerm: any;
  onConfirmationResult(confirmed: boolean) {
    if (confirmed) {
      console.log('User confirmed action');
      // Perform the action
    } else {
      console.log('User cancelled action');
    }
  }
  constructor(
    private formBuilder: FormBuilder,
    private authService: AuthService,
    private formDataService: AddAddressService,
    private router: Router,
    private location: Location,
    private http: HttpClient,
    private checkPhoneNum: PhoneCheckService,
    // start hanan
    private dbService: IndexeddbService,
    // end hanan
    private cdr: ChangeDetectorRef
  ) {
    console.log(this.selectedCountry);
  }
  // toqa
  isRestoring = false;
  phoneNotFound: string | undefined;
  userAddNewAddress: boolean = true;
  searchPhoneLoading: boolean = true;
  selectedAddressControl: FormControl = new FormControl(this.selectedAddress);
  updateApartmentValidator(propertyType: string): void {
    const apartmentCtrl = this.form.get('apartment_number');
    if (!apartmentCtrl) return;

    if (['apartment', 'villa', 'office'].includes(propertyType)) {
      apartmentCtrl.setValidators([Validators.pattern(/^(?!\s+$).+/)]);
    } else {
      apartmentCtrl.clearValidators();
    }

    apartmentCtrl.updateValueAndValidity();
  }
  get requiredPhoneLength(): number {
    return this.selectedCountry?.phoneLength; // default 10 if not set
  }
  // ngDoCheck() {
  //   console.log(this.selectedCountry, ' this.selectedCountry?.phoneLength');
  // }
  logInvalidFields(): void {
    Object.keys(this.form.controls).forEach((field) => {
      const control = this.form.get(field);
      if (control && control.invalid) {
        console.warn(`Invalid field: ${field}`, control.errors);
      }
    });
  }
  //
  ngOnInit() {

    // start hanan
    this.dbService.init();
    // end hanan

    if (this.selectedAddress) {
      this.userAddNewAddress = false;
    }
    this.initializeForm();

    // ✅ التأكد من تعيين القيم الافتراضية بعد تهيئة الفورم
    setTimeout(() => {
      if (!this.form.get('country_code')?.value) {
        this.form.get('country_code')?.setValue(this.selectedCountry);
      }
      if (!this.form.get('whatsapp_number_code')?.value) {
        this.form.get('whatsapp_number_code')?.setValue(this.selectedWhatsappCountry);
      }
      if (!this.form.get('address_type')?.value) {
        this.form.get('address_type')?.setValue(this.selectedProperty);
      }
    }, 100);
    this.restoreFormData();
    this.fetchCountries(() => {
      this.restoreFormData(); // only restore after countries are loaded,case problem fatema
    });
    this.getAreas();
    this.getHotels();
    // start hanan
    this.loadFormDataFromIndexedDB();
    // end hanan
    // this.form.valueChanges.subscribe((formValue) => {
    //   const noteValue = localStorage.getItem('notes') || '';
    //   const formDataWithNote = { ...formValue, notes: noteValue };
    //   localStorage.setItem('form_data', JSON.stringify(formDataWithNote));
    // });
    this.selectedHotel = JSON.parse(localStorage.getItem('selectedHotel')!);
    this.form.get('country_code')?.setValue(this.selectedCountry);
    // this.form.get('country_code')?.setValue(this.selectedCountry.code);
    // this.form.get('country_code')?.setValue('+20');

    this.listenPhoneNumberChange();
    if (localStorage.getItem('selected_address') && !this.selectedHotel) {
      this.selectedAddress = JSON.parse(
        localStorage.getItem('selected_address')!
      );
      this.selectedHotel = this.selectedAddress;
      console.log(this.selectedAddress.name);
    }
    const saved = localStorage.getItem('deliveryForm');
    if (saved) {
      const parsed = JSON.parse(saved);

      // Patch everything from storage
      this.form.patchValue(parsed);

      // Detect if it's the same as phone to set toggle state
      if (
        parsed.whatsapp_number_code &&
        (parsed.whatsapp_number.trim() == '' ||
          parsed.whatsapp_number === parsed.address_phone) &&
        parsed.whatsapp_number_code.code === parsed.country_code.code
      ) {
        this.useSameNumberForWhatsapp = true;
      } else {
        this.useSameNumberForWhatsapp = false;
      }

      this.whatsappPhone = parsed.whatsapp_number;
    }
    this.updateWhatsappValidators();
    this.cdr.detectChanges();
    this.listenToAddressChange()
  }


  // start hanan
  private loadFormDataFromIndexedDB() {
    this.dbService.getFormData().then(formDataArray => {
      if (formDataArray && formDataArray.length > 0) {
        // Get the most recent form data
        const latestFormData = formDataArray.reduce((latest, current) => {
          return new Date(current.savedAt) > new Date(latest.savedAt) ? current : latest;
        });

        console.log('Loaded form data from IndexedDB:', latestFormData);

        // Patch the form with the loaded data
        this.form.patchValue(latestFormData);

        // Restore other state if needed
        if (latestFormData.selectedProperty) {
          this.selectedProperty = latestFormData.selectedProperty;
        }

        if (latestFormData.selectedCountry) {
          this.selectedCountry = latestFormData.selectedCountry;
        }

        if (latestFormData.selectedWhatsappCountry) {
          this.selectedWhatsappCountry = latestFormData.selectedWhatsappCountry;
        }

        if (latestFormData.useSameNumberForWhatsapp !== undefined) {
          this.useSameNumberForWhatsapp = latestFormData.useSameNumberForWhatsapp;
        }

        if (latestFormData.whatsappPhone) {
          this.whatsappPhone = latestFormData.whatsappPhone;
        }
      }
    }).catch(err => {
      console.error('Error loading form data from IndexedDB:', err);
    });
  }

  // New method to save form data to IndexedDB
  private saveFormDataToIndexedDB() {
    const formData = {
      ...this.form.value,
      selectedProperty: this.selectedProperty,
      selectedCountry: this.selectedCountry,
      selectedWhatsappCountry: this.selectedWhatsappCountry,
      useSameNumberForWhatsapp: this.useSameNumberForWhatsapp,
      whatsappPhone: this.whatsappPhone
    };

    this.dbService.saveFormData(formData).then(id => {
      this.formDataId = id;
      console.log('Form data saved to IndexedDB with ID:', id);
    }).catch(err => {
      console.error('Error saving form data to IndexedDB', err);
    });
  }
  // end hanan
  listenPhoneNumberChange() {
    const addressId = localStorage.getItem('address_id');
    this.addressPhone?.valueChanges.subscribe((value) => {
      if (
        (this.userStoredAddress && this.userStoredAddress.length > 0) ||
        localStorage.getItem('form_data')
      ) {
        this.resetToSearchphone();
        console.log('Selected Phone Address:', value, this.userAddNewAddress);
      }
    });
  }
  filteredWhatsappCountries: Country[] = [];

  updateWhatsappValidators(): void {
    const whatsappControl = this.form.get('whatsapp_number');
    if (!whatsappControl) return;

    // Clear existing validators
    whatsappControl.clearValidators();

    // Only add validators if using different number
    if (!this.useSameNumberForWhatsapp) {
      const validators = [
        Validators.required,
        this.noLeadingSpaceValidator(),
        Validators.pattern(
          new RegExp(`^\\d{${this.selectedWhatsappCountry.phoneLength}}$`)
        ),
      ];
      // whatsappControl.setValue(null);

      whatsappControl.setValidators(validators);
    }

    whatsappControl.updateValueAndValidity();
    whatsappControl.updateValueAndValidity({ emitEvent: false });
  }

  // Update selectWhatsappCountry method
  selectWhatsappCountry(country: Country) {
    this.selectedWhatsappCountry = country;

    // ✅ تعيين القيمة في الفورم بشكل صحيح
    this.form.get('whatsapp_number_code')?.setValue(country);
    this.form.get('whatsapp_number_code')?.markAsTouched();

    this.dropdownOpen = false;
    this.filteredWhatsappCountries = [...this.countryList];

    // ✅ تحديث validators رقم الواتساب
    if (!this.useSameNumberForWhatsapp) {
      const whatsappControl = this.form.get('whatsapp_number');
      if (whatsappControl) {
        whatsappControl.setValidators([
          Validators.required,
          Validators.pattern(new RegExp(`^\\d{${country.phoneLength}}$`)),
        ]);
        whatsappControl.updateValueAndValidity();
      }
    }
  }


  // Update useSameWhatsapp method
  useSameWhatsapp(useSame: boolean) {
    this.useSameNumberForWhatsapp = useSame;

    if (useSame) {
      const phone = this.form.get('address_phone')?.value || '';
      const code = this.form.get('country_code')?.value || '';

      this.whatsappPhone = phone;
      this.form.patchValue({
        whatsapp_number: phone,
        whatsapp_number_code: code,
      });
    } else {
      this.whatsappPhone = '';
    }
    console.log(
      'fatma test useSameNumberForWhatsapp',
      this.useSameNumberForWhatsapp
    );

    // Update validators
    this.updateWhatsappValidators();
  }

  filterWhatsappCountries(event: any) {
    const searchValue = event.target.value || '';
    this.filteredWhatsappCountries = this.countryList.filter((country) =>
      country.code.toLowerCase().includes(searchValue.toLowerCase())
    );
  }

  private fetchCountries(callback?: () => void) {
    this.authService.getCountries().subscribe({
      next: (response) => {
        if (response.data && Array.isArray(response.data)) {
          this.countryList = response.data.map(
            (country: { phone_code: any; image: any; length: any }) => ({
              code: country.phone_code,
              flag: country.image,
              phoneLength: country.length,
            })
          );

          // Initialize both filtered lists
          this.filteredCountries = [...this.countryList];
          this.filteredWhatsappCountries = [...this.countryList];

          // Filter allowed countries for both
          const allowedCountryCodes = [
            '+20',
            '+962',
            '+964',
            '+212',
            '+963',
            '+965',
            '+966',
          ];
          this.filteredCountries = this.filteredCountries.filter(
            (country: any) =>
              allowedCountryCodes.includes(country.code.replace(/\s+/g, ''))
          );
          this.filteredWhatsappCountries =
            this.filteredWhatsappCountries.filter((country: any) =>
              allowedCountryCodes.includes(country.code.replace(/\s+/g, ''))
            );

          if (callback) callback();
        }
      },
      error: () => {
        console.error('Failed to load country data.');
      },
    });
  }
  private restoreFormData() {
    if (localStorage.getItem('selected_address')) {
      // return fatma
    }
    const savedFormData = localStorage.getItem('form_data');

    if (!savedFormData) return;

    this.isRestoring = true; // ✅ Move this to the top before anything happens

    const parsedData = JSON.parse(savedFormData);
    this.form.patchValue(parsedData);

    const propertyType = parsedData.address_type;
    if (propertyType) {
      this.previousProperty = propertyType;
      this.selectedProperty = propertyType;
    }

    const savedCode = parsedData.country_code;
    if (savedCode) {
      const match = this.countryList.find((c) => c.code === savedCode.code);

      if (match) this.selectCountry(match);
    }

    // ✅ Apply type logic after patching
    // this.selectPropertyType(this.selectedProperty as PropertyType);

    this.isRestoring = false;
  }
  getDataForSelectedUser() {
    const formData = JSON.parse(localStorage.getItem('form_data')!);

    return {};
  }
  private initializeForm() {
    this.form = this.formBuilder.group({
      searchTerm: [''],
      searchTermhotel: [''],
      searchTermArea: [''],
      hotel_id: '',
      client_name: [
        '',
        [
          Validators.required,
          Validators.minLength(2),
          Validators.maxLength(100),
          Validators.pattern(/^[\u0600-\u06FFa-zA-Z\s]+$/), // فقط أحرف عربية وإنجليزية ومسافات
          this.noLeadingSpaceValidator(),
          this.noOnlySpacesValidator()
        ]
      ],
      address_phone: [
        '',
        [
          Validators.required,
          this.noLeadingSpaceValidator(),
          Validators.pattern(/^\d+$/), // فقط أرقام
          this.phoneLengthValidator()
        ]
      ],
      whatsapp_number_code: [this.selectedWhatsappCountry, Validators.required],
      whatsapp_number: [
        '',
        [
          Validators.required,
          this.noLeadingSpaceValidator(),
          Validators.pattern(/^\d+$/), // فقط أرقام
          this.whatsappLengthValidator()
        ]
      ],
      country_code: [this.selectedCountry, [Validators.required]], // ✅ تعيين قيمة افتراضية
      apartment_number: [
        '',
        [
          Validators.required,
          Validators.minLength(1),
          Validators.maxLength(10),
          Validators.pattern(/^[a-zA-Z0-9\u0600-\u06FF\s\-]+$/) // أحرف وأرقام وشرطات
        ]
      ],
      building: [
        '',
        [
          Validators.required,
          Validators.minLength(2),
          Validators.maxLength(100),
          Validators.pattern(/^[\u0600-\u06FFa-zA-Z0-9\s\-\.]+$/) // أحرف عربية وإنجليزية وأرقام
        ]
      ],
      address_type: [this.selectedProperty, Validators.required], // ✅ تعيين قيمة افتراضية
      address: [
        '',
        [
          Validators.required,
          Validators.minLength(5),
          Validators.maxLength(255),
          Validators.pattern(/^[\u0600-\u06FFa-zA-Z0-9\s\-\.،,]+$/), // أحرف عربية وإنجليزية وأرقام وعلامات ترقيم
          this.noLeadingSpaceValidator()
        ]
      ],
      buildingName: [
        '',
        [
          Validators.minLength(2),
          Validators.maxLength(100),
          Validators.pattern(/^[\u0600-\u06FFa-zA-Z0-9\s\-\.]*$/)
        ]
      ],
      notes: [
        '',
        [
          Validators.maxLength(500),
          Validators.pattern(/^[\u0600-\u06FFa-zA-Z0-9\s\-\.،,!@#$%^&*()]*$/)
        ]
      ],
      floor_number: [
        '',
        [
          Validators.required,
          Validators.min(0),
          Validators.max(100),
          Validators.pattern(/^\d+$/) // فقط أرقام
        ]
      ],
      landmark: [
        '',
        [
          Validators.maxLength(100),
          Validators.pattern(/^[\u0600-\u06FFa-zA-Z0-9\s\-\.]*$/)
        ]
      ],
      villaName: [
        '',
        [
          Validators.minLength(2),
          Validators.maxLength(100),
          Validators.pattern(/^[\u0600-\u06FFa-zA-Z0-9\s\-\.]*$/)
        ]
      ],
      villaNumber: [
        '',
        [
          Validators.minLength(1),
          Validators.maxLength(10),
          Validators.pattern(/^[a-zA-Z0-9\u0600-\u06FF\s\-]*$/)
        ]
      ],
      companyName: [
        '',
        [
          Validators.minLength(2),
          Validators.maxLength(100),
          Validators.pattern(/^[\u0600-\u06FFa-zA-Z0-9\s\-\.]*$/)
        ]
      ],
      area_id: ['', Validators.required],
    });

    // تحديث الـ validators الديناميكية
    this.updateDynamicValidators();
  }
  // Custom Validators
  phoneLengthValidator(): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      if (!control.value) return null;

      const value = control.value.toString();
      const expectedLength = this.selectedCountry?.phoneLength || 11;

      return value.length === expectedLength ? null : {
        phoneLength: {
          requiredLength: expectedLength,
          actualLength: value.length
        }
      };
    };
  }

  whatsappLengthValidator(): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      if (!control.value || this.useSameNumberForWhatsapp) return null;

      const value = control.value.toString();
      const expectedLength = this.selectedWhatsappCountry?.phoneLength || 11;

      return value.length === expectedLength ? null : {
        whatsappLength: {
          requiredLength: expectedLength,
          actualLength: value.length
        }
      };
    };
  }

  noOnlySpacesValidator(): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      const value = control.value;
      if (value && value.trim().length === 0) {
        return { onlySpaces: true };
      }
      return null;
    };
  }

  noLeadingSpaceValidator(): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      const value = control?.value as string;
      if (value && value.trimStart().length !== value.length) {
        return { leadingSpace: true };
      }
      return null;
    };
  }

  emailValidator(): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      if (!control.value) return null;

      const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
      return emailRegex.test(control.value) ? null : { invalidEmail: true };
    };
  }

  // دالة لتحديث الـ validators الديناميكية
  private updateDynamicValidators(): void {
    // تحديث validators رقم الهاتف بناءً على الدولة المختارة
    this.form.get('country_code')?.valueChanges.subscribe((country: Country) => {
      if (country) {
        const phoneControl = this.form.get('address_phone');
        phoneControl?.setValidators([
          Validators.required,
          this.noLeadingSpaceValidator(),
          Validators.pattern(/^\d+$/),
          Validators.minLength(country.phoneLength),
          Validators.maxLength(country.phoneLength)
        ]);
        phoneControl?.updateValueAndValidity();
      }
    });

    // تحديث validators الواتساب بناءً على حالة الاستخدام
    this.form.get('whatsapp_number_code')?.valueChanges.subscribe((country: Country) => {
      if (country && !this.useSameNumberForWhatsapp) {
        const whatsappControl = this.form.get('whatsapp_number');
        whatsappControl?.setValidators([
          Validators.required,
          this.noLeadingSpaceValidator(),
          Validators.pattern(/^\d+$/),
          Validators.minLength(country.phoneLength),
          Validators.maxLength(country.phoneLength)
        ]);
        whatsappControl?.updateValueAndValidity();
      }
    });
  }
  toggleDropdown() {
    this.dropdownOpen = !this.dropdownOpen;
  }

  filterCountries() {
    const searchValue = this.form.get('searchTerm')?.value || '';
    this.filteredCountries = this.countryList.filter((country) =>
      country.code.toLowerCase().includes(searchValue.toLowerCase())
    );
  }
  selectCountry(country: Country) {
    this.selectedCountry = country;
    console.log('Selected country:', country);

    // ✅ تعيين القيمة في الفورم بشكل صحيح
    this.form.get('country_code')?.setValue(country);
    this.form.get('country_code')?.markAsTouched();

    this.dropdownOpen = false;
    this.form.get('searchTerm')?.setValue('');
    this.filteredCountries = [...this.countryList];

    // ✅ تحديث validators رقم الهاتف
    const phoneControl = this.form.get('address_phone');
    if (phoneControl) {
      phoneControl.setValidators([
        Validators.required,
        Validators.pattern(new RegExp(`^\\d{${country.phoneLength}}$`)),
      ]);
      phoneControl.updateValueAndValidity();
    }
  }

  propertyFormValues: { [key in PropertyType]?: any } = {};

  // Update the existing selectPropertyType function
  selectPropertyType(property: PropertyType) {
    console.log('🏨 اختيار نوع العقار:', property);

    this.selectedProperty = property;
    this.form.get('address_type')?.setValue(property);

    // ✅ تحميل الفنادق فوراً عند اختيار نوع الفندق
    if (property === 'hotel') {
      this.ensureHotelsLoaded();
    }

    this.clearPropertyValidators();

    // ✅ تحديث الـ validators بناءً على نوع العقار
    const addressControl = this.form.get('address');
    const buildingControl = this.form.get('building');
    const apartmentNumberControl = this.form.get('apartment_number');
    const floorNumberControl = this.form.get('floor_number');

    switch (property) {
      case 'apartment':
        buildingControl?.setValidators([Validators.required, Validators.pattern(/^(?!\s+$).+/)]);
        apartmentNumberControl?.setValidators([Validators.required, Validators.pattern(/^(?!\s+$).+/)]);
        floorNumberControl?.setValidators([Validators.required, Validators.pattern(/^(?!\s+$).+/)]);
        addressControl?.setValidators([Validators.required, Validators.pattern(/^(?!\s+$).+/)]);
        break;

      case 'villa':
        buildingControl?.setValidators([Validators.required, Validators.pattern(/^(?!\s+$).+/)]);
        apartmentNumberControl?.setValidators([Validators.required, Validators.pattern(/^(?!\s+$).+/)]);
        addressControl?.setValidators([Validators.required, Validators.pattern(/^(?!\s+$).+/)]);
        break;

      case 'office':
        buildingControl?.setValidators([Validators.required, Validators.pattern(/^(?!\s+$).+/)]);
        apartmentNumberControl?.setValidators([Validators.required, Validators.pattern(/^(?!\s+$).+/)]);
        floorNumberControl?.setValidators([Validators.required, Validators.pattern(/^(?!\s+$).+/)]);
        addressControl?.setValidators([Validators.required, Validators.pattern(/^(?!\s+$).+/)]);
        break;

      case 'hotel':
        // ✅ للفندق، نزيل الـ required من الحقول الأخرى
        buildingControl?.clearValidators();
        apartmentNumberControl?.clearValidators();
        floorNumberControl?.clearValidators();

        // ✅ العنوان يكون مطلوباً
        addressControl?.setValidators([Validators.required, Validators.pattern(/^(?!\s+$).+/)]);
        break;

      default:
        this.clearPropertyValidators();
        break;
    }

    buildingControl?.updateValueAndValidity();
    apartmentNumberControl?.updateValueAndValidity();
    floorNumberControl?.updateValueAndValidity();
    addressControl?.updateValueAndValidity();
  }
  // Add a function to clear all property-specific validators
  private clearPropertyValidators() {
    const controlsToClear = [
      'building',
      'apartment_number',
      'floor_number',
      'address', // Address field is common but required only when adding new address
      // 'notes' is optional, no need to clear its validators
    ];

    controlsToClear.forEach(controlName => {
      const control = this.form.get(controlName);
      if (control) {
        control.clearValidators();
        control.updateValueAndValidity();
        // Optional: Reset control state to hide error messages immediately
        // control.markAsUntouched();
        // control.markAsPristine();
      }
    });

    // Specifically for hotel selection, you might have a separate logic outside the form
    this.selectedHotel = null;
  }
  private resetPropertyFields(): void {
    const fieldsToReset = [
      'buildingName',
      'floor_number',
      'companyName',
      'apartment_number',
      'building',
      'villaName',
      'villaNumber',
      'buildingNumber',
      'landmark',
      'notes',
    ];

    fieldsToReset.forEach((field) => {
      this.form.get(field)?.reset('');
    });

    this.form.get('address')?.reset('');
  }

  // private resetPropertyFields(): void {
  //   this.form.patchValue({
  //     buildingName: '',
  //     apartmentNumber: '',
  //     floor_number: '',
  //     building: '',
  //     villaName: '',
  //     villaNumber: '',
  //     companyName: '',
  //     buildingNumber: '',
  //     landmark: '',
  //     address: '',
  //     notes:''
  //   });

  //   this.form.controls['address'].markAsPristine();
  //   this.form.controls['address'].markAsUntouched();
  // }

  // onSubmit(): void {
  //   this.submitted = true;
  //   // if (this.form.invalid) {
  //   //   this.form.markAllAsTouched(); // يجعل كل الحقول كأنها "touched" ليُظهر الرسائل
  //   //   return;
  //   // }
  //   const noteValue = this.form.get('notes')?.value;

  //   const formDataWithNote = { ...this.form.value, notes: noteValue };

  //   this.formDataService.submitForm(formDataWithNote).subscribe({
  //     next: (response) => {
  //       console.log('Full form submission response:', response);

  //       if (!response.status) {
  //         console.warn('Response status is false');
  //         this.handleBackendErrors(response.errorData);
  //         return;
  //       }

  //       if (!response.data || !response.data.address_id) {
  //         console.warn('Missing address_id in response data:', response.data);
  //         return;
  //       }

  //       const noteValue = this.form.get('notes')?.value;

  //       this.form.patchValue({ address_id: response.data.address_id });

  //       const finalResponseData = {
  //         ...response.data,
  //         notes: noteValue,
  //       };

  //       // ✅ Safe logging before localStorage set
  //       console.log('Saving address_id to localStorage:', response.data.address_id);

  //       localStorage.setItem('address_id', response.data.address_id.toString());
  //       localStorage.setItem('form_data', JSON.stringify(this.form.value));
  //       localStorage.setItem('notes', noteValue);

  //       console.log('Successfully saved to localStorage, navigating back');
  //       this.resetForm();
  //       this.location.back();
  //     }
  //     ,
  //     error: (err) => {
  //       console.error('❌ Error submitting form:', err);
  //       if (err.error && err.error.errorData) {
  //         this.handleBackendErrors(err.error.errorData);
  //       }
  //     },
  //   });
  // }
  onSubmit(): void {
    this.submitted = true;
    // ✅ Skip validation for hotel-specific fields when in hotel tab
    // ✅ تحقق خاص من الفندق
    if (this.selectedProperty === 'hotel') {
      if (!this.selectedHotel) {
        console.error('❌ يجب اختيار فندق قبل الإرسال');
        this.form.get('address')?.setErrors({ hotelRequired: true });
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
      } else {
        console.log('✅ فندق مختار:', this.selectedHotel.name);

        // ✅ استخدام اسم الفندق إذا كان العنوان غير متوفر
        const hotelAddress = this.selectedHotel.address || this.selectedHotel.name;

        // ✅ تعيين بيانات الفندق في الفورم
        this.form.patchValue({
          hotel_id: this.selectedHotel.id,
          address: hotelAddress,
        });
        console.log('📍 العنوان النهائي:', hotelAddress);
      }
    }
    if (this.selectedProperty !== 'hotel') {
      this.form.get('building')?.updateValueAndValidity();
      this.form.get('apartment_number')?.updateValueAndValidity();
      this.form.get('floor_number')?.updateValueAndValidity();
    }
    this.form.get('address')?.updateValueAndValidity();

    // Manually check hotel if the tab is selected
    const isHotelValid = this.selectedProperty !== 'hotel' || this.selectedHotel;

    if (this.form.invalid || !isHotelValid) {
      console.log('Form is invalid. Stopping submission.');
      this.logInvalidFields();
      return;
    }

    // ✅ التأكد من تعيين القيم المطلوبة قبل الإرسال
    if (this.useSameNumberForWhatsapp) {
      this.form.patchValue({
        whatsapp_number: this.form.get('address_phone')?.value || '',
        whatsapp_number_code: this.form.get('country_code')?.value || this.selectedWhatsappCountry
      });
    }

    // ✅ التأكد من تعيين address_type
    if (!this.form.get('address_type')?.value) {
      this.form.patchValue({
        address_type: this.selectedProperty
      });
    }

    // ✅ التأكد من أن country_code له قيمة
    if (!this.form.get('country_code')?.value) {
      this.form.patchValue({
        country_code: this.selectedCountry
      });
    }

    // ✅ التأكد من أن whatsapp_number_code له قيمة
    if (!this.form.get('whatsapp_number_code')?.value && !this.useSameNumberForWhatsapp) {
      this.form.patchValue({
        whatsapp_number_code: this.selectedWhatsappCountry
      });
    }

    if (this.userAddNewAddress == false) {
      this.form.markAllAsTouched();
      this.storeAddressinLocalStorage();
      return;
    }

    if (this.form.invalid) {
      this.form.markAllAsTouched();
      console.log('Form validation errors:', this.form.errors);
      Object.keys(this.form.controls).forEach(key => {
        const control = this.form.get(key);
        if (control?.invalid) {
          console.log(`❌ ${key}:`, control.errors, 'Value:', control.value);
        }
      });

      // if (this.selectedProperty === 'hotel') {
      //   console.log('Selected Hotel:', this.selectedHotel);
      //   localStorage.setItem('hotel_id', this.selectedHotel?.id);
      //   this.form.patchValue({
      //     hotel_id: this.selectedHotel?.id,
      //     address: this.selectedHotel?.address,
      //   });
      // } else {
      //   if (this.form.get('hotel_id')) {
      //     this.form.removeControl('hotel_id');
      //   }
      // }

      window.scrollTo({ top: 0, behavior: 'smooth' });
      return;
    }

    // ✅ إعداد البيانات النهائية للإرسال
    const noteValue = this.form.get('notes')?.value;

    if (this.selectedProperty === 'hotel') {
      const hotelAddress = this.selectedHotel.address || this.selectedHotel.name;
      this.form.patchValue({
        hotel_id: this.selectedHotel.id,
        address: hotelAddress,
      });
    } else {
      if (this.form.get('hotel_id')) {
        this.form.removeControl('hotel_id');
      }
    }

    // ✅ التأكد النهائي من جميع الحقول المطلوبة - بس مرة واحدة!
    const finalFormData = {
      ...this.form.value,
      notes: noteValue,
      address_type: this.form.get('address_type')?.value || this.selectedProperty,
      country_code: this.form.get('country_code')?.value?.code || this.selectedCountry.code,
      whatsapp_number_code: this.form.get('whatsapp_number_code')?.value?.code || this.selectedWhatsappCountry.code
    };

    console.log('✅ Final data to be saved:', finalFormData);
    console.log('✅ country_code value:', finalFormData.country_code);
    // ✅ أضف هنا - قبل الإرسال مباشرة
    console.log('📤 البيانات المرسلة للـ Backend:', {
      hotel_id: this.selectedHotel?.id,
      address: this.form.get('address')?.value,
      address_type: this.selectedProperty,
      country_code: this.form.get('country_code')?.value?.code,
      area_id: this.form.get('area_id')?.value,
      client_name: this.form.get('client_name')?.value,
      address_phone: this.form.get('address_phone')?.value,
      whatsapp_number: this.form.get('whatsapp_number')?.value,
      whatsapp_number_code: this.form.get('whatsapp_number_code')?.value?.code
    });
    // ✅ إرسال للـ Backend
    this.formDataService.submitForm(finalFormData).subscribe({
      next: (response) => {
        console.log('🔵 Backend Response:', response);

        if (response.status) {
          // نجح الإرسال
          localStorage.setItem('form_data', JSON.stringify(finalFormData));
          localStorage.setItem('address_id', response.data.address_id);
          localStorage.setItem('notes', noteValue);
          // ✅ حفظ اسم الفندق إذا كان نوع العنوان فندق
          if (this.selectedProperty === 'hotel' && this.selectedHotel?.name) {
            localStorage.setItem('hotel_name', this.selectedHotel.name);
            localStorage.setItem('hotel_id', this.selectedHotel.id);
          }
          // ✅ حفظ في IndexedDB
          this.dbService.saveFormData(finalFormData).then(id => {
            console.log('✅ Form data saved to IndexedDB with ID:', id);
          }).catch(err => {
            console.error('❌ Error saving form data to IndexedDB:', err);
          });

          // ✅ حفظ رسوم التوصيل
          const selectedAreaId = this.form.get('area_id')?.value;
          if (selectedAreaId && this.areas && Array.isArray(this.areas)) {
            const selectedArea = this.areas.find((area) => area.id == selectedAreaId);
            if (selectedArea) {
              localStorage.setItem('delivery_fees', selectedArea.delivery_fees);
              console.log('💰 Delivery fees saved:', selectedArea.delivery_fees);
            }
          }

          localStorage.setItem('deliveryForm', JSON.stringify(this.form.value));
          console.log('🔙 Navigating back after API success');
          this.location.back();
        } else {
          // فشل الإرسال - عرض الأخطاء
          console.error('🔴 Backend Validation Errors:', response.errorData);
          this.handleBackendErrors(response.errorData);
        }
      },
      error: (error) => {
        console.error('🔴 HTTP Error:', error);
      }
    });
  }
  ensureHotelsLoaded(): void {
    if (!this.hotels || this.hotels.length === 0) {
      console.log('📡 تحميل قائمة الفنادق...');
      this.getHotels();
    } else {
      console.log('✅ الفنادق محملة مسبقاً:', this.hotels.length);
    }
  }
  // دالة للتحقق من اكتمال جميع البيانات المطلوبة
  validateFormBeforeSubmit(): boolean {
    const requiredFields = [
      'client_name',
      'address_phone',
      'country_code',
      'area_id',
      // 'address_type',
      'address'
    ];

    let isValid = true;

    requiredFields.forEach(field => {
      const control = this.form.get(field);
      if (!control || !control.value) {
        console.error(`❌ Missing required field: ${field}`);
        isValid = false;
      }
    });

    // تحقق خاص من country_code و whatsapp_number_code
    if (!this.form.get('country_code')?.value) {
      console.error('❌ country_code is required');
      isValid = false;
    }

    if (!this.useSameNumberForWhatsapp && !this.form.get('whatsapp_number_code')?.value) {
      console.error('❌ whatsapp_number_code is required when using different number');
      isValid = false;
    }

    return isValid;
  }
  whatsapp: any;
  private handleBackendErrors(errors: any): void {
    if (!errors) return;

    console.log('Backend validation errors:', errors);

    Object.keys(errors).forEach((field) => {
      const control = this.form.get(field);
      if (control) {
        const errorMessage = Array.isArray(errors[field])
          ? errors[field][0]
          : errors[field];
        control.setErrors({ serverError: errorMessage });
        control.markAsTouched();
        control.markAsDirty();
      }
    });
  }

  private resetForm() {
    this.form.reset();
    this.form.markAsPristine();
    this.form.markAsUntouched();
    this.submitted = false;
    this.selectedProperty = '';

    if (this.countryList.length > 0) {
      this.selectCountry(this.countryList[0]);
    }
  }

  get f(): { [key: string]: AbstractControl } {
    return this.form.controls;
  }

  get addressPhone() {
    return this.form.get('address_phone');
  }
  get clientName() {
    return this.form.get('client_name');
  }
  get countryCode() {
    return this.form.get('country_code');
  }
  get floorNumber() {
    return this.form.get('floor_number');
  }
  get address() {
    return this.form.get('address');
  }
  get building() {
    return this.form.get('building');
  }

  get appartmentNumber() {
    return this.form.get('apartment_number');
  }
  get notes() {
    return this.form.get('notes');
  }
  // _____________________________
  areas: any[] = [];
  // const url = `https://alkoot-restaurant.com/api/areas/${branchId}`;

  getAreas() {
    const branchId = localStorage.getItem('branch_id');

    if (!branchId) {
      console.error('branch_id not found in localStorage');
      return;
    }
    // start hanan
    // First try to dbServiceget areas from IndexedDB
    this.dbService.getAll('areas').then(areas => {
      if (areas && areas.length > 0) {
        this.areas = areas;
        this.allAreas = areas;
        console.log('Areas loaded from IndexedDB', this.areas);
      }

      // Then try to get from API
      const url = `${baseUrl}api/areas/${branchId}`;
      this.http.get<any>(url).subscribe({
        next: (res: { status: any; data: any }) => {
          if (res.status && res.data) {
            this.areas = res.data;
            this.allAreas = res.data;

            // Save to IndexedDB
            this.dbService.saveData('areas', res.data);


            console.log('Areas loaded from API and saved to IndexedDB', this.areas);
          }
        },
        error: (err) => {
          console.error('Error loading areas from API, using cached data:', err);
        },
      });
    }).catch(err => {
      console.error('Error loading areas from IndexedDB:', err);

      // Fallback to API if IndexedDB fails
      const url = `${baseUrl}api/areas/${branchId}`;
      this.http.get<any>(url).subscribe({
        next: (res: { status: any; data: any }) => {
          if (res.status && res.data) {
            this.areas = res.data;
            this.allAreas = res.data;
            console.log('Areas loaded from API (fallback)', this.areas);
          }
        },
        error: (err) => {
          console.error('Error loading areas from API (fallback):', err);
        },
      });
    });
    // end hanan
  }
  propertyLabels: any = {
    apartment: {
      building: 'اسم المبنى',
      buildingExample: 'المبنى الرحاب',
      apartment_number: 'رقم الشقة',
      apartmentNumberExample: 'الشقة 12',
      floor_number: 'رقم الدور',
      floorNumberExample: 'الدور 2',
      address: 'العنوان',
      addressExample: '121 مصدق الدقي ,المهندسين الجيزه',
      notes: 'علامة مميزة (اختياري)',
    },
    villa: {
      building: 'اسم الفيلا',
      buildingExample: 'فيلا الرحاب',
      apartment_number: 'رقم الفيلا',
      apartmentNumberExample: 'الفيلا 12',

      address: 'العنوان',
      addressExample: '121 مصدق الدقي ,المهندسين الجيزه',
      notes: 'علامة مميزة (اختياري)',
    },
    office: {
      building: 'اسم المكتب',
      buildingExample: 'مكتب الرحاب',
      apartment_number: 'رقم المكتب',
      apartmentNumberExample: 'المكتب 12',
      floor_number: 'رقم الدور',
      floorNumberExample: 'الدور 2',
      address: 'العنوان',
      addressExample: '121 مصدق الدقي ,المهندسين الجيزه',
      notes: 'علامة مميزة (اختياري)',
    },
  };

  getValidationMessage(controlName: string): string | null {
    const control = this.form.get(controlName);
    if (!control || !control.errors) return null;

    const labels = this.propertyLabels[this.selectedProperty]; // no error now
    const fieldLabel =
      labels[controlName as keyof typeof labels] ?? 'هذا الحقل';

    if (control.errors['required']) {
      return `${fieldLabel} مطلوب`;
    }
    if (control.errors['pattern']) {
      return `${fieldLabel} لا يمكن أن يحتوي على مسافات فقط`;
    }
    if (control.errors['serverError']) {
      return control.errors['serverError'];
    }
    return null;
  }
  hotels: any;
  // getHotels() {
  //   return this.formDataService.getHotelsData().subscribe({
  //     next: (res: any) => {
  //       console.log(res.data);
  //       this.hotels = res.data;

  //       this.allHotels = res.data;
  //       this.hotels = [...this.allHotels];
  //     },
  //     error: (err) => {
  //       console.log(err);
  //     },
  //   });
  // }

  // start hanan
  getHotels() {
    // First try to get hotels from IndexedDB
    this.dbService.getAll('hotels').then(hotels => {
      if (hotels && hotels.length > 0) {
        this.hotels = hotels;
        this.allHotels = hotels;
        console.log('Hotels loaded from IndexedDB', this.hotels);
      }

      // Then try to get from API
      this.formDataService.getHotelsData().subscribe({
        next: (res: any) => {
          if (res.data) {
            this.hotels = res.data;
            this.allHotels = res.data;

            // Save to IndexedDB
            this.dbService.saveData('hotels', res.data);
            // this.dbService.lastSync('hotels');

            console.log('Hotels loaded from API and saved to IndexedDB', this.hotels);
          }
        },
        error: (err) => {
          console.error('Error loading hotels from API, using cached data:', err);
        },
      });
    }).catch(err => {
      console.error('Error loading hotels from IndexedDB:', err);

      // Fallback to API if IndexedDB fails
      this.formDataService.getHotelsData().subscribe({
        next: (res: any) => {
          if (res.data) {
            this.hotels = res.data;
            this.allHotels = res.data;
            console.log('Hotels loaded from API (fallback)', this.hotels);
          }
        },
        error: (err) => {
          console.log('Error loading hotels from API (fallback):', err);
        },
      });
    });
  }
  // end hanan
  selectedHotel: any;
  onHotelChange(hotel: any) {
    this.selectedHotel = hotel;
    console.log('🏨 تم اختيار الفندق:', hotel);
    localStorage.setItem('selectedHotel', JSON.stringify(this.selectedHotel));

    const addressControl = this.form.get('address');

    if (hotel === 'another') {
      // المستخدم سيكتب العنوان بنفسه → مطلوب
      addressControl?.setValidators([
        Validators.required,
        this.noOnlySpacesValidator(),
      ]);
      addressControl?.setValue('');
    } else {
      // ✅ استخدام اسم الفندق إذا كان العنوان غير متوفر
      const hotelAddress = hotel.address || hotel.name;
      addressControl?.setValue(hotelAddress);
      console.log('📍 العنوان المعين:', hotelAddress);
    }

    // ✅ تعيين hotel_id دائماً
    this.form.get('hotel_id')?.setValue(hotel.id);

    addressControl?.updateValueAndValidity();

    // ✅ إغلاق القائمة المنسدلة وإعادة تعيين البحث
    this.form.get('searchTermhotel')?.setValue('');
    this.hotels = [...this.allHotels];
  }

  allHotels: any[] = [];
  allAreas: any[] = [];
  allUserAddress: any[] = [];
  searchHotel() {
    const searchValue = this.form.get('searchTermhotel')?.value?.trim() || '';
    if (!searchValue) {
      this.hotels = [...this.allHotels];
      return;
    }
    this.hotels = this.allHotels.filter((hotel: any) =>
      hotel.name.toLowerCase().includes(searchValue.toLowerCase())
    );
  }
  sumbitSearch: boolean = false;
  searchPhoneNumber() {
    // this.phoneNotFound = undefined;
    // this.userStoredAddress=[]

    console.log('fatema', this.form);
    this.resetToSearchphone();
    this.sumbitSearch = true;
    localStorage.removeItem('selected_address');
    if (this.addressPhone?.valid && this.form.controls['country_code'].valid) {
      this.searchPhoneLoading = false;
      const body = {
        country_code: this.selectedCountry.code,
        address_phone: this.addressPhone?.value,
      };
      this.checkPhoneNum
        .checkPhone(body)
        .pipe(
          finalize(() => {
            this.searchPhoneLoading = true;
            this.sumbitSearch = false;
          })
        )
        .subscribe({
          next: (res) => {
            if (res.status == true) {
              if (typeof res.data === 'object' && res.data !== null) {
                // this.allUserAddress = {...res.data,country_code:{code:res.data.country_code,flag:res.data['country_flag']||null}};

                this.clientName?.setValue(res.data[0].user_name)
                this.allUserAddress = res.data.map((item: any) => ({
                  ...item,
                  country_code: {
                    code: item?.country_code ?? null,
                    flag: item?.country_flag ?? null,
                  },
                }));// fatma: must ask BE to return country_code as object of flag,code not code only

                this.userStoredAddress = res.data.map((address: any) => {
                  this.userId = address.user_id;

                  // ✅ Save delivery fees from the first address
                  if (address.delivery_fees) {
                    localStorage.setItem(
                      'delivery_fees',
                      address.delivery_fees
                    );
                  }
                  return {
                    id: address.id,
                    name: address.address_type + ' , ' + (address.hotels.length > 0 ? address.hotels[0].name : address.address),
                    delivery_fees: address.delivery_fees,
                    client_name: address.user_name
                    // name: address.address,
                  };
                });
                this.confirmationDialog.confirm();
              } else {
                this.phoneNotFound = res.data;
              }
            } else {
              this.phoneNotFound = res.message;
            }
            console.log('search phone res', res);
          },
          error: (err) => {
            this.phoneNotFound = err.error.message;
          },
        });
    }
  }
  addNewAddress(addAddress: boolean) {
    console.log('add new address', addAddress);
    this.userAddNewAddress = addAddress;
    if (addAddress) {
      // localStorage.removeItem('form_data');
      localStorage.removeItem('address_id');
      localStorage.removeItem('selected_address');
    }
  }
  storeAddressinLocalStorage() {
    if (
      this.addressPhone?.valid &&
      this.form.controls['country_code'].valid &&
      this.form.controls['client_name'].valid
    ) {
      localStorage.removeItem('notes');
      // getall address data
      const storedAddressData = this.allUserAddress.find(
        (item) => item.id == this.selectedAddressControl.value.id
      );

      const formData = {
        ...storedAddressData,
        client_name: this.clientName?.value || storedAddressData.user_name,
        whatsapp_number: this.whatsappPhone, // hereeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee
        whatsapp_number_code: this.form.get('whatsapp_number_code')?.value,
      };

      localStorage.setItem('form_data', JSON.stringify(formData));
      console.log('tracee', this.allUserAddress);

      localStorage.setItem('address_id', this.selectedAddressControl.value.id);
      localStorage.setItem(
        'delivery_fees',
        this.selectedAddressControl.value.delivery_fees
      );

      localStorage.setItem(
        'selected_address',
        JSON.stringify(this.selectedAddressControl.value)
      );
      this.location.back();
    } else {
      console.warn('phone or name or seleced address not valid');
    }
  }
  resetToSearchphone() {
    console.log('resetToSearchphone');
    this.userStoredAddress = [];

    this.phoneNotFound = undefined;
    this.selectedAddress = [];
    this.userAddNewAddress = true;

    if (
      JSON.parse(localStorage.getItem('form_data')!)?.address_phone !=
      this.form.get('address_phone')?.value
    ) {
      localStorage.removeItem('selected_address');
      localStorage.removeItem('address_id');
      localStorage.removeItem('delivery_fees');
    }
    // localStorage.removeItem('form_data');
  }
  // useSameWhatsapp(useSame: boolean) {
  //   this.useSameNumberForWhatsapp = useSame;

  //   if (useSame) {
  //     const phone = this.form.get('address_phone')?.value || '';
  //     const code = this.form.get('country_code')?.value || '';

  //     this.whatsappPhone = phone;
  //     this.whatsappCountryCode = code;

  //     // Patch both into form directly
  //     this.form.patchValue({
  //       whatsapp_number: phone,
  //       whatsapp_number_code: code,
  //     });
  //   } else {
  //     this.whatsappPhone = this.whatsappPhone;
  //   }
  // }
  get whatsappNumberCode() {
    return this.form.get('whatsapp_number_code');
  }
  listenToChangeWhatsappCountry() {
    this.whatsappNumberCode?.valueChanges.subscribe((value) => {
      const whatsappNumControl = this.form.get('whatsapp_number');
      if (value) {
        this.selectedWhatsappCountry = value;
        whatsappNumControl?.setValidators([Validators.required, Validators.pattern(
          new RegExp(`^\\d{${this.whatsappNumberCode?.value?.phoneLength}}$`)
        )]);
      } else {
        whatsappNumControl?.clearValidators();
      }
    });
  }
  listenToAddressChange() {
    this.selectedAddressControl.valueChanges
      .subscribe(arg => {
        this.clientName?.setValue(arg.client_name)

      });
  }
  // دالة للحصول على رسائل الخطأ
  getErrorMessage(controlName: string): string {
    const control = this.form.get(controlName);
    if (!control || !control.errors || !control.touched) return '';

    const errors = control.errors;

    if (errors['required']) return 'هذا الحقل مطلوب';
    if (errors['minlength']) return `الحد الأدنى ${errors['minlength'].requiredLength} أحرف`;
    if (errors['maxlength']) return `الحد الأقصى ${errors['maxlength'].requiredLength} أحرف`;
    if (errors['min']) return `القيمة يجب أن تكون ${errors['min'].min} أو أكثر`;
    if (errors['max']) return `القيمة يجب أن تكون ${errors['max'].max} أو أقل`;
    if (errors['pattern']) return 'التنسيق غير صحيح';
    if (errors['onlySpaces']) return 'لا يمكن أن يحتوي على مسافات فقط';
    if (errors['leadingSpace']) return 'لا يمكن أن يبدأ بمسافة';
    if (errors['phoneLength']) return `يجب أن يكون ${errors['phoneLength'].requiredLength} رقم`;
    if (errors['whatsappLength']) return `يجب أن يكون ${errors['whatsappLength'].requiredLength} رقم`;
    if (errors['invalidEmail']) return 'البريد الإلكتروني غير صحيح';
    if (errors['serverError']) return errors['serverError'];

    return 'قيمة غير صالحة';
  }

  // دالة للتحقق إذا كان الحقل صالحاً للعرض
  isFieldValid(controlName: string): boolean {
    const control = this.form.get(controlName);
    return (
      !!control &&
      control.invalid &&
      (control.dirty || control.touched || this.submitted)
    );
  }

  // دالة للحصول على class الـ CSS المناسب
  getFieldClass(controlName: string): string {
    const control = this.form.get(controlName);
    if (!control) return '';

    if (control.touched || this.submitted) {
      return control.valid ? 'is-valid' : 'is-invalid';
    }
    return '';
  }
}
// aml

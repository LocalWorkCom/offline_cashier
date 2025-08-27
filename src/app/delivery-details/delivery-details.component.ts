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
import { IndexeddbService } from '../services/indexeddb.service';

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

  whatsappCountryCode: string = this.selectedWhatsappCountry.code;
  whatsappPhone: any;

  // Add this property to track form data ID
  private formDataId: number | null = null;

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
    private dbService: IndexeddbService,
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
    this.dbService.init();
    if (this.selectedAddress) {
      this.userAddNewAddress = false;
    }
    this.initializeForm();
    this.restoreFormData();
    this.fetchCountries(() => {
      this.restoreFormData(); // only restore after countries are loaded,case problem fatema
    });
    this.getAreas();
    this.getHotels();

      // Load form data from IndexedDB
  this.loadFormDataFromIndexedDB();

    //  // Listen for form changes to save to IndexedDB
    // this.form.valueChanges.subscribe((formValue) => {
    //   this.saveFormDataToIndexedDB();
    // });


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
  }

   // New method to load form data from IndexedDB
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
    this.form.get('whatsapp_number_code')?.setValue(country);
    this.dropdownOpen = false;
    this.filteredWhatsappCountries = [...this.countryList];

    // Update validators after country change
    this.updateWhatsappValidators();
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
        [Validators.required, Validators.pattern(/^(?!\s*$).+/)],
      ],
      address_phone: [
        '',
        [
          Validators.required,
          this.noLeadingSpaceValidator(),
          Validators.pattern(
            new RegExp(`^\\d{${this.selectedWhatsappCountry.phoneLength}}$`)
          ),
        ],
      ],
      whatsapp_number_code: [this.selectedWhatsappCountry],
      whatsapp_number: [
        '',
        [
          Validators.required,
          this.noLeadingSpaceValidator(), // only numbers, exactly 11 digits
          Validators.pattern(
            new RegExp(`^\\d{${this.selectedCountry.phoneLength}}$`)
          ),
        ],
      ],
      country_code: [this.selectedCountry || '', [Validators.required]],
      apartment_number: [''], //       [Validators.required, Validators.pattern(/^(?!\s*$).+/)],
      building: [''], // , [Validators.required, Validators.pattern(/^(?!\s*$).+/)]
      address_type: ['apartment', Validators.required],
      address: ['', [Validators.required, Validators.pattern(/^(?!\s*$).+/)]],
      // propertyType: ['', Validators.required],
      buildingName: [''],
      notes: [''],
      floor_number: [''], //[Validators.required, Validators.pattern(/^(?!\s*$).+/)],
      landmark: [''],
      villaName: [''],
      villaNumber: [''],
      companyName: [''],
      // buildingNumber: [
      //   '',
      //   [Validators.required, Validators.pattern(/^(?!\s*$).+/)],
      // ],
      area_id: ['', Validators.required],
    });
    // Watch for changes in whatsapp_number

    this.form
      .get('whatsapp_number_code')
      ?.setValue(this.countryCode?.value || this.selectedWhatsappCountry);


    this.form.get('country_code')?.valueChanges.subscribe((value) => {
      this.selectedCountry = value;
      const phoneControl = this.form.get('address_phone');
      if (phoneControl) {
        phoneControl.setValidators([
          Validators.required,
          Validators.pattern(new RegExp(`^\\d{${value.phoneLength}}$`)),
        ]);
      }

    });

    this.form.get('whatsapp_number')?.valueChanges.subscribe((value) => {
      const codeControl = this.form.get('whatsapp_number_code');

      if (value && value.trim() !== '') {
        codeControl?.setValidators([Validators.required]);
      } else {
        codeControl?.clearValidators();
      }

      // codeControl?.updateValueAndValidity({ emitEvent: false });
    });
    this.listenToChangeWhatsappCountry()
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
    this.selectedCountry = country;      //assign user phone country
    this.selectedWhatsappCountry = country;  //assign whatsapp countrry
    console.log(this.selectedCountry, country, 'selectedCountry');
    this.dropdownOpen = false;
    this.form.get('country_code')?.setValue(country);
    this.form.get('country_code')?.markAsTouched();
    this.form.get('searchTerm')?.setValue('');
    this.filteredCountries = [...this.countryList];

    // Update phone number validators dynamically based on selected country
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

  selectPropertyType(type: 'apartment' | 'villa' | 'office' | 'hotel'): void {
    //  Save current values before switching (unless restoring from localStorage)
    if (!this.isRestoring && this.selectedProperty) {
      this.propertyFormValues[this.selectedProperty as PropertyType] = {
        building: this.form.get('building')?.value,
        apartment_number: this.form.get('apartment_number')?.value,
        floor_number: this.form.get('floor_number')?.value,
        address: this.form.get('address')?.value,
        notes: this.form.get('notes')?.value,
      };
    }
    this.selectedProperty = type;
    console.log('gg', localStorage.getItem('form_data'));

    this.form.patchValue({ address_type: type });
    if (this.selectedProperty === 'hotel') {
      console.log('hoteeeeeeeel', this.form);
    }

    const aptCtrl = this.form.get('apartment_number');
    const floorCtrl = this.form.get('floor_number');
    const buildingCtrl = this.form.get('building');

    // Clear all validators
    aptCtrl?.clearValidators();
    floorCtrl?.clearValidators();
    buildingCtrl?.clearValidators();

    // ✅ Only reset floor number if user changes property (not restoring)
    if (!this.isRestoring && type !== 'villa') {
      floorCtrl?.setValue('');
    }
    aptCtrl?.updateValueAndValidity();
    floorCtrl?.updateValueAndValidity();
    buildingCtrl?.updateValueAndValidity();

    //  Restore saved values if available, or reset fields
    const savedValues = this.propertyFormValues[type];
    if (savedValues) {
      console.log('saved previous value ', this.propertyFormValues);

      this.form.patchValue(savedValues);
    } else if (!this.isRestoring) {
      this.resetPropertyFields();
    }
    // if (!this.isRestoring) {
    //   this.resetPropertyFields();
    // }
  }

  noOnlySpacesValidator(): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      const value = control.value || '';
      return value.trim().length === 0 ? { pattern: true } : null;
    };
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

  noLeadingSpaceValidator(): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      const value = control?.value as string;

      if (value && value.trimStart().length !== value.length) {
        return { leadingSpace: true };
      }
      return null;
    };
  }

  // delivery-details.component.ts - Update the onSubmit method
onSubmit(): void {
  console.log('form', this.form);

  this.submitted = true;
  if (this.userAddNewAddress == false) {
    this.form.markAllAsTouched();
    this.storeAddressinLocalStorage();
    return;
  }

  if (this.form.invalid) {
    this.form.markAllAsTouched();
    console.warn('Form is invalid. Logging invalid fields:', this.form.status, this.form);

    if (this.selectedProperty === 'hotel') {
      console.log('ddddd', this.selectedHotel);
      localStorage.setItem('hotel_id', this.selectedHotel?.id);
      this.form.patchValue({
        hotel_id: this.selectedHotel?.id,
        address: this.selectedHotel?.address,
      });
    } else {
      if (this.form.get('hotel_id')) {
        this.form.removeControl('hotel_id');
      }
    }

    console.log(this.form.value);
    return;
  }

  const noteValue = this.form.get('notes')?.value;
  if (this.selectedProperty === 'hotel') {
    this.form.patchValue({
      hotel_id: this.selectedHotel?.id,
      address: this.selectedHotel?.address,
    });
  } else {
    if (this.form.get('hotel_id')) {
      this.form.removeControl('hotel_id');
    }
  }

  if (this.useSameNumberForWhatsapp) {
    this.whatsappPhone = this.form.get('address_phone')?.value || '';
    this.form
      .get('whatsapp_number_code')
      ?.setValue(this.form.get('country_code')?.value || '');
  }

  const formDataWithNote = {
    ...this.form.value,
    notes: noteValue,
    whatsapp_number: this.whatsappPhone,
    whatsapp_number_code: this.form.get('whatsapp_number_code')?.value,
  };

  console.log('✅ Saving to localStorage:', formDataWithNote);

  // Save to localStorage
  localStorage.setItem('form_data', JSON.stringify(formDataWithNote));
  localStorage.setItem('notes', noteValue);

  // SAVE TO INDEXEDDB ON SUBMIT
  this.dbService.saveFormData(formDataWithNote).then(id => {
    console.log('✅ Form data saved to IndexedDB with ID:', id);
  }).catch(err => {
    console.error('❌ Error saving form data to IndexedDB:', err);
  });

  const selectedAreaId = this.form.get('area_id')?.value;
  if (selectedAreaId && this.areas && Array.isArray(this.areas)) {
    const selectedArea = this.areas.find((area) => area.id == selectedAreaId);
    if (selectedArea) {
      localStorage.setItem('delivery_fees', selectedArea.delivery_fees);
      console.log('💰 Delivery fees saved:', selectedArea.delivery_fees);
    }
  }

  if (this.selectedAddress) {
    const selectedArea = this.areas.find((area) => area.id == selectedAreaId);
    localStorage.setItem('delivery_fees', selectedArea.delivery_fees);
    console.log('💰 Delivery fees saved:', selectedArea.delivery_fees);
  }

  localStorage.setItem('deliveryForm', JSON.stringify(this.form.value));
  console.log('🔙 Navigating back after local save');
  this.location.back();
}

  // onSubmit(): void {
  //   console.log('form', this.form);

  //   this.submitted = true;
  //   if (this.userAddNewAddress == false) {
  //     this.form.markAllAsTouched();
  //     this.storeAddressinLocalStorage();

  //     return;
  //   }
  //   if (this.form.invalid) {
  //     this.form.markAllAsTouched(); // Ensure errors are shown in UI

  //     console.warn(
  //       'Form is invalid. Logging invalid fields:',
  //       this.form.status,
  //       this.form
  //     );
  //     if (this.selectedProperty === 'hotel') {
  //       console.log('ddddd', this.selectedHotel);
  //       localStorage.setItem('hotel_id', this.selectedHotel?.id);

  //       this.form.patchValue({
  //         hotel_id: this.selectedHotel?.id,
  //         address: this.selectedHotel?.address,
  //       });
  //     } else {
  //       // احذف hotel_id من النموذج
  //       if (this.form.get('hotel_id')) {
  //         this.form.removeControl('hotel_id');
  //       }
  //     }
  //     console.log(this.form.value);

  //     /*       Object.keys(this.form.controls).forEach((key) => {
  //             const control = this.form.get(key);
  //             if (control && control.invalid) {
  //               console.log(`❌ Invalid field: ${key}`, control.errors);
  //             }
  //           }); */

  //     return;
  //   }

  //   const noteValue = this.form.get('notes')?.value;
  //   if (this.selectedProperty === 'hotel') {
  //     this.form.patchValue({
  //       hotel_id: this.selectedHotel?.id,
  //       address: this.selectedHotel?.address,
  //     });
  //   } else {
  //     // احذف hotel_id من النموذج
  //     if (this.form.get('hotel_id')) {
  //       this.form.removeControl('hotel_id');
  //     }
  //   }
  //   if (this.useSameNumberForWhatsapp) {
  //     this.whatsappPhone = this.form.get('address_phone')?.value || '';
  //     this.form
  //       .get('whatsapp_number_code')
  //       ?.setValue(this.form.get('country_code')?.value || '');
  //   }
  //   const formDataWithNote = {
  //     ...this.form.value,
  //     notes: noteValue,
  //     whatsapp_number: this.whatsappPhone, // hereeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee
  //     whatsapp_number_code: this.form.get('whatsapp_number_code')?.value, //.code
  //   };

  //   console.log('✅ Saving to localStorage:', formDataWithNote);
  //   localStorage.setItem('form_data', JSON.stringify(formDataWithNote));
  //   localStorage.setItem('notes', noteValue);

  //   // localStorage.setItem('address_id', 'DUMMY_ID');
  //   const selectedAreaId = this.form.get('area_id')?.value;

  //   if (selectedAreaId && this.areas && Array.isArray(this.areas)) {
  //     const selectedArea = this.areas.find((area) => area.id == selectedAreaId);
  //     if (selectedArea) {
  //       localStorage.setItem('delivery_fees', selectedArea.delivery_fees);
  //       console.log('💰 Delivery fees saved:', selectedArea.delivery_fees);
  //     }
  //   }
  //   if (this.selectedAddress) {
  //     const selectedArea = this.areas.find((area) => area.id == selectedAreaId);
  //     localStorage.setItem('delivery_fees', selectedArea.delivery_fees);
  //     console.log('💰 Delivery fees saved:', selectedArea.delivery_fees);
  //   }
  //   localStorage.setItem('deliveryForm', JSON.stringify(this.form.value));
  //   console.log('🔙 Navigating back after local save');
  //   // this.resetForm();
  //   this.location.back();
  // }
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

  // getAreas() {
  //   const branchId = localStorage.getItem('branch_id');

  //   if (!branchId) {
  //     console.error('branch_id not found in localStorage');
  //     return;
  //   }
  //   const url = `${baseUrl}api/areas/${branchId}`;

  //   this.http.get<any>(url).subscribe({
  //     next: (res: { status: any; data: any }) => {
  //       if (res.status && res.data) {
  //         this.areas = res.data;
  //         this.allAreas = res.data;
  //         this.areas = [...this.allAreas];
  //       }
  //       console.log(this.areas, 'areas');
  //     },
  //     error: (err) => {
  //       console.error('خطأ في تحميل المناطق:', err);
  //     },
  //   });
  // }

  getAreas() {
    const branchId = localStorage.getItem('branch_id');
    if (!branchId) {
      console.error('branch_id not found in localStorage');
      return;
    }

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
  //       console.log("sssss",res.data);
  //       this.hotels = res.data;
  //          this.dbService.saveData('hotels', this.hotels);

  //       this.allHotels = res.data;
  //       this.hotels = [...this.allHotels];
  //     },
  //     error: (err) => {
  //       console.log(err);
  //     },
  //   });
  // }

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
  selectedHotel: any;
  onHotelChange(hotel: any) {
    this.selectedHotel = hotel;
    console.log(hotel, 'lllllll');
    localStorage.setItem('selectedHotel', JSON.stringify(this.selectedHotel));
    const addressControl = this.form.get('address');

    if (hotel === 'another') {
      // المستخدم هيكتب العنوان بنفسه → مطلوب
      addressControl?.setValidators([
        Validators.required,
        this.noOnlySpacesValidator(),
      ]);
      addressControl?.setValue('');
    } else {
      addressControl?.setValue(hotel.address);
    }

    addressControl?.updateValueAndValidity();
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
                this.allUserAddress = res.data.map((item: any) => ({
                  ...item,
                  country_code: {
                    code: item?.country_code ?? null,
                    flag: item?.country_flag ?? null,
                  },
                }));// fatma: must ask BE to return country_code as object of flag,code not code only

                console.log('allllll', this.allUserAddress);

                this.userStoredAddress = res.data.map((address: any) => {
                  this.userId = address.user_id;

                  // ✅ Save delivery fees from the first address
                  if (address.delivery_fees) {
                    localStorage.setItem(
                      'delivery_fees',
                      address.delivery_fees
                    );
                    console.log(
                      '📦 Delivery fees from stored address:',
                      address.delivery_fees
                    );
                  }

                  return {
                    id: address.id,
                    name: address.address_type + ' , ' + address.address,
                    delivery_fees: address.delivery_fees,
                    // name:address.address,
                  };
                });
                console.log('toqa', this.userStoredAddress);

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

   get whatsappNumberCode() {
    return this.form.get('whatsapp_number_code');
  }
  listenToChangeWhatsappCountry(){
 this.whatsappNumberCode?.valueChanges.subscribe((value) => {
      const whatsappNumControl = this.form.get('whatsapp_number');
      if (value) {
        this.selectedWhatsappCountry=value;
        whatsappNumControl?.setValidators([Validators.required,Validators.pattern(
            new RegExp(`^\\d{${this.whatsappNumberCode?.value?.phoneLength}}$`)
          )]);
      } else {
        whatsappNumControl?.clearValidators();
      }
    });
  }

}
// aml

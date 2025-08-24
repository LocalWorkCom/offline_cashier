import { Injectable } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { Subject } from 'rxjs';  
import { BasicsConstance } from '../../constants';
 
export interface Locale {
  lang: string;
  data: any;
}

@Injectable({
  providedIn: 'root',
})
export class TranslationService {
  private langIds: any = [];
  CurrentLangInfo: { Currentlang: string; CurrentLangImage: string } = { Currentlang: `${BasicsConstance.DefaultLang}`, CurrentLangImage: '' };
  subject = new Subject<string>(); // subject to notify of direction changes
  myObservable = this.subject.asObservable();

  constructor(private translate: TranslateService) {
    // Initialize language options
    this.translate.addLangs(['en', 'ar']);
    this.translate.setDefaultLang(localStorage.getItem(BasicsConstance.LANG) || `${BasicsConstance.DefaultLang}`);
    this.translate.use(this.getSelectedLanguage()); // Use the selected language on service initialization
  }

  loadTranslations(...args: Locale[]): void {
    args.forEach((locale) => {
      this.translate.setTranslation(locale.lang, locale.data, true);
      this.langIds.push(locale.lang);
    });
    this.translate.addLangs(this.langIds);
    this.translate.use(this.getSelectedLanguage());
  }

  changeLang(lang: 'ar' | 'en') {
    
    this.setLanguage(lang);
    this.translate.use(lang);
    location.reload();
  }

  getCurrentLangInfo() {
    const lang = this.getSelectedLanguage();
    this.CurrentLangInfo.Currentlang = lang === 'ar' ? BasicsConstance['AR'] : BasicsConstance['EN'];
    this.CurrentLangInfo.CurrentLangImage =
      lang === 'ar'   
               ? 'https://upload.wikimedia.org/wikipedia/commons/f/fe/Flag_of_Egypt.svg'
               : 'https://upload.wikimedia.org/wikipedia/en/a/a4/Flag_of_the_United_States.svg';
         return this.CurrentLangInfo;
  }

  getHtmlDirection() {
    const lang = this.getSelectedLanguage();
    const Dir = lang === 'ar' ? 'rtl' : 'ltr';
    this.subject.next(Dir); // Notify components of direction change
    return Dir;
  }
getPosition(): 'left' | 'right' {
  return this.getHtmlDirection() === 'rtl' ? 'right' : 'left';
}

  setLanguage(lang: string) {
    localStorage.setItem(BasicsConstance.LANG, lang);
  }

  getSelectedLanguage = () => localStorage.getItem(BasicsConstance.LANG) || this.translate.getDefaultLang();
}

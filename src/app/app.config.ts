import {

  ApplicationConfig,

  provideZoneChangeDetection,

  isDevMode,

} from '@angular/core';

// import { provideRouter } from '@angular/router';

//add withHashLocation

import { provideRouter, withHashLocation } from '@angular/router';



import { routes } from './app.routes';

import {

  provideClientHydration,

  withEventReplay,

} from '@angular/platform-browser';

import { provideServiceWorker } from '@angular/service-worker';



import { provideAnimations } from '@angular/platform-browser/animations';

import {

  HTTP_INTERCEPTORS,

  HttpClient,

  provideHttpClient,

  withFetch,

  withInterceptorsFromDi,

} from '@angular/common/http';

import { TranslateHttpLoader } from '@ngx-translate/http-loader';

import {

  provideTranslateService,

  TranslateLoader,

  TranslateModule,

} from '@ngx-translate/core';

import { providePrimeNG } from 'primeng/config';



import Aura from '@primeng/themes/aura';

import { LanguageInterceptor } from './core/interceptor/languageInterceptor';

import { authInterceptor } from './core/interceptor/auth.interceptor';

export function HttpLoaderFactory(http: HttpClient) {

  return new TranslateHttpLoader(http, './assets/i18n/', '.json');

}



export const appConfig: ApplicationConfig = {

  providers: [

    provideZoneChangeDetection({ eventCoalescing: true }),

    // provideRouter(routes),

    //add withHashLocation



    provideRouter(routes, withHashLocation()),

    provideHttpClient(withFetch(), withInterceptorsFromDi()),

    provideServiceWorker('ngsw-worker.js', {

      enabled: !isDevMode(),

      registrationStrategy: 'registerWhenStable:30000',

    }),

    {

      provide: HTTP_INTERCEPTORS,

      useClass: LanguageInterceptor,

      multi: true,

    },

    {

      provide: HTTP_INTERCEPTORS,

      useClass: authInterceptor,

      multi: true,

    },



    provideTranslateService({

      loader: {

        provide: TranslateLoader,

        useFactory: HttpLoaderFactory,

        deps: [HttpClient],

      },

    }),

    provideAnimations(),

    providePrimeNG({

      theme: {

        preset: Aura,

      },

    }),

  ],

};

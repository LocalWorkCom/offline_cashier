import {  Injectable } from '@angular/core';
import {
  HttpRequest,
  HttpHandler,
  HttpEvent,
  HttpInterceptor
} from '@angular/common/http';
import { Observable } from 'rxjs';   
import { BasicsConstance } from '../../constants';
@Injectable()
export class LanguageInterceptor implements HttpInterceptor {

  intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    const lang =localStorage.getItem(BasicsConstance.LANG) || BasicsConstance.DefaultLang;
 
    const modifiedReq = req.clone({
      setHeaders: {
        'lang': lang
      }
    });
    return next.handle(modifiedReq);
  }
} 
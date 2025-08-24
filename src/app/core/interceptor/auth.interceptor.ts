import   { HttpEvent, HttpHandler, HttpInterceptor, HttpRequest } from "@angular/common/http";
import { inject, Injectable } from "@angular/core";
import { Observable } from "rxjs";
import { BasicsConstance } from "../../constants";

@Injectable()
export class authInterceptor implements HttpInterceptor { 
    intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {

        const token =localStorage.getItem(BasicsConstance.TOKEN)
        if (token) {
            req = req.clone({
                headers: req.headers.set('Authorization', `Bearer ${token}`)
            });
        }
        return next.handle(req).pipe(
        )
    }
}

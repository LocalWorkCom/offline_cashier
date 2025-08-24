// import { Injectable } from '@angular/core';
// import { isPlatformBrowser } from '@angular/common';
// import { Inject, PLATFORM_ID } from '@angular/core';
// import {
//   CanActivate,
//   Router,
//   CanActivateChild,
//   ActivatedRouteSnapshot,
//   RouterStateSnapshot,
// } from '@angular/router';

// @Injectable({
//   providedIn: 'root',
// })
// export class AuthGuard implements CanActivate, CanActivateChild {
//   constructor(private router: Router, @Inject(PLATFORM_ID) private platformId: object) {}
  
//   canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): boolean {
//     if (isPlatformBrowser(this.platformId)) {
//       const token = localStorage.getItem('authToken');
//       const isAuthenticated = token && !this.isTokenExpired(token);
      
//       // Check if the route is the login page or root URL
//       const isLoginRoute = state.url.includes('/login') || state.url === '/';
      
//       if (isAuthenticated) {
//         // If user is authenticated and trying to access login or root, redirect to home
//         if (isLoginRoute) {
//           this.router.navigate(['/home']);
//           return false;
//         }
//         // Allow access to other protected routes
//         return true;
//       } else {
//         // If not authenticated and trying to access protected routes, redirect to login
//         if (!isLoginRoute) {
//           this.router.navigate(['/login']);
//           return false;
//         }
//         // Allow access to login page for unauthenticated users
//         return true;
//       }
//     }
//     return true;
//   }
  
//   private isTokenExpired(token: string): boolean {
//     try {
//       const payload = JSON.parse(atob(token.split('.')[1]));
//       const exp = payload.exp * 1000;
//       return Date.now() > exp;
//     } catch (error) {
//       console.error("⚠️ Invalid Token:", error);
//       return true;
//     }
//   }

//   canActivateChild(
//     childRoute: ActivatedRouteSnapshot,
//     state: RouterStateSnapshot
//   ): boolean {
//     return this.canActivate(childRoute, state);
//   }
// }


import { Injectable } from '@angular/core';
import { isPlatformBrowser } from '@angular/common';
import { Inject, PLATFORM_ID } from '@angular/core';
import {
  CanActivate,
  Router,
  CanActivateChild,
  ActivatedRouteSnapshot,
  RouterStateSnapshot,
} from '@angular/router';

@Injectable({
  providedIn: 'root',
})
export class AuthGuard implements CanActivate, CanActivateChild {
  constructor(private router: Router, @Inject(PLATFORM_ID) private platformId: object) {}

  canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): boolean {
    if (isPlatformBrowser(this.platformId)) {
      const token = localStorage.getItem('authToken');
      
      // If the user is logged in and tries to navigate to the login page, prevent the redirect
      if (token && !this.isTokenExpired(token)) {
        if (state.url === '/login') {
          this.router.navigate(['/']); // Redirect to the home page or any other page
          return false;
        }
        return true;
      }
    }
  
    // Redirect to login if not authenticated
    this.router.navigate(['/login']);
    return false;
  }
  
  // Helper method to check if token is expired
  private isTokenExpired(token: string): boolean {
    try {
      const payload = JSON.parse(atob(token.split('.')[1]));
      const exp = payload.exp * 1000; // Convert to milliseconds
      return Date.now() > exp;
    } catch (error) {
      console.error("⚠️ Invalid Token:", error);
      return true;
    }
  }

  canActivateChild(
    childRoute: ActivatedRouteSnapshot,
    state: RouterStateSnapshot
  ): boolean {
    return this.canActivate(childRoute, state);
  }
}

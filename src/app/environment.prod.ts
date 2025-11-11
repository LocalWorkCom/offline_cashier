  


 export const baseUrlForWebSocket ='https://erpfortest.testdomain100.online/'

/*  export const baseUrl ='http://127.0.0.1:8000/'
 */ export const baseUrl2 ='https://erpsystem.testdomain100.online/api'
//for test domain  
// export const baseUrl='https://erpsystem.testdomain100.online/'
// export const environment = {    
//   production: true,
//   pusher: {
//     key: 'cfd52a74b92f9e278f2d',
//     cluster: 'mt1',
//   }
// };


//alkoot 


// export const baseUrl='https://alkoot-restaurant.com/'
//  export const environment = {  
//    production: true,
//    pusher: {
//      key: '77f608d73899bd256cfa',  
//      cluster: 'mt1',
//    }
//  };



//production


export const baseUrl='https://productowner.testdomain100.online/'
 export const environment = {  
   production: true,
   pusher: {
     key: '77f608d73899bd256cfa',  
     cluster: 'mt1',
   }
 }; 





 
export const environment2 = {
  production: false,
  pusher: {
    key: 'localkey', 
    wsHost: 'erpfortest.testdomain100.online',
    wsPort: 6001,
    cluster: 'mt1',
    forceTLS: false,
  },
};
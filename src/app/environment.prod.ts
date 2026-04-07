



export const baseUrlForWebSocket ='https://erpfortest.testdomain100.online/'

/*  export const baseUrl ='http://127.0.0.1:8000/'
 */

// export const baseUrl2 ='https://erp-cashier.testdomain100.online/api'

export const baseUrl ='https://productowner.testdomain100.online/'
// export const baseUrl2 ='https://productowner.testdomain100.online/api'
export const baseUrl2 ='https://productowner.testdomain100.online/api'

// export const baseUrl2 ='https://erpmain.alkoot-restaurant.com/api'

//for test domain
// export const baseUrl='https://erpsystem.testdomain100.online/'
export const environment = {
  production: true,
  pusher: {
    key: 'cfd52a74b92f9e278f2d',
    cluster: 'mt1',
  },
  wsUrl: 'ws://127.0.0.1:8081'
};


//alkoot


// export const baseUrl='https://erpmain.alkoot-restaurant.com/'
//  export const environment = {
//    production: true,
//    pusher: {
//      key: '77f608d73899bd256cfa',
//      cluster: 'mt1',
//    }
//  };



//production

// for  product owner
//  export const baseUrl='https://productowner.testdomain100.online/'
// for  cashier test

//export const baseUrl='https://erp-cashier.testdomain100.online/'
//export const baseUrl='http://192.168.11.20:8000/'


// for  cashier production
// export const baseUrl='https://erpmain.alkoot-restaurant.com/api'

// export const baseUrl='https://erpsystem.testdomain100.online/'


//  export const environment = {
//    production: true,
//    pusher: {
//      key: 'cfd52a74b92f9e278f2d',
//      cluster: 'mt1',
//    }
//  };

export const environment2 = {
  production: true,
  pusher: {
    key: 'localkey',
    wsHost: '192.168.11.178',
    wsPort: 8081,
    cluster: 'mt1',
    forceTLS: false,
  },
};

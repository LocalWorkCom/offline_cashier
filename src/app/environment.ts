//  export const baseUrl ='https://erp-cashier.testdomain100.online/'
//  export const baseUrlForWebSocket ='https://erpfortest.testdomain100.online/'

//  export const baseUrl ='https://erp-cashier.testdomain100.online/'
export const baseUrl ='http://192.168.11.101:8000/'
export const baseUrl2 ='http://192.168.11.101:8000/api'

//test
export const environment = {
  production: false,
  pusher: {
    key: 'cfd52a74b92f9e278f2d',
    cluster: 'mt1',
  },
  wsUrl: 'ws://localhost:8081'
};

// //localhost
// export const environment = {
//   production: false,
//   pusher: {
//     key: '45700f4c3cc882528180',
//     cluster: 'eu',
//   }
// };

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

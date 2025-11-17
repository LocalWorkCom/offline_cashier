export const baseUrl ='https://erpsystem.testdomain100.online/'
//  export const baseUrlForWebSocket ='https://erpfortest.testdomain100.online/'

/*  export const baseUrl ='http://127.0.0.1:8000/'
 */  export const baseUrl2 ='https://erpsystem.testdomain100.online/api'


export const environment = {
  production: false,
  pusher: {
    key: 'cfd52a74b92f9e278f2d',
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
// export const baseUrl ='https://erpsystem.testdomain100.online/'
//  export const baseUrlForWebSocket ='https://erpfortest.testdomain100.online/'

  export const baseUrl ='http://127.0.0.1:8000/'
   export const baseUrl2 ='http://127.0.0.1:8000/api'


// export const environment = {
//   production: false,
//   reverb: {
//     key: '77f608d73899bd256cfa',
//     wsHost: '127.0.0.1',
//     wsPort: 8080,
//     wssPort: 8080,
//     forceTLS: false,
//     enabledTransports: ['ws', 'wss'],
//   },
// };
export const environment2 = {
  production: false,
  reverb: {
    key: '77f608d73899bd256cfa',
    wsHost: '127.0.0.1',
    wsPort: 8080,
    wssPort: 8080,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
  },
};

export const environment = {
  production: false,
  pusher: {
    key: '45700f4c3cc882528180',
    cluster: 'eu',
    wsHost: 'http://localhost:8000',
    wsPort: 6001,
    forceTLS: false,
  },
  reverb: {
    key: '45700f4c3cc882528180',
    wsHost: '127.0.0.1',
    wsPort: 8080,
    wssPort: 8080,
    forceTLS: false,
    enabledTransports: ['ws'],
  },
};
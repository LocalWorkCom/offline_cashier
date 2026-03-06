importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js');

const firebaseConfig = {
    apiKey: "AIzaSyBQ2cIo6ykJQA_PlBXhWh2NoWzT4ORu72k",
    authDomain: "al-koot-74c79.firebaseapp.com",
    projectId: "al-koot-74c79",
    storageBucket: "al-koot-74c79.firebasestorage.app",
    messagingSenderId: "333138698522",
    appId: "1:333138698522:web:053967bfc305ed53a5cca3",
};

firebase.initializeApp(firebaseConfig);

const messaging = firebase.messaging();

messaging.onBackgroundMessage(function(payload) {
    const title = payload.data?.title || 'New Notification';
    const body = payload.data?.body || 'You have a new message';
    const icon = payload.data?.icon || '/logo.png';
    const url = payload.data?.url || '/';

    const options = {
        body,
        icon,
        data: { url }
    };

    self.registration.showNotification(title, options);
});

// Optional: Handle notification clicks
self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    if (event.notification.data?.url) {
        event.waitUntil(
            clients.openWindow(event.notification.data.url)
        );
    }
});

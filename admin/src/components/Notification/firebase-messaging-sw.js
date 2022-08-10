importScripts('https://www.gstatic.com/firebasejs/9.1.3/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.1.3/firebase-messaging-compat.js');

firebase.initializeApp({
  apiKey: "<FIREBASE_APIKEY>",
  authDomain: "<FIREBASE_DOMAIN>",
  projectId: "<FIREBASE_PROJECTID>",
  storageBucket: "<FIREBASE_STORAGEBUCKET>",
  messagingSenderId: "<FIREBASE_SENDERID>",
  appId: "<FIREBASE_APPID>",
  measurementId: "<FIREBASE_MEASUREMENTID>"
});

firebase.messaging().onBackgroundMessage((payload) => {
  // Customize notification here for 'data' key
  let {data} = payload
  if (data && data.title && data.body) {
    self.registration.showNotification(data.title || 'Raiden App', {
      body: data.body || '',
    });
  }
});

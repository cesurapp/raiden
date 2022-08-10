importScripts('https://www.gstatic.com/firebasejs/9.1.3/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.1.3/firebase-messaging-compat.js');

firebase.initializeApp({
  apiKey: "AIzaSyC_ZbH7KHkv9s72ZEgjZbwduYBmFR2aN_E",
  authDomain: "yeyee-app.firebaseapp.com",
  projectId: "yeyee-app",
  storageBucket: "yeyee-app.appspot.com",
  messagingSenderId: "858568967918",
  appId: "1:858568967918:web:1355ef685eb5b69a4b95ff",
  measurementId: "G-KW1YHX0979"
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

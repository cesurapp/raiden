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

const messaging = firebase.messaging();

// If you would like to customize notifications that are received in the
// background (Web app is closed or not in browser focus) then you should
// implement this optional method.
// Keep in mind that FCM will still show notification messages automatically
// and you should use data messages for custom notifications.
// For more info see:
// https://firebase.google.com/docs/cloud-messaging/concept-options

// FCM automatically displays the message when 'notification' key is in payload
// It will also automatically displays when 'data' key is in payload
// You can be received notification tiwce when both key is in payload

// If you wanna handle notification, use only 'data' key
// See: https://firebase.google.com/docs/cloud-messaging/concept-options#notifications_and_data_messages

messaging.onBackgroundMessage((payload) => {
  console.log('[firebase-messaging-sw.js] Received background message ', payload);
  // Customize notification here for 'data' key
  let {data} = payload
  if (data && data.title && data.body) {
    const notificationTitle = data.title || 'Test title';
    const notificationOptions = {
      body: data.body || 'Test body',
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
  }
});

self.addEventListener('notificationclick', function (event) {
  let {notification} = event;
  // this event will not be occoured by notication of FCM 'notification' key
  console.log('[firebase-messaging-sw.js] Notification clicked: ', event);
  notification.close()
});

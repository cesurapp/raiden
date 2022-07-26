import {boot} from 'quasar/wrappers'
/*import { register as regServiceWorker } from "register-service-worker";*/
import { initializeApp } from "firebase/app";
import { getMessaging, onMessage, getToken } from "firebase/messaging";
import { Notify } from 'quasar'

/*Notify.registerType('my-notif', {
  icon: 'announcement',
  progress: true,
  color: 'brown',
  textColor: 'white',
  classes: 'glossy'
})*/

const app = initializeApp({
  apiKey: "AIzaSyC_ZbH7KHkv9s72ZEgjZbwduYBmFR2aN_E",
  authDomain: "yeyee-app.firebaseapp.com",
  projectId: "yeyee-app",
  storageBucket: "yeyee-app.appspot.com",
  messagingSenderId: "858568967918",
  appId: "1:858568967918:web:1355ef685eb5b69a4b95ff",
  measurementId: "G-KW1YHX0979"
});
const messaging = getMessaging(app);

// Receive Message


// Get Token
getToken(messaging).then((token) => {
  console.log(token)
});

/*navigator.serviceWorker.register('firebase-messaging-sw.js', {scope: "firebase-cloud-messaging-push-scope"})
        .then((registration) => {
          const messaging = getMessaging(app);
          messaging.useServiceWorker(registration);

        }).catch(err => {
        console.log(err)
      })*/

export default boot(({app}) => {
  onMessage(messaging, (payload) => {
    console.log('Received ->', payload);
    Notify.create({
      message: JSON.stringify(payload)
    });
  });
})

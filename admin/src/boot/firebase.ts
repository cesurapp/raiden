import {boot} from 'quasar/wrappers'
import { initializeApp } from "firebase/app";
import { getMessaging, onMessage, getToken } from "firebase/messaging";
import { Notify } from 'quasar'

const fbApp = initializeApp({
  apiKey: "AIzaSyC_ZbH7KHkv9s72ZEgjZbwduYBmFR2aN_E",
  authDomain: "yeyee-app.firebaseapp.com",
  projectId: "yeyee-app",
  storageBucket: "yeyee-app.appspot.com",
  messagingSenderId: "858568967918",
  appId: "1:858568967918:web:1355ef685eb5b69a4b95ff",
  measurementId: "G-KW1YHX0979"
});
const fbMessaging = getMessaging(fbApp);
const convertType = function (type) {
  switch (type) {
    case 'danger': return 'negative';
    case 'error': return 'negative';
    case 'success': return 'positive';
  }

  return type || 'positive';
}

// Receive Message
onMessage(fbMessaging, (payload) => {
  const opts: object = {
    type: convertType(payload.data?.type),
    caption: payload.notification?.title,
    message: payload.notification?.body,
    timeout: 3000,
    position: 'top',
    progress: true,
  };

  // Append Link
  if (payload.fcmOptions?.link) {
    opts['actions'] = [
      { label: 'Dismiss', color: 'white', handler: () => { /* ... */ } }
    ]
  }

  // Notify
  Notify.create(opts);
});

/**
 * Register Only "RequireAuth" Routes
 */
export default boot(({router}) => {
  router.afterEach((to, from) => {
    if (to.matched.some(record => record.meta.requireAuth)) {
      getToken(fbMessaging)
        .then((token) => {
          console.log(token)
        })
        .catch((reason) => {
          console.log(reason)
        })
    }
  })
})

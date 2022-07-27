import {boot} from 'quasar/wrappers'
import {initializeApp} from 'firebase/app';
import {getMessaging, onMessage, getToken} from 'firebase/messaging';
import {Notify} from 'quasar'

/**
 * Save FCM Token to Server
 */
function saveToken(token: string, type: string) {
  console.log(token);
}

/**
 * Register Firebase FCM App
 */
function registerFirebaseApp() {
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
  const convertMessageType = function (type) {
    switch (type) {
      case 'danger':
        return 'negative';
      case 'error':
        return 'negative';
      case 'success':
        return 'positive';
    }

    return type || 'positive';
  }

  // Receive Message Create Notify
  onMessage(fbMessaging, (payload) => {
    const opts: object = {
      type: convertMessageType(payload.data?.type),
      caption: payload.notification?.title,
      message: payload.notification?.body,
      timeout: 3000,
      position: 'top',
      progress: true,
    };

    // Append Link
    if (payload.fcmOptions?.link) {
      opts['actions'] = [
        {
          label: 'Open',
          color: 'white',
          handler: () => {
            console.log('asd');
          }
        }
      ]
    }

    // Notify
    Notify.create(opts);
  });

  // Get Token
  getToken(fbMessaging)
    .then((token) => {
      saveToken(token, 'web');
    })
    .catch((reason) => {
      console.log(reason)
    })
}

/**
 * Register Only "RequireAuth" Routes
 */
export default boot(({router}) => {
  let appInit = false;

  router.afterEach((to, from) => {
    if (!appInit && to.matched.some(record => record.meta.requireAuth)) {
      appInit = true;
      registerFirebaseApp();
    }
  })
})

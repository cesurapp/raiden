import {boot} from 'quasar/wrappers'
import {initializeApp} from 'firebase/app';
import {getMessaging, onMessage, getToken} from 'firebase/messaging';
import {notifyShow} from '../helper/NotifyHelper';

/**
 * Save FCM Token to Server
 */
function saveToken(token: string) {
  console.log(token);
}

/**
 * Register Firebase FCM App
 */
function registerFirebaseApp() {
  const fbApp = initializeApp({
    apiKey: 'AIzaSyC_ZbH7KHkv9s72ZEgjZbwduYBmFR2aN_E',
    authDomain: 'yeyee-app.firebaseapp.com',
    projectId: 'yeyee-app',
    storageBucket: 'yeyee-app.appspot.com',
    messagingSenderId: '858568967918',
    appId: '1:858568967918:web:1355ef685eb5b69a4b95ff',
    measurementId: 'G-KW1YHX0979'
  });
  const fbMessaging = getMessaging(fbApp);


  // Receive Message Create Notify
  onMessage(fbMessaging, (payload) => {
    const options: object = {};

    if (payload.fcmOptions?.link) {
      options['actions'] = [{
        label: 'Open',
        color: 'white',
        handler: () => {
          console.log('asd');
        }
      }]
    }

    notifyShow(
      payload.notification?.body,
      payload.notification?.title,
      payload.data?.type,
      options
    );
  });

  // Get Token
  getToken(fbMessaging)
    .then((token) => {
      saveToken(token);
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

  router.afterEach((to) => {
    if (!appInit && to.matched.some(record => record.meta.requireAuth)) {
      appInit = true;
      registerFirebaseApp();
    }
  })
})

import {initializeApp} from 'firebase/app';
import {getMessaging, onMessage, getToken} from 'firebase/messaging';
import {notifyShow} from 'src/helper/NotifyHelper';
import {Dialog, LocalStorage} from 'quasar'

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
export default (router) => {
  let fbInited = LocalStorage.getItem('firebase-prompt');

  router.afterEach((to) => {
    if (!fbInited && to.matched.some(record => record.meta.requireAuth)) {
      fbInited = true;

      // Prompt User
      Dialog.create({
        title: 'System Notifications',
        message: 'Allow push notifications for system events, file downloads and more.',
        persistent: true,
        ok: {label: 'Allow', flat: true, icon: 'done', color: 'green', size: 'md'},
        cancel: {flat: true, color: 'red', size: 'md'},
      }).onOk(() => {
        registerFirebaseApp();
        LocalStorage.set('firebase-prompt', true);
      }).onCancel(() => {
        LocalStorage.set('firebase-prompt', true);
      })
    }
  })
}

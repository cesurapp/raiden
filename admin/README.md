# Raiden Admin Panel

Admin Application UI for [Quasar 2.7](https://quasar.dev/) & Vue 3 & Vite

**Features**

- Developed with Quasar 2.7.
- Firebase integration for notifications
- Responsive design
- Role based authentication
- Route permission
- Permission helper
- Dynamic form validation

**Requirements**

```
"node": "^18 || ^16 || ^14.19",
"yarn": ">= 1.21.1"
```

## Install

**Development Environment**

```bash
yarn global add @quasar/cli # Install Quasar CLI
yarn install
yarn quasar dev    # Start the app in development mode (hot-code reloading, error reporting, etc.)
yarn quasar build  # Build the app for production
yarn run fix       # Lint the files
```

**Configure Realtime Notification (Firebase FCM)**

1. Create Firebase Project [Open Firebase Console](https://console.firebase.google.com/)
2. Open -> Project Settings -> General
3. Create -> Add App (Web)
4. Configure Panel .env

```dotenv
FIREBASE_APIKEY=""
FIREBASE_DOMAIN=""
FIREBASE_PROJECTID=""
FIREBASE_STORAGEBUCKET=""
FIREBASE_SENDERID=""
FIREBASE_APPID=""
FIREBASE_MEASUREMENTID=""
```

> **Warning:** After setting the variables, you need to build `yarn quasar build` the project. The "
> firebase-messaging-sw.js" file is only created when the build is taken.

#### Deploy CloudFlare Pages

Build Configuration:

```env
Framework = None
Build Command = yarn quasar build
Build output directory = dist/spa
Root directory path = admin
```

Add Variables:

```env
API=https://dev.yeyee.app
NODE_VERSION=16
```

## Documentation

#### Translate

```vue
<template>
  <div>{{ $t('key') }}</div>
</template>
```

#### Dark Mode

Generally all components have dark mode. You can use it for special operations as follows.

```vue
<template>
  <div :class="{ dark: $q.dark.isActive }">Content</div>
</template>
```

#### Page Meta

You can use mixin to set the headers of the pages. See Quasar [documentation](https://quasar.dev/quasar-plugins/meta)
for details.

```js
import { createMetaMixin } from 'quasar';

export default defineComponent({
  name: 'LoginPage',
  mixins: [
    createMetaMixin(function () {
      return {
        title: this.$t('Login'),
      };
    }),
  ],
});
```

#### Notify

```js
import { notifyShow, notifyWarning, notifyInfo, notifyDanger, notifySuccess } from 'src/helper/NotifyHelper';

notifySuccess('message', 'caption');
notifyDanger('message', 'caption');
notifyShow('message', 'caption', 'info', {});
```

#### SFC Globals

```js
import { createMetaMixin } from 'quasar';

export default defineComponent({
  name: 'LoginPage',
  mounted() {
    // Api Request
    this.$api;

    // Axios Client
    this.$client;

    // Axios isBusy (type Ref)
    this.$isBusy;

    // Auth Store -> stores/AuthStore.ts
    this.$authStore;

    // App Store -> stores/AppStore.ts
    this.$appStore;

    // Validation Rules -> boot/helper/rules.ts
    this.$rules;
  },
});
```

Using import:

```js
import { i18n, client, isBusy, api, apiRaw } from 'boot/app';
import { useAuthStore } from 'stores/AuthStore';
import { useAppStore } from 'stores/AppStore';

// Api Request
api;

// Api Request without Axios Interceptors
apiRaw;

// Axios isBusy (type Ref)
isBusy;

// Axios Client
client;

// Using Store
const authStore = useAuthStore();
const appStore = useAppStore();
```

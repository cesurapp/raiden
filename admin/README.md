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

#### Date Helper

Localized Formatter.

```vue
import { dateFormat } from 'src/helper/DateHelper'; dateFormat(date, 'format')
```

Quasar Date Helper [Documentation](https://quasar.dev/quasar-utils/date-utils)

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
this.$appStore.notifyDanger();
this.$appStore.notifySuccess();
```

#### SFC Globals

```js
export default defineComponent({
  name: 'LoginPage',
  mounted() {
    // Api Request
    this.$api;

    // Axios Client
    this.$client;

    // Axios isBusy (type Ref)
    this.$appStore.isBusy;

    // Api Permisson List
    this.$permission;

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
import { i18n, client, api } from 'boot/app';
import { useAuthStore } from 'stores/AuthStore';
import { useAppStore } from 'stores/AppStore';

// Api Request
api;

// Axios Client
client;

// Using Store
const authStore = useAuthStore();
const appStore = useAppStore();
```

#### Custom Axios Config

```js
{
  showMessage: boolean; // Disable Response Notification
  uniqId: string; // Axios Current Request ID
  skipInterceptor: boolean; // Skip Interceptors
}
```

#### Check Permission

```vue
<template>
  <q-btn rounded icon="add" v-if="$authStore.hasPermission($permission.AdminAccount.LIST)"></q-btn>
</template>

<script>
import { Permission } from 'api/Enum/Permission';

export default defineComponent({
  name: 'TestPage',
  methods: {
    test() {
      // Using Global
      if (this.$authStore.hasPermission(this.$permission.AdminAccount.LIST)) {
      }

      // Using Import
      if (this.$authStore.hasPermission(Permission.AdminAccount.LIST)) {
      }
    },
  },
});
</script>
```

#### Add Route & BreadCrumbs

```js
export default [
  {
    path: '/', // Route Path
    name: 'admin', // Route Name
    component: () => import('pages/Admin/Layout.vue'), // Dynamic Component
    meta: {
      requireAuth: true, // Login Required
      userType: [UserType.ADMIN, UserType.SUPERADMIN], // Required User Type
      breadcrumb: 'Dashboard', // Breadcrumbs Name Translated
    },

    // Sub Routes
    children: [
      {
        path: '/account',
        component: () => import('pages/Admin/Account/Accounts.vue'),
        meta: {
          breadcrumb: 'Accounts',
          permission: [Permission.AdminAccount.LIST],
        },
      },
    ],
  },
];
```

### Create New Dashboard (Seller Panel)

Create routes file: `routes/seller.ts`

```js
import { UserType } from 'src/api/Enum/UserType';

export default [
  {
    path: '/',
    name: 'seller',
    component: () => import('pages/Seller/Layout.vue'),
    meta: {
      requireAuth: true,
      userType: [UserType.SELLER], // Only Seller Account
      breadcrumb: 'Seller Dashboard',
    },

    children: [],
  },
];
```

Import routes to `routes/index.ts`:

```js
import SellerRoutes from './seller';

const routes: RouteRecordRaw[] = [...SellerRoutes];
```

Create dashboard layout `pages\Seller\Layout.ts`

```vue
<template>
  <q-layout class="seller" view="lHh LpR lFr">
    <!--Header-->
    <q-header elevated>
      <q-toolbar class="q-pr-md-sm q-pr-lg-md q-pl-md-md q-pl-lg-lg">
        <div id="head-toolbar"></div>

        <!--BreadCrumbs & Title-->
        <q-toolbar-title class="q-pl-none">
          <q-breadcrumbs class="breadcrumbs">
            <q-breadcrumbs-el
              v-for="(route, index) in getBreadcrumbs"
              :key="index"
              :label="$t(route.meta.breadcrumb)"
              :to="route.path"
            />
          </q-breadcrumbs>
        </q-toolbar-title>
      </q-toolbar>
    </q-header>

    <!--Page Container-->
    <q-page-container>
      <router-view v-slot="{ Component }">
        <transition appear mode="out-in" enter-active-class="animated fadeIn" leave-active-class="animated fadeOut">
          <component :is="Component"></component>
        </transition>
      </router-view>
    </q-page-container>
  </q-layout>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { createMetaMixin } from 'quasar';

export default defineComponent({
  name: 'SellerLayout',
  data: () => ({
    test: true,
  }),
  mixins: [
    createMetaMixin(function () {
      return {
        titleTemplate: (title) => `${title} - ` + this.$appStore.title,
      };
    }),
  ],
  computed: {
    getBreadcrumbs() {
      return this.$route.matched.filter((route) => {
        return route.meta?.breadcrumb;
      });
    },
  },
  created() {
    this.$authStore.reloadUser();
  },
});
</script>
```

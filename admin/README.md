# Raiden Admin Panel

Admin Application UI for [Quasar 2.7](https://quasar.dev/) & Vue 3 & Vite

**Features**

- Web, iOS & Android support (Capacitor)
- Vue 3 Composition API & TypeScript
- Quasar 2.16 with Vite
- Pinia state management
- Vue I18n with lazy loading
- Firebase Cloud Messaging (FCM)
- Axios interceptors & request management
- Role-based authentication & authorization
- Route guards & permission system
- Vuelidate form validation
- Dark mode support
- Responsive design

**Requirements**

```
"node": "^28 || ^26 || ^24 || ^22 || ^20",
"yarn": ">= 1.21.1"
```

## Installation

**Setup Development Environment**

```bash
# Install Quasar CLI globally
yarn global add @quasar/cli

# Install dependencies
yarn install

# Copy environment file
cp .env.example .env

# Start development server
yarn dev

# Build for production
yarn build

# Code quality
yarn lint          # Check code issues
yarn lint:fix      # Auto-fix issues
yarn format        # Format code with Prettier
```

**Mobile App Development**

```bash
# iOS (requires Xcode)
yarn dev-ios         # Development mode with Xcode IDE
yarn build-ios       # Build iOS application

# Android (requires Android Studio)
yarn dev-android     # Development mode with Android Studio
yarn build-android   # Build Android application
```

**Configure Realtime Notification (Web SPA) (Firebase FCM)**

1. Create Firebase Project at [Firebase Console](https://console.firebase.google.com/)
2. Go to Project Settings → General
3. Add App → Web
4. Configure `.env` file

```dotenv
FIREBASE_APIKEY=""
FIREBASE_DOMAIN=""
FIREBASE_PROJECTID=""
FIREBASE_STORAGEBUCKET=""
FIREBASE_SENDERID=""
FIREBASE_APPID=""
FIREBASE_MEASUREMENTID=""
```

#### Deploy Cloudflare Pages

Build Configuration:

```env
Framework=None
Build Command=yarn quasar build
Build output directory=dist/spa
Root directory path=admin
```

Environment Variables:

```env
API=https://dev.raiden.app
NODE_VERSION=20
```

## Project Structure

```
admin/
├── src/
│   ├── boot/               # App initialization & plugins
│   │   ├── app.ts          # Main boot file (i18n, axios, stores)
│   │   └── helper/         # Route guards, interceptors, validation rules
│   ├── components/         # Reusable Vue components
│   │   ├── CustomDialog/
│   │   ├── Language/
│   │   ├── Layout/
│   │   ├── Localization/
│   │   ├── Notification/
│   │   └── SimpleTable/
│   ├── pages/              # Page components
│   │   ├── Admin/          # Admin panel pages
│   │   └── Auth/           # Authentication pages
│   ├── router/             # Route definitions
│   ├── stores/             # Pinia stores
│   │   ├── AppStore.ts     # App state & helpers
│   │   └── AuthStore.ts    # Authentication state
│   ├── helper/             # Utility functions
│   ├── i18n/               # Translation files
│   └── css/                # Global styles
├── .api/                   # Auto-generated API client
├── public/                 # Static assets
└── src-capacitor/          # Capacitor native app configuration
```

## Documentation

### Internationalization (i18n)

Add translations in `src/i18n/[locale].ts`:

```ts
// src/i18n/en-US.ts
export default {
  Welcome: 'Welcome',
  Login: 'Login',
  Dashboard: 'Dashboard',
};
```

Usage in templates:

```vue
<template>
  <div>{{ $t('Welcome') }}</div>
</template>
```

Usage with prefix (for nested translations):

```vue
<template>
  <div>{{ $tt('auth', 'Login') }}</div>
</template>
```

Programmatic usage:

```ts
import { i18n, tt } from 'boot/app';

// Standard translation
const text = i18n.global.t('Welcome');

// With prefix
const authText = tt('auth', 'Login');
```

### Dark Mode

Automatic dark mode support. Use in custom components:

```vue
<template>
  <div :class="{ dark: $q.dark.isActive }">Content</div>
</template>

<script setup>
import { useQuasar } from 'quasar';
const $q = useQuasar();
const isDark = $q.dark.isActive;
</script>
```

### Page Meta & SEO

Set page title and meta tags:

```ts
import { createMetaMixin } from 'quasar';

export default defineComponent({
  name: 'LoginPage',
  mixins: [
    createMetaMixin(function () {
      return {
        title: this.$t('Login'),
        meta: {
          description: { name: 'description', content: 'Login to admin panel' },
        },
      };
    }),
  ],
});
```

### Notifications

Using AppStore methods:

```ts
// Success notification
this.$appStore.notifySuccess('Operation completed');

// Error notification
this.$appStore.notifyDanger('Something went wrong');

// Custom notification
import { notifyShow } from 'src/helper/NotifyHelper';
notifyShow('info', 'Custom message');
```

### Helper Functions

Available in `src/helper/AppHelper.ts`:

```ts
import {
  capitalize,      // Capitalize first letter
  ellipsis,        // Truncate text
  enumToOptions,   // Convert enum to select options
  formatJSON,      // Format & parse nested JSON
  mergeWithDefaults // Deep merge objects
} from 'src/helper/AppHelper';

// Example
const options = enumToOptions(UserType, 'user.types');
```

### Global Properties (Options API)

Available in all components via `this`:

```ts
export default defineComponent({
  name: 'MyComponent',
  mounted() {
    // API client (auto-generated from OpenAPI)
    this.$api.admin.UserList();

    // Raw Axios client
    this.$client.get('/custom-endpoint');

    // Check if any request is loading
    this.$appStore.isBusy; // boolean

    // Permission enum
    this.$permission.AdminAccount.LIST;

    // Authentication store
    this.$authStore.user;
    this.$authStore.hasPermission();

    // Application store
    this.$appStore.notifySuccess();

    // Validation rules
    this.$rules.required('Field is required');
    this.$rules.email('Invalid email');

    // Translation with prefix
    this.$tt('auth', 'Login');
  },
});
```

### Composition API Usage

Import and use in `<script setup>`:

```ts
<script setup lang="ts">
import { api, client, i18n, tt } from 'boot/app';
import { useAuthStore } from 'stores/AuthStore';
import { useAppStore } from 'stores/AppStore';
import { Permission } from '@api/enum/Permission';

const authStore = useAuthStore();
const appStore = useAppStore();

// API request
const loadData = async () => {
  const response = await api.admin.UserList();
  console.log(response.data);
};

// Check permission
const canEdit = authStore.hasPermission(Permission.AdminAccount.EDIT);
</script>
```

### Axios Configuration

Custom axios request config options:

```ts
interface CustomAxiosConfig {
  showMessage?: boolean;     // Show/hide response notifications (default: true)
  uniqId?: string;           // Unique request ID for tracking
  skipInterceptor?: boolean; // Skip request/response interceptors
}

// Example usage
await this.$client.post('/api/data', payload, {
  showMessage: false,  // Don't show notification
  uniqId: 'my-request',
});
```

### Form Validation

Using Vuelidate with built-in rules:

```vue
<script setup lang="ts">
import { useVuelidate } from '@vuelidate/core';
import validationRules from 'boot/helper/rules';

const form = reactive({
  email: '',
  password: '',
});

const rules = {
  email: {
    required: validationRules.required('Email is required'),
    email: validationRules.email('Invalid email'),
  },
  password: {
    required: validationRules.required('Password is required'),
    minLength: validationRules.minLength(6, 'Min 6 characters'),
  },
};

const v$ = useVuelidate(rules, form);
</script>
```

### Authentication & Permissions

Check user permissions:

```vue
<template>
  <!-- Template permission check -->
  <q-btn
    icon="add"
    v-if="$authStore.hasPermission($permission.AdminAccount.CREATE)"
  >
    Add Account
  </q-btn>
</template>

<script setup lang="ts">
import { useAuthStore } from 'stores/AuthStore';
import { Permission } from '@api/enum/Permission';

const authStore = useAuthStore();

// Check single permission
const canCreate = authStore.hasPermission(Permission.AdminAccount.CREATE);

// Get current user
const user = authStore.user;

// Login programmatically
await authStore.loginUsername('user@example.com', 'password');

// Logout
await authStore.logout();
</script>
```

### Routing & Navigation

#### Define Routes with Permissions

Create route file in `src/router/`:

```ts
// src/router/admin.ts
import { UserType } from '@api/enum/UserType';
import { Permission } from '@api/enum/Permission';
import type { RouteRecordRaw } from 'vue-router';

export default [
  {
    path: '/',
    name: 'admin',
    component: () => import('pages/Admin/Layout.vue'),
    meta: {
      requireAuth: true,                                // Require authentication
      userType: [UserType.ADMIN, UserType.SUPERADMIN], // Restrict by user type
      breadcrumb: 'Dashboard',                          // Breadcrumb label (translated)
    },
    children: [
      {
        path: '/accounts',
        name: 'accounts',
        component: () => import('pages/Admin/Account/Accounts.vue'),
        meta: {
          breadcrumb: 'Accounts',
          permission: [Permission.AdminAccount.LIST],    // Require permission
        },
      },
    ],
  },
] as RouteRecordRaw[];
```

Register routes in `src/router/index.ts`:

```ts
import { route } from 'quasar/wrappers';
import { createRouter, createWebHistory } from 'vue-router';
import adminRoutes from './admin';
import authRoutes from './auth';

const routes = [
  ...adminRoutes,
  ...authRoutes,
  { path: '/:catchAll(.*)*', component: () => import('pages/404.vue') },
];

export default route(() => {
  return createRouter({
    history: createWebHistory(),
    routes,
  });
});
```

### Create New Dashboard Panel

**Example: Seller Panel**

1. Create route file `src/router/seller.ts`:

```ts
import { UserType } from '@api/enum/UserType';
import type { RouteRecordRaw } from 'vue-router';

export default [
  {
    path: '/seller',
    name: 'seller',
    component: () => import('pages/Seller/Layout.vue'),
    meta: {
      requireAuth: true,
      userType: [UserType.SELLER],
      breadcrumb: 'Seller Dashboard',
    },
    children: [
      {
        path: 'products',
        component: () => import('pages/Seller/Products.vue'),
        meta: { breadcrumb: 'Products' },
      },
    ],
  },
] as RouteRecordRaw[];
```

2. Import in `src/router/index.ts`:

```ts
import sellerRoutes from './seller';

const routes = [
  ...adminRoutes,
  ...sellerRoutes,
  ...authRoutes,
];
```

3. Create layout `src/pages/Seller/Layout.vue`:

```vue
<template>
  <q-layout view="lHh LpR lFr">
    <q-header elevated>
      <q-toolbar>
        <q-toolbar-title>
          <q-breadcrumbs>
            <q-breadcrumbs-el
              v-for="(route, index) in breadcrumbs"
              :key="index"
              :label="$t(route.meta.breadcrumb)"
              :to="route.path"
            />
          </q-breadcrumbs>
        </q-toolbar-title>
      </q-toolbar>
    </q-header>

    <q-page-container>
      <router-view v-slot="{ Component }">
        <transition
          appear
          mode="out-in"
          enter-active-class="animated fadeIn"
          leave-active-class="animated fadeOut"
        >
          <component :is="Component" />
        </transition>
      </router-view>
    </q-page-container>
  </q-layout>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from 'stores/AuthStore';
import { useAppStore } from 'stores/AppStore';
import { useMeta } from 'quasar';

const route = useRoute();
const authStore = useAuthStore();
const appStore = useAppStore();

const breadcrumbs = computed(() => {
  return route.matched.filter((r) => r.meta?.breadcrumb);
});

useMeta(() => ({
  titleTemplate: (title) => `${title} - ${appStore.title}`,
}));

onMounted(() => {
  authStore.reloadUser();
});
</script>
```

## Components

### Reusable Components

The project includes several ready-to-use components:

- **CustomDialog** - Custom dialog with advanced options
- **SimpleTable** - Data table with filtering, pagination, sorting
- **SimpleEditor** - Rich text editor
- **SimpleViewer** - File/content viewer
- **Language Components** - Language selector & input
- **Localization Components** - Country, city, currency, phone inputs
- **Notification** - Firebase push notification handler

Example usage:

```vue
<template>
  <SimpleTable
    :columns="columns"
    :rows="rows"
    :loading="loading"
    @request="onRequest"
  />
</template>
```

## Best Practices

### Environment Variables

Never commit `.env` file. Always use `.env.example` as template.

### TypeScript

Use strict typing:

```ts
// Define types for API responses
interface User {
  id: number;
  name: string;
  email: string;
}

const users = ref<User[]>([]);
```

### Performance

- Use lazy loading for routes
- Implement virtual scrolling for long lists
- Use `v-memo` for expensive renders
- Optimize images (use WebP, lazy loading)

### Security

- Always validate user input
- Use permission checks for sensitive actions
- Sanitize data before rendering
- Keep dependencies updated

## Troubleshooting

**Build fails with Firebase error:**
- Make sure Firebase env variables are set
- Run `yarn build` (not `quasar build` directly)

**Hot reload not working:**
- Clear `.quasar` directory: `rm -rf .quasar`
- Restart dev server

**Mobile app not syncing:**
- Run `npx cap sync` in `src-capacitor/`
- Rebuild the app

## License

MIT License - see [LICENSE](LICENSE) file for details.

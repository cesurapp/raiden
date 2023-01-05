import {boot} from 'quasar/wrappers';

/**
 * Create i18n
 */
import messages from 'src/i18n';
import {createI18n} from 'vue-i18n';

const i18n = createI18n({
  locale: localStorage.getItem('locale') ?? 'en-US',
  fallbackLocale: 'en-US',
  messages,
});
export {i18n};


/**
 * Create Axios
 */
import axios, {AxiosInstance} from 'axios';
import {ref} from 'vue';

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $client: AxiosInstance;
    $isBusy: any
  }
}

const client = axios.create({baseURL: process.env.API});
const isBusy = ref(false);
export {client, isBusy};


/**
 * Create API Client
 */
import Api from 'src/api';

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $api: Api;
  }
}

const api = new Api(client);
export {api}


/**
 * Init Vue Global Properties
 */
import routeGuard from 'boot/helper/route-guard';
import axiosInterceptors from 'boot/helper/axios-interceptor';
import validationRules from 'boot/helper/rules';
import {useAuthStore} from 'stores/AuthStore';
import {Store} from 'pinia';

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $auth: Store
  }
}

export default boot(({app, router, store}) => {
  const exceptions = ref({});

  app.use(i18n);

  // Route Guard
  routeGuard(router, store, i18n.global.t)

  // Axios Interceptors
  axiosInterceptors(client, store, i18n, isBusy, exceptions)

  // Validation Rules
  validationRules(app, i18n.global.t, exceptions)

  // Init Global Properties
  app.config.globalProperties.$api = api;
  app.config.globalProperties.$client = client;
  app.config.globalProperties.$isBusy = isBusy;
  app.config.globalProperties.$auth = useAuthStore(store);
});

import { boot } from 'quasar/wrappers';

/**
 * Create i18n
 */
import messages from 'src/i18n';
import { createI18n } from 'vue-i18n';
const i18n = createI18n({
  locale: localStorage.getItem('locale') ?? 'en-US',
  fallbackLocale: 'en-US',
  messages,
});

/**
 * Create Axios
 */
import axios, { AxiosInstance } from 'axios';
import { ref, Ref } from 'vue';
const client = axios.create({ baseURL: process.env.API });
const isBusy = ref(false);

/**
 * Create API Client
 */
import Api from 'src/api';
const api = new Api(client);
const apiRaw = new Api(axios.create({ baseURL: process.env.API }));

/**
 * Init Vue Global Properties
 */
import routeGuard from 'boot/helper/route-guard';
import axiosInterceptors from 'boot/helper/axios-interceptor';
import validationRules from 'boot/helper/rules';
import { useAuthStore } from 'stores/AuthStore';
import { useAppStore } from 'stores/AppStore';
const typeAuthStore = useAuthStore();
const typeAppStore = useAppStore();

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $api: Api;
    $client: AxiosInstance;
    $isBusy: Ref;
    $authStore: typeof typeAuthStore;
    $appStore: typeof typeAppStore;
  }
}

export { i18n, client, isBusy, api, apiRaw };
export default boot(({ app, router, store }) => {
  app.use(i18n);
  const exceptions = ref({});
  const authStore = useAuthStore(store);

  // Init Global Properties
  app.config.globalProperties.$api = api;
  app.config.globalProperties.$client = client;
  app.config.globalProperties.$isBusy = isBusy;
  app.config.globalProperties.$authStore = authStore;
  app.config.globalProperties.$appStore = useAppStore(store);

  // Route Guard
  routeGuard(router, authStore, i18n);
  axiosInterceptors(client, authStore, i18n, isBusy, exceptions);
  validationRules(app, i18n, exceptions);
});

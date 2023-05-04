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
const client = axios.create({ baseURL: process.env.API });

/**
 * Create API Client
 */
import Api from 'src/api';
const api = new Api(client);

/**
 * Init Vue Global Properties
 */
import routeGuard from 'boot/helper/route-guard';
import axiosInterceptors from 'boot/helper/axios-interceptor';
import validationRules from 'boot/helper/rules';
import { useAuthStore } from 'stores/AuthStore';
import { useAppStore } from 'stores/AppStore';
import { Permission } from 'src/api/Enum/Permission';
const typeAuthStore = useAuthStore();
const typeAppStore = useAppStore();

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $api: Api;
    $client: AxiosInstance;
    $authStore: typeof typeAuthStore;
    $appStore: typeof typeAppStore;
    $permission: typeof Permission;
  }
}

export { i18n, client, api };
export default boot(({ app, router, store }) => {
  app.use(i18n);

  const authStore = useAuthStore(store);
  const appStore = useAppStore(store);
  appStore.platform = app.config.globalProperties.$q.platform.is

  // Init Global Properties
  app.config.globalProperties.$api = api;
  app.config.globalProperties.$client = client;
  app.config.globalProperties.$authStore = authStore;
  app.config.globalProperties.$appStore = appStore;
  app.config.globalProperties.$permission = Permission;

  // Route Guard
  routeGuard(router, authStore, appStore, i18n);
  axiosInterceptors(client, authStore, appStore, i18n);
  validationRules(app, appStore, i18n);
});

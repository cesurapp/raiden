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
  missingWarn: false,
  fallbackWarn: false,
});
const tt = (prefix: string, text: string): string => {
  const result = i18n.global.t(`${prefix}.${text}`);
  return result !== `${prefix}.${text}` ? result : text;
};

/**
 * Create Axios
 */
import axios, { AxiosInstance } from 'axios';
const client = axios.create({ baseURL: process.env.API });

/**
 * Create API Client
 */
import Api from 'api/index';
const api = new Api(client);

/**
 * Init Vue Global Properties
 */
import routeGuard from 'boot/helper/route-guard';
import axiosInterceptors from 'boot/helper/axios-interceptor';
import validationRules from 'boot/helper/rules';
import { useAuthStore } from 'stores/AuthStore';
import { useAppStore } from 'stores/AppStore';
import { Permission } from 'api/enum/Permission';
const authStore = useAuthStore();
const appStore = useAppStore();

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $api: ApiInstance;
    $client: AxiosInstance;
    $authStore: ReturnType<typeof useAuthStore>;
    $appStore: ReturnType<typeof useAppStore>;
    $permission: typeof Permission;
    $tt: (prefix, text) => string;
  }
}

export { api, client, i18n, tt };
export default boot(({ app, router }) => {
  app.use(i18n);

  appStore.platform = app.config.globalProperties.$q.platform.is;

  // Init Global Properties
  app.config.globalProperties.$api = api;
  app.config.globalProperties.$client = client;
  app.config.globalProperties.$authStore = authStore;
  app.config.globalProperties.$appStore = appStore;
  app.config.globalProperties.$permission = Permission;
  app.config.globalProperties.$tt = tt;

  // Route Guard
  routeGuard(router, authStore, appStore, i18n);
  axiosInterceptors(client, authStore, appStore, i18n);
  validationRules(app, appStore, i18n);
});

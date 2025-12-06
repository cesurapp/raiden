import {defineBoot} from '#q-app/wrappers';

/**
 * Create i18n
 */
import {createI18n} from 'vue-i18n';
import messages from 'src/i18n';
export type MessageLanguages = keyof typeof messages;
export type MessageSchema = typeof messages['tr-TR'];
declare module 'vue-i18n' {
  export interface DefineLocaleMessage extends MessageSchema {}
  export interface DefineDateTimeFormat {}
  export interface DefineNumberFormat {}
}

const i18n = createI18n<{ message: MessageSchema }, MessageLanguages>({
  locale: localStorage.getItem('locale') ?? 'en-US',
  legacy: false,
  // @ts-ignore
  messages,
  fallbackLocale: 'en-US',
  missingWarn: false,
  fallbackWarn: false,
});
const tt = (prefix: string, text: string | undefined): string => {
  const mText = text ? `${prefix}.${text}` : prefix;

  // @ts-ignore
  const result = i18n.global.t(mText);
  return result !== mText ? result : (mText.split('.').pop() ?? '');
};

/**
 * Create Axios
 */
import axios from 'axios';
const client = axios.create({baseURL: process.env.API ?? ''});
import Api from '@api/index';
const api = new Api(client);


/**
 * Init Vue Global Properties
 */
import routeGuard from 'boot/helper/route-guard';
import axiosInterceptors from 'boot/helper/axios-interceptor';
import validationRules from 'boot/helper/rules';
import {useAuthStore} from 'stores/AuthStore';
import {useAppStore} from 'stores/AppStore';
import {Permission} from '@api/enum/Permission';

export {api, client, i18n, tt};
export default defineBoot(({app, router}) => {
  app.use(i18n);
  const authStore = useAuthStore();
  const appStore = useAppStore();

  // Init Global Properties
  appStore.platform = app.config.globalProperties.$q.platform.is;
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

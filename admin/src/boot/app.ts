import {defineBoot} from '#q-app/wrappers';

/**
 * Create i18n with Lazy Loading
 */
import {createI18n} from 'vue-i18n';
export const SUPPORT_LOCALES = ['en-US', 'tr-TR'] as const;
export type MessageLanguages = typeof SUPPORT_LOCALES[number];
export type MessageSchema = Record<string, any>;
declare module 'vue-i18n' {
  export interface DefineLocaleMessage extends MessageSchema {}
  export interface DefineDateTimeFormat {}
  export interface DefineNumberFormat {}
}

const i18n = createI18n<{ message: MessageSchema }, MessageLanguages>({
  locale: 'en-US',
  legacy: false,
  fallbackLocale: 'en-US',
  missingWarn: false,
  fallbackWarn: false,
});
const loadedLanguages = new Set<MessageLanguages>();
export const setI18nLanguage = (locale: MessageLanguages): MessageLanguages => {
  // @ts-ignore
  i18n.global.locale.value = locale;
  document.querySelector('html')?.setAttribute('lang', locale);
  return locale;
};
const loadLanguageAsync = async (locale: MessageLanguages): Promise<MessageLanguages> => {
  if (loadedLanguages.has(locale)) return setI18nLanguage(locale);

  try {
    const messages = await import(
      /* webpackChunkName: "locale-[request]" */
      `src/i18n/${locale}.ts`
      );
    // @ts-ignore
    i18n.global.setLocaleMessage(locale, messages.default);
    loadedLanguages.add(locale);
    return setI18nLanguage(locale);
  } catch (error) {
    return setI18nLanguage('en-US');
  }
};
const tt = (prefix: string | null | undefined, text: string | undefined): string => {
  // @ts-ignore
  if (!prefix) return i18n.global.t(text);

  const mText = text ? `${prefix}.${text}` : prefix;

  // @ts-ignore
  const result = i18n.global.t(mText);
  return result !== mText ? result : (mText.substring(mText.indexOf('.') + 1) ?? '');
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

export {api, client, i18n, tt, loadLanguageAsync};

export default defineBoot(async ({app, router}) => {
  app.use(i18n);

  // İlk dili yükle
  const savedLocale = localStorage.getItem('locale') as MessageLanguages;
  const initialLocale = SUPPORT_LOCALES.includes(savedLocale) ? savedLocale : 'en-US';
  await loadLanguageAsync(initialLocale);

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

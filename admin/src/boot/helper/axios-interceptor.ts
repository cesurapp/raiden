import { notifyShow, notifyDanger } from 'src/helper/NotifyHelper';
import { Dialog } from 'quasar';
import { AxiosError, AxiosInstance, AxiosRequestConfig, AxiosResponse } from 'axios';
import { Ref } from 'vue';

/**
 * Configure Request
 */
function requestConfig(config: AxiosRequestConfig, i18n, authStore, appStore) {
  config.uniqId = Math.random().toString(36).replace('0.','');
  appStore.busyProcess(config.uniqId);

  // Language
  config.headers.common['Accept-Language'] = i18n.global.locale['value'];

  // Auth Header
  if (authStore.isLoggedIn()) {
    config.headers.common['Authorization'] = `Bearer ${authStore.appToken}`;
  }

  // Switch User
  if (authStore.isSwitchedUser() && !config.url?.startsWith('/v1/auth')) {
    config.headers.common['SWITCH_USER'] = authStore.switchedUser;
  }

  return config;
}

/**
 * Success Response
 */
function responseSuccess(response: AxiosResponse, appStore) {
  const msg = response.config.showMessage;

  if ((typeof msg === 'undefined' || msg) && response.data.hasOwnProperty('message')) {
    Object.keys(response.data.message).forEach((type) => {
      Object.values(response.data.message[type]).forEach((message: any) => {
        notifyShow(message, undefined, type);
      });
    });
  }

  appStore.busyComplete(response.config.uniqId);

  return response;
}

let networkError = false;

async function responseError(
  error: AxiosError,
  client: AxiosInstance,
  authStore,
  appStore,
  globalExceptions: Ref,
  i18n
) {
  // Api Network Error
  if (error.message === 'Network Error') {
    if (!networkError) {
      networkError = true;
      Dialog.create({
        title: i18n.global.t('Network Error'),
        message: i18n.global.t('Could not connect to the server, refresh the page.'),
        persistent: true,
        ok: i18n.global.t('Refresh Page'),
        color: 'green',
      }).onOk(() => {
        window.location.reload();
      });
    }

    return;
  }

  // Render Response Error
  if (error.response) {
    appStore.busyComplete(error.response?.config);
    const type = error.response.data?.type;

    // Token Refresh and Continue Current Request
    if (['TokenExpiredException'].includes(type) && !error.config.retry) {
      error.config.retry = true;
      delete error.config.headers['Authorization'];

      // Reload Token
      return authStore
        .reloadTokenWithRefreshToken()
        .then(() => {
          return client(error.config);
        })
        .catch(() => {
          authStore.logout(false);
        });
    }

    // Logout for JWTException
    if (['JWTException'].includes(type)) {
      return authStore.logout(false);
    }

    // Global Exception Handling
    if (['ValidationException'].includes(type)) {
      if (Object.keys(error.response.data.errors).length > 0) {
        globalExceptions.value = error.response.data.errors;
      }
    }

    // Show Error Message
    if (error.response.data.message) {
      notifyDanger(error.response.data.message);
    }
  }

  return Promise.reject(error);
}

export default (client, authStore, appStore, i18n, globalExceptions: Ref) => {
  client.interceptors.request.use(
    async (config: AxiosRequestConfig) => requestConfig(config, i18n, authStore, appStore),
    async (error: AxiosError) => () => {
      appStore.busyProcess(error.config.uniqId);
      return Promise.reject(error);
    }
  );
  client.interceptors.response.use(
    async (response) => responseSuccess(response, appStore),
    async (error) => responseError(error, client, authStore, appStore, globalExceptions, i18n)
  );
};

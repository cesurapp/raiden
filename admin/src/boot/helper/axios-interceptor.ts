import { notifyShow, notifyDanger } from 'src/helper/NotifyHelper';
import { Dialog } from 'quasar';
import { AxiosError, AxiosRequestConfig, AxiosResponse } from 'axios';

function requestConfig(config: AxiosRequestConfig, i18n, isBusy, authStore) {
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

  isBusy.value = true;
  return config;
}

function requestError(error, isBusy) {
  isBusy.value = false;
  return Promise.reject(error);
}

function responseSuccess(response: AxiosResponse, isBusy) {
  // Show Message
  if (response.data.hasOwnProperty('message') && (response.config.showMessage ?? true)) {
    Object.keys(response.data.message).forEach((type) => {
      Object.values(response.data.message[type]).forEach((message: any) => {
        notifyShow(message, undefined, type);
      });
    });
  }

  isBusy.value = false;

  return response;
}

let networkError = false;
async function responseError(error: AxiosError, client, authStore, isBusy, globalExceptions, i18n) {
  isBusy.value = false;

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

  // Response NotFound
  if (!error.response) {
    return;
  }

  const type = error.response.data?.type;

  if (['TokenExpiredException'].includes(type) && !error.config.retry) {
    const config = error.config;
    config.retry = true;
    await authStore.reloadTokenWithRefreshToken(config);
    return client(config);
  }

  // Show Error Message
  if (error.response.data.message) {
    notifyDanger(error.response.data.message);
  }

  if (['RefreshTokenExpiredException'].includes(type) || ['JWTException'].includes(type)) {
    return authStore.logout(false);
  }

  if (['ValidationException'].includes(type)) {
    if (Object.keys(error.response.data.errors).length > 0) {
      globalExceptions.value = error.response.data.errors;
    }
  }

  return Promise.reject(error);
}

export default (client, authStore, i18n, isBusy, globalExceptions) => {
  client.interceptors.request.use(
    async (config) => requestConfig(config, i18n, isBusy, authStore),
    async (error) => requestError(error, isBusy)
  );
  client.interceptors.response.use(
    async (response) => responseSuccess(response, isBusy),
    async (error) => responseError(error, client, authStore, isBusy, globalExceptions, i18n)
  );
};

import type { AxiosError, AxiosHeaderValue, AxiosInstance, InternalAxiosRequestConfig, AxiosResponse } from 'axios';
import type { AuthStoreType } from 'stores/AuthStore';
import type { AppStoreType } from 'stores/AppStore';

/**
 * Configure Request
 */
function requestConfig(config: InternalAxiosRequestConfig, authStore: AuthStoreType, appStore: AppStoreType, i18n: any) {
  if (config.skipInterceptor === true) return config;

  // Generate Unique ID
  appStore.busyProcess((config.uniqId = Math.random().toString(36).replace('0.', '')));

  // Set Language
  config.headers['Accept-Language'] = i18n.global.locale['value'];

  // Set Auth Header
  if (authStore.isLoggedIn()) {
    config.headers.Authorization = `Bearer ${authStore.appToken}` as AxiosHeaderValue;
  }

  // Set Switch User
  if (authStore.isSwitchedUser() && !config.url?.startsWith('/v1/auth')) {
    config.headers['SWITCH_USER'] = authStore.switchedUser;
  }

  return config;
}

/**
 * Success Response
 */
function responseSuccess(response: AxiosResponse, appStore: AppStoreType) {
  appStore.busyComplete(response.config.uniqId);

  if (response.config.skipInterceptor === true) return response;

  // Render Error Message
  const msg = response.config.showMessage;
  if ((typeof msg === 'undefined' || msg) && response.data.hasOwnProperty('message')) {
    Object.keys(response.data.message).forEach((type) => {
      Object.values(response.data.message[type]).forEach((message: any) => {
        appStore.notifyShow(message, undefined, type);
      });
    });
  }

  return response;
}

async function responseError(error: AxiosError, client: AxiosInstance, authStore: AuthStoreType, appStore: AppStoreType) {
  appStore.busyComplete(error.response?.config.uniqId);

  if (error.config?.skipInterceptor === true) {
    return Promise.reject(error);
  }

  // Network Error
  if (error.message === 'Network Error') {
    if (!appStore.networkError) {
      appStore.networkError = true;
      setTimeout(() => {
        void appStore
          .dialogDanger('Could not connect to the server, refresh the page.', 'Refresh Page')
          .then(() => window.location.reload());
      }, 100);
    }

    return Promise.reject(error);
  }

  // Render Response Error
  if (error.response && error.config) {
    const data = error.response.data as any;

    // Token Refresh and Continue Current Request
    if (['TokenExpiredException'].includes(data.type) && !error.config.retry) {
      error.config.retry = true;
      delete error.config.headers.Authorization;
      return (
        authStore
          .reloadTokenWithRefreshToken()
          .then(() => client(error.config!))
          .catch(() => authStore.logout(false))
      );
    }

    // Logout for JWTException
    if (['JWTException'].includes(data.type)) {
      authStore.logout(false);
      return Promise.reject(error);
    }

    // Global Exception Handling
    if (['ValidationException'].includes(data.type)) {
      if (Object.keys(data.errors).length > 0) {
        appStore.apiExceptions = data.errors;
      }
    }

    // Show Error Message
    if (data.message) {
      appStore.notifyDanger(data.message);
    }
  }

  return Promise.reject(error);
}

export default (client: AxiosInstance, authStore: AuthStoreType, appStore: AppStoreType, i18n: any) => {
  client.interceptors.request.use(
    (config) => requestConfig(config, authStore, appStore, i18n),
    (error: AxiosError) => () => {
      if (error.config?.uniqId) {
        appStore.busyComplete(error.config.uniqId);
      }
      return Promise.reject(error);
    },
  );

  client.interceptors.response.use(
    (response) => responseSuccess(response, appStore),
    (error) => responseError(error, client, authStore, appStore),
  );
};

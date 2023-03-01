import { AxiosError, AxiosInstance, AxiosRequestConfig, AxiosResponse } from 'axios';

/**
 * Configure Request
 */
function requestConfig(config: AxiosRequestConfig, authStore, appStore, i18n) {
  if (config.skipInterceptor === true) {
    return config;
  }

  // Generate Unique ID
  appStore.busyProcess((config.uniqId = Math.random().toString(36).replace('0.', '')));

  // Set Language
  config.headers.common['Accept-Language'] = i18n.global.locale['value'];

  // Set Auth Header
  if (authStore.isLoggedIn()) {
    config.headers.common['Authorization'] = `Bearer ${authStore.appToken}`;
  }

  // Set Switch User
  if (authStore.isSwitchedUser() && !config.url?.startsWith('/v1/auth')) {
    config.headers.common['SWITCH_USER'] = authStore.switchedUser;
  }

  return config;
}

/**
 * Success Response
 */
function responseSuccess(response: AxiosResponse, appStore) {
  if (response.config.skipInterceptor === true) {
    return response;
  }

  const msg = response.config.showMessage;

  if ((typeof msg === 'undefined' || msg) && response.data.hasOwnProperty('message')) {
    Object.keys(response.data.message).forEach((type) => {
      Object.values(response.data.message[type]).forEach((message: any) => {
        appStore.notifyShow(message, undefined, type);
      });
    });
  }

  appStore.busyComplete(response.config.uniqId);

  return response;
}

async function responseError(error: AxiosError, client: AxiosInstance, authStore, appStore) {
  if (error.config.skipInterceptor === true) {
    return Promise.reject(error);
  }

  // Network Error
  if (error.message === 'Network Error') {
    if (!appStore.networkError) {
      appStore.networkError = true;
      appStore
        .dialogDanger('Could not connect to the server, refresh the page.', 'Refresh Page')
        .then(() => window.location.reload());
    }

    return Promise.reject(error);
  }

  // Render Response Error
  if (error.response) {
    appStore.busyComplete(error.response.config);
    const type = error.response.data?.type;

    // Token Refresh and Continue Current Request
    if (['TokenExpiredException'].includes(type) && !error.config.retry) {
      error.config.retry = true;
      delete error.config.headers['Authorization'];

      return authStore
        .reloadTokenWithRefreshToken()
        .then(() => client(error.config))
        .catch(() => authStore.logout(false));
    }

    // Logout for JWTException
    if (['JWTException'].includes(type)) {
      await authStore.logout(false);

      return Promise.reject(error);
    }

    // Global Exception Handling
    if (['ValidationException'].includes(type)) {
      if (Object.keys(error.response.data.errors).length > 0) {
        appStore.apiExceptions = error.response.data.errors;
      }
    }

    // Show Error Message
    if (error.response.data.message) {
      appStore.notifyDanger(error.response.data.message);
    }
  }

  return Promise.reject(error);
}

export default (client, authStore, appStore, i18n) => {
  client.interceptors.request.use(
    async (config: AxiosRequestConfig) => requestConfig(config, authStore, appStore, i18n),
    async (error: AxiosError) => () => {
      if (error.config?.uniqId) {
        appStore.busyComplete(error.config.uniqId);
      }
      return Promise.reject(error);
    }
  );
  client.interceptors.response.use(
    async (response) => responseSuccess(response, appStore),
    async (error) => responseError(error, client, authStore, appStore)
  );
};

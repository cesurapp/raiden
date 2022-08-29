import {barStart, barSuccess, barDanger} from 'src/helper/LoadingBarHelper';
import {useAuthStore} from 'stores/AuthStore';
import {notifyShow, notifyDanger} from 'src/helper/NotifyHelper';

function requestConfig(config, i18n, isBusy, authStore) {
  // Add Language
  config.headers.common['Accept-Language'] = i18n.global.locale['value'];

  // Add Auth Header
  if (authStore.isLoggedIn()) {
    config.headers.common['Authorization'] = `Bearer ${authStore.token}`;
  }

  // Loading Bar Start
  barStart();
  isBusy.value = true;
  return config;
}

function requestError(error, isBusy) {
  barDanger();
  isBusy.value = false;
  return Promise.reject(error);
}

function responseSuccess(response, isBusy) {
  // Process Response Message
  if (response.data.hasOwnProperty('message') && (response.config.message ?? true)) {
    Object.keys(response.data.message).forEach((type) => {
      Object.values(response.data.message[type]).forEach((message: any) => {
        notifyShow(message, undefined, type)
      })
    })
  }

  // Loading Success
  barSuccess();
  isBusy.value = false;

  return response;
}

async function responseError(error, client, authStore, isBusy, globalExceptions) {
  // Loading Error
  barDanger();
  isBusy.value = false;

  const type = error.response.data?.type;

  if (['TokenExpiredException'].includes(type) && !error.config._retry) {
    const config = error.config;
    config._retry = true;
    await authStore.reloadTokenWithRefreshToken(config);
    return client(config);
  }

  if (['RefreshTokenExpiredException'].includes(type)) {
    notifyDanger(error.response.data.message);
    return authStore.logout(false);
  }

  if (['JWTException'].includes(type)) {
    return authStore.logout(true);
  }

  if (['ValidationException'].includes(type)) {
    if (Object.keys(error.response.data.errors).length > 0) {
      globalExceptions.value = error.response.data.errors;
    }
  }

  // Show Error Message
  if (error.response.data.message) {
    notifyDanger(error.response.data.message);
  }

  return Promise.reject(error);
}

export default (client, store, i18n, isBusy, globalExceptions) => {
  const authStore = useAuthStore(store)

  client.interceptors.request.use(
    async (config) => requestConfig(config, i18n, isBusy, authStore),
    async (error) => requestError(error, isBusy)
  );
  client.interceptors.response.use(
    async (response) => responseSuccess(response, isBusy),
    async (error) => responseError(error, client, authStore, isBusy, globalExceptions)
  );
};


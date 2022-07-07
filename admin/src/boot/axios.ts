import {boot} from 'quasar/wrappers';
import axios, {AxiosInstance} from 'axios';

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $axios: AxiosInstance;
  }
}

/**
 * Loading Bar
 */
import {LoadingBar} from 'quasar';

LoadingBar.setDefaults({
  color: 'secondary',
  size: '15px',
  position: 'top'
});

/**
 * Init Axios
 */
const axiosInstance = axios.create({baseURL: 'https://api.example.com'});
axiosInstance.defaults.withCredentials = true;
axiosInstance.interceptors.request.use(
  function (config) {
    LoadingBar.start();
    return config;
  },
  function (error) {
    LoadingBar.stop();

    return Promise.reject(error);
  }
);

axiosInstance.interceptors.response.use(
  function (response) {
    LoadingBar.stop();
    return response;
  },
  function (error) {
    LoadingBar.stop();
    return Promise.reject(error);
  }
);

export default boot(({app}) => {
  // for use inside Vue files (Options API) through this.$axios and this.$api
  app.config.globalProperties.$axios = axios;
  // ^ ^ ^ this will allow you to use this.$axios (for Vue Options API form)
  //       so you won't necessarily have to import axios in each vue file
  app.config.globalProperties.$api = axiosInstance;
  // ^ ^ ^ this will allow you to use this.$api (for Vue Options API form)
  //       so you can easily perform requests against your app's API
});

export {axiosInstance};

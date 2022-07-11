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
  color: 'info',
  size: '4px',
  position: 'top'
});

/**
 * Init Axios
 */
const client = axios.create({baseURL: process.env.API});
client.defaults.withCredentials = true;

/**
 * Request Interceptor.
 */
client.interceptors.request.use((config) => {
    LoadingBar.setDefaults({color: 'info'})
    LoadingBar.start();

    return config;
  },
  (error) => {
    LoadingBar.setDefaults({color: 'negative'})
    LoadingBar.stop();

    return Promise.reject(error);
  }
);

/**
 * Response Interceptor.
 */
client.interceptors.response.use((response) => {
    LoadingBar.stop();

    return response;
  },
  (error) => {
    LoadingBar.setDefaults({color: 'negative'})
    LoadingBar.stop();

    return Promise.reject(error);
  }
);

/**
 * Vue Options Inject => this.$client | this.$axios
 */
export default boot(({app}) => {
  app.config.globalProperties.$axios = axios;
  app.config.globalProperties.$client = client;
});

export {client, axios};

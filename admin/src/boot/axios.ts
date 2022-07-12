import {boot} from 'quasar/wrappers';
import axios, {AxiosInstance} from 'axios';
import {ref} from 'vue';

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $axios: AxiosInstance;
    $client: AxiosInstance;
    $isBusy: boolean
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
const isBusy = ref(false);

/**
 * Request Interceptor.
 */
client.interceptors.request.use((config) => {
    LoadingBar.setDefaults({color: 'info'})
    LoadingBar.start();
    isBusy.value = true;

    return config;
  },
  (error) => {
    LoadingBar.setDefaults({color: 'negative'})
    LoadingBar.stop();
    isBusy.value = false;

    return Promise.reject(error);
  }
);

/**
 * Response Interceptor.
 */
client.interceptors.response.use((response) => {
    LoadingBar.stop();
    isBusy.value = false;

    return response;
  },
  (error) => {
    LoadingBar.setDefaults({color: 'negative'})
    LoadingBar.stop();
    isBusy.value = false;

    return Promise.reject(error);
  }
);

/**
 * Vue Options Inject => this.$client | this.$axios
 */
export default boot(({app}) => {
  app.config.globalProperties.$axios = axios;
  app.config.globalProperties.$client = client;
  app.config.globalProperties.$isBusy = isBusy;
});

export {client, axios, isBusy};

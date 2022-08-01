import {boot} from 'quasar/wrappers';
import axios, {AxiosError, AxiosInstance} from 'axios';
import {ref} from 'vue';

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $axios: AxiosInstance;
    $client: AxiosInstance;
    $isBusy: boolean
  }
}

/**
 * Init Axios
 */
const client = axios.create({baseURL: process.env.API});
const isBusy = ref(false);

/**
 * Vue Options Inject => this.$client | this.$axios | this.$isBusy
 */
export default boot(({app}) => {
  app.config.globalProperties.$axios = axios;
  app.config.globalProperties.$client = client;
  app.config.globalProperties.$isBusy = isBusy;
});

export {client, axios, isBusy};

/**
 * Interceptors
 */
import {barStart, barSuccess, barDanger} from '../helper/LoadingBarHelper';
import {processException} from '../helper/AxiosExceptionRender';
import {processResponse} from '../helper/AxiosResponseRender';


client.interceptors.request.use((config) => {
    barStart();

    isBusy.value = true;
    return config;
  },
  (error) => {
    barDanger();
    processException(error.response);

    isBusy.value = false;
    return Promise.reject(error);
  }
);
client.interceptors.response.use((response) => {
    barSuccess();
    processResponse(response);

    isBusy.value = false;
    return response;
  },
  (error: AxiosError) => {
    barDanger();
    processException(error.response);

    isBusy.value = false;
    return Promise.reject(error);
  }
);

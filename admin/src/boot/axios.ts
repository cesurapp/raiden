import {boot} from 'quasar/wrappers';
import axios, {AxiosError, AxiosInstance} from 'axios';
import {ref} from 'vue';
import {i18n} from 'boot/i18n';

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $axios: AxiosInstance;
    $client: AxiosInstance;
    $isBusy: boolean
  }
}

/**
 * Create Axios
 * Vue Options Inject => this.$client | this.$axios | this.$isBusy
 */
const client = axios.create({baseURL: process.env.API});
const isBusy = ref(false);

export default boot(({app}) => {
  app.config.globalProperties.$client = client;
  app.config.globalProperties.$isBusy = isBusy;
});

export {client, isBusy};

/**
 * Interceptors
 */
import {barStart, barSuccess, barDanger} from '../helper/LoadingBarHelper';
import {processException} from '../helper/AxiosExceptionRender';
import {processResponse} from '../helper/AxiosResponseRender';

client.interceptors.request.use((config) => {
    config.headers.common['Accept-Language'] = i18n.global.locale['value'];
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

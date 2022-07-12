import {boot} from 'quasar/wrappers'
import Api from 'src/api';
import {client} from 'boot/axios';

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $api: Api;
  }
}

const api = new Api(client);

export default boot(({app}) => {
  app.config.globalProperties.$api = api;
})

export {api}

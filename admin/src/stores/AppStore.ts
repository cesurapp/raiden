import { defineStore } from 'pinia';
import { Dialog, exportFile } from 'quasar';
import CustomDialog from 'components/CustomDialog/Index.vue';
import { notifyShow } from '../helper/NotifyHelper';
import { AxiosResponse } from 'axios';

export const useAppStore = defineStore('app', {
  state: () => ({
    title: 'Raiden Admin',
    apiExceptions: {},
    networkError: false,
    busy: [],
  }),
  getters: {
    isBusy() {
      return this.busy.length > 0;
    },
  },
  actions: {
    /**
     * Axios Process
     */
    busyProcess(id) {
      this.busy.push(id);
    },
    busyComplete(id) {
      this.busy.splice(
        this.busy.findIndex((i) => i === id),
        1
      );
    },

    /**
     * Confirmation Custom Dialog
     */
    confirmPromise(icon, color, message, ok = false, okColor = null, okText = null) {
      return new Promise((resolve, reject) => {
        const props = {
          icon: icon,
          iconBg: color,
          message: String(message),
          persistent: true,
        };

        if (ok) {
          props['cancel'] = false;
          props['yes'] = okText || 'Close';
          props['yes-color'] = okColor;
          props['persistent'] = false;
        }

        Dialog.create({ component: CustomDialog, componentProps: props })
          .onOk(() => resolve(true))
          .onDismiss(() => reject(false))
          .onCancel(() => reject(false));
      });
    },
    confirmDelete() {
      return this.confirmPromise('mdiDeleteOutline', 'negative', 'Are you sure you want to delete?');
    },
    confirmDeleteAll() {
      return this.confirmPromise('mdiDeleteOutline', 'negative', 'Are you sure you want to delete the selected items?');
    },

    /**
     * Custom Dialog
     */
    dialogInfo(message, okText = null) {
      return this.confirmPromise('mdiInformationVariant', 'info', message, true, 'info', okText);
    },
    dialogDanger(message, okText = null) {
      return this.confirmPromise('mdiAlert', 'negative', message, true, 'negative', okText);
    },
    dialogSuccess(message, okText = null) {
      return this.confirmPromise('mdiCheck', 'positive', message, true, 'positive', okText);
    },
    dialogWarning(message, okText = null) {
      return this.confirmPromise('mdiCheck', 'warning', message, true, 'warning', okText);
    },

    /**
     * Notification
     */
    notifyShow(message?: string, caption?: string, type?: string, options: object = {}) {
      notifyShow(message, caption, type, options);
    },
    notifyInfo(message?: string, caption?: string, timeout?: number) {
      notifyShow(message, caption, 'info', timeout ? { timeout: timeout } : {});
    },
    notifyDanger(message?: string, caption?: string, timeout?: number) {
      notifyShow(message, caption, 'danger', timeout ? { timeout: timeout } : {});
    },
    notifyWarning(message?: string, caption?: string, timeout?: number) {
      notifyShow(message, caption, 'warning', timeout ? { timeout: timeout } : {});
    },
    notifySuccess(message?: string, caption?: string, timeout?: number) {
      notifyShow(message, caption, 'success', timeout ? { timeout: timeout } : {});
    },

    /**
     * Axios Download to Blob
     */
    axiosDownloadFile(response: AxiosResponse) {
      const fileName = response.headers['content-disposition'].split('=');
      const status = exportFile(fileName[1], response.data, { mimeType: response.headers['content-type'] });
      if (status !== true) {
        this.dialogError(status);
      }
    },
  },
});

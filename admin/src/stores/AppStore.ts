import { defineStore } from 'pinia';
import { Dialog, exportFile } from 'quasar';
import CustomDialog from 'components/CustomDialog/Index.vue';
import {notifyWarning, notifyInfo, notifyDanger, notifySuccess} from "../helper/NotifyHelper";
import { AxiosResponse } from 'axios';

export const useAppStore = defineStore('app', {
  state: () => ({
    title: 'Raiden Admin',
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
    confirmPromise(icon, color, message, ok = false, okColor = null) {
      return new Promise((resolve, reject) => {
        const props = {
          icon: icon,
          iconBg: color,
          message: String(message),
          persistent: true,
        };

        if (ok) {
          props['cancel'] = false;
          props['yes'] = 'Close';
          props['yes-color'] = okColor
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
    dialogInfo(message) {
      return this.confirmPromise('mdiInformationVariant', 'info', message, true, 'info');
    },
    dialogDanger(message) {
      return this.confirmPromise('mdiAlert', 'negative', message, true, 'negative');
    },
    dialogSuccess(message) {
      return this.confirmPromise('mdiCheck', 'positive', message, true, 'positive');
    },
    dialogWarning(message) {
      return this.confirmPromise('mdiCheck', 'warning', message, true, 'warning');
    },

    /**
     * Notification
     */
    notifyInfo(message?: string, caption?: string, timeout?: number) {
      console.log(timeout);
      notifyInfo(message, caption, timeout ? { "timeout": timeout } : {})
    },
    notifyDanger(message?: string, caption?: string, timeout?: number) {
      notifyDanger(message, caption, timeout ? { "timeout": timeout } : {})
    },
    notifyWarning(message?: string, caption?: string, timeout?: number) {
      notifyWarning(message, caption, timeout ? { "timeout": timeout } : {})
    },
    notifySuccess(message?: string, caption?: string, timeout?: number) {
      notifySuccess(message, caption, timeout ? { "timeout": timeout } : {})
    },

    /**
     * Axios Download to Blob
     */
    axiosDownloadFile(response: AxiosResponse) {
      const fileName = response.headers['content-disposition'].split('=');
      const status = exportFile(fileName[1], response.data, {mimeType: response.headers['content-type']});
      if (status !== true) {
        this.dialogError(status);
      }
    }
  },
});

import { defineStore } from 'pinia';
import { Dialog, exportFile } from 'quasar';
import CustomDialog from 'components/CustomDialog/Index.vue';
import { notifyShow } from '../helper/NotifyHelper';
import { AxiosResponse } from 'axios';
import { Filesystem, Directory, Encoding } from '@capacitor/filesystem';
import { FileOpener } from '@capawesome-team/capacitor-file-opener';

// DayJS Install
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import customParseFormat from 'dayjs/plugin/customParseFormat';
import { i18n } from 'boot/app';
dayjs.extend(utc);
dayjs.extend(customParseFormat);

export const useAppStore = defineStore('app', {
  state: () => ({
    title: 'Raiden Admin',
    panel: 'admin',
    navMenu: true,
    navMini: false,
    apiExceptions: {},
    networkError: false,
    busy: [],
    dateFormat: 'DD/MM/YYYY',
    dateTimeFormat: 'DD/MM/YYYY HH:mm',
    platform: {},
    defaultCountry: 'TR',
  }),
  getters: {
    isBusy() {
      return this.busy.length > 0;
    },
  },
  actions: {
    /**
     * Close Navigation
     */
    closeNav() {
      if (!this.platform.desktop) {
        this.navMenu = false;
      }
    },

    /**
     * Axios Process
     */
    busyProcess(id) {
      this.busy.push(id);
    },
    busyComplete(id) {
      if (id) {
        this.busy.splice(
          this.busy.findIndex((i) => i === id),
          1,
        );
      }
    },

    /**
     * Date ATOM to Custom Format
     */
    formatDate(date: string, time = true) {
      if (!date) {
        return date;
      }
      return dayjs(date).format(time ? this.dateTimeFormat : this.dateFormat);
    },

    currentDate(time = true) {
      return dayjs().format(time ? this.dateTimeFormat : this.dateFormat);
    },

    formatPrice(amount: number, currency: string) {
      const formatter = new Intl.NumberFormat(i18n.global.locale['value'], {
        style: 'currency',
        currency: currency.toLocaleUpperCase(),
      });

      return formatter.format(amount);
    },

    /**
     * Date Convert from UTC-ATOM
     */
    inputDate(date: string, time = true) {
      return dayjs(date, time ? this.dateTimeFormat : this.dateFormat)
        .utc(!time)
        .format();
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

        if (okText) {
          props['yes'] = okText;
        }
        if (ok) {
          props['cancel'] = false;
          props['persistent'] = true;
          props['yes'] = okText || 'Close';
        }
        if (okColor) {
          props['yes-color'] = okColor;
        }

        Dialog.create({ component: CustomDialog, componentProps: props })
          .onOk(() => resolve(true))
          .onDismiss(() => reject(false))
          .onCancel(() => reject(false));
      });
    },
    confirmInfo(message) {
      return this.confirmPromise('mdiInformationVariant', 'info', message);
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
    async axiosDownloadFile(response: AxiosResponse) {
      const fileName = response.headers['content-disposition'].split('=');

      // Web
      if (!this.platform.nativeMobile) {
        const status = exportFile(fileName[1], response.data, { mimeType: response.headers['content-type'] });
        if (status !== true) {
          await this.dialogDanger(String(status));
        }

        return;
      }

      // Native Mobile
      const textContent = await new Blob([response.data]).text();
      await Filesystem.writeFile({
        path: fileName[1],
        data: textContent,
        directory: Directory.Cache,
        encoding: Encoding.UTF8,
      }).then(async (r) => {
        await FileOpener.openFile({
          path: r.uri,
          mimeType: response.headers['content-type'].split(';')[0],
        });
      });
    },
  },
});

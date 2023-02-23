import { defineStore } from 'pinia';
import { Dialog } from 'quasar';
import { i18n } from 'boot/app';

export const useAppStore = defineStore('app', {
  state: () => ({
    title: 'Raiden Admin',
    busy: []
  }),
  getters: {
    isBusy() {
      return this.busy.length > 0;
    }
  },
  actions: {
    busyProcess(id) {
      this.busy.push(id);
    },
    busyComplete(id) {
      this.busy.splice(this.busy.findIndex((i) => i === id), 1);
    },
    confirmPromise(icon, color, message) {
      return new Promise((resolve, reject) => {
        Dialog.create({
          message: `<div class='flex items-center no-wrap'><div class="q-avatar bg-${color} text-white self-start"><div class="q-avatar__content row flex-center overflow-hidden"><i class="q-icon material-icons">${icon}</i></div></div><span class='q-ml-sm'>${i18n.global.t(message)}</span></div>`,
          persistent: true,
          ok: i18n.global.t('Yes'),
          cancel: i18n.global.t('Cancel'),
          color: color,
          html: true
        })
          .onOk(() => resolve(true))
          .onDismiss(() => reject(false))
          .onCancel(() => reject(false));
      });
    },
    confirmDelete() {
      return this.confirmPromise('delete', 'red','Are you sure you want to delete?');
    },
    confirmDeleteAll() {
      return this.confirmPromise('delete','red', 'Are you sure you want to delete the selected items?');
    }
  }
});

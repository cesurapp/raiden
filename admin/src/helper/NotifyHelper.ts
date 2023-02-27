import { Notify } from 'quasar';
import { mdiClose } from '@quasar/extras/mdi-v7';

const convertMessageType = function (type) {
  switch (type) {
    case 'danger':
      return 'negative';
    case 'error':
      return 'negative';
    case 'success':
      return 'positive';
  }

  return type || 'positive';
};

function notifyShow(message?: string, caption?: string, type?: string, options: object = {}) {
  const opts: object = {
    type: convertMessageType(type),
    caption: caption,
    message: message,
    timeout: 3500,
    position: 'top',
    progress: true,
    actions: [
      {
        icon: mdiClose,
        dense: true,
        color: 'white',
        class: 'notify-close-btn',
      },
    ],
  };

  // Merge Options
  if (options['actions']) {
    options['actions'] = [...opts['actions'], ...options['actions']];
  }

  Notify.create({ ...opts, ...options });
}

function notifyWarning(message?: string, caption?: string, options: object = {}) {
  notifyShow(message, caption, 'warning', options);
}

function notifyInfo(message?: string, caption?: string, options: object = {}) {
  notifyShow(message, caption, 'info', options);
}

function notifyDanger(message?: string, caption?: string, options: object = {}) {
  notifyShow(message, caption, 'danger', options);
}

function notifySuccess(message?: string, caption?: string, options: object = {}) {
  notifyShow(message, caption, 'success', options);
}

export { notifyShow, notifyWarning, notifyInfo, notifyDanger, notifySuccess };

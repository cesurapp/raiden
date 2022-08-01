import {Notify} from 'quasar'

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
}

function notifyShow(message?: string, title?: string, type?: string, options: object = {}) {
  const opts: object = {
    type: convertMessageType(type),
    caption: title,
    message: message,
    timeout: 3500,
    position: 'top',
    progress: true,
  };

  Notify.create({
    ...opts,
    ...options
  });
}

function notifyWarning(message?: string, title?: string, options: object = {}) {
  notifyShow(message, title, 'warning', options);
}

function notifyInfo(message?: string, title?: string, options: object = {}) {
  notifyShow(message, title, 'info', options);
}

function notifyDanger(message?: string, title?: string, options: object = {}) {
  notifyShow(message, title, 'danger', options);
}

function notifySuccess(message?: string, title?: string, options: object = {}) {
  notifyShow(message, title, 'success', options);
}

export {
  notifyShow, notifyWarning, notifyInfo, notifyDanger, notifySuccess
}

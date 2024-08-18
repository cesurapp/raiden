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
    multiLine: false,
    actions: [
      {
        icon: mdiClose,
        dense: true,
        color: 'white',
      },
    ],
  };

  // Merge Options
  if (options['actions']) {
    options['actions'] = [...opts['actions'], ...options['actions']];
  }

  Notify.create({ ...opts, ...options });
}

export { notifyShow };

import {boot} from 'quasar/wrappers'
import {useAuthStore} from 'stores/AuthStore';
import {notifyDanger} from '../helper/NotifyHelper';
import {i18n} from 'boot/i18n';

const t = i18n.global.t;


export default boot(({router}) => {
  const authStore = useAuthStore();

  // Router Guard
  router.beforeEach((to, from, next) => {
    // Auhenticated Access
    if (to.matched.some(record => record.meta.requireAuth)) {
      // Check Auth
      if (!authStore.isLoggedIn()) {
        return next({name: 'auth.login', query: {next: to.fullPath}})
      }

      // Check Type
      const routeType = to.matched.flatMap(i => i.meta.type || [])
      if (routeType.length > 0 && !authStore.hasType(routeType)) {
        next({name: 'auth.login'});
        notifyDanger(t('Access denied!'));
        return;
      }

      // Check Permission
      const permission = to.matched.flatMap(i => i.meta.roles || [])
      if (permission.length > 0 && !authStore.hasGranted(permission)) {
        notifyDanger(t('Access denied! Unauthorized operation.'));
        return;
      }
    }

    next();
  })
})

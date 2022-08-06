// Router Guard
import {notifyDanger} from "src/helper/NotifyHelper";
import {useAuthStore} from "stores/AuthStore";

export default (router, store, t) => {
  const authStore = useAuthStore(store)

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
};

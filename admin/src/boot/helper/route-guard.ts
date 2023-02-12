// Router Guard
import { notifyDanger } from 'src/helper/NotifyHelper';

export default (router, authStore, t) => {
  router.beforeEach((to, from, next) => {
    // Auhenticated Access
    if (to.matched.some((record) => record.meta.requireAuth)) {
      // Check Auth
      if (!authStore.isLoggedIn()) {
        return next({ name: 'auth.login', query: { next: to.fullPath } });
      }

      // Check Type
      const routeType = to.matched.flatMap((i) => i.meta.userType || []);
      if (routeType.length > 0 && !authStore.hasUserType(routeType)) {
        next({ name: 'auth.login' });
        notifyDanger(t('Access denied!'));
        return;
      }

      // Check Permission
      const permission = to.matched.flatMap((i) => i.meta.permission || []);
      if (permission.length > 0 && !authStore.hasPermission(permission)) {
        notifyDanger(t('Access denied! Unauthorized operation.'));
        return;
      }
    }

    next();
  });
};

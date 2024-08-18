import { UserType } from 'api/enum/UserType';

export default (router, authStore, appStore, i18n) => {
  router.beforeEach((to, from, next) => {
    // Home Page Redirect
    if (to.matched.length == 1 && to.matched[0].path === '/') {
      if (!authStore.isLoggedIn()) {
        return next({ name: 'auth.login' });
      }
      if (authStore.user.type === UserType.USER) {
        // return next({ name: 'client' });
      }
      if ([UserType.ADMIN, UserType.SUPERADMIN].includes(authStore.user.type)) {
        return next({ name: 'admin' });
      }

      appStore.notifyDanger(i18n.global.t('Access denied!'));
      authStore.logout();
      return next({ name: 'auth.login' });
    }

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
        appStore.notifyDanger(i18n.global.t('Access denied!'));
        return;
      }

      // Check Permission
      const permission = to.matched.flatMap((i) => i.meta.permission || []);
      if (permission.length > 0 && !authStore.hasPermission(permission)) {
        appStore.notifyDanger(i18n.global.t('Access denied! Unauthorized operation.'));
        return;
      }
    }

    next();
  });
};

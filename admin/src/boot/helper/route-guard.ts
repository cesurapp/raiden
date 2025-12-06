import { UserType } from '@api/enum/UserType';
import type { AuthStoreType } from 'stores/AuthStore';
import type { AppStoreType } from 'stores/AppStore';
import type { NavigationGuardNext, RouteLocationNormalized, RouteLocationNormalizedLoaded, Router } from 'vue-router';

export default (router: Router, authStore: AuthStoreType, appStore: AppStoreType, i18n: any) => {
  router.beforeEach((to: RouteLocationNormalized, from: RouteLocationNormalizedLoaded, next: NavigationGuardNext) => {
    /**
     * Home Page Redirect
     */
    if (to.matched.length == 1 && to.matched[0]?.path === '/') {
      // Auth Page
      if (!authStore.isLoggedIn()) return next({ name: 'auth.login' });

      // Client Dashboard
      if (authStore.user.type === UserType.USER) {}

      // Admin Dashboard
      if ([UserType.ADMIN, UserType.SUPERADMIN].includes(authStore.user.type as UserType)) return next({ name: 'admin' });

      // Not Login
      appStore.notifyDanger(i18n.global.t('Access denied!'));
      authStore.logout();
      return next({ name: 'auth.login' });
    }

    /**
     * Authenticated Access
     */
    if (to.matched.some((record) => record.meta.requireAuth)) {
      // Check Auth
      if (!authStore.isLoggedIn()) return next({ name: 'auth.login', query: { next: to.fullPath } });

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

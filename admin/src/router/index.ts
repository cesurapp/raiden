import { route } from 'quasar/wrappers';

/**
 * Routes
 */
import { createMemoryHistory, createRouter, createWebHashHistory, createWebHistory, RouteRecordRaw } from 'vue-router';
import AdminRoutes from './admin';
import AuthRoutes from './auth';

const routes: RouteRecordRaw[] = [...AuthRoutes, ...AdminRoutes];

/**
 * 404 Page
 */
routes.push({
  path: '/:catchAll(.*)*',
  component: () => import('pages/404.vue'),
});

/**
 * Init Router
 */
export default route(function (/* { store, ssrContext } */) {
  const createHistory = process.env.SERVER
    ? createMemoryHistory
    : process.env.VUE_ROUTER_MODE === 'history'
      ? createWebHistory
      : createWebHashHistory;

  return createRouter({
    scrollBehavior: () => ({ left: 0, top: 0 }),
    routes,
    // Leave this as is and make changes in quasar.conf.js instead!
    // quasar.conf.js -> build -> vueRouterMode
    // quasar.conf.js -> build -> publicPath
    history: createHistory(process.env.VUE_ROUTER_BASE),
  });
});

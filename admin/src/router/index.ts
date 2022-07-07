import {route} from 'quasar/wrappers';
import {createMemoryHistory, createRouter, createWebHashHistory, createWebHistory} from 'vue-router';
import {StateInterface} from '../store';

/**
 * Routes
 */
import {RouteRecordRaw} from 'vue-router';
import AdminRoutes from './admin';
import AuthRoutes from './auth';

const routes: RouteRecordRaw[] = [...AdminRoutes, ...AuthRoutes];

/**
 * Init Router
 */
export default route<StateInterface>(function (/* { store, ssrContext } */) {
  const createHistory = process.env.SERVER
    ? createMemoryHistory
    : (process.env.VUE_ROUTER_MODE === 'history' ? createWebHistory : createWebHashHistory);

  return createRouter({
    scrollBehavior: () => ({left: 0, top: 0}),
    routes,
    history: createHistory(process.env.MODE === 'ssr' ? void 0 : process.env.VUE_ROUTER_BASE),
  });
});

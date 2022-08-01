import {boot} from 'quasar/wrappers'
import {useAuthStore} from "stores/AuthStore";

export default boot(({router}) => {
  const authStore = useAuthStore();

  router.beforeEach((to, from, next) => {
    if (to.matched.some(record => record.meta.requireAuth) && !authStore.isLoggedIn()) {
      next({name: 'auth.login', query: {next: to.fullPath}})
    } else {
      next()
    }
  })
})

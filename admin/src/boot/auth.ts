import {boot} from 'quasar/wrappers'

export default boot(({router}) => {
  router.beforeEach((to, from, next) => {
    if (to.matched.some(record => record.meta.requireAuth)) {
      next({name: 'auth.login', query: {next: to.fullPath}})
    } else {
      next()
    }
  })
})

export default ({app}) => {
  app.directive('close-menu', {
    mounted(el) {
      el.addEventListener('click', () => {
        const menuEl = el.closest('.q-menu')
        if (menuEl && menuEl.__vnode.ctx.proxy.$options.name === 'QMenu') {
          menuEl.__vnode.ctx.proxy.hide()
        }
      })
    }
  })
}

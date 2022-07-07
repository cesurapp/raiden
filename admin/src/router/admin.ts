export default [
  {
    path: '/',
    component: () => import('pages/Admin/Layout.vue'),
    children: [
      {path: '/', component: () => import('pages/Admin/Dashboard/Index.vue')},
    ],
  }
]

export default [
  {
    path: '/',
    component: () => import('pages/Admin/Layout.vue'),
    meta: {requireAuth: true},
    name: 'admin',
    children: [
      {path: '/', component: () => import('pages/Admin/Dashboard/Index.vue')},
    ],
  }
]

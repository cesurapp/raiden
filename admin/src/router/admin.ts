export default [
  {
    path: '/',
    component: () => import('pages/Admin/Layout.vue'),
    meta: {requireAuth: true, type: ['ROLE_ADMIN']},
    name: 'admin',
    children: [
      {path: '/', component: () => import('pages/Admin/Dashboard/Index.vue'), meta: {roles: ['ROLE_ADMIN']}},
    ],
  }
]

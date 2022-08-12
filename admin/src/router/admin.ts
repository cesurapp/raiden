export default [
  {
    path: '/',
    component: () => import('pages/Admin/Layout.vue'),
    meta: {requireAuth: true, type: ['admin']},
    name: 'admin',
    children: [
      {path: '/', component: () => import('pages/Admin/Dashboard/Index.vue'), meta: {roles: ['ROLE_ADMIN']}},
      {path: '/accounts', component: () => import('pages/Admin/Account/Accounts.vue'), meta: {roles: ['ROLE_ADMIN']}},
    ],
  }
]

export default [
  {
    path: '/',
    component: () => import('pages/Admin/Layout.vue'),
    meta: {requireAuth: true, type: ['admin', 'super_admin'], breadcrumb: 'Dashboard'},
    name: 'admin',

    children: [
      // Global
      {path: '/', component: () => import('pages/Admin/Dashboard/Index.vue'), meta: {roles: ['ROLE_ADMIN']}},
      {path: '/account/profile', component: () => import('pages/Admin/Account/Profile.vue'), meta: {breadcrumb: 'Edit Profile'}},

      // Account Management
      {path: '/account', component: () => import('pages/Admin/Account/Accounts.vue'), meta: {roles: ['ROLE_ACCOUNT_LIST'], breadcrumb: 'Accounts'}},
    ],
  }
]

export default [
  {
    path: '/',
    component: () => import('pages/Auth/Layout.vue'),
    children: [
      {path: '/login', component: () => import('pages/Auth/Login.vue'), name: 'auth.login'},
      {path: '/register', component: () => import('pages/Auth/Register.vue'), name: 'auth.register'},
      {path: '/confirm/:token', component: () => import('pages/Auth/Confirm.vue'), name: 'auth.register.confirm'},
      {path: '/reset', component: () => import('pages/Auth/ResetRequest.vue'), name: 'auth.reset.request'},
      {path: '/reset-password', component: () => import('pages/Auth/ResetPassword.vue'), name: 'auth.reset.password'},
    ],
  }
]

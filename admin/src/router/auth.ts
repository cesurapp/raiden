export default [
  {
    path: '/',
    component: () => import('pages/Auth/Layout.vue'),
    name: 'auth',
    children: [
      {path: '/login', component: () => import('pages/Auth/Login.vue'), name: 'auth.login'},
      {path: '/register', component: () => import('pages/Auth/Register.vue'), name: 'auth.register'},
      {path: '/confirm/:id', component: () => import('pages/Auth/Confirm.vue'), name: 'auth.register.confirm'},
      {path: '/reset', component: () => import('pages/Auth/ResetRequest.vue'), name: 'auth.reset.request'},
      {path: '/reset-password/:id', component: () => import('pages/Auth/ResetPassword.vue'), name: 'auth.reset.password'},
    ],
  }
]

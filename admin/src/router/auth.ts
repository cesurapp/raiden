export default [
  {
    path: '/',
    component: () => import('pages/Auth/Layout.vue'),
    name: 'auth',
    meta: { breadcrumb: 'Authorization' },

    children: [
      {
        path: 'login',
        component: () => import('pages/Auth/Login.vue'),
        name: 'auth.login',
        meta: { breadcrumb: 'Login' },
      },
      {
        path: 'login-otp/:id',
        component: () => import('pages/Auth/LoginOtp.vue'),
        name: 'auth.login.otp',
        meta: { breadcrumb: 'Security Code' },
      },
      {
        path: 'register',
        component: () => import('pages/Auth/Register.vue'),
        name: 'auth.register',
        meta: { breadcrumb: 'Register' },
      },
      {
        path: 'confirm/:id',
        component: () => import('pages/Auth/RegisterConfirm.vue'),
        name: 'auth.register.confirm',
        meta: { breadcrumb: 'Approve Account' },
      },
      {
        path: 'reset',
        component: () => import('pages/Auth/ResetRequest.vue'),
        name: 'auth.reset.request',
        meta: { breadcrumb: 'Forgot Password' },
      },
      {
        path: 'reset-password/:id',
        component: () => import('pages/Auth/ResetPassword.vue'),
        name: 'auth.reset.password',
        meta: { breadcrumb: 'Change Password' },
      },
    ],
  },
];

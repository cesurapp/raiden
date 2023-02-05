import { UserType } from 'src/api/Enum/UserType';
import { Permission } from 'src/api/Enum/Permission';

export default [
  {
    path: '/',
    name: 'admin',
    component: () => import('pages/Admin/Layout.vue'),
    meta: {
      requireAuth: true,
      userType: [UserType.ADMIN, UserType.SUPERADMIN],
      breadcrumb: 'Dashboard',
    },

    children: [
      // Global
      {
        path: '/',
        component: () => import('pages/Admin/Dashboard/Index.vue'),
      },
      {
        path: '/account/profile',
        component: () => import('pages/Admin/Account/Profile.vue'),
        meta: { breadcrumb: 'Edit Profile' },
      },

      // Account Management
      {
        path: '/account',
        component: () => import('pages/Admin/Account/Accounts.vue'),
        meta: {
          breadcrumb: 'Accounts',
          permission: [Permission.AdminAccount.LIST],
        },
      },
    ],
  },
];

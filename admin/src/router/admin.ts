import { UserType } from '@api/enum/UserType';
import { Permission } from '@api/enum/Permission';

export default [
  {
    path: '/admin',
    component: () => import('pages/Admin/Layout.vue'),
    meta: {
      requireAuth: true,
      userType: [UserType.ADMIN, UserType.SUPERADMIN],
    },

    children: [
      // Admin HomePage
      {
        path: '',
        name: 'admin',
        component: () => import('pages/Admin/Dashboard/Index.vue'),
        meta: { breadcrumb: 'Dashboard' },
      },
      {
        path: 'account/profile',
        name: 'admin.profile',
        component: () => import('pages/Admin/Account/ProfilePage.vue'),
        meta: { breadcrumb: 'Edit Profile' },
      },

      // Account Management
      {
        path: 'account',
        name: 'admin.account',
        component: () => import('pages/Admin/Account/AccountPage.vue'),
        meta: { breadcrumb: 'Accounts', permission: [Permission.AdminAccount.LIST] },
      },

      // Firebase Devices & Scheduled Notifications
      {
        path: 'firebase/devices',
        name: 'admin.firebase.devices',
        component: () => import('pages/Admin/Firebase/DevicePage.vue'),
        meta: { breadcrumb: 'Firebase Devices', permission: [Permission.AdminDevice.LIST] },
      },
      {
        path: 'firebase/scheduler',
        name: 'admin.firebase.scheduler',
        component: () => import('pages/Admin/Firebase/SchedulerPage.vue'),
        meta: { breadcrumb: 'Scheduled Notifications', permission: [Permission.AdminScheduler.LIST] },
      },
    ],
  },
];

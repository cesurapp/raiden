import { defineStore } from 'pinia';
import { LocalStorage } from 'quasar';
import { api } from 'boot/app';
import { UserType } from 'src/api/Enum/UserType';
import { UserResource } from 'src/api/Resource/UserResource';
import { AxiosRequestConfig } from 'axios';
import { watch } from 'vue';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: LocalStorage.getItem('user') as UserResource,
    appToken: LocalStorage.getItem('appToken'),
    refreshToken: LocalStorage.getItem('refreshToken'),
    switchedUser: LocalStorage.getItem('switchedUser'),
  }),

  actions: {
    /**
     * Login with Username & Password
     */
    async loginUsername(username: string, password: string) {
      await api.securityLogin({ username: username, password: password }).then((r) => {
        this.user = r.data.data;
        this.appToken = r.data.token;
        this.refreshToken = r.data.refresh_token;

        // Redirect
        this.router.push({ path: '/' });
      });
    },

    /**
     * Login with Passwordless Request
     */
    async loginOtpRequest(username: string) {
      await api.securityLoginOtpRequest({ username: username }).then(() => {
        this.router.push({
          name: 'auth.login.otp',
          params: { id: btoa(username) },
        });
      });
    },

    /**
     * Login with Passwordless
     */
    async loginOtp(username: string, otpKey: number) {
      await api.securityLoginOtp({ username: username, otp_key: otpKey }).then((r) => {
        this.user = r.data.data;
        this.appToken = r.data.token;
        this.refreshToken = r.data.refresh_token;

        // Redirect
        this.router.push({ path: '/' });
      });
    },

    /**
     * Reload token with Refresh Token
     */
    async reloadTokenWithRefreshToken(config: AxiosRequestConfig): Promise<any> {
      const refreshToken = this.refreshToken;
      this.clearToken();

      return await api.securityRefreshToken({ refresh_token: refreshToken }).then((r) => {
        this.appToken = r.data.data.token;
        config.headers['Authorization'] = `Bearer ${this.appToken}`;
      });
    },

    /**
     * Reload User in Profile Api
     */
    async reloadUser() {
      if (this.isLoggedIn()) {
        await api.accountShowProfile().then((r) => {
          this.updateUser(r.data.data);
        });
      }
    },

    /**
     * Update User State
     */
    updateUser(user: UserResource) {
      this.user = user;
    },

    /**
     * Logout User and Clear Token
     */
    async logout(showMessage = true) {
      await api
        .securityLogout({ refresh_token: this.refreshToken }, { showMessage: showMessage })
        .finally(() => this.clearToken());

      this.router.push({ name: 'auth.login' });
    },

    /**
     * Switch User
     */
    async switchUser(username: string) {
      await api.accountShowProfile({ headers: { SWITCH_USER: username } }).then((r) => {
        if (r.data.data.type !== UserType.USER) {
          this.switchedUser = username;
          this.reloadUser();
          this.router.push({ path: '/' });
        }
      });
    },

    /**
     * Logout Switch User
     */
    switchUserLogout(redirect: boolean) {
      this.switchedUser = null;
      this.reloadUser();
      if (redirect) {
        this.router.push({ path: '/' });
      }
    },

    /**
     * Check Switched User
     */
    isSwitchedUser(): boolean {
      return this.switchedUser !== null;
    },

    /**
     * Clear Token
     */
    clearToken() {
      this.user = null;
      this.appToken = null;
      this.refreshToken = null;
      this.switchedUser = null;
    },

    /**
     * Check Login
     */
    isLoggedIn(): boolean {
      return this.appToken && this.user;
    },

    /**
     * Check User Type
     */
    hasUserType(userType: UserType | Array<string>): boolean {
      if (Array.isArray(userType)) {
        return userType.includes(this.user.type);
      }

      return userType === this.user.type;
    },

    /**
     * Check User Permission
     */
    hasPermission(permission: string | Array<string>): boolean {
      // Super Admin
      if (this.user.type === UserType.SUPERADMIN) {
        return true;
      }

      if (Array.isArray(permission)) {
        return permission.every((r) => this.user.roles.indexOf(r) !== -1);
      }

      return this.user.roles.includes(permission);
    },
  },
});

// Set State to LocalStorage
watch(useAuthStore().$state, (states) => {
  Object.entries(states).forEach(([k, v]) => {
    if (v === null) {
      LocalStorage.remove(k);
    } else {
      LocalStorage.set(k, v);
    }
  });
});

import { defineStore } from 'pinia';
import { LocalStorage } from 'quasar';
import { api } from 'boot/app';
import { UserType } from 'src/api/Enum/UserType';
import { UserResource } from 'src/api/Resource/UserResource';
import { AxiosRequestConfig } from 'axios';

const key = {
  user: 'app-user',
  token: 'app-token',
  refreshToken: 'app-refresh-token',
};

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: LocalStorage.getItem(key['user']) as UserResource,
    token: LocalStorage.getItem(key['token']) as string,
  }),

  actions: {
    async loginUsername(username: string, password: string) {
      await api.securityLogin({ username: username, password: password }).then((r) => {
        // Init State
        this.user = r.data.data;
        this.token = r.data.token;

        // Save Token
        LocalStorage.set(key['user'], r.data.data);
        LocalStorage.set(key['token'], r.data.token);
        LocalStorage.set(key['refreshToken'], r.data.refresh_token);

        // Redirect
        this.router.push({ path: '/' });
      });
    },
    async loginOtpRequest(username: string) {
      await api.securityLoginOtpRequest({ username: username }).then(() => {
        // Redirect
        this.router.push({
          name: 'auth.login.otp',
          params: { id: btoa(username) },
        });
      });
    },
    async loginOtp(username: string, otpKey: number) {
      await api.securityLoginOtp({ username: username, otp_key: otpKey }).then((r) => {
        // Init Satate
        this.user = r.data.data;
        this.token = r.data.token;

        // Save Token
        LocalStorage.set(key['user'], r.data.data);
        LocalStorage.set(key['token'], r.data.token);
        LocalStorage.set(key['refreshToken'], r.data.refresh_token);

        // Redirect
        this.router.push({ path: '/' });
      });
    },
    async reloadTokenWithRefreshToken(config: AxiosRequestConfig): Promise<any> {
      // Clear Current Token
      this.clearToken();

      const refreshToken = LocalStorage.getItem(key['refreshToken'])?.toString();
      if (!refreshToken) {
        return null;
      }

      return await api.securityRefreshToken({ refresh_token: refreshToken }).then((r) => {
        this.token = r.data.data.token;
        LocalStorage.set(key['token'], r.data.data.token);
        config.headers['Authorization'] = `Bearer ${this.token}`;
      });
    },
    async reloadUser() {
      if (this.isLoggedIn()) {
        await api.accountShowProfile().then((r) => {
          this.updateUser(r.data.data);
        });
      }
    },
    async logout(showMessage = true) {
      await api
        .securityLogout(
          {
            refresh_token: LocalStorage.getItem(key['refreshToken'])?.toString(),
          },
          // @ts-ignore
          { message: showMessage }
        )
        .finally(() => {
          this.clearToken();
          this.clearRefreshToken();
        });

      // Redirect
      this.router.push({ name: 'auth.login' });
    },
    clearToken() {
      LocalStorage.remove(key['token']);
      LocalStorage.remove(key['user']);
      this.token = null;
    },
    clearRefreshToken() {
      LocalStorage.remove(key['refreshToken']);
    },
    isLoggedIn(): boolean {
      return this.token && this.user;
    },
    updateUser(user: UserResource) {
      this.user = user;
      LocalStorage.set(key['user'], user);
    },

    /**
     * Check User Type
     */
    hasUserType(userType: UserType | Array<any>): boolean {
      if (Array.isArray(userType)) {
        return userType.includes(this.user.type);
      }

      return userType === this.user.type;
    },

    /**
     * Check User Permission
     */
    hasPermission(permission: string | Array<any>): boolean {
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

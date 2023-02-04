import { defineStore } from 'pinia';
import { LocalStorage } from 'quasar';
import { api } from 'boot/app';
import { SecurityLoginResponse } from 'src/api/Response/SecurityLoginResponse';
import { UserType } from 'src/api/Enum/UserType';

const key = {
  user: 'app-user',
  token: 'app-token',
  refreshToken: 'app-refresh-token',
};

export const useAuthStore = defineStore('auth', {
  state: () =>
    ({
      data: LocalStorage.getItem(key['user']),
      token: LocalStorage.getItem(key['token']),
    } as SecurityLoginResponse),

  getters: {
    roles: (state) => state.data.phone,
  },

  actions: {
    async loginUsername(username, password) {
      await api
        .securityLogin({ username: username, password: password })
        .then((r) => {
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
    async loginOtpRequest(username) {
      await api.securityLoginOtpRequest({ username: username }).then(() => {
        // Redirect
        this.router.push({
          name: 'auth.login.otp',
          params: { id: btoa(username) },
        });
      });
    },
    async loginOtp(username, otpKey) {
      await api
        .securityLoginOtp({ username: username, otp_key: otpKey })
        .then((r) => {
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
    async reloadTokenWithRefreshToken(config) {
      this.clearToken();

      const token = LocalStorage.getItem(key['refreshToken'])?.toString();
      if (token) {
        return await api
          .securityRefreshToken({ refresh_token: token })
          .then((r) => {
            this.token = r.data.data.token;
            LocalStorage.set(key['token'], r.data.data.token);
            config.headers['Authorization'] = `Bearer ${this.token}`;
          });
      }

      return null;
    },
    async reloadUser() {
      if (this.isLoggedIn()) {
        await api.accountShowProfile().then((r) => {
          this.updateUser(r.data.data);
        });
      }
    },
    logout(showMessage = true) {
      // @ts-ignore
      api
        .securityLogout(
          {
            refresh_token: LocalStorage.getItem(
              key['refreshToken']
            )?.toString(),
          },
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
      this.token = null;
    },
    clearRefreshToken() {
      LocalStorage.remove(key['refreshToken']);
    },
    isLoggedIn(): boolean {
      return this.token && this.user;
    },
    updateUser(user: object) {
      this.user = user;
      LocalStorage.set('app-user', user);
    },

    /**
     * Check User Type
     */
    hasUserType(type: UserType): boolean {
      return this.user ? type === this.data.type : false;
    },

    /**
     * Check User Permission
     */
    hasPermission(permission: string | Array<any>): boolean {
      // Super Admin
      if (this.data.type === UserType.SUPERADMIN) {
        return true;
      }

      if (Array.isArray(permission)) {
        return permission.every((r) => this.data.roles.indexOf(r) !== -1);
      }

      return this.data.roles.includes(permission);
    },
  },
});

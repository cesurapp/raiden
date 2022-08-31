import {defineStore} from 'pinia';
import {LocalStorage} from 'quasar';
import {api} from 'boot/app';
import {SecurityLoginResponse} from 'src/api/Response/SecurityLoginResponse';

const key = {
  'user': 'app-user',
  'token': 'app-token',
  'refreshToken': 'app-refresh-token',
};

export const useAuthStore = defineStore('auth', {
  state: () => {
    return {
      user: LocalStorage.getItem(key['user']),
      token: LocalStorage.getItem(key['token']),
    } as SecurityLoginResponse
  },

  getters: {
    roles: (state) => state.user,
  },

  actions: {
    async loginUsername(username, password) {
      await api.securityLogin({username: username, password: password}).then((r) => {
        this.user = r.data.user;
        this.token = r.data.token;

        // Save Token
        LocalStorage.set(key['user'], r.data.user);
        LocalStorage.set(key['token'], r.data.token);
        LocalStorage.set(key['refreshToken'], r.data.refresh_token);

        // Redirect
        this.router.push({path: '/'});
      })
    },
    async loginOtpRequest(username) {
        await api.securityLoginOtpRequest({username: username}).then(() => {
          // Redirect
          this.router.push({name: 'auth.login.otp', params: {'id': btoa(username)}});
        })
    },
    async loginOtp(username, otpKey) {
      await api.securityLoginOtp({username: username, otp_key: otpKey}).then((r) => {
        this.user = r.data.user;
        this.token = r.data.token;

        // Save Token
        LocalStorage.set(key['user'], r.data.user);
        LocalStorage.set(key['token'], r.data.token);
        LocalStorage.set(key['refreshToken'], r.data.refresh_token);

        // Redirect
        this.router.push({path: '/'});
      })
    },
    async reloadTokenWithRefreshToken(config) {
      this.clearToken();

      const token = LocalStorage.getItem(key['refreshToken'])?.toString();
      if (token) {
        return await api.securityRefreshToken({refresh_token: token}).then((r) => {
          this.token = r.data.token;
          LocalStorage.set(key['token'], r.data.token);
          config.headers['Authorization'] = `Bearer ${this.token}`;
        })
      }

      return null;
    },
    logout(showMessage = true) {
      // @ts-ignore
      api.securityLogout({refresh_token: LocalStorage.getItem(key['refreshToken'])?.toString()}, {message: showMessage}).finally(() => {
        this.clearToken();
        this.clearRefreshToken();
      });

      // Redirect
      this.router.push({name: 'auth.login'});
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
    hasGranted(role: string|Array<any>): boolean {
      // Super Admin
      if (this.user.type === 'super_admin') {
        return true;
      }

      if (Array.isArray(role)) {
        return role.every(r => this.user.roles.indexOf(r) !== -1)
      }

      return this.user.roles.includes(role);
    },
    hasType(type: Array<any>): boolean {
      return this.user ? type.includes(this.user.type) : false;
    },
  },
});

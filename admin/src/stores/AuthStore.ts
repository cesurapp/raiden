import {defineStore} from 'pinia';
import {SessionStorage, LocalStorage} from 'quasar';
import {api} from "boot/app";

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: LocalStorage.getItem('app-user'),
    token: LocalStorage.getItem('app-token')
  }),
  getters: {
    roles: (state) => state.user.roles,
  },
  actions: {
    async loginUsername(username, password) {
      await api.securityLogin({username: username, password: password}).then((r) => {
        this.user = r.data.user;
        this.token = r.data.token;

        // Save Token
        LocalStorage.set('app-user', r.data.user);
        LocalStorage.set('app-token', r.data.token);
        SessionStorage.set('app-refresh-token', r.data.refresh_token);

        // Redirect
        this.router.push({path: '/'});
      })
    },
    async reloadTokenWithRefreshToken(config) {
      this.clearToken();

      const token = SessionStorage.getItem('app-refresh-token')?.toString();
      if (token) {
        return await api.securityRefreshToken({refresh_token: token}).then((r) => {
          this.token = r.data.token;
          LocalStorage.set('app-token', r.data.token);
          config.headers['Authorization'] = `Bearer ${this.token}`;
        })
      }

      return null;
    },
    logout() {
      this.clearToken();
      this.clearRefreshToken();

      // Redirect
      this.router.push({name: 'auth.login'});
    },
    clearToken() {
      LocalStorage.remove('app-token');
      this.token = null;
    },
    clearRefreshToken() {
      SessionStorage.remove('app-refresh-token');
    },
    isLoggedIn(): boolean {
      return this.token && this.user;
    },
    updateUser(user: object) {
      this.user = user;
      LocalStorage.set('app-user', user);
    },
    hasGranted(role: string|Array<any>): boolean {
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

import {defineStore} from 'pinia';
import {api} from 'boot/api';
import {SessionStorage, LocalStorage} from "quasar";

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
      api.securityLogin({username: username, password: password}).then((r) => {
        this.user = r.data.user;
        this.token = r.data.token;
        LocalStorage.set('app-user', r.data.user);
        LocalStorage.set('app-token', r.data.token);
        SessionStorage.set('app-refresh-token', r.data.refresh_token);

        this.router.push({path: '/'});
      })
    },
    async loginRefreshToken() {
      const token = SessionStorage.getItem('app-refresh-token')?.toString();
      if (token) {
        api.securityRefreshToken({refresh_token: token}).then((r) => {
          this.token = r.data.token;
          LocalStorage.set('app-token', r.data.token);
        })
      }
    },
    logout() {
      this.user = null;
      this.token = null;
      LocalStorage.remove('app-user');
      LocalStorage.remove('app-token');
      SessionStorage.remove('app-refresh-token');

      this.router.push({name: 'auth.login'});
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
      return type.includes(this.user.type);
    },
  },
});

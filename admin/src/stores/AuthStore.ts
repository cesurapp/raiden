import { defineStore } from 'pinia';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null
  }),
  getters: {
    roles: (state) => state.user,
  },
  actions: {
    isLogin(): boolean {
      return this.user;
    },
    setLogin() {

    },
    reloadLogin() {

    },
    hasRole(role: string): boolean {
      return false;
    },
    hasGranted(role: string): boolean{
      return false;
    },

  },
});

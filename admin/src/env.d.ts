declare namespace NodeJS {
  interface ProcessEnv {
    NODE_ENV: string;
    VUE_ROUTER_MODE: 'hash' | 'history' | 'abstract' | undefined;
    VUE_ROUTER_BASE: string | undefined;
  }
}

/**
 * Axios Custom Config
 */
import 'axios';

declare module 'axios' {
  export interface AxiosRequestConfig {
    retry?: boolean;
    showMessage?: boolean;
    uniqId?: string | number;
    skipInterceptor?: boolean;
  }

  export interface InternalAxiosRequestConfig {
    retry?: boolean;
    showMessage?: boolean;
    uniqId?: string | number;
    skipInterceptor?: boolean;
  }
}

import type {AxiosInstance} from "axios";
import type {ApiInstance} from "@api";
import type {AppStoreType} from "stores/AppStore";
import type {AuthStoreType} from "stores/AuthStore";
import {Permission} from "@api/enum/Permission";
import {Composer} from "vue-i18n";
import {MessageSchema} from "boot/app";

declare module 'vue' {
  interface ComponentCustomProperties {
    $client: AxiosInstance;
    $api: ApiInstance,
    $authStore: AuthStoreType;
    $appStore: AppStoreType;
    $permission: typeof Permission;
    $t: Composer<MessageSchema>['t'];
    $tt: Composer<MessageSchema>['tt'];
  }
}

/* eslint-disable no-param-reassign */
/* eslint-disable max-len */
/* eslint-disable @typescript-eslint/no-unused-vars */
/* eslint-disable no-useless-constructor */

import type { AxiosInstance, AxiosRequestConfig, Method, AxiosResponse } from 'axios';
// @ts-ignore
import { toQueryString } from './flatten';

import type { NotificationListResponse } from './main/response/NotificationListResponse';
import type { NotificationListQuery } from './main/query/NotificationListQuery';
import type { DeviceType } from '@api/enum/DeviceType';
import type { NotificationUnreadCountResponse } from './main/response/NotificationUnreadCountResponse';
import type { NotificationReadAllResponse } from './main/response/NotificationReadAllResponse';
import type { NotificationReadResponse } from './main/response/NotificationReadResponse';
import type { NotificationDeleteResponse } from './main/response/NotificationDeleteResponse';
import type { DeviceRegisterResponse } from './main/response/DeviceRegisterResponse';
import type { DeviceRegisterRequest } from './main/request/DeviceRegisterRequest';
import type { CredentialsRequestResponse } from './main/response/CredentialsRequestResponse';
import type { CredentialsRequestRequest } from './main/request/CredentialsRequestRequest';
import type { CredentialsApproveResponse } from './main/response/CredentialsApproveResponse';
import type { CredentialsApproveRequest } from './main/request/CredentialsApproveRequest';

export default class Main {
  constructor(private client: AxiosInstance) {}

  async NotificationList(device: DeviceType, query?: NotificationListQuery, config: AxiosRequestConfig = {}): Promise<AxiosResponse<NotificationListResponse>> {
    return this.rq('GET', `/v1/main/notification/${device}${toQueryString(query)}`, config, null)
  }

  async NotificationUnreadCount(config: AxiosRequestConfig = {}): Promise<AxiosResponse<NotificationUnreadCountResponse>> {
    return this.rq('GET', '/v1/main/notification/unread-count', config, null)
  }

  async NotificationReadAll(config: AxiosRequestConfig = {}): Promise<AxiosResponse<NotificationReadAllResponse>> {
    return this.rq('POST', '/v1/main/notification/read-all', config, null)
  }

  async NotificationRead(id?: string, config: AxiosRequestConfig = {}): Promise<AxiosResponse<NotificationReadResponse>> {
    return this.rq('PUT', `/v1/main/notification/${id}`, config, null)
  }

  async NotificationDelete(id?: string, config: AxiosRequestConfig = {}): Promise<AxiosResponse<NotificationDeleteResponse>> {
    return this.rq('DELETE', `/v1/main/notification/${id}`, config, null)
  }

  async DeviceRegister(request?: DeviceRegisterRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<DeviceRegisterResponse>> {
    return this.rq('POST', '/v1/main/notification/fcm-register', config, request)
  }

  async CredentialsRequest(request?: CredentialsRequestRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<CredentialsRequestResponse>> {
    return this.rq('PUT', '/v1/main/credentials', config, request)
  }

  async CredentialsApprove(request?: CredentialsApproveRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<CredentialsApproveResponse>> {
    return this.rq('POST', '/v1/main/credentials', config, request)
  }

  async rq(method: Method, url: string, config: AxiosRequestConfig = {}, data?: any) {
    config.method = method;
    config.url = url;
    if (data) {
      config.data = data;
    }

    return await this.client.request(config);
  }

  rl(method: Method, url: string, config: AxiosRequestConfig = {}) {
    config.method = method;
    config.url = url;

    return this.client.getUri(config);
  }
}
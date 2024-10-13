/* eslint-disable no-param-reassign */
/* eslint-disable max-len */
/* eslint-disable @typescript-eslint/no-unused-vars */
/* eslint-disable no-useless-constructor */

import type { AxiosInstance, AxiosRequestConfig, Method, AxiosResponse } from 'axios';
// @ts-ignore
import { toQueryString } from './flatten';

import type { AccountListResponse } from './admin/response/AccountListResponse';
import type { AccountListQuery } from './admin/query/AccountListQuery';
import type { AccountCreateResponse } from './admin/response/AccountCreateResponse';
import type { AccountCreateRequest } from './admin/request/AccountCreateRequest';
import type { AccountEditResponse } from './admin/response/AccountEditResponse';
import type { AccountEditRequest } from './admin/request/AccountEditRequest';
import type { AccountShowResponse } from './admin/response/AccountShowResponse';
import type { AccountDeleteResponse } from './admin/response/AccountDeleteResponse';
import type { AccountEditPermissionResponse } from './admin/response/AccountEditPermissionResponse';
import type { AccountEditPermissionRequest } from './admin/request/AccountEditPermissionRequest';
import type { DeviceListResponse } from './admin/response/DeviceListResponse';
import type { DeviceListQuery } from './admin/query/DeviceListQuery';
import type { DeviceDeleteResponse } from './admin/response/DeviceDeleteResponse';
import type { DeviceSendResponse } from './admin/response/DeviceSendResponse';
import type { DeviceSendRequest } from './admin/request/DeviceSendRequest';
import type { SchedulerListResponse } from './admin/response/SchedulerListResponse';
import type { SchedulerListQuery } from './admin/query/SchedulerListQuery';
import type { SchedulerCreateResponse } from './admin/response/SchedulerCreateResponse';
import type { SchedulerCreateRequest } from './admin/request/SchedulerCreateRequest';
import type { SchedulerEditResponse } from './admin/response/SchedulerEditResponse';
import type { SchedulerEditRequest } from './admin/request/SchedulerEditRequest';
import type { SchedulerDeleteResponse } from './admin/response/SchedulerDeleteResponse';

export default class Admin {
  constructor(private client: AxiosInstance) {}

  async AccountList(query?: AccountListQuery, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AccountListResponse>> {
    return this.rq('GET', `/v1/admin/account/manager${toQueryString(query)}`, config, null)
  }

  async AccountCreate(request?: AccountCreateRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AccountCreateResponse>> {
    return this.rq('POST', '/v1/admin/account/manager', config, request)
  }

  async AccountEdit(id?: string, request?: AccountEditRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AccountEditResponse>> {
    return this.rq('PUT', `/v1/admin/account/manager/${id}`, config, request)
  }

  async AccountShow(id?: string, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AccountShowResponse>> {
    return this.rq('GET', `/v1/admin/account/manager/${id}`, config, null)
  }

  async AccountDelete(id?: string, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AccountDeleteResponse>> {
    return this.rq('DELETE', `/v1/admin/account/manager/${id}`, config, null)
  }

  async AccountEditPermission(id?: string, request?: AccountEditPermissionRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AccountEditPermissionResponse>> {
    return this.rq('PUT', `/v1/admin/account/permission/${id}`, config, request)
  }

  async DeviceList(query?: DeviceListQuery, config: AxiosRequestConfig = {}): Promise<AxiosResponse<DeviceListResponse>> {
    return this.rq('GET', `/v1/admin/notification/device${toQueryString(query)}`, config, null)
  }

  async DeviceDelete(id?: string, config: AxiosRequestConfig = {}): Promise<AxiosResponse<DeviceDeleteResponse>> {
    return this.rq('DELETE', `/v1/admin/notification/device/${id}`, config, null)
  }

  async DeviceSend(id?: string, request?: DeviceSendRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<DeviceSendResponse>> {
    return this.rq('POST', `/v1/admin/notification/device/${id}`, config, request)
  }

  async SchedulerList(query?: SchedulerListQuery, config: AxiosRequestConfig = {}): Promise<AxiosResponse<SchedulerListResponse>> {
    return this.rq('GET', `/v1/admin/scheduler${toQueryString(query)}`, config, null)
  }

  async SchedulerCreate(request?: SchedulerCreateRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<SchedulerCreateResponse>> {
    return this.rq('POST', '/v1/admin/scheduler', config, request)
  }

  async SchedulerEdit(id?: string, request?: SchedulerEditRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<SchedulerEditResponse>> {
    return this.rq('PUT', `/v1/admin/scheduler/${id}`, config, request)
  }

  async SchedulerDelete(id?: string, config: AxiosRequestConfig = {}): Promise<AxiosResponse<SchedulerDeleteResponse>> {
    return this.rq('DELETE', `/v1/admin/scheduler/${id}`, config, null)
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
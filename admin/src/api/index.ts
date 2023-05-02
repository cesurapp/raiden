/* eslint-disable no-param-reassign */
/* eslint-disable max-len */
/* eslint-disable @typescript-eslint/no-unused-vars */
/* eslint-disable no-useless-constructor */

import type { AxiosInstance, AxiosRequestConfig, Method, AxiosResponse } from 'axios';
import { toQueryString } from './flatten';

import type { SecurityLoginResponse } from './Response/SecurityLoginResponse';
import type { SecurityLoginRequest } from './Request/SecurityLoginRequest';
import type { SecurityRefreshTokenResponse } from './Response/SecurityRefreshTokenResponse';
import type { SecurityRefreshTokenRequest } from './Request/SecurityRefreshTokenRequest';
import type { SecurityLoginOtpRequestResponse } from './Response/SecurityLoginOtpRequestResponse';
import type { SecurityLoginOtpRequestRequest } from './Request/SecurityLoginOtpRequestRequest';
import type { SecurityLoginOtpResponse } from './Response/SecurityLoginOtpResponse';
import type { SecurityLoginOtpRequest } from './Request/SecurityLoginOtpRequest';
import type { SecurityLogoutResponse } from './Response/SecurityLogoutResponse';
import type { SecurityLogoutRequest } from './Request/SecurityLogoutRequest';
import type { SecurityRegisterResponse } from './Response/SecurityRegisterResponse';
import type { SecurityRegisterRequest } from './Request/SecurityRegisterRequest';
import type { SecurityApproveResponse } from './Response/SecurityApproveResponse';
import type { SecurityApproveRequest } from './Request/SecurityApproveRequest';
import type { SecurityResetRequestResponse } from './Response/SecurityResetRequestResponse';
import type { SecurityResetRequestRequest } from './Request/SecurityResetRequestRequest';
import type { SecurityResetPasswordResponse } from './Response/SecurityResetPasswordResponse';
import type { SecurityResetPasswordRequest } from './Request/SecurityResetPasswordRequest';
import type { NotificationListResponse } from './Response/NotificationListResponse';
import type { NotificationListQuery } from './Query/NotificationListQuery';
import type { DeviceType } from './Enum/DeviceType';
import type { NotificationUnreadCountResponse } from './Response/NotificationUnreadCountResponse';
import type { NotificationReadAllResponse } from './Response/NotificationReadAllResponse';
import type { NotificationReadResponse } from './Response/NotificationReadResponse';
import type { NotificationDeleteResponse } from './Response/NotificationDeleteResponse';
import type { DeviceRegisterResponse } from './Response/DeviceRegisterResponse';
import type { DeviceRegisterRequest } from './Request/DeviceRegisterRequest';
import type { AccountShowProfileResponse } from './Response/AccountShowProfileResponse';
import type { AccountEditProfileResponse } from './Response/AccountEditProfileResponse';
import type { AccountEditProfileRequest } from './Request/AccountEditProfileRequest';
import type { AccountListResponse } from './Response/AccountListResponse';
import type { AccountListQuery } from './Query/AccountListQuery';
import type { AccountCreateResponse } from './Response/AccountCreateResponse';
import type { AccountCreateRequest } from './Request/AccountCreateRequest';
import type { AccountEditResponse } from './Response/AccountEditResponse';
import type { AccountEditRequest } from './Request/AccountEditRequest';
import type { AccountShowResponse } from './Response/AccountShowResponse';
import type { AccountDeleteResponse } from './Response/AccountDeleteResponse';
import type { AccountEditPermissionResponse } from './Response/AccountEditPermissionResponse';
import type { AccountEditPermissionRequest } from './Request/AccountEditPermissionRequest';
import type { DeviceListResponse } from './Response/DeviceListResponse';
import type { DeviceListQuery } from './Query/DeviceListQuery';
import type { DeviceDeleteResponse } from './Response/DeviceDeleteResponse';
import type { DeviceSendResponse } from './Response/DeviceSendResponse';
import type { DeviceSendRequest } from './Request/DeviceSendRequest';
import type { SchedulerListResponse } from './Response/SchedulerListResponse';
import type { SchedulerListQuery } from './Query/SchedulerListQuery';
import type { SchedulerCreateResponse } from './Response/SchedulerCreateResponse';
import type { SchedulerCreateRequest } from './Request/SchedulerCreateRequest';
import type { SchedulerEditResponse } from './Response/SchedulerEditResponse';
import type { SchedulerEditRequest } from './Request/SchedulerEditRequest';
import type { SchedulerDeleteResponse } from './Response/SchedulerDeleteResponse';
import type { CredentialsRequestResponse } from './Response/CredentialsRequestResponse';
import type { CredentialsRequestRequest } from './Request/CredentialsRequestRequest';
import type { CredentialsApproveResponse } from './Response/CredentialsApproveResponse';
import type { CredentialsApproveRequest } from './Request/CredentialsApproveRequest';

export default class Api {
  constructor(private client: AxiosInstance) {}

  async securityLogin(request?: SecurityLoginRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<SecurityLoginResponse>> {
    return this.rq('POST', '/v1/auth/login', config, request)
  }

  async securityRefreshToken(request?: SecurityRefreshTokenRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<SecurityRefreshTokenResponse>> {
    return this.rq('POST', '/v1/auth/refresh-token', config, request)
  }

  async securityLoginOtpRequest(request?: SecurityLoginOtpRequestRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<SecurityLoginOtpRequestResponse>> {
    return this.rq('PUT', '/v1/auth/login-otp', config, request)
  }

  async securityLoginOtp(request?: SecurityLoginOtpRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<SecurityLoginOtpResponse>> {
    return this.rq('POST', '/v1/auth/login-otp', config, request)
  }

  async securityLogout(request?: SecurityLogoutRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<SecurityLogoutResponse>> {
    return this.rq('POST', '/v1/auth/logout', config, request)
  }

  async securityRegister(request?: SecurityRegisterRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<SecurityRegisterResponse>> {
    return this.rq('POST', '/v1/auth/register', config, request)
  }

  async securityApprove(request?: SecurityApproveRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<SecurityApproveResponse>> {
    return this.rq('POST', '/v1/auth/approve', config, request)
  }

  async securityResetRequest(request?: SecurityResetRequestRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<SecurityResetRequestResponse>> {
    return this.rq('POST', '/v1/auth/reset-request', config, request)
  }

  async securityResetPassword(request?: SecurityResetPasswordRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<SecurityResetPasswordResponse>> {
    return this.rq('POST', '/v1/auth/reset-password/', config, request)
  }

  async notificationList(device: DeviceType, query?: NotificationListQuery, config: AxiosRequestConfig = {}): Promise<AxiosResponse<NotificationListResponse>> {
    return this.rq('GET', `/v1/main/notification/${device}${toQueryString(query)}`, config, null)
  }

  async notificationUnreadCount(config: AxiosRequestConfig = {}): Promise<AxiosResponse<NotificationUnreadCountResponse>> {
    return this.rq('GET', '/v1/main/notification/unread-count', config, null)
  }

  async notificationReadAll(config: AxiosRequestConfig = {}): Promise<AxiosResponse<NotificationReadAllResponse>> {
    return this.rq('POST', '/v1/main/notification/read-all', config, null)
  }

  async notificationRead(id?: string, config: AxiosRequestConfig = {}): Promise<AxiosResponse<NotificationReadResponse>> {
    return this.rq('PUT', `/v1/main/notification/${id}`, config, null)
  }

  async notificationDelete(id?: string, config: AxiosRequestConfig = {}): Promise<AxiosResponse<NotificationDeleteResponse>> {
    return this.rq('DELETE', `/v1/main/notification/${id}`, config, null)
  }

  async deviceRegister(request?: DeviceRegisterRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<DeviceRegisterResponse>> {
    return this.rq('POST', '/v1/main/notification/fcm-register', config, request)
  }

  async accountShowProfile(config: AxiosRequestConfig = {}): Promise<AxiosResponse<AccountShowProfileResponse>> {
    return this.rq('GET', '/v1/admin/account/profile', config, null)
  }

  async accountEditProfile(request?: AccountEditProfileRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AccountEditProfileResponse>> {
    return this.rq('PUT', '/v1/admin/account/profile', config, request)
  }

  async accountList(query?: AccountListQuery, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AccountListResponse>> {
    return this.rq('GET', `/v1/admin/account/manager${toQueryString(query)}`, config, null)
  }

  async accountCreate(request?: AccountCreateRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AccountCreateResponse>> {
    return this.rq('POST', '/v1/admin/account/manager', config, request)
  }

  async accountEdit(id?: string, request?: AccountEditRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AccountEditResponse>> {
    return this.rq('PUT', `/v1/admin/account/manager/${id}`, config, request)
  }

  async accountShow(id?: string, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AccountShowResponse>> {
    return this.rq('GET', `/v1/admin/account/manager/${id}`, config, null)
  }

  async accountDelete(id?: string, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AccountDeleteResponse>> {
    return this.rq('DELETE', `/v1/admin/account/manager/${id}`, config, null)
  }

  async accountEditPermission(id?: string, request?: AccountEditPermissionRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AccountEditPermissionResponse>> {
    return this.rq('PUT', `/v1/admin/account/permission/${id}`, config, request)
  }

  async deviceList(query?: DeviceListQuery, config: AxiosRequestConfig = {}): Promise<AxiosResponse<DeviceListResponse>> {
    return this.rq('GET', `/v1/admin/notification/device${toQueryString(query)}`, config, null)
  }

  async deviceDelete(id?: string, config: AxiosRequestConfig = {}): Promise<AxiosResponse<DeviceDeleteResponse>> {
    return this.rq('DELETE', `/v1/admin/notification/device/${id}`, config, null)
  }

  async deviceSend(id?: string, request?: DeviceSendRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<DeviceSendResponse>> {
    return this.rq('POST', `/v1/admin/notification/device/${id}`, config, request)
  }

  async schedulerList(query?: SchedulerListQuery, config: AxiosRequestConfig = {}): Promise<AxiosResponse<SchedulerListResponse>> {
    return this.rq('GET', `/v1/admin/scheduler${toQueryString(query)}`, config, null)
  }

  async schedulerCreate(request?: SchedulerCreateRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<SchedulerCreateResponse>> {
    return this.rq('POST', '/v1/admin/scheduler', config, request)
  }

  async schedulerEdit(id?: string, request?: SchedulerEditRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<SchedulerEditResponse>> {
    return this.rq('PUT', `/v1/admin/scheduler/${id}`, config, request)
  }

  async schedulerDelete(id?: string, config: AxiosRequestConfig = {}): Promise<AxiosResponse<SchedulerDeleteResponse>> {
    return this.rq('DELETE', `/v1/admin/scheduler/${id}`, config, null)
  }

  async credentialsRequest(request?: CredentialsRequestRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<CredentialsRequestResponse>> {
    return this.rq('PUT', '/v1/main/credentials', config, request)
  }

  async credentialsApprove(request?: CredentialsApproveRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<CredentialsApproveResponse>> {
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
}
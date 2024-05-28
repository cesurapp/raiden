/* eslint-disable no-param-reassign */
/* eslint-disable max-len */
/* eslint-disable @typescript-eslint/no-unused-vars */
/* eslint-disable no-useless-constructor */

import type { AxiosInstance, AxiosRequestConfig, Method, AxiosResponse } from 'axios';
import { toQueryString } from './flatten';

import type { AuthSecurityLoginResponse } from './Response/AuthSecurityLoginResponse';
import type { AuthSecurityLoginRequest } from './Request/AuthSecurityLoginRequest';
import type { AuthSecurityRefreshTokenResponse } from './Response/AuthSecurityRefreshTokenResponse';
import type { AuthSecurityRefreshTokenRequest } from './Request/AuthSecurityRefreshTokenRequest';
import type { AuthSecurityLoginOtpRequestResponse } from './Response/AuthSecurityLoginOtpRequestResponse';
import type { AuthSecurityLoginOtpRequestRequest } from './Request/AuthSecurityLoginOtpRequestRequest';
import type { AuthSecurityLoginOtpResponse } from './Response/AuthSecurityLoginOtpResponse';
import type { AuthSecurityLoginOtpRequest } from './Request/AuthSecurityLoginOtpRequest';
import type { AuthSecurityLogoutResponse } from './Response/AuthSecurityLogoutResponse';
import type { AuthSecurityLogoutRequest } from './Request/AuthSecurityLogoutRequest';
import type { AuthSecurityRegisterResponse } from './Response/AuthSecurityRegisterResponse';
import type { AuthSecurityRegisterRequest } from './Request/AuthSecurityRegisterRequest';
import type { AuthSecurityApproveResponse } from './Response/AuthSecurityApproveResponse';
import type { AuthSecurityApproveRequest } from './Request/AuthSecurityApproveRequest';
import type { AuthSecurityResetRequestResponse } from './Response/AuthSecurityResetRequestResponse';
import type { AuthSecurityResetRequestRequest } from './Request/AuthSecurityResetRequestRequest';
import type { AuthSecurityResetPasswordResponse } from './Response/AuthSecurityResetPasswordResponse';
import type { AuthSecurityResetPasswordRequest } from './Request/AuthSecurityResetPasswordRequest';
import type { MainNotificationListResponse } from './Response/MainNotificationListResponse';
import type { MainNotificationListQuery } from './Query/MainNotificationListQuery';
import type { DeviceType } from './Enum/DeviceType';
import type { MainNotificationUnreadCountResponse } from './Response/MainNotificationUnreadCountResponse';
import type { MainNotificationReadAllResponse } from './Response/MainNotificationReadAllResponse';
import type { MainNotificationReadResponse } from './Response/MainNotificationReadResponse';
import type { MainNotificationDeleteResponse } from './Response/MainNotificationDeleteResponse';
import type { MainDeviceRegisterResponse } from './Response/MainDeviceRegisterResponse';
import type { MainDeviceRegisterRequest } from './Request/MainDeviceRegisterRequest';
import type { AdminAccountShowProfileResponse } from './Response/AdminAccountShowProfileResponse';
import type { AdminAccountEditProfileResponse } from './Response/AdminAccountEditProfileResponse';
import type { AdminAccountEditProfileRequest } from './Request/AdminAccountEditProfileRequest';
import type { AdminAccountListResponse } from './Response/AdminAccountListResponse';
import type { AdminAccountListQuery } from './Query/AdminAccountListQuery';
import type { AdminAccountCreateResponse } from './Response/AdminAccountCreateResponse';
import type { AdminAccountCreateRequest } from './Request/AdminAccountCreateRequest';
import type { AdminAccountEditResponse } from './Response/AdminAccountEditResponse';
import type { AdminAccountEditRequest } from './Request/AdminAccountEditRequest';
import type { AdminAccountShowResponse } from './Response/AdminAccountShowResponse';
import type { AdminAccountDeleteResponse } from './Response/AdminAccountDeleteResponse';
import type { AdminAccountEditPermissionResponse } from './Response/AdminAccountEditPermissionResponse';
import type { AdminAccountEditPermissionRequest } from './Request/AdminAccountEditPermissionRequest';
import type { AdminDeviceListResponse } from './Response/AdminDeviceListResponse';
import type { AdminDeviceListQuery } from './Query/AdminDeviceListQuery';
import type { AdminDeviceDeleteResponse } from './Response/AdminDeviceDeleteResponse';
import type { AdminDeviceSendResponse } from './Response/AdminDeviceSendResponse';
import type { AdminDeviceSendRequest } from './Request/AdminDeviceSendRequest';
import type { AdminSchedulerListResponse } from './Response/AdminSchedulerListResponse';
import type { AdminSchedulerListQuery } from './Query/AdminSchedulerListQuery';
import type { AdminSchedulerCreateResponse } from './Response/AdminSchedulerCreateResponse';
import type { AdminSchedulerCreateRequest } from './Request/AdminSchedulerCreateRequest';
import type { AdminSchedulerEditResponse } from './Response/AdminSchedulerEditResponse';
import type { AdminSchedulerEditRequest } from './Request/AdminSchedulerEditRequest';
import type { AdminSchedulerDeleteResponse } from './Response/AdminSchedulerDeleteResponse';
import type { MainCredentialsRequestResponse } from './Response/MainCredentialsRequestResponse';
import type { MainCredentialsRequestRequest } from './Request/MainCredentialsRequestRequest';
import type { MainCredentialsApproveResponse } from './Response/MainCredentialsApproveResponse';
import type { MainCredentialsApproveRequest } from './Request/MainCredentialsApproveRequest';

export default class Api {
  constructor(private client: AxiosInstance) {}

  async authSecurityLogin(request?: AuthSecurityLoginRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AuthSecurityLoginResponse>> {
    return this.rq('POST', '/v1/auth/login', config, request)
  }

  async authSecurityRefreshToken(request?: AuthSecurityRefreshTokenRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AuthSecurityRefreshTokenResponse>> {
    return this.rq('POST', '/v1/auth/refresh-token', config, request)
  }

  async authSecurityLoginOtpRequest(request?: AuthSecurityLoginOtpRequestRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AuthSecurityLoginOtpRequestResponse>> {
    return this.rq('PUT', '/v1/auth/login-otp', config, request)
  }

  async authSecurityLoginOtp(request?: AuthSecurityLoginOtpRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AuthSecurityLoginOtpResponse>> {
    return this.rq('POST', '/v1/auth/login-otp', config, request)
  }

  async authSecurityLogout(request?: AuthSecurityLogoutRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AuthSecurityLogoutResponse>> {
    return this.rq('POST', '/v1/auth/logout', config, request)
  }

  async authSecurityRegister(request?: AuthSecurityRegisterRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AuthSecurityRegisterResponse>> {
    return this.rq('POST', '/v1/auth/register', config, request)
  }

  async authSecurityApprove(request?: AuthSecurityApproveRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AuthSecurityApproveResponse>> {
    return this.rq('POST', '/v1/auth/approve', config, request)
  }

  async authSecurityResetRequest(request?: AuthSecurityResetRequestRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AuthSecurityResetRequestResponse>> {
    return this.rq('POST', '/v1/auth/reset-request', config, request)
  }

  async authSecurityResetPassword(request?: AuthSecurityResetPasswordRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AuthSecurityResetPasswordResponse>> {
    return this.rq('POST', '/v1/auth/reset-password/', config, request)
  }

  async mainNotificationList(device: DeviceType, query?: MainNotificationListQuery, config: AxiosRequestConfig = {}): Promise<AxiosResponse<MainNotificationListResponse>> {
    return this.rq('GET', `/v1/main/notification/${device}${toQueryString(query)}`, config, null)
  }

  async mainNotificationUnreadCount(config: AxiosRequestConfig = {}): Promise<AxiosResponse<MainNotificationUnreadCountResponse>> {
    return this.rq('GET', '/v1/main/notification/unread-count', config, null)
  }

  async mainNotificationReadAll(config: AxiosRequestConfig = {}): Promise<AxiosResponse<MainNotificationReadAllResponse>> {
    return this.rq('POST', '/v1/main/notification/read-all', config, null)
  }

  async mainNotificationRead(id?: string, config: AxiosRequestConfig = {}): Promise<AxiosResponse<MainNotificationReadResponse>> {
    return this.rq('PUT', `/v1/main/notification/${id}`, config, null)
  }

  async mainNotificationDelete(id?: string, config: AxiosRequestConfig = {}): Promise<AxiosResponse<MainNotificationDeleteResponse>> {
    return this.rq('DELETE', `/v1/main/notification/${id}`, config, null)
  }

  async mainDeviceRegister(request?: MainDeviceRegisterRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<MainDeviceRegisterResponse>> {
    return this.rq('POST', '/v1/main/notification/fcm-register', config, request)
  }

  async adminAccountShowProfile(config: AxiosRequestConfig = {}): Promise<AxiosResponse<AdminAccountShowProfileResponse>> {
    return this.rq('GET', '/v1/admin/account/profile', config, null)
  }

  async adminAccountEditProfile(request?: AdminAccountEditProfileRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AdminAccountEditProfileResponse>> {
    return this.rq('PUT', '/v1/admin/account/profile', config, request)
  }

  async adminAccountList(query?: AdminAccountListQuery, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AdminAccountListResponse>> {
    return this.rq('GET', `/v1/admin/account/manager${toQueryString(query)}`, config, null)
  }

  async adminAccountCreate(request?: AdminAccountCreateRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AdminAccountCreateResponse>> {
    return this.rq('POST', '/v1/admin/account/manager', config, request)
  }

  async adminAccountEdit(id?: string, request?: AdminAccountEditRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AdminAccountEditResponse>> {
    return this.rq('PUT', `/v1/admin/account/manager/${id}`, config, request)
  }

  async adminAccountShow(id?: string, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AdminAccountShowResponse>> {
    return this.rq('GET', `/v1/admin/account/manager/${id}`, config, null)
  }

  async adminAccountDelete(id?: string, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AdminAccountDeleteResponse>> {
    return this.rq('DELETE', `/v1/admin/account/manager/${id}`, config, null)
  }

  async adminAccountEditPermission(id?: string, request?: AdminAccountEditPermissionRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AdminAccountEditPermissionResponse>> {
    return this.rq('PUT', `/v1/admin/account/permission/${id}`, config, request)
  }

  async adminDeviceList(query?: AdminDeviceListQuery, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AdminDeviceListResponse>> {
    return this.rq('GET', `/v1/admin/notification/device${toQueryString(query)}`, config, null)
  }

  async adminDeviceDelete(id?: string, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AdminDeviceDeleteResponse>> {
    return this.rq('DELETE', `/v1/admin/notification/device/${id}`, config, null)
  }

  async adminDeviceSend(id?: string, request?: AdminDeviceSendRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AdminDeviceSendResponse>> {
    return this.rq('POST', `/v1/admin/notification/device/${id}`, config, request)
  }

  async adminSchedulerList(query?: AdminSchedulerListQuery, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AdminSchedulerListResponse>> {
    return this.rq('GET', `/v1/admin/scheduler${toQueryString(query)}`, config, null)
  }

  async adminSchedulerCreate(request?: AdminSchedulerCreateRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AdminSchedulerCreateResponse>> {
    return this.rq('POST', '/v1/admin/scheduler', config, request)
  }

  async adminSchedulerEdit(id?: string, request?: AdminSchedulerEditRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AdminSchedulerEditResponse>> {
    return this.rq('PUT', `/v1/admin/scheduler/${id}`, config, request)
  }

  async adminSchedulerDelete(id?: string, config: AxiosRequestConfig = {}): Promise<AxiosResponse<AdminSchedulerDeleteResponse>> {
    return this.rq('DELETE', `/v1/admin/scheduler/${id}`, config, null)
  }

  async mainCredentialsRequest(request?: MainCredentialsRequestRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<MainCredentialsRequestResponse>> {
    return this.rq('PUT', '/v1/main/credentials', config, request)
  }

  async mainCredentialsApprove(request?: MainCredentialsApproveRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<MainCredentialsApproveResponse>> {
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
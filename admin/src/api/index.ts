/* eslint-disable no-param-reassign */
/* eslint-disable max-len */
/* eslint-disable @typescript-eslint/no-unused-vars */
/* eslint-disable no-useless-constructor */

import type { AxiosInstance, AxiosRequestConfig, Method } from 'axios';
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
import type { NotificationReadResponse } from './Response/NotificationReadResponse';
import type { NotificationDeleteResponse } from './Response/NotificationDeleteResponse';
import type { NotificationReadAllResponse } from './Response/NotificationReadAllResponse';
import type { AccountMeResponse } from './Response/AccountMeResponse';
import type { AccountGetProfileResponse } from './Response/AccountGetProfileResponse';
import type { NotificationTestappResponse } from './Response/NotificationTestappResponse';

export default class Api {
  constructor(private client: AxiosInstance) {}

  async securityLogin(request?: SecurityLoginRequest, config: AxiosRequestConfig = {}): Promise<SecurityLoginResponse> {
    return this.rq('POST', '/v1/auth/login', config, request)
  }

  async securityRefreshToken(request?: SecurityRefreshTokenRequest, config: AxiosRequestConfig = {}): Promise<SecurityRefreshTokenResponse> {
    return this.rq('POST', '/v1/auth/refresh-token', config, request)
  }

  async securityLoginOtpRequest(request?: SecurityLoginOtpRequestRequest, config: AxiosRequestConfig = {}): Promise<SecurityLoginOtpRequestResponse> {
    return this.rq('PUT', '/v1/auth/login-otp', config, request)
  }

  async securityLoginOtp(request?: SecurityLoginOtpRequest, config: AxiosRequestConfig = {}): Promise<SecurityLoginOtpResponse> {
    return this.rq('POST', '/v1/auth/login-otp', config, request)
  }

  async securityLogout(request?: SecurityLogoutRequest, config: AxiosRequestConfig = {}): Promise<SecurityLogoutResponse> {
    return this.rq('POST', '/v1/auth/logout', config, request)
  }

  async securityRegister(request?: SecurityRegisterRequest, config: AxiosRequestConfig = {}): Promise<SecurityRegisterResponse> {
    return this.rq('POST', '/v1/auth/register', config, request)
  }

  async securityApprove(request?: SecurityApproveRequest, config: AxiosRequestConfig = {}): Promise<SecurityApproveResponse> {
    return this.rq('POST', '/v1/auth/approve', config, request)
  }

  async securityResetRequest(request?: SecurityResetRequestRequest, config: AxiosRequestConfig = {}): Promise<SecurityResetRequestResponse> {
    return this.rq('POST', '/v1/auth/reset-request', config, request)
  }

  async securityResetPassword(request?: SecurityResetPasswordRequest, config: AxiosRequestConfig = {}): Promise<SecurityResetPasswordResponse> {
    return this.rq('POST', '/v1/auth/reset-password/', config, request)
  }

  async notificationList(query?: NotificationListQuery, config: AxiosRequestConfig = {}): Promise<NotificationListResponse> {
    return this.rq('GET', `/v1/main/notification?${toQueryString(query)}`, config, null)
  }

  async notificationRead(id?: string, config: AxiosRequestConfig = {}): Promise<NotificationReadResponse> {
    return this.rq('PUT', `/v1/main/notification/${id}`, config, null)
  }

  async notificationDelete(id?: string, config: AxiosRequestConfig = {}): Promise<NotificationDeleteResponse> {
    return this.rq('DELETE', `/v1/main/notification/${id}`, config, null)
  }

  async notificationReadAll(config: AxiosRequestConfig = {}): Promise<NotificationReadAllResponse> {
    return this.rq('POST', '/v1/main/notification/read-all', config, null)
  }

  async accountMe(config: AxiosRequestConfig = {}): Promise<AccountMeResponse> {
    return this.rq('GET', '/v1/admin/profile', config, null)
  }

  async accountGetProfile(config: AxiosRequestConfig = {}): Promise<AccountGetProfileResponse> {
    return this.rq('GET', '/v1/profile', config, null)
  }

  async notificationTestapp(config: AxiosRequestConfig = {}): Promise<NotificationTestappResponse> {
    return this.rq('GET', '/v1/sendnotify', config, null)
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
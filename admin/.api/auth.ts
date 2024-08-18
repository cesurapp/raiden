/* eslint-disable no-param-reassign */
/* eslint-disable max-len */
/* eslint-disable @typescript-eslint/no-unused-vars */
/* eslint-disable no-useless-constructor */

import type { AxiosInstance, AxiosRequestConfig, Method, AxiosResponse } from 'axios';
// @ts-ignore
import { toQueryString } from './flatten';

import type { SecurityLoginResponse } from './auth/response/SecurityLoginResponse';
import type { SecurityLoginRequest } from './auth/request/SecurityLoginRequest';
import type { SecurityRefreshTokenResponse } from './auth/response/SecurityRefreshTokenResponse';
import type { SecurityRefreshTokenRequest } from './auth/request/SecurityRefreshTokenRequest';
import type { SecurityLoginOtpRequestResponse } from './auth/response/SecurityLoginOtpRequestResponse';
import type { SecurityLoginOtpRequestRequest } from './auth/request/SecurityLoginOtpRequestRequest';
import type { SecurityLoginOtpResponse } from './auth/response/SecurityLoginOtpResponse';
import type { SecurityLoginOtpRequest } from './auth/request/SecurityLoginOtpRequest';
import type { SecurityLogoutResponse } from './auth/response/SecurityLogoutResponse';
import type { SecurityLogoutRequest } from './auth/request/SecurityLogoutRequest';
import type { SecurityRegisterResponse } from './auth/response/SecurityRegisterResponse';
import type { SecurityRegisterRequest } from './auth/request/SecurityRegisterRequest';
import type { SecurityApproveResponse } from './auth/response/SecurityApproveResponse';
import type { SecurityApproveRequest } from './auth/request/SecurityApproveRequest';
import type { SecurityResetRequestResponse } from './auth/response/SecurityResetRequestResponse';
import type { SecurityResetRequestRequest } from './auth/request/SecurityResetRequestRequest';
import type { SecurityResetPasswordResponse } from './auth/response/SecurityResetPasswordResponse';
import type { SecurityResetPasswordRequest } from './auth/request/SecurityResetPasswordRequest';

export default class Auth {
  constructor(private client: AxiosInstance) {}

  async SecurityLogin(request?: SecurityLoginRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<SecurityLoginResponse>> {
    return this.rq('POST', '/v1/auth/login', config, request)
  }

  async SecurityRefreshToken(request?: SecurityRefreshTokenRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<SecurityRefreshTokenResponse>> {
    return this.rq('POST', '/v1/auth/refresh-token', config, request)
  }

  async SecurityLoginOtpRequest(request?: SecurityLoginOtpRequestRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<SecurityLoginOtpRequestResponse>> {
    return this.rq('PUT', '/v1/auth/login-otp', config, request)
  }

  async SecurityLoginOtp(request?: SecurityLoginOtpRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<SecurityLoginOtpResponse>> {
    return this.rq('POST', '/v1/auth/login-otp', config, request)
  }

  async SecurityLogout(request?: SecurityLogoutRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<SecurityLogoutResponse>> {
    return this.rq('POST', '/v1/auth/logout', config, request)
  }

  async SecurityRegister(request?: SecurityRegisterRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<SecurityRegisterResponse>> {
    return this.rq('POST', '/v1/auth/register', config, request)
  }

  async SecurityApprove(request?: SecurityApproveRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<SecurityApproveResponse>> {
    return this.rq('POST', '/v1/auth/approve', config, request)
  }

  async SecurityResetRequest(request?: SecurityResetRequestRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<SecurityResetRequestResponse>> {
    return this.rq('POST', '/v1/auth/reset-request', config, request)
  }

  async SecurityResetPassword(request?: SecurityResetPasswordRequest, config: AxiosRequestConfig = {}): Promise<AxiosResponse<SecurityResetPasswordResponse>> {
    return this.rq('POST', '/v1/auth/reset-password/', config, request)
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
/* eslint-disable prettier/prettier */
/* eslint-disable max-len */

import type { AxiosResponse } from 'axios';

interface SecurityLoginOtpRequestResponse200 {
  message?: {
    success?: Array<string|number|boolean>
  }
}
export type SecurityLoginOtpRequestResponse = AxiosResponse<SecurityLoginOtpRequestResponse200>;

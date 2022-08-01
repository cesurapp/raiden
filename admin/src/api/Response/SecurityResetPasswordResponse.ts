/* eslint-disable prettier/prettier */
/* eslint-disable max-len */

import type { AxiosResponse } from 'axios';

interface SecurityResetPasswordResponse200 {
  message?: {
    success?: Array<string|number|boolean>
  }
}
export type SecurityResetPasswordResponse = AxiosResponse<SecurityResetPasswordResponse200>;

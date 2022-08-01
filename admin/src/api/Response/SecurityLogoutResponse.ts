/* eslint-disable prettier/prettier */
/* eslint-disable max-len */

import type { AxiosResponse } from 'axios';

interface SecurityLogoutResponse200 {
  message?: {
    success?: Array<string|number|boolean>
  }
}
export type SecurityLogoutResponse = AxiosResponse<SecurityLogoutResponse200>;

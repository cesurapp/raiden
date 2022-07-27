/* eslint-disable prettier/prettier */
/* eslint-disable max-len */

import type { AxiosResponse } from 'axios';

interface SecurityResetRequestResponse200 {
  message?: {
    success?: Array<string|number|boolean>
  }
}
export type SecurityResetRequestResponse = AxiosResponse<SecurityResetRequestResponse200>;

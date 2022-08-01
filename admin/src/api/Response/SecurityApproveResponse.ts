/* eslint-disable prettier/prettier */
/* eslint-disable max-len */

import type { AxiosResponse } from 'axios';

interface SecurityApproveResponse200 {
  message?: {
    success?: Array<string|number|boolean>
  }
}
export type SecurityApproveResponse = AxiosResponse<SecurityApproveResponse200>;

/* eslint-disable prettier/prettier */
/* eslint-disable max-len */

import type { AxiosResponse } from 'axios';

interface AccountGetProfileResponse200 {
  message?: {
    success?: Array<string|number|boolean>
  }
}
export type AccountGetProfileResponse = AxiosResponse<AccountGetProfileResponse200>;

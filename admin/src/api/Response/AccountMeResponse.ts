/* eslint-disable prettier/prettier */
/* eslint-disable max-len */

import type { AxiosResponse } from 'axios';

interface AccountMeResponse200 {
  message?: {
    success?: Array<string|number|boolean>
  }
}
export type AccountMeResponse = AxiosResponse<AccountMeResponse200>;

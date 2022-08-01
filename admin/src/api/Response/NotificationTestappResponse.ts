/* eslint-disable prettier/prettier */
/* eslint-disable max-len */

import type { AxiosResponse } from 'axios';

interface NotificationTestappResponse200 {
  message?: {
    success?: Array<string|number|boolean>
  }
}
export type NotificationTestappResponse = AxiosResponse<NotificationTestappResponse200>;

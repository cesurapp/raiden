/* eslint-disable prettier/prettier */
/* eslint-disable max-len */

import type { AxiosResponse } from 'axios';

interface NotificationDeleteResponse200 {
  message?: {
    success?: Array<string|number|boolean>
  }
}
export type NotificationDeleteResponse = AxiosResponse<NotificationDeleteResponse200>;

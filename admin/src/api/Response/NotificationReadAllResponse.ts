/* eslint-disable prettier/prettier */
/* eslint-disable max-len */

import type { AxiosResponse } from 'axios';

interface NotificationReadAllResponse200 {
  message?: {
    success?: Array<string|number|boolean>
  }
}
export type NotificationReadAllResponse = AxiosResponse<NotificationReadAllResponse200>;

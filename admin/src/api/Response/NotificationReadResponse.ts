/* eslint-disable prettier/prettier */
/* eslint-disable max-len */

import type { AxiosResponse } from 'axios';

interface NotificationReadResponse200 {
  message?: {
    success?: Array<string|number|boolean>
  }
}
export type NotificationReadResponse = AxiosResponse<NotificationReadResponse200>;

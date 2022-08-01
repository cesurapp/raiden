/* eslint-disable prettier/prettier */
/* eslint-disable max-len */

import type { AxiosResponse } from 'axios';

interface NotificationListResponse200 {
  data?: [
    {
      id: string,
      type: string,
      title: string,
      message: string,
      readed: boolean,
      data: Array<string|number|boolean>,
      createdAt: string
    }
  ],
  pager: {
    max: number,
    current: number,
    prev?: number,
    next?: number,
    total?: number
  }
}
export type NotificationListResponse = AxiosResponse<NotificationListResponse200>;

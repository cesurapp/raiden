/* eslint-disable max-len */

import type { NotificationResource } from '../Resource/NotificationResource';

export interface MainNotificationListResponse {
  data: [
    NotificationResource
  ],
  pager: {
    max: number,
    current: number,
    prev?: number,
    next?: number,
    total?: number
  }
}

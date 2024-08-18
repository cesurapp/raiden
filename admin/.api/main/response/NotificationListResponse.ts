/* eslint-disable max-len */

import type { NotificationResource } from '@api/admin/resource/NotificationResource';

export interface NotificationListResponse {
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

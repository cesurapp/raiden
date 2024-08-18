/* eslint-disable max-len */

import type { SchedulerResource } from '@api/admin/resource/SchedulerResource';

export interface SchedulerListResponse {
  data: [
    SchedulerResource
  ],
  pager: {
    max: number,
    current: number,
    prev?: number,
    next?: number,
    total?: number
  }
}

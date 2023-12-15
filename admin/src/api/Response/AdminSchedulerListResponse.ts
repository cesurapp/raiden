/* eslint-disable max-len */

import { SchedulerResource } from '../Resource/SchedulerResource';

export interface AdminSchedulerListResponse {
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

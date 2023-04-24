/* eslint-disable max-len */

import { SchedulerResource } from '../Resource/SchedulerResource';

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

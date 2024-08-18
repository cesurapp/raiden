/* eslint-disable max-len */

import type { DeviceResource } from '@api/admin/resource/DeviceResource';

export interface DeviceListResponse {
  data: [
    DeviceResource
  ],
  pager: {
    max: number,
    current: number,
    prev?: number,
    next?: number,
    total?: number
  }
}

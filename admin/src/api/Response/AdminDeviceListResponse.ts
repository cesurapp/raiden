/* eslint-disable max-len */

import { DeviceResource } from '../Resource/DeviceResource';

export interface AdminDeviceListResponse {
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

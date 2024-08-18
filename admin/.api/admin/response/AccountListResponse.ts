/* eslint-disable max-len */

import type { UserResource } from '@api/admin/resource/UserResource';

export interface AccountListResponse {
  data: [
    UserResource
  ],
  pager: {
    max: number,
    current: number,
    prev?: number,
    next?: number,
    total?: number
  }
}

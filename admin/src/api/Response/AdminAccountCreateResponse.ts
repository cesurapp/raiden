/* eslint-disable max-len */

import type { UserResource } from '../Resource/UserResource';

export interface AdminAccountCreateResponse {
  data: UserResource,
  message?: {
    success?: Array<string|number|boolean>
  }
}

/* eslint-disable max-len */

import type { UserResource } from '../Resource/UserResource';

export interface AdminAccountEditResponse {
  data: UserResource,
  message?: {
    success?: Array<string|number|boolean>
  }
}

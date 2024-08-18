/* eslint-disable max-len */

import type { UserResource } from '@api/admin/resource/UserResource';

export interface AccountEditProfileResponse {
  data: UserResource,
  message?: {
    success?: Array<string|number|boolean>
  }
}

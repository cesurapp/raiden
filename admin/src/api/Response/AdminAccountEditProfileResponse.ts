/* eslint-disable max-len */

import type { UserResource } from '../Resource/UserResource';

export interface AdminAccountEditProfileResponse {
  data: UserResource,
  message?: {
    success?: Array<string|number|boolean>
  }
}

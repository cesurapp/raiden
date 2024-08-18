/* eslint-disable max-len */

import type { UserResource } from '@api/admin/resource/UserResource';

export interface SecurityLoginResponse {
  data: UserResource,
  token: string,
  refresh_token: string
}

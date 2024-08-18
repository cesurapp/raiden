/* eslint-disable max-len */

import type { UserResource } from '@api/admin/resource/UserResource';

export interface SecurityLoginOtpResponse {
  data: UserResource,
  token: string,
  refresh_token: string
}

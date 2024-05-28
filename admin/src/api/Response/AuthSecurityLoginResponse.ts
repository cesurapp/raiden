/* eslint-disable max-len */

import type { UserResource } from '../Resource/UserResource';

export interface AuthSecurityLoginResponse {
  data: UserResource,
  token: string,
  refresh_token: string
}

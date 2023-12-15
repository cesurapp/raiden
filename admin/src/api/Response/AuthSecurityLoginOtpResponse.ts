/* eslint-disable max-len */

import { UserResource } from '../Resource/UserResource';

export interface AuthSecurityLoginOtpResponse {
  data: UserResource,
  token: string,
  refresh_token: string
}

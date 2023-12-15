/* eslint-disable max-len */

import { UserResource } from '../Resource/UserResource';

export interface AdminAccountEditResponse {
  data: UserResource,
  message?: {
    success?: Array<string|number|boolean>
  }
}

/* eslint-disable max-len */

import { UserResource } from '../Resource/UserResource';

export interface AccountCreateResponse {
  data: UserResource,
  message?: {
    success?: Array<string|number|boolean>
  }
}

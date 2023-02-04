/* eslint-disable max-len */

import { UserResource } from '../Resource/UserResource';

export interface SecurityRegisterResponse {
  data: UserResource,
  message?: {
    success?: Array<string|number|boolean>
  }
}

/* eslint-disable prettier/prettier */
/* eslint-disable max-len */

import type { AxiosResponse } from 'axios';

interface SecurityLoginResponse200 {
  user: {
    id: string,
    type: string,
    email: string,
    email_approved: boolean,
    phone: number,
    phone_country: string,
    phone_approved: boolean,
    approved: boolean,
    roles: Array<string|number|boolean>,
    language: string,
    first_name: string,
    last_name: string,
    meta: Array<string|number|boolean>
  },
  token: string,
  refresh_token: string
}
export type SecurityLoginResponse = AxiosResponse<SecurityLoginResponse200>;

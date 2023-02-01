/* eslint-disable prettier/prettier */
/* eslint-disable max-len */

export interface AccountShowResponse {
  data: {
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
    created_at: string,
    meta: Array<string|number|boolean>
  }
}

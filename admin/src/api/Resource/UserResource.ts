/* eslint-disable max-len */

export type UserResource = {
  id: string,
  first_name: string,
  last_name: string,
  type: string,
  email: string,
  email_approved: boolean,
  phone: number,
  phone_country: string,
  phone_approved: boolean,
  approved: boolean,
  frozen: boolean,
  roles: Array<string|number|boolean>,
  language: string,
  created_at: string,
  meta: Array<string|number|boolean>
}
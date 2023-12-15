/* eslint-disable max-len */

export type AdminAccountEditRequest = {
  email_approved: boolean,
  phone_approved: boolean,
  frozen: boolean,
  first_name: string,
  last_name: string,
  email?: string,
  phone?: string | number | null,
  phone_country?: string,
  type?: string,
  password?: string,
  language?: string
}

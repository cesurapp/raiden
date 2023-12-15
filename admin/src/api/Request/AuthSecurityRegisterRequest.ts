/* eslint-disable max-len */

export type AuthSecurityRegisterRequest = {
  password: string,
  first_name: string,
  last_name: string,
  email?: string,
  phone_country?: string,
  phone?: string | number | null,
  type?: string
}

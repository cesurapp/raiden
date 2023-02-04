/* eslint-disable max-len */

export type AccountEditProfileRequest = {
  first_name: string,
  last_name: string,
  email?: string,
  phone?: string|number|null,
  phone_country?: string,
  password?: string,
  current_password?: string,
  language?: string,
  id?: string
}

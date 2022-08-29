/* eslint-disable prettier/prettier */
/* eslint-disable max-len */

export type AccountEditProfileRequest = {
  emailApproved: boolean,
  phoneApproved: boolean,
  frozen: boolean,
  firstName: string,
  lastName: string,
  email?: string,
  phone?: number,
  phoneCountry?: string,
  type?: string,
  password?: string,
  language?: string,
  id?: string
}

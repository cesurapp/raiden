/* eslint-disable prettier/prettier */
/* eslint-disable max-len */

export type SecurityRegisterRequest = {
  password: string,
  firstName: string,
  lastName: string,
  email?: string,
  phoneCountry?: string,
  phone?: number,
  type?: string
}

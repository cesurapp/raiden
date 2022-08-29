/* eslint-disable prettier/prettier */
/* eslint-disable max-len */

export type SecurityResetPasswordRequest = {
  username: string|number,
  otp_key: number,
  password: string,
  password_confirm: string,
  id?: string
}

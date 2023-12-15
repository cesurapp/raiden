/* eslint-disable max-len */

export type AuthSecurityResetPasswordRequest = {
  username: string | number,
  otp_key: number,
  password: string,
  password_confirm: string
}

/* eslint-disable max-len */

export type MainCredentialsApproveRequest = {
  otp_key: number,
  email?: string,
  phone?: string | number | null,
  phone_country?: string
}

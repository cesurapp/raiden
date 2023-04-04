/* eslint-disable max-len */

export type NotificationResource = {
  id: string,
  status: string,
  title: string,
  message: string,
  readed: boolean,
  data: Array<string|number|boolean>,
  created_at: string
}
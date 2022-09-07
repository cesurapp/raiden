/* eslint-disable prettier/prettier */
/* eslint-disable max-len */

export interface NotificationListResponse {
  data?: [
    {
      id: string,
      type: string,
      title: string,
      message: string,
      readed: boolean,
      data: Array<string|number|boolean>,
      created_at: string
    }
  ],
  pager: {
    max: number,
    current: number,
    prev?: number,
    next?: number,
    total?: number
  }
}

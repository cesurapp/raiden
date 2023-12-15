/* eslint-disable max-len */

export type AdminSchedulerListQuery = {
  page?: number,
  sort?: 'ASC' | 'DESC',
  sort_by?: 'id' | 'status' | 'persist_notification' | 'delivered_count' | 'failed_count' | 'send_at' | 'created_at',
  export?: 'csv' | 'xls',
  export_field?: Array<'id' | 'campaign_title' | 'status' | 'persist_notification' | 'delivered_count' | 'failed_count' | 'send_at' | 'created_at'>,
  filter?: {
    id?: any,
    campaign_title?: any,
    status?: any,
    persist_notification?: any,
    delivered_count?: {
      min?: any,
      max?: any
    },
    failed_count?: {
      min?: any,
      max?: any
    },
    send_at?: {
      from?: any,
      to?: any
    },
    created_at?: {
      from?: any,
      to?: any
    }
  }
}

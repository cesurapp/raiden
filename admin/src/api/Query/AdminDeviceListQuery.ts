/* eslint-disable max-len */

export type AdminDeviceListQuery = {
  page?: number,
  sort?: 'ASC' | 'DESC',
  sort_by?: 'id' | 'type' | 'owner_type' | 'created_at',
  export?: 'csv' | 'xls',
  export_field?: Array<'id' | 'type' | 'owner_type' | 'owner' | 'created_at'>,
  filter?: {
    id?: any,
    type?: any,
    owner_type?: any,
    owner?: any,
    created_at?: {
      from?: any,
      to?: any
    }
  }
}

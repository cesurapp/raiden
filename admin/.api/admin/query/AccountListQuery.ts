/* eslint-disable max-len */

export type AccountListQuery = {
  page?: number,
  max?: number,
  sort?: 'ASC' | 'DESC',
  sort_by?: 'id' | 'type' | 'email' | 'email_approved' | 'phone' | 'phone_country' | 'phone_approved' | 'approved' | 'frozen' | 'language' | 'created_at',
  export?: 'csv' | 'xls',
  export_field?: Array<'id' | 'first_name' | 'last_name' | 'type' | 'email' | 'email_approved' | 'phone' | 'phone_country' | 'phone_approved' | 'approved' | 'frozen' | 'roles' | 'language' | 'created_at'>,
  filter?: {
    id?: any,
    first_name?: any,
    last_name?: any,
    type?: any,
    email?: any,
    email_approved?: any,
    phone?: any,
    phone_country?: any,
    phone_approved?: any,
    approved?: any,
    frozen?: any,
    language?: any,
    created_at?: {
      from?: any,
      to?: any
    }
  }
}

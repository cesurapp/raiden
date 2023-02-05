/* eslint-disable max-len */

export type AccountListQuery = {
  page?: number,
  sort?: 'ASC' | 'DESC',
  sort_by?: 'id' | 'type' | 'email' | 'email_approved' | 'phone' | 'phone_country' | 'phone_approved' | 'approved' | 'language' | 'created_at',
  export?: 'csv' | 'xls',
  export_field?: Array<'id' | 'type' | 'email' | 'email_approved' | 'phone' | 'phone_country' | 'phone_approved' | 'approved' | 'roles' | 'language' | 'first_name' | 'last_name' | 'created_at'>,
  filter?: {
    id?: any,
    type?: any,
    email?: any,
    email_approved?: any,
    phone?: any,
    phone_country?: any,
    phone_approved?: any,
    approved?: any,
    language?: any,
    first_name?: any,
    last_name?: any,
    created_at?: {
      min?: any,
      max?: any
    }
  }
}

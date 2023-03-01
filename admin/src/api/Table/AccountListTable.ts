/* eslint-disable max-len */

export default [
  { name: 'id', label: 'ID', sortable: true, sortable_default: true, sortable_desc: true, filter_input: 'input', export: true },
  { name: 'type', label: 'Type', sortable: true, export: true },
  { name: 'email', label: 'Email', sortable: true, filter_input: 'input', export: true },
  { name: 'email_approved', label: 'Email Approved', sortable: true, filter_input: 'checkbox', export: true },
  { name: 'phone', label: 'Phone', sortable: true, filter_input: 'number', export: true },
  { name: 'phone_country', label: 'Phone Country', sortable: true, filter_input: 'country', export: true },
  { name: 'phone_approved', label: 'Phone Approved', sortable: true, filter_input: 'checkbox', export: true },
  { name: 'approved', label: 'Approved', sortable: true, filter_input: 'checkbox', export: true },
  { name: 'roles', label: 'Roles', sortable: false, export: true },
  { name: 'language', label: 'Language', sortable: true, filter_input: 'language', export: true },
  { name: 'first_name', label: 'First Name', sortable: false, filter_input: 'input', export: true },
  { name: 'last_name', label: 'Last Name', sortable: false, filter_input: 'input', export: true },
  { name: 'created_at', label: 'Created', sortable: true, filter_input: 'daterange', export: true },
];
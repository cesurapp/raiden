/* eslint-disable max-len */

export default [
  { name: 'id', label: 'ID', sortable: true, sortable_default: true, sortable_desc: true, filter_input: 'input', export: true },
  { name: 'type', label: 'Type', sortable: true, export: true },
  { name: 'owner_type', label: 'Owner Type', sortable: true, export: true },
  { name: 'owner', label: 'User', sortable: false, filter_input: 'input', export: true },
  { name: 'created_at', label: 'Created', sortable: true, filter_input: 'daterange', export: true },
];
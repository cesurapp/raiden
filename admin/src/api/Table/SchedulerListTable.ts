/* eslint-disable max-len */

export default [
  { name: 'id', label: 'ID', sortable: true, sortable_default: true, sortable_desc: true, filter_input: 'input', export: true },
  { name: 'campaign_title', label: 'Campaign Title', sortable: false, export: true },
  { name: 'status', label: 'Status', sortable: true, export: true },
  { name: 'persist_notification', label: 'Persistent Notification', sortable: true, filter_input: 'checkbox', export: true },
  { name: 'delivered_count', label: 'Delivered Count', sortable: true, filter_input: 'range', export: true },
  { name: 'failed_count', label: 'Failed Count', sortable: true, filter_input: 'range', export: true },
  { name: 'send_at', label: 'Sending Date', sortable: true, filter_input: 'daterange', export: true },
  { name: 'created_at', label: 'Created', sortable: true, filter_input: 'daterange', export: true },
];
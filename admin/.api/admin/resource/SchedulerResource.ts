/* eslint-disable max-len */

import type { NotificationResource } from '@api/admin/resource/NotificationResource';

export type SchedulerResource = {
  notification: NotificationResource,
  id: string,
  campaign_title: string,
  status: string,
  persist_notification: boolean,
  delivered_count: number,
  failed_count: number,
  send_at: string,
  created_at: string,
  device_filter?: object
}
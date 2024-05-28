/* eslint-disable max-len */

import type { NotificationStatus } from './../Enum/NotificationStatus';

export type AdminSchedulerCreateRequest = {
  campaign_title: string,
  persist_notification: boolean,
  send_at: string,
  refresh_campaign: boolean,
  status: NotificationStatus,
  device_filter: {
    'device.type': [
      'web',
      'android',
      'ios'
    ],
    'user.createdAt'?: {
      from?: string,
      to?: string
    },
    'user.type'?: Array<string|number|boolean>,
    'user.frozen'?: boolean,
    'user.language'?: string,
    'user.phoneCountry'?: string
  },
  title?: string,
  message?: string,
  data?: {
    web?: {
      icon?: string,
      sound?: string,
      color?: string,
      click_action?: string,
      route_action?: string,
      download_action?: string
    },
    ios?: {
      icon?: string,
      sound?: string,
      color?: string,
      click_action?: string,
      route_action?: string,
      download_action?: string
    },
    android?: {
      icon?: string,
      sound?: string,
      color?: string,
      click_action?: string,
      route_action?: string,
      download_action?: string
    }
  }
}

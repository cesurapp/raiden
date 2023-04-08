/* eslint-disable max-len */

import type { NotificationStatus } from './../Enum/NotificationStatus';

export type DeviceSendRequest = {
  status: NotificationStatus,
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
  },
  id?: string
}

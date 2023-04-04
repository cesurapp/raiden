/* eslint-disable max-len */

import type { NotificationStatus } from './../Enum/NotificationStatus';

export type DeviceSendRequest = {
  status: NotificationStatus,
  title?: string,
  message?: string,
  data?: Array<string|number|boolean>,
  id?: string
}

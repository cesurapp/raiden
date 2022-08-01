/* eslint-disable prettier/prettier */
/* eslint-disable max-len */

import type { AxiosResponse } from 'axios';

interface DeviceRegisterResponse200 {
  message?: {
    success?: Array<string|number|boolean>
  }
}
export type DeviceRegisterResponse = AxiosResponse<DeviceRegisterResponse200>;

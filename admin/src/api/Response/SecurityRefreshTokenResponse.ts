/* eslint-disable prettier/prettier */
/* eslint-disable max-len */

import type { AxiosResponse } from 'axios';

interface SecurityRefreshTokenResponse200 {
  token: string
}
export type SecurityRefreshTokenResponse = AxiosResponse<SecurityRefreshTokenResponse200>;

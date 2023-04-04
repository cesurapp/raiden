/* eslint-disable max-len */

export namespace Permission {
  export enum AdminCore {
    ALLOWED_TO_SWITCH = 'ROLE_ALLOWED_TO_SWITCH',
  }
  export enum AdminAccount {
    LIST = 'ROLE_ACCOUNT_LIST',
    CREATE = 'ROLE_ACCOUNT_CREATE',
    EDIT = 'ROLE_ACCOUNT_EDIT',
    DELETE = 'ROLE_ACCOUNT_DELETE',
    PERMISSION = 'ROLE_ACCOUNT_PERMISSION',
  }
  export enum AdminDevice {
    LIST = 'ROLE_DEVICE_LIST',
    DELETE = 'ROLE_DEVICE_DELETE',
    SEND = 'ROLE_DEVICE_SEND',
  }
}
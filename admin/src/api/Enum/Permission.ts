/* eslint-disable max-len */

export namespace Permission {
  export enum AdminCore {
    SWITCH = 'ROLE_ALLOWED_TO_SWITCH',
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
  }
  export enum AdminScheduler {
    LIST = 'ROLE_SCHEDULER_LIST',
    CREATE = 'ROLE_SCHEDULER_CREATE',
    EDIT = 'ROLE_SCHEDULER_EDIT',
    DELETE = 'ROLE_SCHEDULER_DELETE',
  }
}
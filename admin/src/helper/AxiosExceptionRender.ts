import {notifyDanger} from './NotifyHelper';
import {AxiosResponse} from 'axios';
import {addValidationException} from 'boot/rules';

function TokenExpiredException(response: AxiosResponse) {
  notifyDanger(response.data.message)
}

function RefreshTokenExpiredException(response: AxiosResponse) {
  notifyDanger(response.data.message)
}

function ValidationException(response: AxiosResponse) {
  if (Object.keys(response.data.errors).length > 0) {
    addValidationException(response.data.errors);
  }
}

function renderException(response: AxiosResponse) {
  notifyDanger(response.data.message)
}

export const processException = (response?: AxiosResponse) => {
  if (!response) {
    return;
  }

  switch (response.data.type) {
    case 'TokenExpiredException':
      return TokenExpiredException(response)
    case 'RefreshTokenExpiredException':
      return RefreshTokenExpiredException(response)
    case 'ValidationException':
      return ValidationException(response)
    default:
      return renderException(response)
  }
}

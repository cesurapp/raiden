import {notifyDanger} from "./NotifyHelper";
import {AxiosResponse} from "axios";
import {errors} from "boot/rules";

function TokenExpiredException(response: AxiosResponse) {
  notifyDanger(response.data.message)
}

function RefreshTokenExpiredException(response: AxiosResponse) {
  notifyDanger(response.data.message)
}

function renderException(response: AxiosResponse) {
  notifyDanger(response.data.message)
}

function ValidationException(response: AxiosResponse) {
  // Set
  Object.entries(response.data.errors).forEach(([id, exceptions]) => {
    errors[id] = exceptions;
  })
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

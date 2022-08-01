import {notifyDanger} from "./NotifyHelper";
import {AxiosResponse} from "axios";

function TokenExpiredException(response: AxiosResponse) {
  notifyDanger(response.data.message)
}

function RefreshTokenExpiredException(response: AxiosResponse) {
  notifyDanger(response.data.message)
}

function renderException(response: AxiosResponse) {
  notifyDanger(response.data.message)
}

export const processException = (response?: AxiosResponse) => {
  if (!response) {
    return;
  }

  switch (response.data.type) {
    case 'TokenExpiredException': return TokenExpiredException(response)
    case 'RefreshTokenExpiredException': return RefreshTokenExpiredException(response)
    default: return renderException(response)
  }
}

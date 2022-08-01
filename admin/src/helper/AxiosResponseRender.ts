import {notifyShow} from "./NotifyHelper";
import {AxiosResponse} from "axios";

function showMessage(response: AxiosResponse) {
  if (response.data.message) {
    Object.keys(response.data.message).forEach((type) => {
      Object.values(response.data.message[type]).forEach((message: any) => {
        notifyShow(message, undefined, type)
      })
    })
  }
}

export const processResponse = (response?: AxiosResponse) => {
  if (!response) {
    return;
  }

  // Show Notification
  showMessage(response);
}

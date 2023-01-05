/*
import {LoadingBar} from 'quasar';

LoadingBar.setDefaults({
  color: 'info',
  size: '3px',
  position: 'top'
});
*/

function barStart() {
/*  LoadingBar.setDefaults({color: 'info'})
  LoadingBar.start();*/
}

function barSuccess() {
/*  LoadingBar.stop();*/
}

function barDanger() {
/*  LoadingBar.setDefaults({color: 'negative'})
  LoadingBar.stop();*/
}

export {
  barStart, barSuccess, barDanger
}

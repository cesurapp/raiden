import {i18n} from 'boot/app';
import {DateLocale} from "quasar";

const locales: Record<string, DateLocale> = {
  'tr-TR': {
    days: ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'],
    daysShort: ['Paz', 'Pts', 'Sal', 'Çar', 'Per', 'Cum', 'Cts'],
    months: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'],
    monthsShort: ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara'],
  },
};

const getCurrentLocale = (): DateLocale | undefined => {
  // @ts-ignore
  return locales.hasOwnProperty(i18n.global.locale['value']) ? locales[i18n.global.locale['value']] : undefined;
};

export {getCurrentLocale};

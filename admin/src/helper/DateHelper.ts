import { DateLocale } from 'quasar/dist/types/utils/date';
import { i18n } from 'boot/app';
import dayjs from 'dayjs'
import utc from 'dayjs/plugin/utc'
import customParseFormat from 'dayjs/plugin/customParseFormat'

dayjs.extend(utc);
dayjs.extend(customParseFormat);

const locales: Record<string, DateLocale> = {
  'tr-TR': {
    days: ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'],
    daysShort: ['Paz', 'Pts', 'Sal', 'Çar', 'Per', 'Cum', 'Cts'],
    months: [
      'Ocak',
      'Şubat',
      'Mart',
      'Nisan',
      'Mayıs',
      'Haziran',
      'Temmuz',
      'Ağustos',
      'Eylül',
      'Ekim',
      'Kasım',
      'Aralık',
    ],
    monthsShort: ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara'],
  },
};

const dateFormat = (date: string, format = 'DD/MM/YYYY HH:mm') => {
  return dayjs(date).format(format);
};

const dateInput = (date: string, format = 'DD/MM/YYYY HH:mm') => {
  return dayjs(date, format).utc().format(format)
}

const getCurrentLocale = () => {
  return locales.hasOwnProperty(i18n.global.locale['value']) ? locales[i18n.global.locale['value']] : null;
};

export { dateFormat, dateInput, getCurrentLocale };

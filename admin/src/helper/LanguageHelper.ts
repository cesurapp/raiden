import { i18n } from 'boot/app';

const Languages = {};

i18n.global.availableLocales.forEach((locale) => {
  Languages[locale.split('-')[0].toLowerCase()] = i18n.global.t(locale)
});

export default Languages;

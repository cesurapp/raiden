import {i18n} from 'boot/app';

const Languages: Record<string, string> = {};

// @ts-ignore
i18n.global.availableLocales.forEach((locale) => {
  const lang = locale.split('-')[0] ?? 'en';
  // @ts-ignore
  Languages[lang.toLowerCase()] = i18n.global.t(locale);
});

export default Languages;

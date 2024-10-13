/**
 * Countries
 */
const getActiveCountryList: string[] = ['TR'];

interface Country {
  name: string;
  iso2: string;
  phone_code: string;
  emoji: string;
}

interface CountryOptions {
  value: string;
  label: string;
  icon: string;
}

import countries from './data/countries.json';

const getCountries = (): Country[] => countries;
const findCountry = (iso2: string): Country | undefined => getCountries().find((c) => c.iso2 === iso2);
const getCountryFlag = (iso2: string): string | undefined => getCountries().find((c) => c.iso2 === iso2)?.emoji;
const getCountryOptions = (): CountryOptions[] =>
  getCountries().map((c) => ({
    value: c.iso2,
    label: c.name,
    icon: c.emoji,
  }));

/**
 * States
 */
interface State {
  name: string;
  code: string | number;
}

interface StateOptions {
  value: string | number;
  label: string;
}

const getStates = async (countryCode: string): Promise<State[]> =>
  await import(`./data/state/${countryCode}.json`).then((r) => r.default);
const findState = async (cc: string, stateCode: string): Promise<State | undefined> =>
  (await getStates(cc)).find((c) => c.code === stateCode);
const getStateOptions = async (countryCode: string): Promise<StateOptions[]> =>
  (await getStates(countryCode)).map((c) => ({
    value: c.code,
    label: c.name,
  }));

/**
 * City
 */
interface City {
  [key: string]: string[];
}

interface CityOptions {
  value: string | number;
  label: string;
}

const getCitys = async (cc: string): Promise<City[]> => await import(`./data/city/${cc}.json`).then((r) => r.default);
const getCityOptions = async (cc: string, code: string | number): Promise<CityOptions[]> => {
  return (await getCitys(cc))[code]?.map((c) => ({
    value: c,
    label: c,
  }));
};

/**
 * Currencies
 */
interface Currency {
  currency: string;
  currency_name: string;
  currency_symbol: string;
}

interface CurrencyOptions {
  value: string;
  label: string;
  symbol: string;
}

import currencies from './data/currencies.json';

const getCurrencies = (): Currency[] => currencies;
const getCurrencyOptions = (): CurrencyOptions[] =>
  getCurrencies().map((c) => ({
    value: c.currency,
    label: c.currency_name,
    symbol: c.currency_symbol,
  }));

export {
  getCountries,
  getCountryOptions,
  getActiveCountryList,
  getCountryFlag,
  findCountry,
  getStates,
  getStateOptions,
  findState,
  getCitys,
  getCityOptions,
  getCurrencies,
  getCurrencyOptions,
};

import * as methods from '@vuelidate/validators';
import { isValidPhone } from 'components/Phone/PhoneCodeList';

/**
 * Global Form Validation Rules
 */
export default (app, t, globalExceptions) => {
  app.config.globalProperties.$rules = {
    is(value, message) {
      message = message !== undefined ? message : false;
      return (val) => {
        let result;
        switch (typeof value) {
          case 'string':
            result = String(val) === value;
            break;
          case 'number':
            result = Number(val) === value;
            break;
          default:
            result = val === value;
        }
        return result || String(message);
      };
    },
    required(message = false) {
      return (val) => {
        return methods.required.$validator(val) || message || t(methods.required.$message);
      };
    },
    requiredIf(ref, message = false) {
      return (val) => {
        const r = methods.requiredIf(ref);
        return r.$validator(val) || message || t(r.$message);
      };
    },
    requiredUnless(ref, message = false) {
      return (val) => {
        const r = methods.requiredUnless(ref);
        return r.$validator(val) || message || t(r.$message);
      };
    },
    minLength(length, message = false) {
      return (val) => {
        const r = methods.minLength(length);
        return r.$validator(val) || message || t(r.$message({ $params: { min: 0 } })).replace('0', length);
      };
    },
    maxLength(length, message = false) {
      return (val) => {
        const r = methods.maxLength(length);
        return r.$validator(val) || message || t(r.$message({ $params: { max: 0 } })).replace('0', length);
      };
    },
    minValue(value, message = false) {
      return (val) => {
        const r = methods.minValue(value);
        return r.$validator(val) || message || t(r.$message({ $params: { min: 0 } })).replace('0', value);
      };
    },
    maxValue(value, message = false) {
      return (val) => {
        const r = methods.maxValue(value);
        return r.$validator(val) || message || t(r.$message({ $params: { max: 0 } })).replace('0', value);
      };
    },
    between(min, max, message = false) {
      return (val) => {
        const r = methods.between(min, max);
        return (
          r.$validator(val) ||
          message ||
          t(
            r.$message({
              $params: {
                min: 0,
                max: 1,
              },
            })
          )
            .replace('0', min)
            .replace('1', max)
        );
      };
    },
    alpha(message = false) {
      return (val) => {
        return methods.alpha.$validator(val) || message || t(methods.alpha.$message);
      };
    },
    alphaNum(message = false) {
      return (val) => {
        return methods.alphaNum.$validator(val) || message || t(methods.alphaNum.$message);
      };
    },
    numeric(message = false) {
      return (val) => {
        return methods.numeric.$validator(val) || message || t(methods.numeric.$message);
      };
    },
    integer(message = false) {
      return (val) => {
        return methods.integer.$validator(val) || message || t(methods.integer.$message);
      };
    },
    decimal(message = false) {
      return (val) => {
        return methods.decimal.$validator(val) || message || t(methods.decimal.$message);
      };
    },
    email(message = false) {
      return (val) => {
        return methods.email.$validator(val) || message || t(methods.email.$message);
      };
    },
    ipAddress(message = false) {
      return (val) => {
        return methods.ipAddress.$validator(val) || message || t(methods.ipAddress.$message);
      };
    },
    macAddress(separator = ':', message = false) {
      return (val) => {
        const r = methods.macAddress(separator);
        return r.$validator(val) || message || t(r.$message);
      };
    },
    url(message = false) {
      return (val) => {
        return methods.url.$validator(val) || message || t(methods.url.$message);
      };
    },
    or(...args) {
      let message = false;
      if (typeof args[args.length - 1] === 'string') {
        message = args.pop();
      }
      return (val) => {
        const r = methods.or(...args);
        return r.$validator(val) || message || t(r.$message);
      };
    },
    and(...args) {
      let message = false;
      if (typeof args[args.length - 1] === 'string') {
        message = args.pop();
      }
      return (val) => {
        const r = methods.and(...args);
        return r.$validator(val) || message || t(r.$message);
      };
    },
    not(rule, message = false) {
      return (val) => {
        const r = methods.not(rule);
        return r.$validator(val) || message || t(r.$message);
      };
    },
    sameAs(locator, message = false) {
      return (val) => {
        return val === locator || message || t('Passwords do not match.');
      };
    },
    isIdentity(message = false) {
      return (val) => {
        return (
          (!isNaN(val) && val ? isValidPhone(val.replace('/s/g', '')) : methods.email.$validator(val)) ||
          message ||
          t('Value is not a valid email or phone')
        );
      };
    },
    isPhone(phoneCountry, message = false) {
      return (val) => {
        if (val === '') {
          return true;
        }
        return isValidPhone(val.replace('/s/g', ''), phoneCountry) || message || t('Value is not a valid phone number');
      };
    },

    /**
     * Only Q-Input ":error" directive
     */
    ssrValid(id) {
      return globalExceptions.value.hasOwnProperty(id);
    },
    /**
     * Only Q-Input ":error-message" directive
     */
    ssrException(id, merge = true) {
      const e = globalExceptions.value[id] ?? [];
      return merge ? e.join('\r\n') : e;
    },
    /**
     * Only Call
     */
    clearSSRException() {
      globalExceptions.value = {};
    },
  };
};

import * as methods from '@vuelidate/validators'
import {isValidPhone} from 'components/PhoneValidation/PhoneCodeList';
import {ref} from 'vue';
import {i18n} from 'boot/i18n';

const t = i18n.global.t;

/**
 * Server Side Exception Handling
 */
const exceptions = ref({});

function addValidationException(e) {
  exceptions.value = e;
}

function checkValidationException(id) {
  return exceptions.value.hasOwnProperty(id);
}

function clearValidationException() {
  exceptions.value = {};
}

function getValidationException(id) {
  return exceptions.value[id] ?? [];
}

export {addValidationException, checkValidationException, clearValidationException, getValidationException};

/**
 * Global Form Validation Rules
 */
export default ({app}) => {
  app.config.globalProperties.$rules = {
    is(value, message) {
      message = message !== undefined ? message : false
      return (val) => {
        let result
        switch (typeof value) {
          case 'string':
            result = String(val) === value
            break
          case 'number':
            result = Number(val) === value
            break
          default:
            result = val === value
        }
        return result || String(message)
      }
    },
    required(message = false) {
      return (val) => {
        return methods.required.$validator(val) || message || t(methods.required.$message)
      }
    },
    requiredIf(ref, message = false) {
      return (val) => {
        const r = methods.requiredIf(ref);
        return r.$validator(val) || message || t(r.$message)
      }
    },
    requiredUnless(ref, message = false) {
      return (val) => {
        const r = methods.requiredUnless(ref);
        return r.$validator(val) || message || t(r.$message)
      }
    },
    minLength(length, message = false) {
      return (val) => {
        const r = methods.minLength(length);
        return r.$validator(val) || message || t(r.$message({$params: {min: 0}})).replace('0', length)
      }
    },
    maxLength(length, message = false) {
      return (val) => {
        const r = methods.maxLength(length);
        return r.$validator(val) || message || t(r.$message({$params: {max: 0}})).replace('0', length)
      }
    },
    minValue(value, message = false) {
      return (val) => {
        const r = methods.minValue(value);
        return r.$validator(val) || message || t(r.$message({$params: {min: 0}})).replace('0', value)
      }
    },
    maxValue(value, message = false) {
      return (val) => {
        const r = methods.maxValue(value);
        return r.$validator(val) || message || t(r.$message({$params: {max: 0}})).replace('0', value)
      }
    },
    between(min, max, message = false) {
      return (val) => {
        const r = methods.between(min, max);
        return r.$validator(val) || message || t(r.$message({$params: {min: 0, max: 1}})).replace('0', min).replace('1', max)
      }
    },
    alpha(message = false) {
      return (val) => {
        return methods.alpha.$validator(val) || message || t(methods.alpha.$message)
      }
    },
    alphaNum(message = false) {
      return (val) => {
        return methods.alphaNum.$validator(val) || message || t(methods.alphaNum.$message)
      }
    },
    numeric(message = false) {
      return (val) => {
        return methods.numeric.$validator(val) || message || t(methods.numeric.$message)
      }
    },
    integer(message = false) {
      return (val) => {
        return methods.integer.$validator(val) || message || t(methods.integer.$message)
      }
    },
    decimal(message = false) {
      return (val) => {
        return methods.decimal.$validator(val) || message || t(methods.decimal.$message)
      }
    },
    email(message = false) {
      return (val) => {
        return methods.email.$validator(val) || message || t(methods.email.$message)
      }
    },
    ipAddress(message = false) {
      return (val) => {
        return methods.ipAddress.$validator(val) || message || t(methods.ipAddress.$message)
      }
    },
    macAddress(separator = ':', message = false) {
      return (val) => {
        const r = methods.macAddress(separator);
        return r.$validator(val) || message || t(r.$message)
      }
    },
    url(message = false) {
      return (val) => {
        return methods.url.$validator(val) || message || t(methods.url.$message);
      }
    },
    or(...args) {
      let message = false
      if (typeof args[args.length - 1] === 'string') {
        message = args.pop()
      }
      return (val) => {
        const r = methods.or(...args);
        return r.$validator(val) || message || t(r.$message)
      }
    },
    and(...args) {
      let message = false
      if (typeof args[args.length - 1] === 'string') {
        message = args.pop()
      }
      return (val) => {
        const r = methods.and(...args);
        return r.$validator(val) || message || t(r.$message)
      }
    },
    not(rule, message = false) {
      return (val) => {
        const r = methods.not(rule);
        return r.$validator(val) || message || t(r.$message)
      }
    },
    sameAs(locator, message = false) {
      return (val) => {
        return val === locator || t(message);
      }
    },
    isIdentity(message = false) {
      return (val) => {
        return (!isNaN(val) && val ? isValidPhone(val.replace('/\s/g', '')) : methods.email.$validator(val)) || message || t('Value is not a valid email or phone');
      }
    },
    isPhone(countryCode, message = false) {
      return (val) => {
        return isValidPhone(val.replace('/\s/g', ''), countryCode) || message || t('Value is not a valid phone number');
      }
    },

    /**
     * Only Q-Input ":error" directive
     */
    ssrValid(id) {
      return checkValidationException(id);
    },
    /**
     * Only Q-Input ":error-message" directive
     */
    ssrException(id, merge = true) {
      return merge ? getValidationException(id).join('\r\n') : getValidationException(id);
    },
    /**
     * Only Call
     */
    clearSSRException() {
      clearValidationException();
    }
  }
}

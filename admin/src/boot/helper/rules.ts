import * as methods from '@vuelidate/validators';

class Rules {
  constructor(
    public app,
    public appStore,
    public t,
  ) {}

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
  }
  required(message = false): any {
    return (val) => {
      return methods.required.$validator(val, null, null) || message || this.t(methods.required.$message);
    };
  }
  requiredIf(ref, message = false): any {
    return (val) => {
      const r = methods.requiredIf(ref);
      return r.$validator(val, null, null) || message || this.t(r.$message);
    };
  }
  requiredUnless(ref, message = false): any {
    return (val) => {
      const r = methods.requiredUnless(ref);
      return r.$validator(val, null, null) || message || this.t(r.$message);
    };
  }
  minLength(length, message = false): any {
    return (val) => {
      const r = methods.minLength(length);
      return (
        r.$validator(val, null, null) || message || this.t(r.$message({ $params: { min: 0 } })).replace('0', length)
      );
    };
  }
  maxLength(length, message = false): any {
    return (val) => {
      const r = methods.maxLength(length);
      return (
        r.$validator(val, null, null) || message || this.t(r.$message({ $params: { max: 0 } })).replace('0', length)
      );
    };
  }
  minValue(value, message = false): any {
    return (val) => {
      const r = methods.minValue(value);
      return (
        r.$validator(val, null, null) || message || this.t(r.$message({ $params: { min: 0 } })).replace('0', value)
      );
    };
  }
  maxValue(value, message = false): any {
    return (val) => {
      const r = methods.maxValue(value);
      return (
        r.$validator(val, null, null) || message || this.t(r.$message({ $params: { max: 0 } })).replace('0', value)
      );
    };
  }
  between(min, max, message = false): any {
    return (val) => {
      const r = methods.between(min, max);
      return (
        r.$validator(val, null, null) ||
        message ||
        this.t(
          r.$message({
            $params: {
              min: 0,
              max: 1,
            },
          }),
        )
          .replace('0', min)
          .replace('1', max)
      );
    };
  }
  alpha(message = false): any {
    return (val) => {
      return methods.alpha.$validator(val, null, null) || message || this.t(methods.alpha.$message);
    };
  }
  alphaNum(message = false): any {
    return (val) => {
      return methods.alphaNum.$validator(val, null, null) || message || this.t(methods.alphaNum.$message);
    };
  }
  numeric(message = false): any {
    return (val) => {
      return methods.numeric.$validator(val, null, null) || message || this.t(methods.numeric.$message);
    };
  }
  integer(message = false): any {
    return (val) => {
      return methods.integer.$validator(val, null, null) || message || this.t(methods.integer.$message);
    };
  }
  decimal(message = false): any {
    return (val) => {
      return methods.decimal.$validator(val, null, null) || message || this.t(methods.decimal.$message);
    };
  }
  email(message = false): any {
    return (val) => {
      return methods.email.$validator(val, null, null) || message || this.t(methods.email.$message);
    };
  }
  ipAddress(message = false): any {
    return (val) => {
      return methods.ipAddress.$validator(val, null, null) || message || this.t(methods.ipAddress.$message);
    };
  }
  macAddress(separator = ':', message = false): any {
    return (val) => {
      const r = methods.macAddress(separator);
      return r.$validator(val, null, null) || message || this.t(r.$message);
    };
  }
  url(message = false): any {
    return (val) => {
      return methods.url.$validator(val, null, null) || message || this.t(methods.url.$message);
    };
  }
  or(...args): any {
    let message = false;
    if (typeof args[args.length - 1] === 'string') {
      message = args.pop();
    }
    return (val) => {
      const r = methods.or(...args);
      return r.$validator(val, null, null) || message || this.t(r.$message);
    };
  }
  and(...args): any {
    let message = false;
    if (typeof args[args.length - 1] === 'string') {
      message = args.pop();
    }
    return (val) => {
      const r = methods.and(...args);
      return r.$validator(val, null, null) || message || this.t(r.$message);
    };
  }
  not(rule, message = false): any {
    return (val) => {
      const r = methods.not(rule);
      return r.$validator(val, null, null) || message || this.t(r.$message);
    };
  }
  sameAs(locator, message = false): any {
    return (val) => {
      return val === locator || message || this.t('Passwords do not match.');
    };
  }

  /**
   * Only Q-Input ":error" directive
   */
  ssrValid(id) {
    return this.appStore.apiExceptions.hasOwnProperty(id);
  }
  /**
   * Only Q-Input ":error-message" directive
   */
  ssrException(id, merge = true) {
    const e = this.appStore.apiExceptions[id] ?? [];
    return merge ? e.join('\r\n') : e;
  }
  /**
   * Only Call
   */
  clearSSRException() {
    this.appStore.apiExceptions = {};
  }
}

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $rules: Rules;
  }
}

/**
 * Global Form Validation Rules
 */
export default (app, appStore, i18n) => {
  app.config.globalProperties.$rules = new Rules(app, appStore, i18n.global.t);
};

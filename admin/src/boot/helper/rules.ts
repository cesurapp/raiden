import * as methods from '@vuelidate/validators';
import type { AppStoreType } from 'src/stores/AppStore';
import type { App } from "vue";

export class Rules {
  constructor(
    public app: App,
    public appStore: AppStoreType,
    public t: (arg0: string) => string,
  ) { }

  is(value: string | number, message: any) {
    message = message !== undefined ? message : false;
    return (val: any) => {
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
    return (val: any) => {
      return methods.required.$validator(val, null, null) || message || this.t(methods.required.$message);
    };
  }
  requiredIf(ref: any, message = false): any {
    return (val: any) => {
      const r = methods.requiredIf(ref);
      return r.$validator(val, null, null) || message || this.t(r.$message);
    };
  }
  requiredUnless(ref: any, message = false): any {
    return (val: any) => {
      const r = methods.requiredUnless(ref);
      return r.$validator(val, null, null) || message || this.t(r.$message);
    };
  }
  minLength(length: number, message = false): any {
    return (val: any) => {
      const r = methods.minLength(length);
      return r.$validator(val, null, null) || message || this.t(r.$message({ $params: { min: 0 } })).replace('0', length.toString());
    };
  }
  maxLength(length: number, message = false): any {
    return (val: any) => {
      const r = methods.maxLength(length);
      return r.$validator(val, null, null) || message || this.t(r.$message({ $params: { max: 0 } })).replace('0', length.toString());
    };
  }
  minValue(value: number, message = false): any {
    return (val: any) => {
      const r = methods.minValue(value);
      return r.$validator(val, null, null) || message || this.t(r.$message({ $params: { min: 0 } })).replace('0', value.toString());
    };
  }
  maxValue(value: number, message = false): any {
    return (val: any) => {
      const r = methods.maxValue(value);
      return r.$validator(val, null, null) || message || this.t(r.$message({ $params: { max: 0 } })).replace('0', value.toString());
    };
  }
  between(min: number, max: number, message = false): any {
    return (val: any) => {
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
          .replace('0', min.toString())
          .replace('1', max.toString())
      );
    };
  }
  alpha(message = false): any {
    return (val: any) => {
      return methods.alpha.$validator(val, null, null) || message || this.t(methods.alpha.$message);
    };
  }
  alphaNum(message = false): any {
    return (val: any) => {
      return methods.alphaNum.$validator(val, null, null) || message || this.t(methods.alphaNum.$message);
    };
  }
  numeric(message = false): any {
    return (val: any) => {
      return methods.numeric.$validator(val, null, null) || message || this.t(methods.numeric.$message);
    };
  }
  phoneNumber(message = false): any {
    return (val: any) => {
      // International phone number format: + followed by country code (1-3 digits) and phone number (4-14 digits)
      // No spaces allowed
      const phoneRegex = /^\+[1-9]\d{1,2}\d{4,14}$/;
      const hasSpaces = /\s/.test(String(val));
      const isValid = !val || (!hasSpaces && phoneRegex.test(String(val)));
      return isValid || message || this.t('Please enter a valid phone number +1XXXXXXXXXX');
    };
  }
  integer(message = false): any {
    return (val: any) => {
      return methods.integer.$validator(val, null, null) || message || this.t(methods.integer.$message);
    };
  }
  decimal(message = false): any {
    return (val: any) => {
      return methods.decimal.$validator(val, null, null) || message || this.t(methods.decimal.$message);
    };
  }
  email(message = false): any {
    return (val: any) => {
      return methods.email.$validator(val, null, null) || message || this.t(methods.email.$message);
    };
  }
  ipAddress(message = false): any {
    return (val: any) => {
      return methods.ipAddress.$validator(val, null, null) || message || this.t(methods.ipAddress.$message);
    };
  }
  macAddress(separator = ':', message = false): any {
    return (val: any) => {
      const r = methods.macAddress(separator);
      return r.$validator(val, null, null) || message || this.t(r.$message);
    };
  }
  url(message = false): any {
    return (val: any) => {
      return methods.url.$validator(val, null, null) || message || this.t(methods.url.$message);
    };
  }

  hostname(message = false): any {
    return (val: any) => {
      const ipValid = methods.ipAddress.$validator(val, null, null);
      const urlValid = methods.url.$validator(val, null, null);
      return ipValid || urlValid || message || this.t('Please enter a valid hostname or IP address');
    };
  }

  domain(allowWildcard = false, message = false): any {
    return (val: any) => {
      if (!val) return true;

      const domainStr = String(val).toLowerCase().trim();

      // Domain regex: allows letters, numbers, hyphens, and dots
      // Must start and end with alphanumeric, hyphens only in middle
      // TLD must be at least 2 characters
      const domainRegex = /^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/;

      if (allowWildcard) {
        // Wildcard regex: allows asterisk anywhere within domain parts
        // Each part can contain letters, numbers, hyphens, and asterisks
        // Must have at least one non-wildcard character per part
        const wildcardDomainRegex = /^(?:[a-z0-9*](?:[a-z0-9*-]{0,61}[a-z0-9*])?)(?:\.(?:[a-z0-9*](?:[a-z0-9*-]{0,61}[a-z0-9*])?))*\.[a-z]{2,}$/;
        const isValid = wildcardDomainRegex.test(domainStr);
        return isValid || message || this.t('Please enter a valid domain (wildcards allowed, example*.com)');
      }

      const isValid = domainRegex.test(domainStr);
      return isValid || message || this.t('Please enter a valid domain (e.g., example.com)');
    };
  }

  or(...args: any[]): any {
    let message = false;
    if (typeof args[args.length - 1] === 'string') {
      message = args.pop() ?? false;
    }
    return (val: any) => {
      // Execute each validator function with the value
      const validators = args.map(arg => {
        // If arg is a function (like the return value from $rules.ipAddress()), call it
        if (typeof arg === 'function') {
          const result = arg(val);
          // If result is true, validation passed. If it's a string, validation failed
          return result === true;
        }
        return arg;
      });

      // Use vuelidate's or with the validation results
      const r = methods.or(...validators.map(isValid => ({
        $validator: () => isValid
      })));

      return r.$validator(val, null, null) || message || this.t('Value must satisfy at least one condition');
    };
  }

  and(...args: boolean[]): any {
    let message = false;
    if (typeof args[args.length - 1] === 'string') {
      message = args.pop() ?? false;
    }
    return (val: any) => {
      const r = methods.and(...args);
      return r.$validator(val, null, null) || message || this.t(r.$message);
    };
  }
  not(rule: any, message = false): any {
    return (val: any) => {
      const r = methods.not(rule);
      return r.$validator(val, null, null) || message || this.t(r.$message);
    };
  }
  sameAs(locator: any, message = false): any {
    return (val: any) => {
      return val === locator || message || this.t('Passwords do not match.');
    };
  }

  /**
   * Only Q-Input ":error" directive
   */
  ssrValid(id: PropertyKey) {
    return Object.prototype.hasOwnProperty.call(this.appStore.apiExceptions, id);
  }
  /**
   * Only Q-Input ":error-message" directive
   */
  ssrException(id: string | number, merge = true): any {
    const e = (this.appStore.apiExceptions as Record<string, string[]>)[String(id)] ?? [];
    return merge ? e.join('\r\n') : e;
  }

  /**
   * Only Call
   */
  clearSSRException() {
    this.appStore.apiExceptions = {};
  }
}

/**
 * Global Form Validation Rules
 */
export default (app: App, appStore: AppStoreType, i18n: any) => {
  app.config.globalProperties.$rules = new Rules(app, appStore, i18n.global.t);
};

declare module 'vue' {
  interface ComponentCustomProperties {
    $rules: Rules;
  }
}

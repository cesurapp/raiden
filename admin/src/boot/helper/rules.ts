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
  or(...args: boolean[]): any {
    let message = false;
    if (typeof args[args.length - 1] === 'string') {
      message = args.pop() ?? false;
    }
    return (val: any) => {
      const r = methods.or(...args);
      return r.$validator(val, null, null) || message || this.t(r.$message);
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

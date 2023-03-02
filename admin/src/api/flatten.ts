/* eslint-disable */

const { toString, hasOwnProperty } = Object.prototype;
const OBJECT_TYPE = '[object Object]';
const ARRAY_TYPE = '[object Array]';

export function flatten(obj: any, path?: string, result?: any) {
    const type = toString.call(obj);

    if (result === undefined) {
        if (type === OBJECT_TYPE) {
            result = {};
        } else if (type === ARRAY_TYPE) {
            result = [];
        } else {
            return;
        }
    }

    for (const key in obj) {
        if (!hasOwnProperty.call(obj, key)) {
            continue;
        }

        const val = obj[key];
        if (val == null) {
            continue;
        }

        switch (toString.call(val)) {
            case ARRAY_TYPE:
            case OBJECT_TYPE:
                flatten(val, join(path, key), result);
                break;
            default:
                result[join(path, key)] = val;
                break;
        }
    }

    return result;
}

const set = (obj, path, val) => {
  const keys = path.split('.');
  const lastKey = keys.pop();
  const lastObj = keys.reduce((obj, key) => obj[key] = obj[key] || {}, obj);
  lastObj[lastKey] = val;
};

export function deFlatten(qs: string): any {
  const query = Object.fromEntries(new URLSearchParams(qs));
  let data = {};

  Object.entries(query).forEach(([key, value]) => {
    if (key.indexOf('[') === -1) {
      data[key] = value;
      return;
    }

    set(data, key.replaceAll(']', '').split('[').join('.'), value);
  });

  return data;
}

function join(path: string | void, key: string) {
    return path != null ? `${path}[${key}]` : key;
}

export function toQueryString(obj: any) {
    const qs = new URLSearchParams(flatten(obj)).toString();
    return qs ? `?${qs}` : '';
}

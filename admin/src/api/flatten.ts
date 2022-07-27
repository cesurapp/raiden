/* eslint-disable */

const { toString, hasOwnProperty } = Object.prototype;
const OBJECT_TYPE = '[object Object]';
const ARRAY_TYPE = '[object Array]';

function flatten(obj: any, path?: string, result?: any) {
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

function join(path: string | void, key: string) {
    return path != null ? `${path}[${key}]` : key;
}

export function toQueryString(obj: any) {
    const qs = new URLSearchParams(flatten(obj)).toString();
    return qs ? `?${qs}` : '';
}

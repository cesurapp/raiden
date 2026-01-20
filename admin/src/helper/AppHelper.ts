import { tt } from 'boot/app';

export function mapObject(obj: object, mapFn: any) {
  return Object.fromEntries(Object.entries(obj).map(([key, value]) => [key, mapFn(value, key, obj)]));
}

export function capitalize(str: string): string {
  return str.charAt(0).toUpperCase() + str.slice(1);
}

export function enumToOptions(obj: object, transPrefix: any = null) {
  return Object.values(obj).map((key) => ({
    label: transPrefix !== null ? tt(transPrefix, key) : capitalize(key),
    value: key,
  }));
}

export function ellipsis(text: string) {
  return text && text.length > 15 ? text.substring(0, 15) + '...' : text;
}

export function formatJSON(content: any, depth: number = 0, maxDepth: number = 3): string {
  try {
    let parsed = typeof content === 'string' ? JSON.parse(content) : content;
    if (depth < maxDepth) {
      const parseNested = (obj: any): any => {
        if (typeof obj === 'string') {
          try {
            return parseNested(JSON.parse(obj));
          } catch {
            return obj;
          }
        } else if (Array.isArray(obj)) {
          return obj.map(item => parseNested(item));
        } else if (obj !== null && typeof obj === 'object') {
          const result: any = {};
          for (const key in obj) {
            result[key] = parseNested(obj[key]);
          }
          return result;
        }
        return obj;
      };

      parsed = parseNested(parsed);
    }

    return JSON.stringify(parsed, null, 2);
  } catch (e) {
    return content;
  }
}

export function mergeWithDefaults<T extends Record<string, any>>(defaults: T, source: any): T {
  const result: any = {};
  for (const key in defaults) {
    if (key in source) {
      const defaultValue = defaults[key];
      const sourceValue = source[key];

      if (defaultValue !== null && typeof defaultValue === 'object' && !Array.isArray(defaultValue) &&
          sourceValue !== null && typeof sourceValue === 'object' && !Array.isArray(sourceValue)) {
        result[key] = mergeWithDefaults(defaultValue, sourceValue);
      } else {
        result[key] = sourceValue;
      }
    } else {
      result[key] = defaults[key];
    }
  }
  return result;
}

/* eslint-disable */

/// <reference types="vite/client" />

// Mocks all files ending in `.vue` showing them as plain Vue instances
declare module '*.vue' {
  import type { DefineComponent } from '@vue/runtime-core';
  const component: DefineComponent<{}, {}, any>;
  export default component;
}

import * as runtimeCore from '@vue/runtime-core';

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $refs: {
      [key: string]: HTMLElement | any;
    };
  }
}

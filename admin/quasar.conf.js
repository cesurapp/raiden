/* eslint-env node */

/*
 * This file runs in a Node context (it's NOT transpiled by Babel), so use only
 * the ES6 features that are supported by your Node version. https://node.green/
 */

// Configuration for your app
// https://v2.quasar.dev/quasar-cli-vite/quasar-config-js

const { configure } = require('quasar/wrappers');
const path = require('path');
require('dotenv').config();
require('dotenv').config({ path: `.env.local`, override: true });

module.exports = configure(function (ctx) {
  return {
    eslint: {
      // fix: true,
      // include = [],
      // exclude = [],
      // rawOptions = {},
      warnings: true,
      errors: true,
    },

    // https://v2.quasar.dev/quasar-cli-vite/prefetch-feature
    // preFetch: true,

    // app boot file (/src/boot)
    // --> boot files are part of "main.js"
    // https://v2.quasar.dev/quasar-cli-vite/boot-files
    boot: ['app'],

    // https://v2.quasar.dev/quasar-cli-vite/quasar-config-js#css
    css: ['app.scss'],

    // https://github.com/quasarframework/quasar/tree/dev/extras
    extras: [
      // 'ionicons-v4',
      //'mdi-v5',
      //'mdi-v7',
      //'svg-mdi-v6',
      //'fontawesome-v6',
      // 'eva-icons',
      // 'themify',
      // 'line-awesome',

      //'roboto-font', // optional, you are not bound to it
      'roboto-font-latin-ext', // this or either 'roboto-font', NEVER both!
      //'material-icons', // optional, you are not bound to it
    ],

    // Full list of options: https://v2.quasar.dev/quasar-cli-vite/quasar-config-js#build
    build: {
      target: {
        browser: ['es2022', 'firefox115', 'chrome115', 'safari14'],
        node: 'node20',
      },
      env: {
        API: process.env.API,
        FIREBASE_APIKEY: process.env.FIREBASE_APIKEY,
        FIREBASE_DOMAIN: process.env.FIREBASE_DOMAIN,
        FIREBASE_PROJECTID: process.env.FIREBASE_PROJECTID,
        FIREBASE_STORAGEBUCKET: process.env.FIREBASE_STORAGEBUCKET,
        FIREBASE_SENDERID: process.env.FIREBASE_SENDERID,
        FIREBASE_APPID: process.env.FIREBASE_APPID,
      },

      vueRouterMode: 'history', // available values: 'hash', 'history'
      // vueRouterBase,
      // vueDevtools,
      // vueOptionsAPI: false,

      // rebuildCache: true, // rebuilds Vite/linter/etc cache on startup

      // publicPath: '/',
      // analyze: true,
      //env: require('dotenv').config().parsed,
      // rawDefine: {}
      // ignorePublicFolder: true,
      // minify: false,
      // polyfillModulePreload: true,
      distDir: 'dist',

      // extendViteConf (viteConf) {},
      // viteVuePluginOptions: {},

      vitePlugins: [
        [
          '@intlify/vite-plugin-vue-i18n',
          {
            // if you want to use Vue I18n Legacy API, you need to set `compositionOnly: false`
            // compositionOnly: false,

            // you need to set i18n resource including paths !
            include: path.resolve(__dirname, './src/i18n/**'),
          },
        ],
        [
          'rollup-plugin-copy',
          {
            targets: [
              {
                // Copying firebase-messaging-sw.js to SPA/PWA/SSR dest build folder
                src: 'src/components/Notification/firebase-messaging-sw.js',
                dest: ['dist', 'public'], // example when building SPA
                transform: (contents) =>
                  contents
                    .toString()
                    .replace('<FIREBASE_APIKEY>', process.env.FIREBASE_APIKEY)
                    .replace('<FIREBASE_DOMAIN>', process.env.FIREBASE_DOMAIN)
                    .replace('<FIREBASE_PROJECTID>', process.env.FIREBASE_PROJECTID)
                    .replace('<FIREBASE_STORAGEBUCKET>', process.env.FIREBASE_STORAGEBUCKET)
                    .replace('<FIREBASE_SENDERID>', process.env.FIREBASE_SENDERID)
                    .replace('<FIREBASE_APPID>', process.env.FIREBASE_APPID),
              },
            ],
          },
        ],
      ],
    },

    // Full list of options: https://v2.quasar.dev/quasar-cli-vite/quasar-config-js#devServer
    devServer: {
      https: false,
      open: true, // opens browser window automatically
    },

    // https://quasar.dev/quasar-cli/quasar-conf-js#Property%3A-framework
    framework: {
      config: {
        notify: {
          /* look at QuasarConfOptions from the API card */
        },
        screen: {
          bodyClasses: true,
        },
        dark: 'auto',
      },
      cssAddon: true,
      iconSet: 'svg-mdi-v7', // Quasar icon set
      lang: 'en-US', // Quasar language pack

      // For special cases outside of where the auto-import strategy can have an impact
      // (like functional components as one of the examples),
      // you can manually specify Quasar components/directives to be available everywhere:
      //
      // components: [],
      // directives: [],

      // Quasar plugins
      plugins: ['Notify', 'Meta', 'Dialog', /*'LoadingBar',*/ 'LocalStorage'],
    },

    // animations: 'all', // --- includes all animations
    // https://v2.quasar.dev/options/animations
    animations: ['fadeIn', 'fadeOut'],

    // https://v2.quasar.dev/quasar-cli-vite/developing-ssr/configuring-ssr
    ssr: {
      // ssrPwaHtmlFilename: 'offline.html', // do NOT use index.html as name!
      // will mess up SSR

      // extendSSRWebserverConf (esbuildConf) {},
      // extendPackageJson (json) {},

      pwa: false,

      // manualStoreHydration: true,
      // manualPostHydrationTrigger: true,

      prodPort: 3000, // The default port that the production server should use
      // (gets superseded if process.env.PORT is specified at runtime)

      middlewares: [
        'render', // keep this as last one
      ],
    },

    // https://v2.quasar.dev/quasar-cli-vite/developing-pwa/configuring-pwa
    pwa: {
      workboxMode: 'generateSW', // or 'injectManifest'
      injectPwaMetaTags: true,
      swFilename: 'sw.js',
      manifestFilename: 'manifest.json',
      useCredentialsForManifestTag: false,
      // extendGenerateSWOptions (cfg) {}
      // extendInjectManifestOptions (cfg) {},
      // extendManifestJson (json) {}
      // extendPWACustomSWConf (esbuildConf) {}
    },

    // Full list of options: https://v2.quasar.dev/quasar-cli-vite/developing-cordova-apps/configuring-cordova
    cordova: {
      // noIosLegacyBuildFlag: true, // uncomment only if you know what you are doing
    },

    // Full list of options: https://v2.quasar.dev/quasar-cli-vite/developing-capacitor-apps/configuring-capacitor
    capacitor: {
      hideSplashscreen: false,
    },

    // Full list of options: https://v2.quasar.dev/quasar-cli-vite/developing-electron-apps/configuring-electron
    electron: {
      // extendElectronMainConf (esbuildConf)
      // extendElectronPreloadConf (esbuildConf)

      inspectPort: 5858,

      bundler: 'packager', // 'packager' or 'builder'

      packager: {
        // https://github.com/electron-userland/electron-packager/blob/master/docs/api.md#options
        // OS X / Mac App Store
        // appBundleId: '',
        // appCategoryType: '',
        // osxSign: '',
        // protocol: 'myapp://path',
        // Windows only
        // win32metadata: { ... }
      },

      builder: {
        // https://www.electron.build/configuration/configuration

        appId: 'quasar-project',
      },
    },

    // Full list of options: https://v2.quasar.dev/quasar-cli-vite/developing-browser-extensions/configuring-bex
    bex: {
      contentScripts: ['my-content-script'],

      // extendBexScriptsConf (esbuildConf) {}
      // extendBexManifestJson (json) {}
    },
  };
});

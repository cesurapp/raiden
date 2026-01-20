<template>
  <q-drawer
    :mini="$appStore.navMini && miniState"
    :mini-to-overlay="$appStore.navMini"
    :mini-width="60"
    :width="300"
    class="main-nav text-white"
    v-model="$appStore.navMenu"
    :behavior="$q.platform.is.desktop ? 'desktop' : 'mobile'"
  >
    <!--Logo-->
    <q-toolbar class="logo">
      <q-toolbar-title class="flex items-center no-wrap">
        <q-avatar size="32px" square class="q-ml-sm"><q-img src="/icons/favicon-128x128.png" /></q-avatar>
        <span class="q-ml-sm q-pl-sm text-weight-bold text" style="margin-top: 1px">Webfon</span>
      </q-toolbar-title>
      <slot></slot>
    </q-toolbar>

    <!--Menu List-->
    <q-list class="menus">
      <div class="item" v-for="(nav, index) in navFiltered" :key="index">
        <!--With Header-->
        <template v-if="nav.header && nav.hasOwnProperty('items')">
          <q-item-label v-if="nav.header" caption header class="grup-header captext">
            <span>{{ $t(nav.header) }}</span>
          </q-item-label>

          <template v-for="(subNav, index) in nav.items" :key="index">
            <!--Single-->
            <q-item
              exact
              clickable
              dense
              class="menu-link"
              active-class="active-link"
              v-if="!subNav.child"
              :to="subNav.to"
            >
              <q-item-section avatar v-if="subNav.icon"><q-icon :name="subNav.icon" /></q-item-section>
              <q-item-section class="text-weight-medium">{{ $t(subNav.text) }}</q-item-section>
              <q-tooltip class="nav-tooltip" v-if="$q.platform.is.desktop && $appStore.navMini" anchor="center right" self="center left" :offset="[10, 0]">
                {{ $t(subNav.text) }}
              </q-tooltip>
            </q-item>

            <!--Dropdown with Childed Menu-->
            <HoverMenu
              v-else-if="$q.platform.is.desktop && $appStore.navMini"
              clickable
              dense
              class="menu-link"
              :class="{'active-link': subNav.subPaths.includes($route.path)}"
              :icon="subNav.icon"
            >
              <q-list style="padding: 6px 0">
                <q-item-section class="q-px-md q-py-sm">
                  <q-item-label overline class="captext">{{ $t(subNav.text) }}</q-item-label>
                </q-item-section>
                <q-item
                  exact
                  clickable
                  dense
                  class="menu-link"
                  active-class="active-link"
                  v-for="(childNav, childIndex) in subNav.child"
                  :key="childIndex"
                  :to="childNav.to"
                >
                  <q-item-section avatar v-if="childNav.icon"><q-icon :name="childNav.icon" size="18px" /></q-item-section>
                  <q-item-section class="text-weight-medium">{{ $t(childNav.text) }}</q-item-section>
                </q-item>
              </q-list>
            </HoverMenu>

            <!--Dropdown with Childed - Expansion-->
            <q-expansion-item
              v-else
              dense
              group="navigation"
              active-class="active-item-grup"
              :icon="subNav.icon"
              :label="$t(subNav.text)"
              v-model="subNav.active"
            >
              <q-list>
                <q-item
                  exact
                  clickable
                  dense
                  class="menu-link"
                  active-class="active-link"
                  v-for="(childNav, childIndex) in subNav.child"
                  :key="childIndex"
                  :to="childNav.to"
                >
                  <q-item-section avatar v-if="childNav.icon"><q-icon :name="childNav.icon" size="18px" /></q-item-section>
                  <q-item-section class="text-weight-medium">{{ $t(childNav.text) }}</q-item-section>
                </q-item>
              </q-list>
            </q-expansion-item>
          </template>
        </template>

        <!-- Headerless-->
        <template v-else-if="!nav.header">
          <!--Single-->
          <q-item exact clickable dense class="menu-link" active-class="active-link" v-if="!nav.child" :to="nav.to">
            <q-item-section avatar v-if="nav.icon"><q-icon :name="nav.icon" /></q-item-section>
            <q-item-section class="text-weight-medium">{{ $t(nav.text) }}</q-item-section>
            <q-tooltip class="nav-tooltip" v-if="$q.platform.is.desktop && $appStore.navMini" anchor="center right" self="center left" :offset="[10, 0]">
              {{ $t(nav.text) }}
            </q-tooltip>
          </q-item>

          <!--Dropdown with Childed - Menu-->
          <HoverMenu
            v-else-if="$q.platform.is.desktop && $appStore.navMini"
            clickable
            dense
            class="menu-link"
            :class="{'active-link': nav.subPaths.includes($route.path)}"
            :icon="nav.icon"
          >
            <q-list style="padding: 6px 0">
              <q-item-section class="q-px-md q-py-sm"><q-item-label overline class="captext">{{ $t(nav.text) }}</q-item-label></q-item-section>
              <q-item
                exact
                clickable
                dense
                class="menu-link"
                active-class="active-link"
                v-for="(childNav, childIndex) in nav.child"
                :key="childIndex"
                :to="childNav.to"
              >
                <q-item-section avatar v-if="childNav.icon"><q-icon :name="childNav.icon" size="18px" /></q-item-section>
                <q-item-section class="text-weight-medium">{{ $t(childNav.text) }}</q-item-section>
              </q-item>
            </q-list>
          </HoverMenu>

          <!--Dropdown with Childed - Expansion-->
          <q-expansion-item
            v-else
            dense
            group="navigation"
            active-class="active-item-grup"
            :icon="nav.icon"
            :label="$t(nav.text)"
            v-model="nav.active"
          >
            <q-list>
              <q-item
                exact
                clickable
                dense
                class="menu-link"
                active-class="active-link"
                v-for="(childNav, childIndex) in nav.child"
                :key="childIndex"
                :to="childNav.to"
              >
                <q-item-section avatar v-if="childNav.icon"><q-icon :name="childNav.icon" /></q-item-section>
                <q-item-section class="text-weight-medium">{{ $t(childNav.text) }}</q-item-section>
              </q-item>
            </q-list>
          </q-expansion-item>
        </template>
      </div>
    </q-list>

    <!--Footer-->
    <div class="footer">
      <NavigationProfile>
        <LanguageChanger :list-item="true"></LanguageChanger>
        <DarkModeChanger :list-item="true"></DarkModeChanger>
        <q-separator spaced />
      </NavigationProfile>
    </div>
  </q-drawer>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { mdiAutoMode } from '@quasar/extras/mdi-v7';
import LanguageChanger from 'components/Language/LanguageChanger.vue';
import DarkModeChanger from 'components/DarkModeChanger.vue';
import NavigationProfile from 'components/Layout/NavigationProfile.vue';
import HoverMenu from "components/Layout/HoverMenu.vue";

export default defineComponent({
  name: 'AppNavigation',
  components: {HoverMenu, NavigationProfile, DarkModeChanger, LanguageChanger },
  setup: () => ({ mdiAutoMode }),
  props: ['navs', 'title'],
  data: () => ({
    miniState: false,
    navFiltered: [],
    activePath: '',
  }),
  watch: {
    '$appStore.navMini'(val) {
      this.miniState = val;
      this.$q.localStorage.set('navMini', val);
    },
    $route() {
      this.activePath = window.location.pathname
    }
  },
  created() {
    this.$appStore.navMenu = this.$q.platform.is.desktop;
    this.$appStore.navMini = this.$q.localStorage.getItem('navMini') ?? false;
    this.miniState = this.$appStore.navMini;
  },
  mounted() {
    this.initNavs();
  },
  methods: {
    initNavs() {
      this.navFiltered = this.navs
        .filter((nav) => {
          // With Header
          if (nav.hasOwnProperty('items')) {
            nav.items = nav.items.filter((c) => {
              if (c.hasOwnProperty('child')) {
                c.child = c.child.filter((cd) => !(cd.hasOwnProperty('permission') && !this.$authStore.hasPermission(cd.permission)));
                return c.child.length !== 0;
              }
              return !(c.hasOwnProperty('permission') && !this.$authStore.hasPermission(c.permission));
            });

            return nav.items.length !== 0;
          }

          // Headerless
          if (nav.hasOwnProperty('child')) {
            nav.child = nav.child.filter((c) => !(c.hasOwnProperty('permission') && !this.$authStore.hasPermission(c.permission)));
            return nav.child.length !== 0;
          }

          return !(nav.hasOwnProperty('permission') && !this.$authStore.hasPermission(nav.permission));
        })
        .map((nav) => {
          // With Header
          if (nav.hasOwnProperty('items')) {
            nav.items.map((n) => {
              if (n.hasOwnProperty('child') && n.child.some((child) => typeof child.to === 'string' ? child.to === this.$route.path : child.to.path === this.$route.path)) {
                n.active = true;
              }

              if (n.hasOwnProperty('child')) {
                n.subPaths = n.child.map(c => c.to ?? c.to.path);
              }

              return n;
            });
          }

          // Headerless
          if (nav.hasOwnProperty('child') && nav.child.some((child) => typeof child.to === 'string' ? child.to === this.$route.path : child.to.path === this.$route.path,)) {
            nav.active = true;
          }
          if (nav.hasOwnProperty('child')) {
            nav.subPaths = nav.child.map(c => c.to ?? c.to.path);
          }

          return nav;
        });
    },
  }
});
</script>

<style lang="scss">
.main-nav {
  background: $dark;
  overflow: hidden !important;
  display: flex;
  flex-direction: column;
  box-shadow: 0 0 5px 2px rgb(0 0 0 / 10%);
  padding-top: 0 !important;
  padding-bottom: 0 !important;

  .menus {
    flex: 1;
    overflow: auto;
    padding: 8px 8px;
  }

  .item {
    margin: 1px 0;
  }

  .logo {
    position: relative;
    padding: 8px;
    padding-top: max(env(safe-area-inset-top), 8px);
    min-height: var(--header-size);
    transition: 1s all;
  }

  .nav-actions {
    width: 36.58px;
    transition: 0.15s all;
    opacity: 1;
    overflow: hidden;
  }

  .q-item {
    padding: 6px 11px;
    margin-bottom: 1px;
  }
  .q-item.q-router-link--active,
  .q-item {
     color: white;
    border-radius: $button-border-radius;
  }

  .q-expansion-item {
    margin-bottom: 1px;
    .q-item {
      margin-bottom: 0;
    }
  }

  .q-expansion-item__content {
    &:after {
      content: " ";
      position: absolute;
      left: 22px;
      top: 0;
      width: 1px;
      background: #a1a1a1;
      bottom: 27px;
    }

    .q-list{
      margin-left: 34px;
      display: flex;
      flex-direction: column;
      gap: 1px;
    }

    .q-item {
      border-radius: $button-border-radius;
      min-height: 34px;
      z-index: 1;
      transition: 1s all;

      &:before {
        content: " ";
        position: absolute;
        top: 0;
        right: calc(100% + 2px);
        height: calc(50% + 1px);
        border-width: 0 0 1px 1px;
        border-color: #a1a1a1;
        border-style: solid;
        width: 10px;
        border-bottom-left-radius: 3px;
      }
    }

    .q-item__section--avatar{
      min-width: 28px;
      padding-right: 10px;
    }
  }

  .q-item__section--avatar {
    min-width: 34px;
  }

  .q-item__section--side > .q-icon {
    font-size: $button-font-size + 8;
  }

  .active-link {
    color: white;
    background: var(--q-primary);
  }

  .q-item__label--header {
    color: rgba(255, 255, 255, 0.55);
    padding: 11px;
    text-transform: uppercase;
    height: 36px;
  }

  .captext{
    color: rgba(255, 255, 255, 0.6);
    text-transform: uppercase;
    letter-spacing: 0.08667em;
  }

  .q-toolbar__title{
    transition: all .25s;
  }

  .footer {
    min-height: 46px;
    padding: 6px 13px;
    padding-bottom: max(env(safe-area-inset-bottom) / 2, 6px) !important;
    //border-top: 1px solid rgba(255,255,255,.05);

    .profile-btn {
      .q-btn__content {
        flex-wrap: nowrap;
        white-space: nowrap;
      }
    }
  }
}

.nav-tooltip{
    background: $dark;
}

// Mini
.q-drawer--mini .main-nav {
  .q-toolbar__title {
    padding: 0;
  }
  .logo {
    border-bottom: none;
    padding: 8px 8px 8px 8px;

    .q-toolbar__title{
      transition: width 0.3s ease, opacity 0.3s ease;
      width: 0;
      opacity: 0;
    }
  }
  .footer {
    // display: none;
    .nav-text,
    .nav-dropdown {
      display: none;
    }
    .profile-btn {
      padding-right: 0;
    }
  }

  .nav-actions {
    width: 100%;
    //opacity: 0;
    //width: 0;
  }

  .grup-header {
    display: flex !important;
    align-items: center;
    position: relative;
    padding: 0;
    width: 100%;
    &:after {
      background: linear-gradient(
        90deg,
        rgba(255, 255, 255, 0.03) 0%,
        rgba(255, 255, 255, 0.2) 50%,
        rgba(255, 255, 255, 0.03) 100%
      );
      width: 100%;
      content: ' ';
      position: absolute;
      height: 1px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    & > span {
      display: none;
    }
  }
}
</style>

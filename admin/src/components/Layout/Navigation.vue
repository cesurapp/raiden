<template>
  <q-drawer
    :mini="$appStore.navMini && miniState"
    :mini-to-overlay="$appStore.navMini"
    :no-mini-animation="miniAnimation"
    @mouseover="miniState = false"
    @mouseout="miniState = true"
    :mini-width="60"
    :width="300"
    class="main-nav text-white"
    v-model="$appStore.navMenu"
    :behavior="$q.platform.is.desktop ? 'desktop' : 'mobile'"
  >
    <!--Logo-->
    <q-toolbar class="logo">
      <q-toolbar-title class="flex no-wrap">
        <q-avatar size="32px"><q-img src="/icons/favicon-128x128.png" /></q-avatar>
      </q-toolbar-title>
      <slot></slot>
    </q-toolbar>

    <!--Menu List-->
    <q-list class="menus">
      <div class="item" v-for="(nav, index) in navFiltered" :key="index">
        <!--With Header-->
        <template v-if="nav.header && nav.hasOwnProperty('items')">
          <q-item-label v-if="nav.header" caption header class="grup-header"
            ><span>{{ $t(nav.header) }}</span></q-item-label
          >
          <template v-for="(subNav, index) in nav.items" :key="index">
            <!--Single-->
            <q-item
              exact
              clickable
              v-ripple
              dense
              class="menu-link"
              active-class="active-link"
              v-if="!subNav.child"
              :to="subNav.to"
            >
              <q-item-section avatar v-if="subNav.icon"><q-icon :name="subNav.icon" /></q-item-section>
              <q-item-section class="text-weight-medium">{{ $t(subNav.text) }}</q-item-section>
            </q-item>

            <!--Dropdown with Childed-->
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
                  v-ripple
                  dense
                  class="menu-link"
                  active-class="active-link"
                  v-for="(childNav, childIndex) in subNav.child"
                  :key="childIndex"
                  :to="childNav.to"
                >
                  <q-item-section avatar v-if="childNav.icon"><q-icon :name="childNav.icon" /></q-item-section>
                  <q-item-section class="text-weight-medium">{{ $t(childNav.text) }}</q-item-section>
                </q-item>
              </q-list>
            </q-expansion-item>
          </template>
        </template>

        <!-- Headerless-->
        <template v-else-if="!nav.header">
          <!--Single-->
          <q-item exact clickable v-ripple dense class="menu-link" active-class="active-link" v-if="!nav.child" :to="nav.to">
            <q-item-section avatar v-if="nav.icon"><q-icon :name="nav.icon" /></q-item-section>
            <q-item-section class="text-weight-medium">{{ $t(nav.text) }}</q-item-section>
          </q-item>

          <!--Dropdown with Childed-->
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
                v-ripple
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
    <div class="footer flex items-center justify-between">
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
import { mdiBackburger, mdiForwardburger, mdiAutoMode } from '@quasar/extras/mdi-v7';
import LanguageChanger from 'components/Language/LanguageChanger.vue';
import DarkModeChanger from 'components/DarkModeChanger.vue';
import NavigationProfile from 'components/Layout/NavigationProfile.vue';

export default defineComponent({
  name: 'AdminNavigation',
  components: { NavigationProfile, DarkModeChanger, LanguageChanger },
  setup: () => ({ mdiBackburger, mdiForwardburger, mdiAutoMode }),
  props: ['navs', 'title'],
  data: () => ({
    mounted: false,
    miniState: false,
    miniAnimation: true,
    navFiltered: [],
  }),
  created() {
    this.$appStore.navMenu = this.$q.platform.is.desktop;
    this.$appStore.navMini = this.$q.localStorage.getItem('navMini') ?? false;
    this.miniState = this.$appStore.navMini;
  },
  mounted() {
    this.mounted = true;
    setTimeout(() => {
      this.miniAnimation = false;
    }, 50);

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
                c.child = c.child.filter(
                  (cd) => !(cd.hasOwnProperty('permission') && !this.$authStore.hasPermission(cd.permission)),
                );
                return c.child.length !== 0;
              }
              return !(c.hasOwnProperty('permission') && !this.$authStore.hasPermission(c.permission));
            });

            return nav.items.length !== 0;
          }

          // Headerless
          if (nav.hasOwnProperty('child')) {
            nav.child = nav.child.filter(
              (c) => !(c.hasOwnProperty('permission') && !this.$authStore.hasPermission(c.permission)),
            );

            return nav.child.length !== 0;
          }

          return !(nav.hasOwnProperty('permission') && !this.$authStore.hasPermission(nav.permission));
        })
        .map((nav) => {
          // With Header
          if (nav.hasOwnProperty('items')) {
            nav.items.map((n) => {
              if (
                n.hasOwnProperty('child') &&
                n.child.some((child) =>
                  typeof child.to === 'string' ? child.to === this.$route.path : child.to.path === this.$route.path,
                )
              ) {
                n.active = true;
              }

              return n;
            });
          }

          // Headerless
          if (
            nav.hasOwnProperty('child') &&
            nav.child.some((child) =>
              typeof child.to === 'string' ? child.to === this.$route.path : child.to.path === this.$route.path,
            )
          ) {
            nav.active = true;
          }

          return nav;
        });
    },
  },
  watch: {
    '$appStore.navMini'(val) {
      this.miniState = val;
      this.$q.localStorage.set('navMini', val);
    },
  },
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
    padding: 8px 7px;
  }

  .item {
    margin: 1px 0;
  }

  .logo {
    height: var(--header-size);
    position: relative;
    padding: 8px 14px 8px 14px;
    padding-top: max(env(safe-area-inset-top), 8px);
    //box-shadow: 0 5px 5px -2px rgba(0, 0, 0, 0.1);
  }

  .q-item {
    padding-top: 8px;
    padding-bottom: 8px;
  }
  .q-item.q-router-link--active,
  .q-item {
    color: white;
    border-radius: $button-border-radius;
  }

  .q-expansion-item--expanded > div > .q-item {
    border-radius: $button-border-radius $button-border-radius 0 0;
  }

  .q-expansion-item__content {
    .q-item {
      border-radius: 0;

      &:last-of-type {
        border-radius: 0 0 $button-border-radius $button-border-radius;
      }
    }
  }

  .q-expansion-item--expanded {
    background: rgba(255, 255, 255, 0.15);
    border-radius: $button-border-radius;
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
    padding: 14px;
    text-transform: uppercase;
    height: 42px;
  }

  .footer {
    min-height: 46px;
    padding: 8px 10px;
    padding-bottom: max(env(safe-area-inset-bottom) / 2, 8px) !important;

    .profile-btn {
      .q-btn__content {
        flex-wrap: nowrap;
        white-space: nowrap;
      }
    }
  }
}

// Mini
.q-drawer--mini .main-nav {
  .q-toolbar__title {
    // display: none;
    text-overflow: unset;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .logo {
    padding: 0 14px 0 14px;
    height: var(--header-size) !important;
    border-bottom: none;
    .q-btn-group {
      display: none;
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

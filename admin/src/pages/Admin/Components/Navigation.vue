<template>
  <q-scroll-area class="fit">
    <q-list>
      <div v-for="(nav, index) in getNavs" :key="index">
        <!--Header-->
        <q-item-label v-if="nav.header" header class="text-weight-bold text-uppercase">{{ nav.header }}</q-item-label>

        <!--Single-->
        <q-item v-if="!nav.child" clickable v-ripple :to="nav.route">
          <q-item-section avatar><q-icon :name="nav.icon"/></q-item-section>
          <q-item-section>{{ nav.text }}</q-item-section>
        </q-item>

        <!--Multiple-->
        <q-expansion-item v-else expand-separator :icon="nav.icon" :label="nav.text">
          <q-list>
            <q-item v-for="(childNav, childIndex) in nav.child" :key="childIndex" clickable v-ripple :to="childNav.route">
              <q-item-section avatar><q-icon :name="childNav.icon"/></q-item-section>
              <q-item-section>{{ childNav.text }}</q-item-section>
            </q-item>
          </q-list>
        </q-expansion-item>
      </div>
    </q-list>
  </q-scroll-area>
</template>

<script lang="ts">
import {defineComponent} from "vue";

export default defineComponent({
  name: 'AdminNavigation',
  data: () => ({
    navs: [
      {icon: 'dashboard', text: 'Dasboard', route: '/', roles: []},
      {
        icon: 'home',
        text: 'Home',
        roles: ['ROLE_AAA'],
        header: 'More',
        child: [
          {icon: 'whatshot', text: 'Trending', route: '/', roles: []},
          {icon: 'whatshot', text: 'Trending', route: '/', roles: []},
        ]
      },
      {
        icon: 'home',
        text: 'Home',
        roles: [],
        header: 'More',
        child: [
          {icon: 'whatshot', text: 'Trending', route: '/', roles: []},
          {icon: 'whatshot', text: 'Trending', route: '/', roles: []},
        ]
      },
      {icon: 'subscriptions', text: 'Subscriptions', route: '/', roles: []},
      {icon: 'help', text: 'Help', route: '/', roles: []},
      {icon: 'help', text: 'Help', route: '/', roles: []},
      {icon: 'help', text: 'Help', route: '/', roles: []},
    ],
  }),
  computed: {
    getNavs() {
      return this.navs
        .filter((nav) => {
          if (nav.hasOwnProperty('roles') && !this.$auth.hasGranted(nav.roles)) {
            return false;
          }

          return true;
        })
        .map((nav) => {
          if (nav.hasOwnProperty('child') && nav.child.length === 0) {
            delete nav.child;
          }

          return nav;
        });
    }
  }
});
</script>

<template>
  <q-btn flat no-caps rounded class="q-pt-none q-pb-none q-pl-none q-pr-xs profile-btn full-width">
    <q-avatar square size="52px"><q-icon :name="mdiAccountCircle"></q-icon></q-avatar>
    <div class="nav-text q-pl-sm">
      <div class="title">{{ $authStore.user.first_name }} {{ $authStore.user.last_name }}</div>
      <div class="sub">{{ $t($authStore.user.type) }}</div>
    </div>
    <q-icon class="nav-dropdown q-ml-auto" size="sm" :name="mdiChevronRight" />
    <q-popup-proxy class="popup-dropdown" breakpoint="600">
      <q-list style="min-width: 200px">
        <slot></slot>

        <!--Profile-->
        <q-item clickable v-close-popup to="/admin/account/profile">
          <q-item-section side><q-icon :name="mdiAccount" /></q-item-section>
          <q-item-section
            ><q-item-label>{{ $t('Edit Profile') }}</q-item-label></q-item-section
          >
        </q-item>

        <!--Return Admin-->
        <q-item
          clickable
          v-close-popup
          class="text-amber-7"
          v-show="$authStore.isSwitchedUser()"
          @click="$authStore.switchUserLogout(true)"
        >
          <q-item-section side>
            <q-icon color="amber-7" :name="mdiAccountMultipleOutline" />
          </q-item-section>
          <q-item-section>
            <q-item-label>{{ $t('Return Admin') }}</q-item-label>
          </q-item-section>
        </q-item>

        <!--Logout-->
        <q-item clickable v-close-popup class="text-red-5" @click="$authStore.logout()">
          <q-item-section side>
            <q-icon color="red-5" :name="mdiLogout" />
          </q-item-section>
          <q-item-section>
            <q-item-label>{{ $t('Sign out') }}</q-item-label>
          </q-item-section>
        </q-item>

        <slot name="end"></slot>
      </q-list>
    </q-popup-proxy>
  </q-btn>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  mdiAccountCircle,
  mdiLogout,
  mdiAccountMultipleOutline,
  mdiChevronRight,
  mdiAccount,
} from '@quasar/extras/mdi-v7';

export default defineComponent({
  name: 'NavigationProfile',
  setup: () => ({ mdiAccountCircle, mdiLogout, mdiAccountMultipleOutline, mdiChevronRight, mdiAccount }),
});
</script>

<style lang="scss">
.profile-btn {
  .q-avatar {
    height: auto;
    width: auto;
  }
}

.nav-text {
  display: flex;
  flex-direction: column;
  line-height: 15px;
  align-items: flex-start;

  .title {
    line-height: 18px;
    margin-bottom: 4px;

    body.body--dark & {
      color: #fff;
    }
  }
  .sub {
    text-transform: uppercase;
    font-size: 12px;
    line-height: 12px;
    font-weight: 600;
    #color: rgba(255, 255, 255, 0.75);
  }
}
</style>

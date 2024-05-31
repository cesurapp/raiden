<template>
  <q-drawer
    class="notification-drawer"
    side="right"
    :width="320"
    :breakpoint="1440"
    v-model="drawer"
    elevated
    @before-show="onShowPanel"
    behavior="mobile"
  >
    <q-scroll-area class="fit">
      <!--System Notification Alert-->
      <q-card class="bg-primary text-white square shadow-0" square v-if="access.permission !== true && this.support">
        <q-card-section class="flex no-wrap items-center q-py-md q-px-md">
          <div class="q-mr-md">
            <div class="text-subtitle1">{{ $t('System Notification') }}</div>
            <div class="text-body2">
              {{ $t('Enable browser notifications for instant system alerts and file downloads.') }}
            </div>
          </div>
          <q-btn outline color="white" size="md" :icon="mdiCheckAll" rounded dense @click="accessNotification(true)">
            <q-tooltip>{{ $t('Activate') }}</q-tooltip>
          </q-btn>
        </q-card-section>
      </q-card>

      <q-list v-ripple="false">
        <!--Header-->
        <q-item class="panel-head q-mb-sm">
          <q-item-section avatar><q-icon color="white" :name="mdiBell" /></q-item-section>
          <q-item-section
            ><q-item-label>{{ $t('Notifications') }}</q-item-label></q-item-section
          >
          <q-item-section side top class="flex justify-center items-center">
            <q-btn color="white" size="sm" flat round :icon="mdiCheckAll" @click="readAll" v-close-popup>
              <q-tooltip>{{ $t('Mark all as read') }}</q-tooltip>
            </q-btn>
          </q-item-section>
        </q-item>

        <!--Items-->
        <q-item
          v-for="item in getData"
          :key="item.id"
          v-ripple
          clickable
          @click="
            read(item);
            open(item);
          "
          class="cursor-pointer item"
          :active="!item.readed"
          active-class="text-blue"
        >
          <q-item-section>
            <q-item-label lines="2">{{ item.title || item.message }}</q-item-label>
            <q-item-label caption>{{ $appStore.formatDate(item.created_at) }}</q-item-label>
          </q-item-section>
          <q-item-section side class="q-pl-none">
            <q-btn @click.stop="remove(item)" size="sm" flat round color="red" :icon="mdiDeleteOutline">
              <q-tooltip>{{ $t('Remove') }}</q-tooltip>
            </q-btn>
          </q-item-section>
        </q-item>

        <!--Items-->
        <div class="full-width flex justify-center q-my-xs">
          <q-btn
            v-if="resp.pager?.next"
            @click="next()"
            :label="$t('Load More')"
            size="11px"
            :icon="mdiRefresh"
            flat
          ></q-btn>
        </div>
      </q-list>
    </q-scroll-area>
  </q-drawer>

  <!--Firebase Request Access-->
  <q-dialog v-model="access.modal" seamless position="top">
    <q-card style="width: 350px">
      <q-card-section class="row items-center no-wrap">
        <div>
          <div class="text-h6 text-weight-regular q-mb-xs">{{ $t('System Notification') }}</div>
          <div class="text-grey">
            {{ $t('Enable browser notifications for instant system alerts and file downloads.') }}
          </div>
        </div>
        <q-space />
        <q-btn flat round :icon="mdiClose" v-close-popup color="red" @click="accessNotification(false)" />
        <q-btn flat round :icon="mdiCheck" v-close-popup color="green" @click="accessNotification(true)">
          <q-tooltip>{{ $t('Activate') }}</q-tooltip>
        </q-btn>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { MainNotificationListResponse } from 'src/api/Response/MainNotificationListResponse';
import { initializeApp } from 'firebase/app';
import { getMessaging, getToken, onMessage } from 'firebase/messaging';
import { LocalStorage } from 'quasar';
import { mdiBell, mdiCheck, mdiCheckAll, mdiClose, mdiDeleteOutline, mdiRefresh } from '@quasar/extras/mdi-v7';
import { DeviceType } from 'src/api/Enum/DeviceType';
import { NotificationResource } from 'src/api/Resource/NotificationResource';

export default defineComponent({
  name: 'NotificationComponent',
  setup: () => ({ mdiDeleteOutline, mdiRefresh, mdiCheckAll, mdiBell, mdiClose, mdiCheck }),
  inheritAttrs: false,
  data: () => ({
    resp: {} as MainNotificationListResponse,
    unreadCount: 0,
    firebase: {
      app: null,
      messaging: null,
    },
    drawer: false,
    access: {
      modal: false,
      permission: LocalStorage.getItem('fbPermission'),
    },
    support: 'Notification' in window,
  }),
  mounted() {
    this.loadUnreadCount();
    this.initNotification();
  },
  watch: {
    'access.permission'(val) {
      LocalStorage.set('fbPermission', val);
    },
    unreadCount(v) {
      this.$emit('update:unreadcount', v);
    },
  },
  computed: {
    getData() {
      return this.resp.data ? this.resp.data : null;
    },
  },
  methods: {
    toggle() {
      this.drawer = !this.drawer;
    },
    onShowPanel() {
      if (!this.resp.pager) {
        this.load();
      }
    },
    next() {
      this.resp.pager.current++;
      this.load();
    },
    loadUnreadCount() {
      this.$api.mainNotificationUnreadCount().then((r) => {
        this.unreadCount = r.data.data;
      });
    },
    load() {
      this.$api.mainNotificationList(DeviceType.WEB, { page: this.resp.pager?.current || 1 }).then((r) => {
        if (!Object.keys(this.resp).length) {
          return (this.resp = r.data);
        }

        r.data.data.map((n) => {
          if (!this.resp.data.some((d) => d.id === n.id)) {
            this.resp.data?.push(n);
          }
        });

        this.resp.pager = r.data.pager;
      });
    },
    read(item) {
      if (item.readed) {
        return;
      }

      // Descrease Unread Count
      this.unreadCount--;

      this.$api.mainNotificationRead(item.id, { showMessage: false }).then(() => {
        item.readed = true;
      });
    },
    readAll() {
      this.$api.mainNotificationReadAll().then(() => {
        this.resp.data.forEach((i) => {
          i.readed = true;
        });
      });

      // Clear Unread Count
      this.unreadCount = 0;
    },
    remove(item) {
      this.$api.mainNotificationDelete(item.id, { showMessage: false }).then(() => {
        this.resp.data?.splice(this.resp.data.indexOf(item), 1);
      });
    },
    open(item: NotificationResource) {
      const actions: Array<object> = [];

      // Open Action
      if (item.data['click_action']) {
        actions.push({
          label: this.$t('Open'),
          color: 'white',
          'no-caps': true,
          size: 'md',
          handler: () => window.open(item.data['click_action'], '_blank'),
        });
      }

      // Download Action
      if (item.data['download_action']) {
        actions.push({
          label: this.$t('Download'),
          'no-caps': true,
          color: 'white',
          size: 'md',
          handler: () => window.open(item.data['download_action'], '_blank'),
        });
      }

      // Route Action
      if (item.data['route_action']) {
        actions.push({
          label: this.$t('Open'),
          'no-caps': true,
          color: 'white',
          size: 'md',
          handler: () => this.$router.push(item.data['route_action']),
        });
      }

      this.$appStore.notifyShow(item.message, item.title, item.status, { actions: actions });
    },
    initNotification() {
      if (!this.support) {
        return;
      }

      if (!process.env.FIREBASE_APIKEY) {
        return;
      }

      if (this.access.permission === null) {
        this.access.modal = true;
      }

      if (this.access.permission === true) {
        this.accessNotification(true);
      }
    },
    accessNotification(status) {
      if (!status) {
        return (this.access.permission = false);
      }

      Notification.requestPermission().then((permission) => {
        if (permission === 'granted') {
          this.access.permission = true;
          this.installFirebase();
        } else {
          this.access.permission = false;
          this.$q.dialog({
            title: this.$t('System Notification'),
            message: this.$t('Push notifications are turned off, reset browser permissions to enable it.'),
          });
        }
      });
    },
    installFirebase() {
      const config = {
        apiKey: process.env.FIREBASE_APIKEY,
        authDomain: process.env.FIREBASE_DOMAIN,
        projectId: process.env.FIREBASE_PROJECTID,
        storageBucket: process.env.FIREBASE_STORAGEBUCKET,
        messagingSenderId: process.env.FIREBASE_SENDERID,
        appId: process.env.FIREBASE_APPID,
        measurementId: process.env.FIREBASE_MEASUREMENTID,
      };
      localStorage.setItem('fbConfig', btoa(JSON.stringify(config)));
      this.firebase.app = initializeApp(config);
      this.firebase.messaging = getMessaging(this.firebase.app);

      // Get & Save Token
      getToken(this.firebase.messaging).then((token) => {
        if (token) {
          this.saveFirebaseToken(token);
        }
      });

      // Message Event
      onMessage(this.firebase.messaging, this.onFirebaseMessage);
    },
    saveFirebaseToken(token: string) {
      const fbToken = LocalStorage.getItem('fbToken');
      if (fbToken !== token) {
        this.$api.mainDeviceRegister({ token: token, device: 'web' }, { showMessage: false }).then(() => {
          LocalStorage.set('fbToken', token);
        });
      }
    },
    onFirebaseMessage(payload) {
      const notification = JSON.parse(payload.data.item);

      // Append
      if (notification.id !== null) {
        if (!this.resp.hasOwnProperty('data')) {
          this.resp.data = [];
        }
        this.resp.data.unshift(notification);
        this.unreadCount++;
      }

      // Open
      this.open(notification);
    },
  },
});
</script>

<style lang="scss" scoped>
.panel-head {
  padding-top: max(#{map-get($space-sm, 'y')}, calc(#{map-get($space-sm, 'y')} + env(safe-area-inset-top)));
  font-weight: 500;
  height: 60px;
  background: $primary;
  color: #fff;
  font-size: $button-font-size + 2;
}

.item {
  min-height: 40px;
  padding: 8px 10px;
  margin: 0 6px;
  border-radius: 4px;

  &:hover {
    background: rgba(255, 255, 255, 0.1);
  }
}

.q-pl-none {
  padding-left: 0 !important;
}
</style>

<style lang="scss">
.notification-drawer {
  padding-top: 0 !important;
  .q-scrollarea__content {
    width: 100%;
  }
}
</style>

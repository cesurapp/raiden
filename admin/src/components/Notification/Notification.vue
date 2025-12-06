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
      <q-list v-ripple="false">
        <!--Header-->
        <q-item class="panel-head q-mb-sm bg-primary shadow-bottom">
          <q-item-section avatar><q-icon color="white" :name="mdiBell" /></q-item-section>
          <q-item-section
            ><q-item-label>{{ $t('Notifications') }}</q-item-label></q-item-section
          >
          <q-item-section side top>
            <div>
              <q-btn
                color="white"
                size="12px"
                flat
                round
                :icon="mdiBellPlus"
                @click="onClickInstall"
                v-if="access.permission !== true && this.support"
              ></q-btn>
              <q-btn color="white" size="12px" flat round :icon="mdiRefresh" @click="load" v-close-popup>
                <q-tooltip>{{ $t('Refresh') }}</q-tooltip>
              </q-btn>
              <q-btn color="white" size="12px" flat round :icon="mdiCheckAll" @click="readAll" v-close-popup>
                <q-tooltip>{{ $t('Mark all as read') }}</q-tooltip>
              </q-btn>
            </div>
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
          <q-btn v-if="resp.pager?.next" @click="next()" :label="$t('Load More')" size="11px" :icon="mdiRefresh" flat></q-btn>
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
import { NotificationListResponse } from '@api/main/response/NotificationListResponse';
import { initializeApp } from 'firebase/app';
import { getMessaging, getToken, onMessage } from 'firebase/messaging';
import { LocalStorage } from 'quasar';
import { mdiBell, mdiCheck, mdiCheckAll, mdiClose, mdiDeleteOutline, mdiRefresh, mdiBellPlus } from '@quasar/extras/mdi-v7';
import { DeviceType } from '@api/enum/DeviceType';
import { NotificationResource } from '@api/admin/resource/NotificationResource';

export default defineComponent({
  name: 'NotificationComponent',
  setup: () => ({ mdiDeleteOutline, mdiRefresh, mdiCheckAll, mdiBell, mdiClose, mdiCheck, mdiBellPlus }),
  inheritAttrs: false,
  data: () => ({
    resp: {} as NotificationListResponse,
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
      this.$api.main.NotificationUnreadCount().then((r) => {
        this.unreadCount = r.data.data;
      });
    },
    load() {
      this.$api.main.NotificationList(DeviceType.WEB, { page: this.resp.pager?.current || 1 }).then((r) => {
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

      this.$api.main.NotificationRead(item.id, { showMessage: false }).then(() => {
        item.readed = true;
      });
    },
    readAll() {
      this.$api.main.NotificationReadAll().then(() => {
        this.resp.data.forEach((i) => {
          i.readed = true;
        });
      });

      // Clear Unread Count
      this.unreadCount = 0;
    },
    remove(item) {
      this.$api.main.NotificationDelete(item.id, { showMessage: false }).then(() => {
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

      if (Notification.permission === 'granted') {
        this.installFirebase();
        return;
      }

      Notification.requestPermission().then((permission) => {
        if (permission === 'granted') {
          this.access.permission = true;
          this.installFirebase();
        } else {
          this.access.permission = false;
          this.$appStore.dialogWarning('Push notifications are turned off, reset browser permissions to enable it.');
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
        this.$api.main.DeviceRegister({ token: token, device: 'web' }, { showMessage: false }).then(() => {
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
    onClickInstall() {
      this.$appStore
        .confirmPromise(
          'mdiInformationVariant',
          'positive',
          'Enable browser notifications for instant system alerts and file downloads.',
          false,
          'positive',
          'Activate',
        )
        .then(() => this.accessNotification(true));
    },
  },
});
</script>

<style lang="scss" scoped>
.panel-head {
  padding-top: max(env(safe-area-inset-top), 7px);
  padding-right: calc(16px + env(safe-area-inset-right) / 2);
  padding-left: 16px;
  padding-bottom: 7px;
  font-weight: 500;
  color: #fff;
  font-size: $button-font-size + 2;
  min-height: var(--header-size);
  align-items: center;
  position: sticky;
  top: 0;
  z-index: 1;
}

.item {
  min-height: 40px;
  padding: 8px 16px;

  &:hover {
    background: rgba(255, 255, 255, 0.1);
  }
}

.q-pl-none {
  padding-left: 0 !important;
}

.sys-notification > div {
  padding-right: calc(16px + env(safe-area-inset-right) / 2);
}
</style>

<style lang="scss">
.notification-drawer {
  //padding-top: 0 !important;
  .q-drawer {
    max-width: 87vw;
  }
  .q-scrollarea__content {
    width: 100%;
  }

  .q-drawer__content {
    padding-top: 0 !important;
  }
}
</style>

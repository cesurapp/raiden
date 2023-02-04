<template>
  <q-btn dense flat round icon="notifications" size="md">
    <q-tooltip>{{ $t('Notifications') }}</q-tooltip>
    <q-badge v-if="unreadCount > 0" color="red" rounded floating></q-badge>

    <!--Notifications-->
    <q-menu
      @before-show="onShowPanel"
      anchor="bottom end"
      self="top end"
      style="min-width: 290px"
      :offset="[0, 12]"
    >
      <q-card class="bg-info q-my-md q-mx-md" v-if="access.permission !== true">
        <q-card-section class="flex items-center q-py-sm q-px-md">
          <div class="q-mr-md">
            <div class="text-subtitle1">{{ $t('System Notification') }}</div>
            <div class="text-body2">
              {{
                $t(
                  'Enable browser notifications for instant system alerts and file downloads.'
                )
              }}
            </div>
          </div>
          <q-btn
            outline
            color="primary"
            size="md"
            icon="done"
            rounded
            dense
            @click="accessNotification(true)"
          >
            <q-tooltip>{{ $t('Activate') }}</q-tooltip>
          </q-btn>
        </q-card-section>
      </q-card>

      <q-list>
        <!--Header-->
        <q-item-label header class="flex items-center justify-between">
          <span class="header">{{ $t('Notifications') }}</span>
          <q-btn
            color="primary"
            size="sm"
            flat
            round
            icon="done_all"
            @click="readAll"
            v-close-popup
          >
            <q-tooltip>{{ $t('Mark all as read') }}</q-tooltip>
          </q-btn>
        </q-item-label>

        <!--Items-->
        <q-item
          v-for="item in resp.data"
          :key="item.id"
          class="cursor-pointer item"
          :active="!item.readed"
          active-class="text-blue"
        >
          <q-item-section
            @click="
              read(item);
              open(item);
            "
          >
            <q-item-label lines="1">{{
              item.title || item.message
            }}</q-item-label>
            <q-item-label caption>{{ item.created_at.date }}</q-item-label>
          </q-item-section>
          <q-item-section side class="q-pl-none">
            <q-btn
              @click="remove(item)"
              size="sm"
              flat
              round
              color="red"
              icon="delete"
            >
              <q-tooltip>{{ $t('Remove') }}</q-tooltip>
            </q-btn>
          </q-item-section>
        </q-item>

        <!--Items-->
        <q-btn
          v-if="resp.pager?.next"
          @click="next()"
          class="full-width"
          :label="$t('Load More')"
          size="md"
          flat
        ></q-btn>
      </q-list>
    </q-menu>
  </q-btn>

  <!--Firebase Request Access-->
  <q-dialog v-model="access.modal" seamless position="top">
    <q-card style="width: 350px">
      <q-card-section class="row items-center no-wrap">
        <div>
          <div class="text-h6 text-weight-regular q-mb-xs">
            System Notification
          </div>
          <div class="text-grey">
            Enable browser notifications for instant system alerts and file
            downloads.
          </div>
        </div>
        <q-space />
        <q-btn
          flat
          round
          icon="close"
          v-close-popup
          color="red"
          @click="accessNotification(false)"
        />
        <q-btn
          flat
          round
          icon="done"
          v-close-popup
          color="green"
          @click="accessNotification(true)"
        >
          <q-tooltip>Activate</q-tooltip>
        </q-btn>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { NotificationListResponse } from 'src/api/Response/NotificationListResponse';
import { initializeApp } from 'firebase/app';
import { getMessaging, onMessage, getToken } from 'firebase/messaging';
import { notifyShow } from 'src/helper/NotifyHelper';
import { LocalStorage } from 'quasar';

export default defineComponent({
  name: 'NotificationComponent',
  data: () => ({
    resp: {} as NotificationListResponse,
    unreadCount: 0,
    firebase: {
      app: null,
      messaging: null,
    },
    access: {
      modal: false,
      permission: LocalStorage.getItem('fbPermission'),
    },
  }),
  mounted() {
    this.loadUnreadCount();
    this.initNotification();
  },
  watch: {
    'access.permission'(val) {
      LocalStorage.set('fbPermission', val);
    },
  },
  methods: {
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
      this.$api.notificationUnreadCount().then((r) => {
        this.unreadCount = r.data.data;
      });
    },
    load() {
      this.$api
        .notificationList({ page: this.resp.pager?.current || 1 })
        .then((r) => {
          if (!Object.keys(this.resp).length) {
            return (this.resp = r.data);
          }

          r.data.data.map((n) => {
            if (!this.resp.data.some((d) => d.id === n.id)) {
              this.resp.data?.push(n);
            }
          });

          //this.resp.data?.push(...r.data.data);
          this.resp.pager = r.data.pager;
        });
    },
    read(item) {
      if (item.readed) {
        return;
      }

      // Descrease Unread Count
      this.unreadCount--;

      this.$api.notificationRead(item.id, { message: false }).then(() => {
        item.readed = true;
      });
    },
    readAll() {
      this.$api.notificationReadAll().then(() => {
        this.resp.data.forEach((i) => {
          i.readed = true;
        });
      });

      // Clear Unread Count
      this.unreadCount = 0;
    },
    remove(item) {
      this.$api.notificationDelete(item.id).then(() => {
        this.resp.data?.splice(this.resp.data.indexOf(item), 1);
      });
    },
    open(item) {
      const actions: Array<object> = [];

      // Open Action
      if (item.data.click_action) {
        actions.push({
          label: this.$t('Open'),
          color: 'white',
          'no-caps': true,
          size: 'md',
          handler: () => window.open(item.data.click_action, '_blank'),
        });
      }

      // Download Action
      if (item.data.download_action) {
        actions.push({
          label: this.$t('Download'),
          'no-caps': true,
          color: 'white',
          size: 'md',
          handler: () => window.open(item.data.download_action, '_blank'),
        });
      }

      notifyShow(item.message, item.title, item.type, { actions: actions });
    },
    initNotification() {
      if (!Notification) {
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
            message: this.$t(
              'Push notifications are turned off, reset browser permissions to enable it.'
            ),
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
        this.$api
          .deviceRegister({ token: token, device: 'web' }, { message: false })
          .then(() => {
            LocalStorage.set('fbToken', token);
          });
      }
    },
    onFirebaseMessage(payload) {
      // Parse & Append Data
      let n = payload.data;
      n.data = JSON.parse(n.data);
      n.readed = JSON.parse(n.readed);
      n.created_at = JSON.parse(n.created_at);
      if (!this.resp.hasOwnProperty('data')) {
        this.resp.data = [];
      }
      this.resp.data.unshift(n);

      // Increase Count
      this.unreadCount++;

      this.open(n);
    },
  },
});
</script>

<style lang="scss" scoped>
.header {
  font-size: 16px;
}

.item {
  &:hover {
    background: rgba(0, 0, 0, 0.1);
  }
}

.q-pl-none {
  padding-left: 0 !important;
}
</style>

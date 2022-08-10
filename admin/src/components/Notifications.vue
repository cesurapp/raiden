<template>
  <q-btn dense flat round icon="notifications" size="md">
    <q-tooltip>{{ $t('Notifications') }}</q-tooltip>
    <q-badge v-if="isUnreaded" color="red" rounded floating></q-badge>

    <!--Notifications-->
    <q-menu anchor="bottom end" self="top end" style="min-width: 275px">
      <q-list>
        <!--Header-->
        <q-item-label header class="flex items-center justify-between">
          <span class="header">{{ $t('Notifications') }}</span>
          <q-btn color="primary" size="sm" flat dense round icon="done_all" @click="readAll" v-close-popup>
            <q-tooltip>{{ $t('Mark all as read') }}</q-tooltip>
          </q-btn>
        </q-item-label>

        <!--Items-->
        <q-item v-for="item in resp.data" :key="item.id" class="cursor-pointer item" :active="!item.readed"
                active-class="text-blue">
          <q-item-section @click="read(item); open(item)">
            <q-item-label lines="1">{{ item.title }}</q-item-label>
            <q-item-label caption>{{ item.createdAt.date }}</q-item-label>
          </q-item-section>
          <q-item-section side class="q-pl-none">
            <q-btn @click="remove(item)" size="sm" flat dense round color="red" icon="delete">
              <q-tooltip>{{ $t('Remove') }}</q-tooltip>
            </q-btn>
          </q-item-section>
        </q-item>

        <!--Items-->
        <q-item v-if="resp.pager?.next">
          <q-btn @click="next()" class="full-width" :label="$t('Load More')" size="md" flat></q-btn>
        </q-item>
      </q-list>
    </q-menu>
  </q-btn>
</template>

<script lang="ts">
import {defineComponent} from 'vue';
import {NotificationListResponse} from 'src/api/Response/NotificationListResponse';
import {initializeApp} from 'firebase/app';
import {getMessaging, onMessage, getToken} from 'firebase/messaging';
import {notifyShow} from 'src/helper/NotifyHelper';

export default defineComponent({
  name: 'NotificationComponent',
  data: () => ({
    resp: {} as NotificationListResponse,
    firebase: {
      app: null,
      messaging: null
    }
  }),
  mounted() {
    this.load();
    this.installFirebase();
  },
  computed: {
    isUnreaded() {
      return this.resp.data?.some((i) => !i.readed);
    }
  },
  methods: {
    next() {
      this.resp.pager.current++
      this.load();
    },
    prev() {
      this.resp.pager.current--;
      this.load();
    },
    load() {
      this.$api.notificationList({page: this.resp.pager?.current || 1}).then((r) => {
        if (!Object.keys(this.resp).length) {
          return this.resp = r.data;
        }

        this.resp.data?.push(...r.data.data);
        this.resp.pager = r.data.pager;
      })
    },
    read(item) {
      if (item.readed) {
        return;
      }

      this.$api.notificationRead(item.id, {message: false}).then(() => {
        item.readed = true;
      })
    },
    readAll() {
      this.$api.notificationReadAll().then(() => {
        this.resp.data.forEach((i) => {
          i.readed = true;
        })
      });
    },
    remove(item) {
      this.$api.notificationDelete(item.id).then(() => {
        this.resp.data?.splice(this.resp.data.indexOf(item), 1);
      })
    },
    open(item) {
      const options: object = {};

     /* if (payload.fcmOptions?.link) {
        options['actions'] = [{
          label: 'Open',
          color: 'white',
          handler: () => {
            console.log('asd');
          }
        }]
      }*/

      notifyShow(
        item.message,
        item.title,
        item.type,
        options
      );
    },
    installFirebase() {
      this.firebase.app = initializeApp({
        apiKey: 'AIzaSyC_ZbH7KHkv9s72ZEgjZbwduYBmFR2aN_E',
        authDomain: 'yeyee-app.firebaseapp.com',
        projectId: 'yeyee-app',
        storageBucket: 'yeyee-app.appspot.com',
        messagingSenderId: '858568967918',
        appId: '1:858568967918:web:1355ef685eb5b69a4b95ff',
        measurementId: 'G-KW1YHX0979'
      });
      this.firebase.messaging = getMessaging(this.firebase.app);

      // Get & Save Token
      getToken(this.firebase.messaging).then((token) => this.$api.deviceRegister({token: token, device: 'web'}, {message: false}))

      // Message Event
      onMessage(this.firebase.messaging, this.onFirebaseMessage);
    },
    onFirebaseMessage(payload) {
      // Parse & Append Data
      let n = payload.data;
      n.data = JSON.parse(n.data);
      n.readed = JSON.parse(n.readed);
      n.createdAt = JSON.parse(n.createdAt);
      this.resp.data.unshift(n);

      this.open(n);
    }
  }
});
</script>

<style lang="scss" scoped>
.header {
  font-size: 16px;
}

.item {
  &:hover {
    background: rgba(0, 0, 0, .1);
  }
}

.q-pl-none {
  padding-left: 0 !important;
}
</style>

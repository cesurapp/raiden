<template>
  <!--Toggle Button-->
  <q-btn dense flat round @click="this.open = !this.open" icon="notifications" size="md">
    <q-tooltip>Notifications</q-tooltip>
    <q-badge v-if="isUnreaded" color="red" rounded floating></q-badge>
  </q-btn>

  <Teleport to="#layout" v-if="this.isMounted">
    <q-drawer v-model="open" side="right" overlay elevated>
      <!--Tabs-->
      <q-tabs dense no-caps inline-label v-model="tab" class="bg-grey-3" align="justify">
        <q-tab class="text-orange" name="unread" icon="sms_failed" label="Unread"/>
        <q-tab class="text-cyan" name="all" icon="done_all" label="All"/>
      </q-tabs>

      <!--Items-->
      <q-list bordered padding class="rounded-borders" style="max-width: 350px">
        <q-item clickable v-ripple v-for="item in resp.data" :key="item.id">
          <q-item-section>
            <q-item-label lines="1">{{ item.title }}</q-item-label>
            <q-item-label caption>{{ item.createdAt.date }}</q-item-label>
          </q-item-section>
          <q-item-section side>
            <q-icon name="info" color="green"/>
          </q-item-section>
        </q-item>
      </q-list>
    </q-drawer>
  </Teleport>
</template>

<script lang="ts">
import {defineComponent} from 'vue';
import {NotificationListResponse} from "src/api/Response/NotificationListResponse";

export default defineComponent({
  name: 'NotificationComponent',
  data: () => ({
    open: false,
    isMounted: false,
    tab: 'unread',
    resp: {} as NotificationListResponse
  }),
  mounted() {
    this.isMounted = true;
    this.load();
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
        this.resp = r.data;
      })
    }
  }
});
</script>

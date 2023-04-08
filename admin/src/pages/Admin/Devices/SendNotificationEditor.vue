<template>
  <SimpleEditor ref="editor" :icon="mdiBell" title-create="Send Notification" class="borderless">
    <template #content>
      <q-form @keydown.enter.prevent="send" class="q-gutter-xs" ref="form">
        <!--Status-->
        <q-select
          emit-value
          map-options
          outlined
          bottom-slots
          v-model="form.status"
          :options="getStatus"
          :error="$rules.ssrValid('status')"
          :error-message="$rules.ssrException('status')"
          :rules="[$rules.required()]"
        >
        </q-select>

        <!--Title-->
        <q-input outlined lazy-rules v-model="form.title" :label="$t('Title')" :rules="[$rules.minLength(2)]"></q-input>

        <!--Message-->
        <q-input
          outlined
          lazy-rules
          v-model="form.message"
          :label="$t('Message')"
          :rules="[$rules.minLength(2)]"
          hide-bottom-space
        ></q-input>

        <!--Custom Data-->
        <div class="flex justify-between gap-x-md q-mt-lg" v-for="(item, index) in data" :key="index">
          <q-select
            emit-value
            map-options
            outlined
            style="width: 28%"
            multiple
            :label="$t('Device')"
            v-model="item.type"
            :options="[
              { label: 'Web', value: DeviceType.WEB },
              { label: 'Android', value: DeviceType.ANDROID },
              { label: 'Ios', value: DeviceType.IOS },
            ]"
          ></q-select>
          <q-select
            emit-value
            map-options
            outlined
            style="width: 25%"
            v-model="item.action"
            :label="$t('Action')"
            :options="['icon', 'sound', 'color', 'click_action', 'route_action', 'download_action']"
          ></q-select>
          <q-input outlined :label="$t('Value')" class="col-grow" v-model="item.value"></q-input>
          <q-btn flat dense color="negative" :icon="mdiClose" @click="removeOptions(index)"></q-btn>
        </div>
      </q-form>
    </template>

    <!--Actions-->
    <template #actionsLeft>
      <q-btn flat color="primary" :label="$t('New Option')" :icon="mdiPlus" @click="addOptions"></q-btn>
    </template>
    <template #actionsRight>
      <q-btn flat color="primary" :label="$t('Send')" :icon="mdiSend" :loading="$appStore.isBusy" @click="send"></q-btn>
    </template>
  </SimpleEditor>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import SimpleEditor from 'components/SimpleEditor/Index.vue';
import { mdiBell, mdiPlus, mdiSend, mdiClose } from '@quasar/extras/mdi-v7';
import { DeviceSendRequest } from 'src/api/Request/DeviceSendRequest';
import { NotificationStatus } from 'src/api/Enum/NotificationStatus';
import { DeviceType } from 'src/api/Enum/DeviceType';

export default defineComponent({
  name: 'SendNotificationEditor',
  components: { SimpleEditor },
  setup: () => ({ mdiBell, mdiSend, mdiPlus, mdiClose, DeviceType }),
  data: () => ({
    deviceId: null as string | null,
    form: {} as DeviceSendRequest,
    data: [],
  }),
  computed: {
    getStatus() {
      return Object.keys(NotificationStatus).map((key) => ({
        label: key,
        value: NotificationStatus[key],
      }));
    },
  },
  methods: {
    init(deviceId: string) {
      this.deviceId = deviceId;
      this.form = { status: NotificationStatus.INFO, data: {} };
      this.data = [];
      this.$refs.editor.toggle();
    },
    send() {
      // Clear Backend Validation Errors
      this.$rules.clearSSRException();

      // Merge Data
      if (this.data.length > 0) {
        this.data.forEach((item) => {
          item.type.forEach((device) => {
            if (!this.form.data.hasOwnProperty(device)) {
              this.form.data[device] = {};
            }
            this.form.data[device][item.action] = item.value;
          });
        });
      }

      this.$refs.form.validate().then((success: any) => {
        if (success) {
          this.$api.deviceSend(this.deviceId, this.form, { showMessage: false }).then((r) => {
            this.$emit('created', r.data.data);
            this.$refs.editor.toggle();
          });
        }
      });
    },
    addOptions() {
      this.data.push({ type: ['web'], action: 'click_action', value: '' });
    },
    removeOptions(index) {
      this.data.splice(index, 1);
    },
  },
});
</script>

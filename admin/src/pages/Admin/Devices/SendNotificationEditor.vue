<template>
  <SimpleEditor ref="editor" :icon="mdiBell" title-create="Yeni Bildirim" class="borderless">
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
        ></q-input>

        <!--Custom Data-->
      </q-form>
    </template>

    <template #actionsLeft>
      <q-btn flat color="primary" :label="$t('Yeni Özellik')" :icon="mdiPlus" @click="send"></q-btn>
    </template>
    <template #actionsRight>
      <q-btn flat color="primary" :label="$t('Send')" :icon="mdiSend" :loading="$appStore.isBusy" @click="send"></q-btn>
    </template>
  </SimpleEditor>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import SimpleEditor from 'components/SimpleEditor/Index.vue';
import { mdiBell, mdiSend, mdiPlus } from '@quasar/extras/mdi-v7';
import { DeviceSendRequest } from 'src/api/Request/DeviceSendRequest';
import { NotificationStatus } from 'src/api/Enum/NotificationStatus';

export default defineComponent({
  name: 'SendNotificationEditor',
  components: { SimpleEditor },
  setup: () => ({ mdiBell, mdiSend, mdiPlus }),
  data: () => ({
    deviceId: null as string | null,
    form: {} as DeviceSendRequest,
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
      this.form = {};
      this.$refs.editor.toggle();
    },
    send() {
      // Clear Backend Validation Errors
      this.$rules.clearSSRException();

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
      this.form.data.push({});
    },
  },
});
</script>

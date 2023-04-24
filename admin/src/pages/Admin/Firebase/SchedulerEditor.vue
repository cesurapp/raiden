<template>
  <SimpleEditor ref="editor" :icon="mdiBell" title-create="Scheduled Notification" class="borderless">
    <template #content>
      <q-form @keydown.enter.prevent="send" class="q-gutter-xs" ref="form">
        <!--Scheduler-->
        <div class='q-mb-md'>
          <div class="text-h5 q-mb-md">{{ $t('Scheduler') }}</div>
          <!--Campaign Title-->
          <q-input outlined lazy-rules
                   v-model='form.campaign_title'
                   :label="$t('Campaign Title')"
                   :rules='[$rules.required()]'
                   :error="$rules.ssrValid('campaign_title')"
                   :error-message="$rules.ssrException('campaign_title')"
          ></q-input>

          <!--Send At-->
          <q-input outlined lazy-rules
                   v-model="form.send_at"
                   :label="$t('Send Date')"
                   :rules="[$rules.required()]"
                   :error="$rules.ssrValid('send_at')"
                   :error-message="$rules.ssrException('send_at')"
          >
            <template v-slot:prepend>
              <q-icon :name="mdiCalendar" class="cursor-pointer">
                <q-popup-proxy cover transition-show="scale" transition-hide="scale" class="datepopup">
                  <q-date minimal :options='dateRules' v-model="form.send_at" mask="DD/MM/YYYY HH:mm" :locale="getCurrentLocale()"></q-date>
                </q-popup-proxy>
              </q-icon>
            </template>
            <template v-slot:append>
              <q-icon :name="mdiClockOutline" class="cursor-pointer">
                <q-popup-proxy cover transition-show="scale" transition-hide="scale" class='datepopup'>
                  <q-time v-model="form.send_at" mask="DD/MM/YYYY HH:mm" format24h></q-time>
                </q-popup-proxy>
              </q-icon>
            </template>
          </q-input>

          <!--Persist Notification-->
          <q-checkbox outlined v-model='form.persist_notification' :label="$t('Persist Notification')"></q-checkbox>
        </div>

        <!--Notification-->
        <div>
          <div class="text-h5 q-mb-md">{{ $t('Notification') }}</div>
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
import { mdiBell, mdiPlus, mdiSend, mdiClose, mdiCalendar, mdiClockOutline } from '@quasar/extras/mdi-v7';
import { NotificationStatus } from 'src/api/Enum/NotificationStatus';
import { DeviceType } from 'src/api/Enum/DeviceType';
import { SchedulerCreateRequest } from 'src/api/Request/SchedulerCreateRequest';
import { getCurrentLocale, dateInput } from 'src/helper/DateHelper';
import { SchedulerResource } from 'src/api/Resource/SchedulerResource';

export default defineComponent({
  name: 'SendNotificationEditor',
  components: { SimpleEditor },
  setup: () => ({ mdiBell, mdiSend, mdiPlus, mdiClose, DeviceType, mdiCalendar, mdiClockOutline, getCurrentLocale }),
  data: () => ({
    tab: 'notification',
    form: {} as SchedulerCreateRequest,
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
    init(scheduled: SchedulerResource | null) {
      if (scheduled) {
        this.data = [];
        this.form = { ...scheduled, data: {} };
        this.form.title = scheduled.notification.title
        this.form.message = scheduled.notification.message
        this.form.status = scheduled.notification.status

        Object.entries(scheduled.notification.data).forEach(([device, data]) => {
          Object.entries(data).forEach(([action, value]) => {
            let index = this.data.findIndex((item) => item.action === action && item.value === value);
            if (index === -1) {
              this.data.push({ type: [device], action: action, value: value })
            } else {
              this.data[index].type.push(device)
            }
          })
        })
      } else {
        this.form = { status: NotificationStatus.INFO, data: {} };
        this.data = [];
      }

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
          // Edit
          if (this.form.hasOwnProperty('id')) {
            return this.$api.schedulerEdit(this.form.id, this.form).then((r) => {
              this.$emit('updated', r.data.data);
              this.$refs.editor.toggle();
            });
          }

          // Create
          this.$api.schedulerCreate({...this.form, ...{send_at: dateInput(this.form.send_at)}}).then((r) => {
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
    dateRules(date) {
      return new Date(date).getDate() >= new Date().getDate()
    }
  },
});
</script>

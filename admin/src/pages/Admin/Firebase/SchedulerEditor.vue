<template>
  <SimpleEditor ref="editor" :icon="mdiBell" title-create="Scheduled Notification" class="borderless">
    <!--Form-->
    <template #content>
      <q-form @keydown.enter.prevent="send" class="q-gutter-xs" ref="form">
        <!--Device Filter-->
        <div class="fit row wrap justify-start items-start content-start gap-x-md q-mb-md">
          <div class="text-h5 q-mb-md col-12">{{ $t('Device Filter') }}</div>
          <div class="col-grow">
            <q-select
              emit-value
              map-options
              outlined
              bottom-slots
              style="width: 28%"
              multiple
              class="full-width"
              :label="$t('Device')"
              v-model="form.device_filter['device.type']"
              :error="$rules.ssrValid('device_filter[device.type]')"
              :error-message="$rules.ssrException('device_filter[device.type]')"
              :options="[
                { label: 'Web', value: DeviceType.WEB },
                { label: 'Android', value: DeviceType.ANDROID },
                { label: 'Ios', value: DeviceType.IOS },
              ]"
            ></q-select>
            <LanguageInput
              outlined
              bottom-slots
              :label="$t('User Language')"
              v-model="form.device_filter['user.language']"
              :error="$rules.ssrValid('device_filter[user.language]')"
              :error-message="$rules.ssrException('device_filter[user.language]')"
            ></LanguageInput>
            <CountryInput
              outlined
              bottom-slots
              :label="$t('Phone Country')"
              v-model="form.device_filter['user.phoneCountry']"
              :error="$rules.ssrValid('device_filter[user.phoneCountry]')"
              :error-message="$rules.ssrException('device_filter[user.phoneCountry]')"
            ></CountryInput>
          </div>
          <div class="col-grow">
            <UserTypeInput
              outlined
              multiple
              bottom-slots
              :label="$t('User Type')"
              v-model="form.device_filter['user.type']"
              :error="$rules.ssrValid('device_filter[user.type]')"
              :error-message="$rules.ssrException('device_filter[user.type]')"
            ></UserTypeInput>
            <DateRangeInput
              outlined
              bottom-slots
              v-model="form.device_filter['user.createdAt']"
              :error="$rules.ssrValid('device_filter[user.createdAt][to]')"
              :error-message="$rules.ssrException('device_filter[user.createdAt][to]')"
            ></DateRangeInput>
            <q-checkbox outlined v-model="form.device_filter['user.frozen']" :label="$t('Frozen')"></q-checkbox>
          </div>
        </div>

        <!--Scheduler-->
        <div class="q-mb-md">
          <div class="text-h5 q-mb-md">{{ $t('Scheduler') }}</div>
          <!--Campaign Title-->
          <q-input
            outlined
            lazy-rules
            v-model="form.campaign_title"
            :label="$t('Campaign Title')"
            :rules="[$rules.required()]"
            :error="$rules.ssrValid('campaign_title')"
            :error-message="$rules.ssrException('campaign_title')"
          ></q-input>
          <!--Send At-->
          <DateInput
            outlined
            timer
            v-model="form.send_at"
            :label="$t('Send Date')"
            :rules="[$rules.required()]"
            :error="$rules.ssrValid('send_at')"
            :error-message="$rules.ssrException('send_at')"
            :date-rules="dateRules"
          ></DateInput>
          <!--Persist Notification-->
          <q-checkbox
            outlined
            v-model="form.persist_notification"
            class="q-mr-md"
            :label="$t('Persist Notification')"
          ></q-checkbox>
          <!--Refresh Campaign-->
          <q-checkbox outlined v-model="form.refresh_campaign" :label="$t('Refresh Campaign')"></q-checkbox>
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
          <q-input
            outlined
            lazy-rules
            v-model="form.title"
            :label="$t('Title')"
            :rules="[$rules.minLength(2)]"
            :error="$rules.ssrValid('title')"
            :error-message="$rules.ssrException('title')"
          ></q-input>
          <!--Message-->
          <q-input
            outlined
            lazy-rules
            v-model="form.message"
            :label="$t('Message')"
            :rules="[$rules.minLength(2)]"
            hide-bottom-space
            :error="$rules.ssrValid('message')"
            :error-message="$rules.ssrException('message')"
          ></q-input>
          <!--Custom Data-->
          <div class="flex justify-between no-wrap q-mt-lg" v-for="(item, index) in data" :key="index">
            <div class='flex full-width justify-between gap-x-md gap-y-md q-pr-md'>
              <q-select
                emit-value
                map-options
                outlined
                style="width: 28%"
                multiple
                :label="$t('Device')"
                v-model="item.type"
                class='col-grow'
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
                class='col-grow'
                style="width: 25%"
                v-model="item.action"
                :label="$t('Action')"
                :options="['icon', 'sound', 'color', 'click_action', 'route_action', 'download_action']"
              ></q-select>
              <q-input outlined :label="$t('Value')" class="col-grow" v-model="item.value"></q-input>
            </div>
            <q-btn outline dense size='sm' color="negative" :icon="mdiClose" @click="removeOptions(index)"></q-btn>
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
import { SchedulerResource } from 'src/api/Resource/SchedulerResource';
import UserTypeInput from 'pages/Admin/Components/UserTypeInput.vue';
import LanguageInput from 'components/Language/LanguageInput.vue';
import CountryInput from 'components/Country/CountryInput.vue';
import DateRangeInput from 'components/Date/DateRangeInput.vue';
import DateInput from 'components/Date/DateInput.vue';

export default defineComponent({
  name: 'SendNotificationEditor',
  components: { DateInput, DateRangeInput, CountryInput, LanguageInput, UserTypeInput, SimpleEditor },
  setup: () => ({ mdiBell, mdiSend, mdiPlus, mdiClose, DeviceType, mdiCalendar, mdiClockOutline }),
  data: () => ({
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
        this.form = {
          ...scheduled,
          title: scheduled.notification.title,
          message: scheduled.notification.message,
          status: scheduled.notification.status,
          device_filter: scheduled.device_filter ?? {},
          data: {},
        };

        Object.entries(scheduled.notification.data).forEach(([device, data]) => {
          Object.entries(data).forEach(([action, value]) => {
            let index = this.data.findIndex((item) => item.action === action && item.value === value);
            if (index === -1) {
              this.data.push({ type: [device], action: action, value: value });
            } else {
              this.data[index].type.push(device);
            }
          });
        });
      } else {
        this.data = [];
        this.form = {
          status: NotificationStatus.INFO,
          data: {},
          device_filter: {},
        };
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
          this.$api.schedulerCreate(this.form).then((r) => {
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
      return new Date(date).getDate() >= new Date().getDate();
    },
  },
});
</script>

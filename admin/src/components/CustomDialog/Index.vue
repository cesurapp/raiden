<template>
  <q-dialog ref="dialog" :no-backdrop-dismiss="persistent">
    <q-card class="q-dialog-plugin">
      <!-- Header ICONS -->
      <q-card-section class="row items-center no-wrap" v-if="icon">
        <q-avatar :icon="getIcon" :color="iconBg" text-color="white" class="self-start" />
        <span class="q-ml-md">{{ $t(message) }}</span>
      </q-card-section>

      <template v-else>
        <q-card-section v-if="title"
          ><div class="text-h6">{{ $t(title) }}</div></q-card-section
        >
        <q-card-section v-if="message" class="q-pt-none">{{ $t(message) }}</q-card-section>
      </template>

      <!-- Action Buttons -->
      <q-card-actions align="right">
        <q-btn
          v-if="cancel"
          flat
          color="red"
          :label="typeof cancel === 'string' ? $t(cancel) : $t('Cancel')"
          @click="onCancelClick"
        />
        <q-btn
          v-if="yes"
          flat
          :color="yesColor"
          :label="typeof yes === 'string' ? $t(yes) : $t('Yes')"
          @click="onOKClick"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script>
import {
  mdiDeleteOutline,
  mdiCheck,
  mdiClose,
  mdiAlert,
  mdiInformationVariant,
  mdiServerNetwork,
} from '@quasar/extras/mdi-v7';

export default {
  name: 'CustomDialog',
  props: {
    icon: String,
    iconBg: {
      type: String,
      default: () => 'primary',
    },
    title: String,
    message: String,
    persistent: Boolean,
    cancel: {
      type: [String, Boolean],
      default: true,
    },
    yes: {
      type: [Boolean, String],
      default: true,
    },
    yesColor: {
      type: String,
      default: () => 'primary',
    },
  },
  computed: {
    getIcon() {
      switch (this.icon) {
        case 'mdiDeleteOutline':
          return mdiDeleteOutline;
        case 'mdiCheck':
          return mdiCheck;
        case 'mdiClose':
          return mdiClose;
        case 'mdiAlert':
          return mdiAlert;
        case 'mdiInformationVariant':
          return mdiInformationVariant;
        case 'mdiServerNetwork':
          return mdiServerNetwork;
        default:
          return null;
      }
    },
  },
  methods: {
    show() {
      this.$refs.dialog.show();
    },
    hide() {
      this.$refs.dialog.hide();
    },
    onDialogHide() {
      this.$emit('hide');
    },
    onOKClick() {
      this.$emit('ok');
      this.hide();
    },
    onCancelClick() {
      this.hide();
    },
  },
};
</script>

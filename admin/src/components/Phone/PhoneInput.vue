<template>
  <q-input
    type="tel"
    ref="tel"
    unmasked-value
    lazy-rules
    :placeholder="`+${getCC}`"
    :mask="`+${getCC} ############`"
    :label="$t('Phone')"
    :rules="required ? [$rules.required(), $rules.minLength(7)] : []"
    :error="$rules.ssrValid(serverSideInput)"
    :error-message="$rules.ssrException(serverSideInput)"
    @update:modelValue="(val) => $emit('update:fullNumber', val ? getCC + val : null)"
  >
    <template v-slot:prepend>
      <CountryInput
        :readonly="$attrs.readonly"
        ref="country"
        class="country-input"
        hide-dropdown-icon
        dense
        borderless
        :modelValue="phoneCountry"
        @update:modelValue="(val) => $emit('update:phoneCountry', val)"
        :clearable="false"
        :bottom-slots="false"
        @popupHide="onPopupHide"
      ></CountryInput>
    </template>
    <template #append v-if="$slots.append">
      <slot name="append"></slot>
    </template>
  </q-input>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import CountryInput from 'components/Country/CountryInput.vue';
import { countries } from 'countries-list';

export default defineComponent({
  name: 'PhoneInput',
  components: { CountryInput },
  props: {
    phoneCountry: { type: [String], required: false },
    fullNumber: { type: [String, Number], default: null, required: false },
    required: { type: Boolean, default: true },
    serverSideInput: { type: String, default: 'phone' },
  },
  computed: {
    getCC() {
      return this.phoneCountry ? countries[this.phoneCountry].phone : null;
    },
  },
  methods: {
    onPopupHide() {
      this.$refs.tel.focus();
      setTimeout(() => {
        this.$refs.tel.$el.querySelector('input[type="tel"]').select();
      }, 50);
    },
  },
});
</script>

<style lang="scss">
.country-input {
  .q-field__control {
    padding-left: 0;
    padding-right: 0;
  }

  .q-field__control:before {
    border: none;
  }
  &.q-field--readonly .q-field__control:before {
    border: none;
  }

  .q-field__control:after {
    border: none;
  }

  .q-field__label {
    display: none;
  }

  .q-field__control-container {
    display: none;
  }

  .q-field__append {
    display: none;
  }

  .q-field__prepend {
    padding-right: 0;
    cursor: pointer;
  }

  .q-field__input {
    padding-left: 12px;
    min-width: 25px !important;
    width: auto;
  }

  &.q-field--highlighted {
    .q-field__control-container {
      display: inline-flex;
    }
  }
}
</style>

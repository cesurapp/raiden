<template>
  <q-input
    type="tel"
    ref="tel"
    unmasked-value
    lazy-rules
    :mask="`+${code} ############`"
    :label="$t('Phone')"
    :rules="required ? [$rules.required(), $rules.minLength(7)] : []"
    :error="$rules.ssrValid(serverSideInput)"
    :error-message="$rules.ssrException(serverSideInput)"
    @update:modelValue="(val) => $emit('update:fullNumber', val ? code + val : null)"
  >
    <template v-slot:prepend>
      <CountryInput
        ref="country"
        class="country-input"
        hide-dropdown-icon
        dense
        borderless
        :modelValue="phoneCountry"
        @update:modelValue="(val) => $emit('update:phoneCountry', val)"
        v-model:phone-code="code"
        :clearable="false"
        :bottom-slots="false"
        @popupHide="onPopupHide"
      ></CountryInput>
    </template>
  </q-input>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import CountryInput from 'components/Country/CountryInput.vue';

export default defineComponent({
  name: 'PhoneInput',
  components: { CountryInput },
  props: {
    phoneCountry: [String],
    fullNumber: [String, Number],
    required: { type: Boolean, default: true },
    serverSideInput: { type: String, default: 'phone' },
  },
  data: () => ({
    code: null,
  }),
  mounted() {
    if (!this.phoneCountry) {
      this.$emit('update:phoneCountry', 'TR');
    }
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
    padding-left: 2px;
    padding-right: 0;
  }

  .q-field__control:before {
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

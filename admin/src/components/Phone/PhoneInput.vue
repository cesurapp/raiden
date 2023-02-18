<template>
  <div class="phone-input">
    <!--Phone-->
    <q-input
      type="tel"
      v-model="proxyPhone"
      v-bind="$attrs"
      :mask="phoneCodes[data.phoneCountry].mask"
      unmasked-value
      fill-mask
      :label="label"
      lazy-rules
      :rules="dynamicRules(data.phoneCountry)"
      :error="$rules.ssrValid(serverSideInput)"
      :error-message="$rules.ssrException(serverSideInput)"
    >
      <template v-slot:prepend>
        <!--Select Country-->
        <q-select
          class="country-input"
          hide-dropdown-icon
          hide-selected
          dense
          borderless
          v-model="data.phoneCountry"
          @update:model-value="updateModel"
          :options="getCountryPhoneList"
          emit-value
          map-options
        >
          <template v-slot:prepend
            ><q-icon :name="'img:/images/flags/' + data.phoneCountry.toLowerCase() + '.svg'"
          /></template>
          <template v-slot:option="scope">
            <q-item v-bind="scope.itemProps">
              <q-item-section avatar><q-icon :name="scope.opt.icon" /></q-item-section>
              <q-item-section
                ><q-item-label>{{ scope.opt.description }}</q-item-label></q-item-section
              >
            </q-item>
          </template>
        </q-select>
        <!--End Select Country-->
      </template>
    </q-input>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { phoneCodes, extractPhone } from './PhoneCodeList';

export default defineComponent({
  name: 'PhoneInput',
  inheritAttrs: false,
  props: {
    phoneNumber: [String, Number],
    phoneCode: [String],
    phoneCountry: [String],

    label: [String],
    required: { type: Boolean, default: true },
    serverSideInput: { type: String, default: 'phone' },
  },
  data: () => ({
    data: {
      phoneNumber: '',
      phoneCode: '90',
      phoneCountry: 'TR',
    },
    phoneCodes: phoneCodes,
  }),
  mounted() {
    if (this.phoneNumber && this.phoneCountry) {
      this.data = extractPhone(this.phoneNumber, this.phoneCountry);
    }
  },
  computed: {
    proxyPhone: {
      get() {
        return String(this.data.phoneNumber);
      },
      set(val: string) {
        this.data.phoneNumber = val;
        this.updateModel();
      },
    },
    getCountryPhoneList() {
      return Object.entries(this.phoneCodes).map(([phoneCountry, item]: [string, any]) => {
        return {
          value: phoneCountry,
          description: `${String(item.label)} (+${String(item.phoneCode)})`,
          icon: `img:/images/flags/${String(phoneCountry).toLowerCase()}.svg`,
        };
      });
    },
  },
  methods: {
    updateModel() {
      this.data.phoneCode = this.phoneCodes[this.data.phoneCountry].phoneCode;

      this.$emit(
        'update:phoneNumber',
        String(this.data.phoneNumber ? this.data.phoneCode + this.data.phoneNumber : '')
      );
      this.$emit('update:phoneCode', String(this.data.phoneCode));
      this.$emit('update:phoneCountry', String(this.data.phoneCountry));
    },
    dynamicRules(phoneCountry) {
      return this.required
        ? [this.$rules.required(), this.$rules.isPhone(phoneCountry)]
        : [this.$rules.isPhone(phoneCountry)];
    },
  },
});
</script>

<style lang="scss">
.country-input {
  .q-field__control {
    padding-left: 3px;
    padding-right: 0;
  }

  .q-field__control:before {
    border: none;
  }

  .q-field__control:after {
    border: none;
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
}
</style>

<template>
  <div class="phone-input">
    <!--Phone-->
    <q-input outlined type="tel" v-model="proxyPhone" v-bind="$attrs"
             :mask="masks[code].mask" fill-mask unmasked-value
             :label="label" lazy-rules
             :error="$rules.ssrValid(serverSideInput)"
             :error-message="$rules.ssrException(serverSideInput)"
             :rules="[$rules.required(), $rules.isPhone(code)]">
      <template v-slot:prepend>
        <!--Select Country-->
        <q-select class="country-input" hide-selected outlined dense :dropdown-icon="null" v-model="code" :options="getCountryCodes" emit-value map-options>
          <template v-slot:prepend><q-icon :name="'img:/images/flags/'+ masks[code].country +'.svg'"/></template>
          <template v-slot:option="scope">
            <q-item v-bind="scope.itemProps">
              <q-item-section avatar><q-icon :name="scope.opt.icon" /></q-item-section>
              <q-item-section>
                <q-item-label>{{ scope.opt.label }}</q-item-label>
                <q-item-label>{{ scope.opt.description }}</q-item-label>
              </q-item-section>
            </q-item>
          </template>
        </q-select>
      </template>
    </q-input>
  </div>
</template>

<script lang="ts">
import {defineComponent} from 'vue';
import {phoneCodes, extractPhone} from './PhoneCodeList';

export default defineComponent({
  name: 'PhoneInput',
  inheritAttrs: false,
  props: {
    modelValue: [String, Number],
    label: [String],
    serverSideInput: {
      type: String,
      default: 'phone',
    }
  },
  data: () => ({
    country: 'TR',
    code: '90',
    phone: '',
    masks: phoneCodes,
  }),
  computed: {
    proxyPhone: {
      get() {
        return String(this.phone);
      },
      set(val: string) {
        this.phone = val;
        this.$emit('update:modelValue', String(this.masks[this.code].code) + String(val));
      }
    },
    getCountryCodes() {
      return Object.entries(this.masks).map(([country, item]: [string, any]) => {
        return {
          value: country,
          description: `${String(item.label)} (+${String(item.code)})`,
          icon: `img:/images/flags/${String(item.country)}.svg`
        };
      });
    }
  },
  created() {
    if (this.modelValue) {
      const phone = extractPhone(String(this.modelValue));
      if (phone) {
        this.phone = phone.phone;
        this.code = phone.code;
        this.country = phone.country;
      }
    }
  }
})
</script>

<style lang="scss">
.phone-input {
  .country-input {
    &.q-field--outlined .q-field__control {
      padding-left: 3px;
      padding-right: 0;
    }

    &.q-field--outlined .q-field__control:before {
      border: none;
    }

    &.q-field--outlined .q-field__control:after {
      border: none;
    }

    .q-field__control-container {
      display: none;
    }

    & .q-field__append {
      display: none;
    }

    .q-field__prepend {
      padding-right: 0;
    }
  }
}
</style>

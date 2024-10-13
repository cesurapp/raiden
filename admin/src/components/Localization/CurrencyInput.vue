<template>
  <q-select
    bottom-slots
    outlined
    :options="options"
    v-model="getSelected"
    :label="$t('Currency')"
    @filter="filterFn"
    clearable
    use-input
    hide-selected
    fill-input
    input-debounce="0"
    transition-duration="0"
    virtual-scroll-slice-ratio-before="12"
    virtual-scroll-slice-ratio-after="12"
  >
    <template v-slot:prepend v-if="getSelected">
      <q-icon :name="getSelected.symbol" class="currency-symbol" />
    </template>
  </q-select>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { getCurrencyOptions } from 'components/Localization/LocalizationLoader';
let currencyOptions = getCurrencyOptions();

export default defineComponent({
  name: 'CurrencyInput',
  emits: ['update:modelValue'],
  props: {
    modelValue: { type: String, required: false },
  },
  data: () => ({
    options: currencyOptions,
    selProxy: undefined,
  }),
  computed: {
    getSelected: {
      get() {
        return this.selProxy || (this.modelValue ? currencyOptions.find((c) => c.value === this.modelValue) : null);
      },
      set(sel) {
        this.selProxy = sel;
        this.$emit('update:modelValue', sel?.value);
      },
    },
  },
  methods: {
    filterFn(val, update) {
      val = val.toLowerCase();
      update(() => {
        this.options = currencyOptions.filter((c) => c.label.toLowerCase().indexOf(val) !== -1);
      });
    },
  },
});
</script>

<style lang="scss">
.currency-symbol {
  font-size: 18px;
  transform: rotateZ(-10deg);
  top: 1px;
}
</style>

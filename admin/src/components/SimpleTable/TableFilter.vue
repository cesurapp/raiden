<template>
  <div class="column items-end table-filter" :class="[$q.dark.isActive ? 'bg-dark' : 'bg-white']">
    <slot>
      <!--Q-Input-->
      <q-input
        v-model="getValue"
        v-if="filter.filter_input === 'input'"
        :label="filter.label || column.label || ''"
        :debounce="150"
        clearable
        class="q-mb-sm"
        outlined
        dense
        @clear="$emit('onSearch')"
        ref="input"
      ></q-input>

      <!--Q-Number-->
      <q-input
        v-model="getValue"
        v-if="filter.filter_input === 'number'"
        :label="filter.label || column.label || ''"
        :debounce="150"
        clearable
        autofocus
        type="number"
        class="q-mb-sm"
        outlined
        dense
        @clear="$emit('onSearch')"
        ref="input"
      ></q-input>

      <!--Q-Number-Range-->
      <div v-if="filter.filter_input === 'range'" ref="input">
        <q-input
          :model-value="getValue ? getValue.min : null"
          @update:modelValue="$emit('update:modelValue', { min: $event, max: getValue?.max })"
          :label="$t('Minimum')"
          :debounce="150"
          clearable
          autofocus
          type="number"
          class="q-mb-sm"
          outlined
          dense
          @clear="$emit('onSearch')"
          ref="inputMin"
        ></q-input>
        <q-input
          :model-value="getValue ? getValue.max : null"
          @update:modelValue="$emit('update:modelValue', { max: $event, min: getValue?.min })"
          :label="$t('Maximum')"
          :debounce="150"
          clearable
          autofocus
          type="number"
          class="q-mb-sm"
          outlined
          dense
          @clear="$emit('onSearch')"
          ref="inputMax"
        ></q-input>
      </div>

      <!--Country Select-->
      <CountryInput
        class="q-mb-sm"
        dense
        :onlyActive="false"
        v-model="getValue"
        v-if="filter.filter_input === 'country'"
        :label="filter.label || column.label || ''"
        :bottom-slots="false"
        @update:modelValue="$emit('onSearch')"
      ></CountryInput>

      <!--Language Input-->
      <LanguageInput
        v-model="getValue"
        v-if="filter.filter_input === 'language'"
        :label="filter.label || column.label || ''"
        style="min-width: 230px"
        :bottom-slots="false"
        class="q-mb-sm"
        @update:modelValue="$emit('onSearch')"
        dense
      ></LanguageInput>

      <!--Currency Input-->
      <CurrencyInput
        v-if="filter.filter_input === 'currency'"
        v-model="getValue"
        :label="filter.label || column.label || ''"
        style="min-width: 230px"
        :bottom-slots="false"
        class="q-mb-sm"
        @update:modelValue="$emit('onSearch')"
        dense
      ></CurrencyInput>

      <!--Q-Checkbox-->
      <q-checkbox
        v-model="getValue"
        v-if="filter.filter_input === 'checkbox'"
        :label="filter.label || column.label || ''"
        toggle-indeterminate
        class="q-mb-sm"
        style="min-width: 100px"
        outlined
        @update:modelValue="$emit('onSearch')"
        dense
      ></q-checkbox>

      <!--Q-Date-->
      <DateInput
        outlined
        class="q-mb-sm"
        v-model="getValue"
        v-if="filter.filter_input === 'date'"
        :label="filter.label || column.label || ''"
        @clear="$emit('update:modelValue', null)"
        @update:modelValue="$emit('onSearch')"
      ></DateInput>

      <!--Q-DateRange-->
      <DateRangeInput
        v-model="getValue"
        v-if="filter.filter_input === 'daterange'"
        :label="filter.label || column.label || ''"
        @clear="$emit('update:modelValue', null)"
        :style="['min-width: 265px']"
        class="q-mb-sm"
        outlined
        @update:modelValue="$emit('onSearch')"
      ></DateRangeInput>
    </slot>

    <!--Submit-->
    <q-btn :icon="mdiMagnify" :loading="$appStore.isBusy" size="12px" color="primary" @click="$emit('onSearch')" v-close-popup/>
  </div>
</template>

<script lang="ts">
import {defineComponent, defineAsyncComponent} from 'vue';
import {mdiMagnify, mdiCalendar} from '@quasar/extras/mdi-v7';
import {getCurrentLocale} from 'src/helper/DateHelper';

const CountryInput = defineAsyncComponent(() => import('components/Localization/CountryInput.vue'));
const LanguageInput = defineAsyncComponent(() => import('components/Language/LanguageInput.vue'));
const DateRangeInput = defineAsyncComponent(() => import('components/Date/DateRangeInput.vue'));
const DateInput = defineAsyncComponent(() => import('components/Date/DateInput.vue'));
const CurrencyInput = defineAsyncComponent(() => import('components/Localization/CurrencyInput.vue'));

export default defineComponent({
  name: 'SimpleTableFilter',
  components: {CurrencyInput, DateInput, DateRangeInput, LanguageInput, CountryInput},
  setup: () => ({mdiMagnify, mdiCalendar, getCurrentLocale}),
  emits: ['update:modelValue', 'onSearch'],
  props: {
    modelValue: [String, Boolean, Array, Object],
    filter: {
      type: Object,
      default: () => ({}),
    },
    column: {
      type: Object,
      default: () => ({}),
    },
  },
  computed: {
    getValue: {
      get() {
        return this.modelValue;
      },
      set(val) {
        this.$emit('update:modelValue', val);
      },
    },
  },
  mounted() {
    if (this.$refs.input) {
      this.$nextTick(() => this.$refs.input.focus())
    }
  },
});
</script>

<style lang="scss">
.table-filter {
  padding: 12px;
}
</style>

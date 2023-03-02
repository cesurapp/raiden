<template>
  <div class="column items-end">
    <slot>
      <!--Q-Input-->
      <q-input
        v-model="getValue"
        v-if="filter.filter_input === 'input'"
        :label="filter.label || column.label || ''"
        :debounce="75"
        clearable
        class="q-mb-sm"
        outlined
        dense
        @clear="$emit('onSearch')"
        ref='input'
      ></q-input>

      <!--Q-Number-->
      <q-input
        v-model="getValue"
        v-if="filter.filter_input === 'number'"
        :label="filter.label || column.label || ''"
        :debounce="75"
        clearable
        type="number"
        class="q-mb-sm"
        outlined
        dense
        @clear="$emit('onSearch')"
        ref='input'
      ></q-input>

      <!--Country Select-->
      <CountryInput
        v-model="getValue"
        v-if="filter.filter_input === 'country'"
        :label="filter.label || column.label || ''"
        :bottom-slots="false"
        class="q-mb-sm"
        @update:modelValue="$emit('onSearch')"
        dense
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

      <!--Q-Checkbox-->
      <q-checkbox
        v-model="getValue"
        v-if="filter.filter_input === 'checkbox'"
        :label="filter.label || column.label || ''"
        toggle-indeterminate
        class="q-mb-sm"
        outlined
        @update:modelValue="$emit('onSearch')"
        dense
      ></q-checkbox>

      <!--Q-Date-->
      <q-input
        :model-value="getValue"
        v-if="filter.filter_input === 'date'"
        :label="filter.label || column.label || ''"
        @clear="$emit('update:modelValue', null)"
        clearable
        :debounce="75"
        class="q-mb-sm"
        outlined
        dense
        @update:modelValue="$emit('onSearch')"
      >
        <template v-slot:prepend>
          <q-icon :name="mdiCalendar" class="cursor-pointer">
            <q-popup-proxy cover transition-show="scale" transition-hide="scale" ref="dateProxy">
              <q-date
                v-model="getValue"
                minimal
                @update:modelValue="
                  $refs.dateProxy.hide();
                  $emit('onSearch');
                "
                :locale="getCurrentLocale()"
              ></q-date>
            </q-popup-proxy>
          </q-icon>
        </template>
      </q-input>

      <!--Q-DateRange-->
      <q-input
        :model-value="getValue ? `${this.getValue.from} - ${this.getValue.to}` : ''"
        v-if="filter.filter_input === 'daterange'"
        :label="filter.label || column.label || ''"
        @clear="$emit('update:modelValue', null)"
        clearable
        :debounce="75"
        :style="['min-width: 250px']"
        class="q-mb-sm"
        outlined
        dense
        @update:modelValue="$emit('onSearch')"
      >
        <template v-slot:prepend>
          <q-icon :name="mdiCalendar" class="cursor-pointer">
            <q-popup-proxy cover transition-show="scale" transition-hide="scale" ref="dateProxy" class="datepopup">
              <q-date
                @update:modelValue="$emit('onSearch')"
                v-model="getValue"
                range
                minimal
                @rangeEnd="$refs.dateProxy.hide()"
                :locale="getCurrentLocale()"
              ></q-date>
            </q-popup-proxy>
          </q-icon>
        </template>
      </q-input>
    </slot>

    <!--Submit-->
    <q-btn
      :icon="mdiMagnify"
      :loading="$appStore.isBusy"
      size="12px"
      color="primary"
      @click="$emit('onSearch')"
      v-close-popup
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { mdiMagnify, mdiCalendar } from '@quasar/extras/mdi-v7';
import { getCurrentLocale } from 'src/helper/DateHelper';
import CountryInput from 'components/Country/CountryInput.vue';
import LanguageInput from 'components/Language/LanguageInput.vue';

export default defineComponent({
  name: 'SimpleTableFilter',
  components: { LanguageInput, CountryInput },
  setup: () => ({ mdiMagnify, mdiCalendar, getCurrentLocale }),
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
      setTimeout(() => this.$refs.input.focus(), 1);
    }
  }
});
</script>

<style lang="scss">
.datepopup {
  padding: 0;
  backdrop-filter: none;
}
</style>

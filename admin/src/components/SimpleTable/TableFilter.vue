<template>
  <div class='column items-end'>
    <slot>
      <!--Q-Input-->
      <q-input v-model='getValue' v-if='filter.filter_input === "input"' :label='filter.label || column.label || ""' :debounce='75' clearable class='q-mb-sm' outlined dense></q-input>

      <!--Q-Number-->
      <q-input v-model='getValue' v-if='filter.filter_input === "number"' :label='filter.label || column.label || ""' :debounce='75' clearable type='number' class='q-mb-sm' outlined dense></q-input>

      <!--Q-Checkbox-->
      <q-checkbox v-model='getValue' v-if='filter.filter_input === "checkbox"' :label='filter.label || column.label || ""' toggle-indeterminate class='q-mb-sm' outlined dense></q-checkbox>

      <!--Q-Date-->
      <q-input :model-value="getValue" @clear='$emit("update:modelValue", null)' clearable v-if='filter.filter_input === "date"' :label='filter.label || column.label || ""' :debounce='75' class='q-mb-sm' outlined dense>
        <template v-slot:prepend>
          <q-icon :name="mdiCalendar" class="cursor-pointer">
            <q-popup-proxy cover transition-show="scale" transition-hide="scale" ref="dateProxy">
              <q-date v-model='getValue' minimal @update:modelValue='$refs.dateProxy.hide()'></q-date>
            </q-popup-proxy>
          </q-icon>
        </template>
      </q-input>

      <!--Q-DateRange-->
      <q-input :model-value="getValue ? `${this.getValue.from} - ${this.getValue.to}` : ''" @clear='$emit("update:modelValue", null)' clearable v-if='filter.filter_input === "daterange"' :label='filter.label || column.label || ""' :debounce='75'  :style='["min-width: 250px"]' class='q-mb-sm' outlined dense>
        <template v-slot:prepend>
          <q-icon :name="mdiCalendar" class="cursor-pointer">
            <q-popup-proxy cover transition-show="scale" transition-hide="scale" ref="dateProxy">
              <q-date v-model='getValue' range minimal @rangeEnd='$refs.dateProxy.hide()'></q-date>
            </q-popup-proxy>
          </q-icon>
        </template>
      </q-input>
    </slot>

    <!--Submit-->
    <q-btn :icon='mdiMagnify' :loading='$appStore.isBusy' size='12px' color='primary' @click='$emit("onSearch")' v-close-popup/>
  </div>
</template>

<script lang='ts'>
import { defineComponent} from 'vue';
import { mdiMagnify, mdiCalendar } from '@quasar/extras/mdi-v7';

export default defineComponent({
  name: 'SimpleTableFilter',
  setup:() => ({ mdiMagnify, mdiCalendar }),
  emits: ['update:modelValue', 'onSearch'],
  props: {
    modelValue: [String, Boolean, Array, Object],
    filter: {
      type: Object,
      default: () => ({})
    },
    column: {
      type: Object,
      default: () => ({})
    }
  },
  computed: {
    getValue: {
      get() {
        return this.modelValue
      },
      set(val) {
        this.$emit('update:modelValue', val)
      },
    }
  }
})
</script>

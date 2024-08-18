<template>
  <q-select
    bottom-slots
    outlined
    :options="options"
    v-model="getSelected"
    :label="$t('Country')"
    clearable
    use-input
    hide-selected
    fill-input
    @filter="filterFn"
    input-debounce="0"
    ref="country"
    transition-duration="0"
    virtual-scroll-slice-ratio-before="12"
    virtual-scroll-slice-ratio-after="12"
    class="emoji-input"
  >
    <template v-slot:prepend>
      <q-icon v-if="!getSelected" :name="mdiWeb" />
      <q-icon v-else :name="getSelected.icon" class="language-emoji" />
    </template>
    <template v-slot:option="scope">
      <q-item v-bind="scope.itemProps">
        <q-item-section avatar>
          <q-icon :name="scope.opt.icon" class="language-emoji" />
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ scope.opt.label }}</q-item-label>
        </q-item-section>
      </q-item>
    </template>
  </q-select>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { mdiWeb } from '@quasar/extras/mdi-v7';
import { countries } from 'countries-list';

let countryOptions = [] as any;
Object.entries(countries).forEach(([code, country]: [string, any]) => {
  if (code !== 'default') {
    countryOptions.push({
      value: code,
      label: country.name,
      icon: country.emoji,
    });
  }
});

export default defineComponent({
  name: 'CountryInput',
  emits: ['update:modelValue'],
  setup: () => ({ mdiWeb }),
  props: {
    modelValue: { type: String, required: false },
  },
  data: () => ({
    countries: {},
    options: countryOptions,
    selectedProxied: undefined,
  }),
  computed: {
    getSelected: {
      get() {
        if (this.selectedProxied) {
          return this.selectedProxied;
        }

        return this.modelValue
          ? {
              value: this.modelValue,
              label: countries[this.modelValue].name,
              icon: countries[this.modelValue].emoji,
            }
          : null;
      },
      set(value) {
        this.selectedProxied = value;
        this.$emit('update:modelValue', value?.value);
      },
    },
  },
  methods: {
    filterFn(val, update) {
      val = val.toLowerCase();
      update(() => {
        this.options = countryOptions.filter((item) => item.label.toLowerCase().indexOf(val) !== -1);
      });
    },
  },
});
</script>

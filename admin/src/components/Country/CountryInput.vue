<template>
  <q-select
    bottom-slots
    outlined
    :options="options"
    :label="$t('Country')"
    emit-value
    map-options
    clearable
    use-input
    hide-selected
    fill-input
    input-debounce="0"
    @filter="filterFn"
    @update:modelValue="updateCountry"
    ref="country"
    transition-duration='0'
    virtual-scroll-slice-ratio-before='12'
    virtual-scroll-slice-ratio-after='12'
  >
    <template v-slot:prepend>
      <q-icon v-if="!selectedCountry" :name="mdiWeb" />
      <q-icon v-else :name="selectedCountry.icon" />
    </template>
    <template v-slot:option="scope">
      <q-item v-bind="scope.itemProps">
        <q-item-section avatar>
          <q-icon :name="scope.opt.icon" />
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

export default defineComponent({
  name: 'CountryInput',
  emits: ['update:phoneCode'],
  setup: () => ({ mdiWeb }),
  props: {
    phoneCode: [String, Number],
  },
  data: () => ({
    countries: [],
    options: [],
    selectedCountry: null,
  }),
  async created() {
    // Load Countries
    await import('countries-list/dist/countries.min.json').then((list: any) => {
      Object.entries(list).forEach(([code, country]: [string, any]) => {
        if (code !== 'default') {
          this.countries.push({
            value: code,
            label: country.name,
            icon: `img:/images/flags/${code.toLowerCase()}.svg`,
            code: country.phone,
          });
        }
      });
    });
    this.options = this.countries;

    if (this.$attrs['modelValue']) {
      this.updateCountry(this.$attrs['modelValue']);
    }
  },
  methods: {
    filterFn(val, update) {
      val = val.toLowerCase();
      update(() => {
        this.options = this.countries.filter((item) => item.label.toLowerCase().indexOf(val) !== -1);
      });
    },
    updateCountry(value) {
      this.selectedCountry = this.countries.find((c) => c.value === value);
      this.$emit('update:phoneCode', this.selectedCountry?.code);
    },
  },
});
</script>

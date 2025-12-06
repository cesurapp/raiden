<template>
  <q-select
    bottom-slots
    outlined
    :options="options"
    v-model="getSelected"
    :label="$t('Country')"
    @filter="filterFn"
    clearable
    use-input
    hide-selected
    fill-input
    input-debounce="0"
    transition-duration="0"
    virtual-scroll-slice-ratio-before="12"
    virtual-scroll-slice-ratio-after="12"
    class="emoji-input"
  >
    <template v-slot:prepend v-if="useFlag">
      <q-icon v-if="!getSelected" :name="mdiWeb" />
      <q-icon v-else :name="getSelected.icon" class="language-emoji" />
    </template>
    <template v-slot:option="scope" v-if="useFlag">
      <q-item v-bind="scope.itemProps">
        <q-item-section avatar><q-icon :name="scope.opt.icon" class="language-emoji" /></q-item-section>
        <q-item-section
        ><q-item-label>{{ scope.opt.label }}</q-item-label></q-item-section
        >
      </q-item>
    </template>
  </q-select>
</template>

<script lang="ts">
import { defineComponent, toRaw } from 'vue';
import { mdiWeb } from '@quasar/extras/mdi-v7';
import { getCountryOptions, getActiveCountryList } from 'components/Localization/LocalizationLoader';
let countryOptions = getCountryOptions();

export default defineComponent({
  name: 'CountryInput',
  emits: ['update:modelValue'],
  setup: () => ({ mdiWeb }),
  props: {
    modelValue: { type: String, required: false },
    onlyActive: { type: Boolean, required: false, default: true },
    useFlag: { type: Boolean, required: false, default: true },
  },
  data: () => ({
    options: countryOptions,
    optionsRaw: undefined,
    selProxy: undefined,
  }),
  computed: {
    getSelected: {
      get() {
        return this.selProxy || (this.modelValue ? countryOptions.find((c) => c.value === this.modelValue) : null);
      },
      set(sel) {
        this.selProxy = sel;
        this.$emit('update:modelValue', sel?.value);
      },
    },
  },
  mounted() {
    this.optionsRaw = toRaw(this.options);

    if (this.onlyActive) {
      this.optionsRaw = this.optionsRaw.filter((c) => getActiveCountryList.includes(c.value));
      this.options = this.options.filter((c) => getActiveCountryList.includes(c.value));
    }
  },
  methods: {
    filterFn(val, update) {
      val = val.toLowerCase();
      update(() => {
        this.options = this.optionsRaw.filter((c) => c.label.toLowerCase().indexOf(val) !== -1);
      });
    },
  },
});
</script>

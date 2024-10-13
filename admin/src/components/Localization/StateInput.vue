<template>
  <q-select
    :disable="!country"
    bottom-slots
    outlined
    clearable
    use-input
    hide-selected
    fill-input
    @filter="filterFn"
    :options="options"
    v-model="getSelected"
    :label="$t('State')"
    input-debounce="0"
    transition-duration="0"
    virtual-scroll-slice-ratio-before="12"
    virtual-scroll-slice-ratio-after="12"
  >
  </q-select>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { getStateOptions } from 'components/Localization/LocalizationLoader';

export default defineComponent({
  name: 'StateInput',
  emits: ['update:modelValue'],
  props: {
    modelValue: { type: String, required: false },
    country: { type: String, required: false },
  },
  data: () => ({
    options: [],
    optionsRaw: [],
    selProxy: undefined,
  }),
  computed: {
    getSelected: {
      get() {
        return this.selProxy || (this.modelValue ? this.optionsRaw.find((c) => c.value === this.modelValue) : null);
      },
      set(sel) {
        this.selProxy = sel;
        this.$emit('update:modelValue', sel?.value);
      },
    },
  },
  watch: {
    async country(val) {
      await this.loadStates(val);
    },
  },
  async mounted() {
    if (this.country) {
      await this.loadStates(this.country);
    }
  },
  methods: {
    async loadStates(cc: string | undefined) {
      if (!cc) {
        this.getSelected = null;
        this.options = [];
        this.optionsRaw = [];
        return;
      }

      const states = await getStateOptions(cc);
      this.options = states;
      this.optionsRaw = states;
    },
    filterFn(val, update) {
      val = val.toLocaleLowerCase(this.country);
      update(() => {
        this.options = this.optionsRaw.filter((c) => {
          return c.label.toLocaleLowerCase(this.country).indexOf(val) !== -1;
        });
      });
    },
  },
});
</script>

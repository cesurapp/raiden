<template>
  <div
    class="fullscreen overflow-auto text-white text-center q-pa-md flex flex-center"
    :class="[$q.dark.isActive ? 'bg-dark-page' : 'bg-blue']"
  >
    <div>
      <div class="h1 text-weight-light">404</div>
      <div class="h2 text-h4 text-weight-light">
        {{ $t('Oops. Nothing here...') }}
      </div>
      <q-btn class="q-mt-xl q-mb-xl" color="white" text-color="blue" unelevated to="/" :label="$t('Go Home')" no-caps />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { createMetaMixin } from 'quasar';
export default defineComponent({
  name: 'ErrorPage404',
  mixins: [
    createMetaMixin(function () {
      return {
        title: this.$t('404 Not Found'),
        titleTemplate: (title) => `${title} - ` + process.env.APP_TITLE,
      };
    }),
  ],
});
</script>

<style lang="scss" scoped>
.h1 {
  font-size: 10rem;
}

.h2 {
  opacity: 0.5;
}
</style>

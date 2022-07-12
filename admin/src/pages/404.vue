<template>
  <div class="fullscreen text-white text-center q-pa-md flex flex-center" :class="[$q.dark.isActive ? 'bg-dark-page' : 'bg-blue']">
    <div>
      <div class="h1">404</div>
      <div class="h2 text-h2">{{ $t('Oops. Nothing here...') }}</div>
      <q-btn class="q-mt-xl" color="white" text-color="blue" unelevated to="/" :label="$t('Go Home')" no-caps/>
    </div>
  </div>
</template>

<script lang="ts">
import {defineComponent} from 'vue'
export default defineComponent({
  name: 'ErrorPage404',
})
</script>

<style lang="scss" scoped>
.h1 {
  font-size: 20rem;
}

.h2 {
  opacity: .5;
}
</style>

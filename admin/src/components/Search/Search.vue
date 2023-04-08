<template>
  <div>
    <!--Search Bar-->
    <div id="searchBar" class="q-mx-md" @click="dialog = true">
      <q-icon :name="mdiMagnify"></q-icon>
      <span class="text">{{ $t('Search') }}</span>
    </div>

    <!--Search Dialog-->
    <q-dialog v-model="dialog" position="top" class="search-dialog" no-route-dismiss>
      <q-card style="width: 640px" class="search-card">
        <q-card-section class="input-area q-pb-sm">
          <q-input
            :debounce="100"
            autofocus
            outlined
            :placeholder="$t('Search')"
            :loading="$appStore.isBusy"
            v-model="search"
            :style="{ background: $q.dark.isActive ? 'var(--q-dark-page)' : 'white' }"
          >
            <template #prepend>
              <q-icon :name="mdiMagnify" size="28px"></q-icon>
            </template>
          </q-input>
        </q-card-section>

        <!--Help Content-->
        <q-card-section v-if="search.length < 3" class="q-pt-none content"></q-card-section>

        <!--Found Content-->
        <q-card-section v-else-if="isData || $appStore.isBusy" class="q-pt-sm content">
          <template v-for="(items, group) in data" :key="group">
            <q-list separator v-if="items.length > 0">
              <!--<div class='title q-pb-sm'>{{ $t('search.' + group) }}</div>-->
              <q-item
                ref="items"
                @click="onClickItem(item.route)"
                clickable
                v-ripple
                active-class=""
                v-for="(item, index) in items"
                :key="group + index"
              >
                <q-item-section avatar>
                  <q-icon :name="mdiFileOutline" />
                </q-item-section>
                <q-item-section>
                  <q-item-label v-if="item.label">{{ item.label }}</q-item-label>
                  <q-item-label caption lines="1" v-if="item.route">{{ item.route }}</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-icon :name="mdiArrowLeftBottom" />
                </q-item-section>
              </q-item>
            </q-list>
          </template>
        </q-card-section>

        <!--Not Found Content-->
        <q-card-section v-else>
          <div class="not-found flex column items-center justify-center">
            <q-icon :name="mdiMagnify" size="48px" color="grey-7"></q-icon>
            <p class="q-mt-sm" v-html="$t('no_result', { msg: `<b>&quot;${search}&quot;</b>` })"></p>
          </div>
        </q-card-section>

        <!--Actions-->
        <q-card-actions align="right">
          <q-btn disable push no-caps size="11px">
            <template #default
              ><b class="q-mr-xs">META+K</b><span>{{ $t('to Open') }}</span></template
            >
          </q-btn>
          <q-btn disable push no-caps size="11px">
            <template #default
              ><b class="q-mr-xs">ESC</b><span> {{ $t('to Close') }}</span></template
            >
          </q-btn>
          <q-space></q-space>
          <q-btn flat size="12px" :label="$t('Close')" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { mdiMagnify, mdiFileOutline, mdiArrowLeftBottom } from '@quasar/extras/mdi-v7';
import { useMagicKeys } from '@vueuse/core';

const magicKeys = useMagicKeys();

export default defineComponent({
  name: 'SearchBar',
  setup: () => ({ mdiMagnify, mdiFileOutline, mdiArrowLeftBottom }),
  data: () => ({
    dialog: false,
    hotkeyOpen: magicKeys['META+K'],
    arrowUpKey: magicKeys['arrowup'],
    arrowDownKey: magicKeys['arrowdown'],
    focused: -1,
    search: '',
    routes: [],
    data: {
      routes: [],
    },
  }),
  computed: {
    isData() {
      return (
        Object.values(this.data).filter((i: any) => {
          return i.length > 0;
        }).length > 0
      );
    },
  },
  mounted() {
    this.routes = this.$route.matched[0].children
      .filter((route) => {
        return !route.path.includes('/:') && route.meta.breadcrumb;
      })
      .map((route) => ({
        label: this.$t(route.meta.breadcrumb ?? ''),
        route: route.path,
      }));
  },
  watch: {
    hotkeyOpen(v) {
      if (v) {
        this.dialog = !this.dialog;
      }
    },
    arrowUpKey(v) {
      if (v && this.dialog && this.$refs.items && this.$refs.items.length > 0) {
        this.focused -= 1;
        if (this.focused <= -1) {
          this.focused = this.$refs.items.length - 1;
        }

        this.$refs.items[this.focused].$el.focus();
      }
    },
    arrowDownKey(v) {
      if (v && this.dialog && this.$refs.items && this.$refs.items.length > 0) {
        this.focused += 1;
        if (this.focused >= this.$refs.items.length) {
          this.focused = 0;
        }

        this.$refs.items[this.focused].$el.focus();
      }
    },
    search() {
      this.clearResults();
      if (this.search.length > 2) {
        this.onSearchRouter();
        this.onSearchedApi();
      }
    },
  },
  methods: {
    onClickItem(route) {
      this.$router.push({ path: route }).then(() => {
        this.dialog = false;
      });
    },

    /**
     * Clear Data
     */
    clearResults() {
      Object.keys(this.data).forEach((k) => {
        this.data[k] = [];
      });
    },

    /**
     * Search Routes
     */
    onSearchRouter() {
      this.data.routes = this.routes.filter((route) => {
        return (
          route.label.toLowerCase().includes(this.search.toLowerCase()) ||
          route.route.includes(this.search.toLowerCase())
        );
      });
    },

    /**
     * Search Api
     */
    onSearchedApi() {
      // Todo
    },
  },
});
</script>

<style lang="scss">
.search-dialog {
  .q-dialog__inner {
    padding-top: 25px !important;
  }

  .q-card {
    border-radius: 5px !important;
  }

  .search-card {
    background: $grey-3;
    display: flex;
    flex-direction: column;

    .q-card__actions {
      background: #fff;
      box-shadow: $shadow-3;
    }
  }

  .input-area {
    position: sticky;
    top: 0;
    z-index: 1;
    background: $grey-3;
  }

  .content {
    overflow: scroll;
    height: 100%;
  }

  .title {
    position: sticky;
    top: 0;
    opacity: 0.75;
    font-weight: 500;
    text-transform: capitalize;
    background: $grey-3;
    z-index: 1;
    padding-left: 14px;
  }
}

.body--dark {
  .search-dialog {
    .input-area {
      background: $dark;
    }

    .search-card {
      background: $dark;
      .q-card__actions {
        background: $dark-page;
      }
    }

    .title {
      background: $dark;
    }
  }
}

#searchBar {
  padding: 5px 18px 5px 15px;
  background: rgba(0, 0, 0, 0.1);
  border-radius: 25px;
  position: relative;
  display: flex;
  align-items: center;
  transition: 0.2s all;
  cursor: pointer;
  user-select: none;

  .q-icon {
    font-size: 22px;
    margin-right: 8px;
  }

  .text {
    font-weight: 500;
    opacity: 0.9;
  }

  &:hover {
    background: rgba(0, 0, 0, 0.2);
  }
}
</style>

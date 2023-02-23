<template>
  <q-table
    ref='table'
    :rows='rows'
    :columns='getColumns'
    :rows-per-page-options='[]'
    :loading='$appStore.isBusy'
    :selection='selectable ? "multiple" : "none"' v-model:selected='selectedRows'
    v-model:pagination='pagination'
    :selected-rows-label='(v) => ($t("count record selected").replace("count", v))'
    :no-data-label="$t('There were no results!')"
    class='table-sticky'
    :class='{ "sticky-action": rowActions }'
    @request='onRequest'
    binary-state-sort
  >
    <!--Title-->
    <template #top-left v-if='header'>
      <div class='table-title text-h4'><slot name="title">{{ $t($route.meta?.breadcrumb ?? '') }}</slot></div>
    </template>

    <!--Selected Actions-->
    <template #top-selection v-if='header'>
      <q-btn-group v-if='!$q.screen.xs'><slot name='selectedActions' :props='selectedRows'></slot></q-btn-group>
      <q-btn-dropdown
        v-else
        dropdown-icon="more_vert"
        content-class="shadow-0 transparent-dropdown"
        dense
        outline
        rounded
        color="primary"
        :menu-offset='[0,10]'
      >
        <div class="column q-gutter-sm"><slot name="selectedActions" :props='selectedRows'></slot></div>
      </q-btn-dropdown>
    </template>

    <!--Table Actions-->
    <template #top-right v-if='header'>
      <div class='row q-gutter-sm'>
        <q-btn-group v-if='!$q.screen.xs'>
          <q-btn v-if='refreshButton' color="primary" v-close-popup icon='refresh' size='12px' @click='refresh'><q-tooltip>{{ $t('Refresh') }}</q-tooltip></q-btn>
          <q-btn v-if='exportButton' color="primary" icon='download' size='12px'>
            <q-tooltip>{{ $t('Export') }}</q-tooltip>
            <q-menu :offset='[0,12]'>
              <q-list>
                <q-item clickable v-close-popup><q-item-section><q-item-label>CSV</q-item-label></q-item-section></q-item>
                <q-item clickable v-close-popup><q-item-section><q-item-label>Excel</q-item-label></q-item-section></q-item>
              </q-list>
            </q-menu>
          </q-btn>
          <slot name="tableActions"></slot>
        </q-btn-group>
        <q-btn-dropdown
          v-else
          dropdown-icon="more_vert"
          content-class="shadow-0 transparent-dropdown"
          dense
          outline
          rounded
          color="primary"
          :menu-offset='[0,10]'
        >
          <div class="column q-gutter-sm">
            <q-btn v-if='refreshButton' v-close-popup color="primary" icon='refresh' size='12px' @click='refresh'><q-tooltip>{{ $t('Refresh') }}</q-tooltip></q-btn>
            <q-btn v-if='exportButton' color="primary" icon='download' size='12px'>
              <q-tooltip>{{ $t('Export') }}</q-tooltip>
              <q-menu :offset='[0,12]'>
                <q-list>
                  <q-item clickable v-close-popup><q-item-section><q-item-label>CSV</q-item-label></q-item-section></q-item>
                  <q-item clickable v-close-popup><q-item-section><q-item-label>Excel</q-item-label></q-item-section></q-item>
                </q-list>
              </q-menu>
            </q-btn>
            <slot name="tableActions"></slot>
          </div>
        </q-btn-dropdown>
      </div>
    </template>

    <!--Row Actions-->
    <template v-if='rowActions' #body-cell-actions='props'>
      <q-td :props='props' class='actions-column'>
        <q-btn-dropdown color='primary' dropdown-icon='more_horiz' menu-anchor='bottom start' menu-self='top left' dense flat rounded @click.stop>
          <q-list><slot name='rowActions' :props='props'></slot></q-list>
        </q-btn-dropdown>
      </q-td>
    </template>

    <!--Loading-->
    <template #loading>
      <q-linear-progress dark reverse indeterminate query size='3px' color="primary" class="table-loading" :animation-speed='1' :class='{"headed": header}' />
    </template>

    <!--Checkbox-->
    <template v-slot:header-selection='scope'>
      <q-checkbox dense v-model='scope.selected' />
    </template>
    <template v-slot:body-selection='scope'>
      <q-checkbox dense v-model='scope.selected' />
    </template>

    <!--BodyCell-->
    <template #body-cell='props'>
      <q-td :props='props'>
        <span v-if="typeof props.value !== 'string' || props.value.search('<')">{{ props.value }}</span>
        <span v-else v-html='props.value'></span>
      </q-td>

      <!--Context Actions-->
      <q-menu touch-position context-menu v-if='contextActions && $slots.rowActions'>
        <q-list dense style='min-width: 100px'><slot name='rowActions' :props='props'></slot></q-list>
      </q-menu>
    </template>

    <!--Bottoms-->
    <template #pagination='props'>
      <!--View Total-->
      <div class='q-mr-md' v-if='props.pagination.isTotal'>
        {{ Math.min(props.pagination.page * props.pagination.rowsPerPage, props.pagination.rowsNumber) }} /
        {{ props.pagination.rowsNumber }}
      </div>

      <!--View Pagination Buttons-->
      <q-btn v-if='props.pagesNumber > 2 && props.pagination.isTotal' :disable='props.isFirstPage' @click='props.firstPage' icon='first_page' round dense flat />
      <q-btn :disable='props.isFirstPage' @click='props.prevPage' icon='chevron_left' round dense flat />
      <q-btn :disable='props.isLastPage' @click='props.nextPage' icon='chevron_right' round dense flat />
      <q-btn v-if='props.pagesNumber > 2 && props.pagination.isTotal' :disable='props.isLastPage' @click='props.lastPage' icon='last_page' round dense flat />
    </template>
  </q-table>
</template>

<script lang='ts'>
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'SimpleTable',
  mounted() {
    this.$refs.table.requestServerInteraction();
    this.isMounted = true
  },
  computed: {
    getColumns() {
      let all = this.tableColumns.map((c) => {
        let config = this.tableColumnConfig.hasOwnProperty(c.name) ? this.tableColumnConfig[c.name] : {}
        //return { ...c, ...{ field: c.name, label: this.$t(c.label) }, ...config };
        return { ...c, ...{ field: c.name, label: c.label, align: 'left' }, ...config };
      });

      if (this.rowActions) {
        all.unshift({
          name: 'actions',
          label: '',
          style: 'width: 10px'
        })
      }

      return all;
    }
  },
  props: {
    header: {
      type: Boolean,
      default: true
    },
    rowActions: {
      type: Boolean,
      default: true
    },
    contextActions: {
      type: Boolean,
      default: true
    },
    selectable: {
      type: Boolean,
      default: true
    },
    tableColumns: {
      type: [Array, String],
      default: () => ([])
    },
    tableColumnConfig: {
      type: Object,
    },
    refreshButton: {
      type: Boolean,
      default: true
    },
    exportButton: {
      type: Boolean,
      default: true
    },
  },
  data: () => ({
    rows: [],
    selectedRows: [],
    pagination: {
      page: 1,
      rowsPerPage: 20,
      rowsNumber: null,
      isTotal: false,
      sortBy: 'id',
      descending: false
    },
    isMounted: false,
  }),
  methods: {
    onRequest(props) {
      this.selectedRows = [];
      this.pagination = props.pagination;

      this.$api
        .accountList({
          page: props.pagination.page,
          sort_by: props.pagination.sortBy,
          sort: this.pagination.descending ? 'ASC' : 'DESC',
        })
        .then((r) => {
          // Data
          this.rows = r.data.data;

          // Paginator
          this.pagination.page = r.data.pager.current;
          this.pagination.rowsPerPage = r.data.pager.max;

          // Set Total
          if (r.data.pager.hasOwnProperty('total')) {
            this.pagination.rowsNumber = r.data.pager.total;
            this.pagination.isTotal = true;
          } else {
            this.pagination.rowsNumber = r.data.pager.next ? r.data.pager.next * r.data.pager.max : r.data.pager.current * r.data.pager.max;
            this.pagination.isTotal = false;
          }
        });
    },

    /**
     * Refresh Current Request
     */
    refresh() {
      this.$refs.table.requestServerInteraction();
    },

    /**
     * Append Row
     */
    add(...items) {
      this.rows.push(items)
    },

    /**
     * Append Row to First Line
     */
    addFirst(...items) {
      this.rows.unshift(items);
    },

    /**
     * Remove Row using Array Index
     */
    removeIndex(index) {
      this.rows.splice(index, 1)
    },

    /**
     * Remove Row using Vue Proxy Object
     */
    removeItem(proxyItem) {
      this.rows.splice(this.findIndex(proxyItem), 1)
    },

    /**
     * Remove Rows using Vue Proxy Object
     */
    removeItems(proxyItems) {
      proxyItems.forEach(itemProxy => this.remove(this.findIndex(itemProxy)))
    },

    /**
     * Find Objects Index
     */
    findIndex(itemProxy) {
      return this.rows.findIndex((row) => itemProxy === row)
    },
  }
});
</script>

<style lang='scss'>
.table-title {
  font-size: 1.8rem;
  line-height: 1.8rem;
}

.q-table__top {
  min-height: 60px;
}

.screen--xl,
.screen--lg{
  .q-table__top {
    padding-left: 24px;
    padding-right: 24px;
  }
}
</style>

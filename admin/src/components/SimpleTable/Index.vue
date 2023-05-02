<template>
  <q-table
    ref="table"
    :rows="rows"
    :columns="getColumns"
    :rows-per-page-options="[]"
    :loading="$appStore.isBusy"
    :selection="selectable ? 'multiple' : 'none'"
    v-model:selected="selectedRows"
    v-model:pagination="pagination"
    :selected-rows-label="(v) => $t('count record selected').replace('count', v)"
    :no-data-label="$t('There were no results!')"
    class="table-sticky"
    :class="{ 'sticky-action': getRowActions, 'sticky-first': !getRowActions && selectable }"
    @request="onRequest"
    @rowClick="(event, row, index) => $emit('rowClick', event, row, index)"
    binary-state-sort
  >
    <!--Top Area-->
    <template #top v-if='header'>
      <q-pull-to-refresh class='full-width' @refresh='refresh(false, $event)'>
        <div class='relative-position row items-center full-width'>
          <!--Selected Actions-->
          <div class='q-table__control' v-if="selectedRows.length > 0">
            <q-btn-group v-if="!$q.screen.xs">
              <q-btn
                color="red"
                size="12px"
                v-close-popup
                v-if="deleteProp && $authStore.hasPermission(this.deletePermission)"
                :icon="mdiDeleteOutline"
                @click="onActionRemoveAll(this.selectedRows)"
              >
                <q-tooltip>{{ $t('Delete All') }}</q-tooltip>
              </q-btn>
              <slot name="selectedActions" :props="selectedRows"></slot>
            </q-btn-group>
            <q-btn-dropdown
              v-else
              size="12px"
              padding="7px 8px"
              :dropdown-icon="mdiDotsVertical"
              content-class="shadow-0 transparent-dropdown"
              dense
              outline
              rounded
              color="primary"
              :menu-offset="[0, 10]"
            >
              <div class="column q-gutter-sm">
                <q-btn
                  color="red"
                  size="12px"
                  v-close-popup
                  v-if="deleteProp && $authStore.hasPermission(this.deletePermission)"
                  :icon="mdiDeleteOutline"
                  @click="onActionRemoveAll(this.selectedRows)"
                ><q-tooltip>{{ $t('Delete All') }}</q-tooltip></q-btn
                >
                <slot name="selectedActions" :props="selectedRows"></slot>
              </div>
            </q-btn-dropdown>
          </div>

          <!--Title-->
          <div class='q-table__control' v-else>
            <div v-if="!isFiltered" class="table-title text-h5">
              <slot name="title">{{ $t($route.meta?.breadcrumb ?? '') }}</slot>
            </div>

            <!--View Filter Parameters-->
            <template v-else>
              <q-btn-dropdown
                size="12px"
                split
                color="positive"
                :label="!$q.screen.xs ? 'Clear Filters' : ''"
                :icon="mdiFilterRemove"
                :content-style="{ padding: '6px 12px' }"
                :menu-offset="[0, 10]"
                menu-self="top start"
                menu-anchor="bottom start"
                @click="clearFilter"
              >
                <div class="flex column items-start">
                  <q-chip
                    v-for="(val, type) in filterValues"
                    :key="val"
                    clickable
                    @click="unsetFilter(type)"
                    square
                    class="q-pr-sm q-pl-sm q-mr-sm"
                  >
                    <q-tooltip>{{ $t('Click to remove') }}</q-tooltip>
                    <q-avatar :color="$q.dark.isActive ? 'primary' : 'secondary'" text-color="white" class="full-w q-px-sm">{{
                        type
                      }}</q-avatar>
                    {{ val }}
                  </q-chip>
                </div>
              </q-btn-dropdown>
            </template>
          </div>

          <div class='q-table__separator col'></div>

          <!--Table Actions-->
          <div class='q-table__control'>
            <template v-if="header">
              <div class="row q-gutter-sm">
                <q-btn-group v-if="!$q.screen.xs">
                  <q-btn v-if="refreshButton" color="primary" v-close-popup :icon="mdiRefresh" size="12px" @click="refresh"
                  ><q-tooltip>{{ $t('Refresh') }}</q-tooltip></q-btn
                  >
                  <q-btn
                    v-if="exportButton && getExportedColumns.length > 0"
                    color="primary"
                    v-close-popup
                    :icon="mdiFileExportOutline"
                    size="12px"
                    @click="$refs.exporter.toggle()"
                  ><q-tooltip>{{ $t('Export') }}</q-tooltip></q-btn
                  >
                  <slot name="tableActions"></slot>
                </q-btn-group>
                <q-btn-dropdown
                  v-else
                  :dropdown-icon="mdiDotsVertical"
                  content-class="shadow-0 transparent-dropdown"
                  size="12px"
                  outline
                  padding="7px 8px"
                  rounded
                  color="primary"
                  :menu-offset="[0, 10]"
                >
                  <div class="column q-gutter-sm">
                    <q-btn v-if="refreshButton" v-close-popup color="primary" :icon="mdiRefresh" size="12px" @click="refresh"
                    ><q-tooltip>{{ $t('Refresh') }}</q-tooltip></q-btn
                    >
                    <q-btn
                      v-if="exportButton && getExportedColumns.length > 0"
                      color="primary"
                      v-close-popup
                      :icon="mdiFileExportOutline"
                      size="12px"
                      @click="$refs.exporter.toggle()"
                    ><q-tooltip>{{ $t('Export') }}</q-tooltip></q-btn
                    >
                    <slot name="tableActions"></slot>
                  </div>
                </q-btn-dropdown>
              </div>
            </template>
          </div>
        </div>
      </q-pull-to-refresh>
    </template>

    <!--Row Actions-->
    <template v-if="getRowActions" #body-cell-actions="props">
      <q-td :props="props" class="actions-column">
        <q-btn-dropdown
          color="primary"
          :dropdown-icon="mdiDotsHorizontal"
          menu-anchor="bottom start"
          menu-self="top left"
          dense
          flat
          rounded
          @click.stop
        >
          <q-list style="min-width: 130px">
            <slot name="rowActions" :props="props"></slot>
            <q-item
              clickable
              v-close-popup
              class="text-red-5"
              v-if="deleteProp && $authStore.hasPermission(this.deletePermission)"
              @click="onActionRemoveItem(props)"
            >
              <q-item-section side><q-icon color="red-5" :name="mdiDeleteOutline" /></q-item-section>
              <q-item-section>{{ $t('Delete') }}</q-item-section>
            </q-item>
          </q-list>
        </q-btn-dropdown>
      </q-td>
    </template>

    <!--Loading-->
    <template #loading>
      <q-linear-progress
        dark
        reverse
        indeterminate
        query
        size="3px"
        color="primary"
        class="table-loading"
        :animation-speed="1"
        :class="{ headed: header }"
      />
    </template>

    <!--Checkbox-->
    <template v-slot:header-selection="scope">
      <q-checkbox dense v-model="scope.selected" />
    </template>
    <template v-slot:body-selection="scope">
      <q-checkbox dense v-model="scope.selected" />
    </template>

    <!--BodyCell-->
    <template #body-cell="props">
      <q-td :props="props">
        <span v-if="$slots['column_' + props.col.name]">
          <slot :name="'column_' + props.col.name" :props="props"></slot>
        </span>
        <span v-else>{{ props.value }}</span>
      </q-td>

      <!--Context Actions-->
      <q-popup-proxy :breakpoint='600' context-menu v-if="contextActions || $slots.getRowActions">
        <q-list dense style="min-width: 130px">
          <slot name="rowActions" :props="props"></slot>
          <q-item
            clickable
            v-close-popup
            class="text-red-5"
            v-if="deleteProp && $authStore.hasPermission(this.deletePermission)"
            @click="onActionRemoveItem(props)"
          >
            <q-item-section side><q-icon color="red-5" :name="mdiDeleteOutline" /></q-item-section>
            <q-item-section>{{ $t('Delete') }}</q-item-section>
          </q-item>
        </q-list>
      </q-popup-proxy>
    </template>

    <!--Header Cell -->
    <template #header-cell="props">
      <q-th :props="props">
        <span class="text">{{ props.col.label }}</span>

        <!--Filters-->
        <q-btn
          v-if="getColumnFilter.hasOwnProperty(props.col.name) || $slots['filter_' + props.col.name]"
          size="11px"
          class="q-ml-xs"
          flat
          dense
          rounded
          @click.stop
          :icon="![null, undefined, ''].includes(filterValues[props.col.name]) ? mdiFilter : mdiFilterOutline"
          :color="![null, undefined, ''].includes(filterValues[props.col.name]) ? 'primary' : 'default'"
          :style="[![null, undefined, ''].includes(filterValues[props.col.name]) ? 'opacity: 1' : 'opacity: .6']"
        >
          <q-popup-proxy :breakpoint='600' style='padding: 0 !important;'>
            <TableFilter
              v-if="$slots['filter_' + props.col.name]"
              :filter="getColumnFilter[props.col.name]"
              :column="props.col"
              v-model="filterValues[props.col.name]"
              @onSearch="refresh"
            >
              <slot
                :name="'filter_' + props.col.name"
                :column="props.col"
                :values="filterValues"
                :refresh="refresh"
              ></slot>
            </TableFilter>

            <TableFilter
              v-else
              :filter="getColumnFilter[props.col.name]"
              :column="props.col"
              v-model="filterValues[props.col.name]"
              @onSearch="refresh"
              @keydown.enter="refresh"
            ></TableFilter>
          </q-popup-proxy>
        </q-btn>
      </q-th>
    </template>

    <!--Bottoms-->
    <template #pagination="props">
      <!--View Total-->
      <div class="q-mr-md" v-if="props.pagination.isTotal">
        {{ Math.min(props.pagination.page * props.pagination.rowsPerPage, props.pagination.rowsNumber) }} /
        {{ props.pagination.rowsNumber }}
      </div>

      <!--View Pagination Buttons-->
      <q-btn
        v-if="props.pagesNumber > 2 && props.pagination.isTotal"
        :disable="props.isFirstPage"
        @click="props.firstPage"
        :icon="mdiPageFirst"
        round
        dense
        flat
      />
      <q-btn
        :disable="props.isFirstPage"
        :style="[props.isFirstPage ? 'opacity: 0.4 !important' : '']"
        @click="props.prevPage"
        :icon="mdiChevronLeft"
        round
        dense
        flat
      />
      <q-btn
        :disable="props.isLastPage"
        :style="[props.isLastPage ? 'opacity: 0.4 !important' : '']"
        @click="props.nextPage"
        :icon="mdiChevronRight"
        round
        dense
        flat
      />
      <q-btn
        v-if="props.pagesNumber > 2 && props.pagination.isTotal"
        :disable="props.isLastPage"
        @click="props.lastPage"
        :icon="mdiPageLast"
        round
        dense
        flat
      />
    </template>
  </q-table>

  <!--Exporter Dialog-->
  <SimpleDialog ref="exporter" v-if="exportButton" clean>
    <template #header>
      <q-avatar :icon="mdiFileExportOutline" color="primary" text-color="white" />
      <h6 class="q-ml-sm q-ma-none">{{ $t('Export') }}</h6>
    </template>
    <template #content>
      <q-option-group
        ref="exportItem"
        class="table-exporter"
        v-model="exportedColumns"
        :options="getExportedColumns"
        type="checkbox"
        inline
      />
    </template>
    <template #actions>
      <q-btn flat :label="$t('Select All')" color="primary" :icon="mdiCheckAll" @click="selectAllExportedColumns()" />
      <q-separator vertical spaced />
      <q-btn flat label="Csv" color="primary" :icon="mdiFileDelimited" v-close-popup @click="onExport('csv')" />
      <q-btn flat label="Excel" color="green" :icon="mdiFileExcel" v-close-popup @click="onExport('xls')" />
    </template>
  </SimpleDialog>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  mdiRefresh,
  mdiDotsHorizontal,
  mdiFileExportOutline,
  mdiDotsVertical,
  mdiChevronLeft,
  mdiChevronRight,
  mdiPageFirst,
  mdiPageLast,
  mdiFileDelimited,
  mdiFileExcel,
  mdiFilter,
  mdiFilterRemove,
  mdiFilterOutline,
  mdiCheckAll,
  mdiDeleteOutline,
} from '@quasar/extras/mdi-v7';
import SimpleDialog from 'components/SimpleDialog/Index.vue';
import TableFilter from 'components/SimpleTable/TableFilter.vue';
import { AxiosResponse } from 'axios';
import { deFlatten, flatten } from 'src/api/flatten';

export default defineComponent({
  name: 'SimpleTable',
  components: { SimpleDialog, TableFilter },
  emits: ['rowClick', 'rowRightClick'],
  setup: () => ({
    mdiRefresh,
    mdiDotsHorizontal,
    mdiFileExportOutline,
    mdiDotsVertical,
    mdiChevronLeft,
    mdiChevronRight,
    mdiPageFirst,
    mdiPageLast,
    mdiFileDelimited,
    mdiFileExcel,
    mdiFilter,
    mdiFilterRemove,
    mdiFilterOutline,
    mdiCheckAll,
    mdiDeleteOutline,
  }),
  props: {
    transKey: String,
    header: {
      type: Boolean,
      default: true,
    },
    rowActions: {
      type: Boolean,
      default: true,
    },
    contextActions: {
      type: Boolean,
      default: true,
    },
    selectable: {
      type: Boolean,
      default: true,
    },
    columns: {
      type: [Array, String],
      default: () => [],
    },
    refreshButton: {
      type: Boolean,
      default: true,
    },
    exportButton: {
      type: Boolean,
      default: true,
    },
    requestProp: Function,
    deleteProp: Function,
    deletePermission: String,
    updateHistory: {
      type: Boolean,
      default: true,
    },
  },
  data: () => ({
    rows: [],
    selectedRows: [],
    pagination: {
      page: 1,
      rowsPerPage: 20,
      rowsNumber: null,
      sortBy: 'id',
      descending: false,
      isTotal: false,
    },
    isMounted: false,
    exportedColumns: [],
    filterValues: {},
    backEvent: false,
    loadEvent: true,
  }),
  computed: {
    getRowActions() {
      if (!this.rowActions) {
        return false;
      }

      return !this.$q.screen.lt.md;
    },
    getColumns() {
      let all = this.columns.map((c) => {
        if (c.name.endsWith('_at')) {
          c.format = (val) => this.$appStore.formatDate(val);
        }

        return {
          ...c,
          ...{ field: c.name, label: this.$t(this.getTranslatePrefix() + c.label) },
        };
      });

      if (this.getRowActions) {
        all.unshift({
          name: 'actions',
          label: '',
          style: 'width: 10px',
        });
      }

      return all;
    },
    getExportedColumns() {
      return this.columns
        .filter((c) => c.hasOwnProperty('export') && c.export)
        .map((c) => {
          return {
            label: this.$t(this.getTranslatePrefix() + c.label),
            value: c.name,
          };
        });
    },
    getColumnFilter() {
      let filters = {};
      this.getColumns
        .filter((c) => c.hasOwnProperty('filter_input'))
        .map((c) => {
          filters[c.name] = c;
        });

      return filters;
    },
    getDefaultSortBy() {
      return this.columns.find((c) => c.hasOwnProperty('sortable_default'))?.name || 'id';
    },
    getDefaultSortDescending() {
      return this.columns.find((c) => c.hasOwnProperty('sortable_desc'))?.sortable_desc || false;
    },
    isFiltered() {
      return Object.values(this.filterValues).filter((item: any) => ! [undefined, null, ''].includes(item)).length > 0;
    },
  },
  mounted() {
    this.pagination.descending = this.getDefaultSortDescending;
    this.pagination.sortBy = this.getDefaultSortBy;
    this.isMounted = true;

    if (this.updateHistory) {
      window.onpopstate = this.historyPopstate;
    }

    this.loadQueryString(false);
    this.refresh(false);
  },
  beforeUnmount() {
    window.onpopstate = null;
  },
  methods: {
    /**
     * Table List | Sort | Filter
     */
    onRequest(props) {
      this.clearSelection();
      this.pagination = props.pagination;

      // Init Request
      if (!this.loadEvent) {
        this.loadQueryString(true);
      }

      this.requestProp(this.getQuery())
        .then((r) => this.setResponse(r))
        .finally(() => {
          if (props.pullRefresh) {
            props.pullRefresh();
          }
        });
      this.backEvent = false;
      this.loadEvent = false;
    },

    /**
     * Export to File
     */
    onExport(type) {
      this.requestProp(
        { ...this.getQuery(), ...{ export: type, export_field: this.exportedColumns } },
        { responseType: 'blob' }
      ).then((r) => this.$appStore.axiosDownloadFile(r));
    },

    /**
     * Convert Table Parameter to Backend Parameters
     */
    getQuery() {
      let data = {
        page: this.pagination.page,
        sort_by: this.pagination.sortBy,
        sort: this.pagination.descending ? 'ASC' : 'DESC',
      };

      // Add Filter
      if (Object.values(this.getColumnFilter).length > 0) {
        let items = {};
        Object.entries(this.filterValues).forEach(([key, value]) => {
          if (value !== null && value !== undefined && value !== '') {
            items[key] = value;
          }
        });

        data['filter'] = items;
      }

      return data;
    },

    /**
     * Init Response
     */
    setResponse(r: AxiosResponse) {
      this.rows = r.data.data;

      // Paginator
      this.pagination.page = r.data.pager.current;
      this.pagination.rowsPerPage = r.data.pager.max;
      this.pagination.rowsNumber = r.data.pager.total || null;
      this.pagination.isTotal = true;

      // Set Total
      if (!r.data.pager.hasOwnProperty('total')) {
        this.pagination.rowsNumber = r.data.pager.next
          ? r.data.pager.next * r.data.pager.max
          : r.data.pager.current * r.data.pager.max;
        this.pagination.isTotal = false;
      }
    },

    /**
     * Refresh Current Request
     */
    refresh(checkPagination = true, pullRefresh) {
      if (checkPagination && this.rows.length === 0 && this.pagination.page > 1) {
        this.pagination.page--;
      }

      this.onRequest({ pagination: this.pagination, pullRefresh })
    },

    /**
     * Append Row
     */
    add(...items) {
      this.rows.push(items);
    },

    /**
     * Append Row to First Line
     */
    addFirst(items) {
      this.rows.unshift(items);
    },

    /**
     * Remove Row using Array Index
     */
    removeIndex(index) {
      this.rows.splice(index, 1);
    },

    /**
     * Remove Row using Vue Proxy Object
     */
    removeItem(itemProxy) {
      this.rows.splice(this.findIndex(itemProxy), 1);
    },

    updateItem(item, key) {
      const index = this.rows.findIndex((row) => row[key] === item[key]);
      if (index !== -1) {
        this.rows[index] = item;
      }
    },

    /**
     * Find Objects Index
     */
    findIndex(itemProxy) {
      return this.rows.findIndex((row) => row === itemProxy);
    },

    /**
     * Remove Row using Vue Proxy Object
     */
    removeSelection(itemProxy) {
      this.selectedRows.splice(this.findSelectedIndex(itemProxy), 1);
    },

    /**
     * Find Objects Index
     */
    findSelectedIndex(itemProxy) {
      return this.selectedRows.findIndex((row) => row === itemProxy);
    },

    /**
     * Clear Selected Items
     */
    clearSelection() {
      this.$refs.table.clearSelection();
    },

    /**
     * Select All Exported Columns
     */
    selectAllExportedColumns() {
      this.getExportedColumns.forEach((c) => {
        if (!this.exportedColumns.includes(c.value)) {
          this.exportedColumns.push(c.value);
        }
      });
    },

    /**
     * Selected Remove All to Backend
     */
    onActionRemoveAll(props) {
      this.$appStore.confirmDeleteAll().then(() => {
        props.forEach((item) => this.deleteProp(item).then(() => this.removeItem(item)));
        this.clearSelection();
      });
    },

    /**
     * Remove Single Item to Backend
     */
    onActionRemoveItem(props) {
      this.$appStore.confirmDelete().then(() => {
        this.deleteProp(props.row).then(() => {
          this.removeItem(props.row);
          this.removeSelection(props.row);
        });
      });
    },

    /**
     * Generate Table Translate Prefix
     */
    getTranslatePrefix() {
      return this.$i18n.locale === 'en-US' ? '' : this.transKey + '.';
    },

    /**
     * Request Params to URL String
     */
    loadQueryString(updateHash) {
      if (!this.updateHistory || this.backEvent) {
        return;
      }

      if (updateHash) {
        return this.$router.push({ query: flatten(this.getQuery()) });
      }

      const params = deFlatten(location.search);
      this.filterValues = params.filter || {};
      this.pagination = {
        ...this.pagination,
        ...{
          descending: params.sort ? params.sort.toUpperCase() === 'ASC' : this.pagination.descending,
          page: params.page || this.pagination.page,
          sortBy: params.sort_by || this.pagination.sortBy,
        },
      };
    },

    /**
     * History Back Event
     */
    historyPopstate() {
      this.loadQueryString(false);
      this.backEvent = true;
      this.refresh(false);
    },

    /**
     * Remove Filter Item
     */
    unsetFilter(type) {
      delete this.filterValues[type];
      this.refresh();
    },

    /**
     * Clear Filters
     */
    clearFilter() {
      this.filterValues = {};
      this.refresh();
    },
  },
});
</script>

<style lang="scss">
.table-title {
  // font-size: 1.8rem;
  // line-height: 1.8rem;
  overflow: hidden;
  text-overflow: ellipsis;
}

.q-table__top {
  min-height: 60px;
  flex-wrap: nowrap;

  .q-table__control{
    overflow: hidden;
    white-space: nowrap;
    display: block;
  }
}

.screen--xl,
.screen--lg {
  .q-table__top {
    padding-left: 24px;
    padding-right: 24px;
  }
}

.table-exporter {
  display: flex;
  flex-flow: row wrap;
  & > div {
    flex: 1 0 47%;
    min-width: 170px;
  }
}
</style>

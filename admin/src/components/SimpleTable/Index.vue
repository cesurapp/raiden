<template>
  <q-table
    ref="table"
    :rows="rows"
    :columns="getColumns"
    :rows-per-page-options="[]"
    :loading="$appStore.isBusy"
    :selection="selectable ? 'multiple' : 'none'"
    v-model:selected="selectedRows"
    v-model:pagination="pagination as any"
    :selected-rows-label="(v) => $t('count record selected').replace('count', String(v))"
    :no-data-label="$t('There were no results!')"
    :hide-pagination="!isPaginate"
    class="table-sticky"
    :class="{ 'sticky-action': getRowActions, 'sticky-first': !getRowActions && selectable, 'no-data': rows.length === 0 }"
    @request="onRequest"
    @rowClick="(event, row, index) => $emit('rowClick', event, row, index)"
    @rowDblclick="(event, row, index) => $emit('rowDblclick', event, row, index)"
    @rowContextmenu="onRowContextMenu"
    binary-state-sort
  >
    <!--Top Area-->
    <template #top v-if="header">
      <q-pull-to-refresh class="full-width" @refresh="refresh(false, $event)">
        <div class="relative-position row items-center full-width no-wrap">
          <q-btn v-if="titleCloseButton" :icon="mdiClose" v-close-popup round dense flat style="margin-right: 10px" />
          <NavigationToggle v-else></NavigationToggle>

          <!--Selected Actions-->
          <div class="q-table__control" v-if="selectedRows.length > 0">
            <q-btn-group v-if="!$q.screen.xs" unelevated>
              <q-btn
                color="red"
                size="12px"
                v-close-menu
                v-if="deleteProp && $authStore.hasPermission(deletePermission as string)"
                :icon="mdiDeleteOutline"
                @click="onActionRemoveAll(selectedRows)"
              >
                <q-tooltip>{{ $t('Delete All') }}</q-tooltip>
              </q-btn>
              <slot name="selectedActions" :props="selectedRows"></slot>
            </q-btn-group>
            <q-btn-dropdown
              v-else
              unelevated
              size="12px"
              padding="5px 6px"
              :dropdown-icon="mdiDotsVertical"
              content-class="shadow-0 transparent-dropdown"
              dense
              outline
              rounded
              :menu-offset="[0, 10]"
            >
              <div class="column q-gutter-sm">
                <q-btn
                  color="red"
                  unelevated
                  size="12px"
                  v-close-menu
                  v-if="deleteProp && $authStore.hasPermission(deletePermission as string)"
                  :icon="mdiDeleteOutline"
                  @click="onActionRemoveAll(selectedRows)"
                ><q-tooltip>{{ $t('Delete All') }}</q-tooltip></q-btn
                >
                <slot name="selectedActions" :props="selectedRows"></slot>
              </div>
            </q-btn-dropdown>
          </div>

          <!--Title-->
          <div class="q-table__control ellipsis" v-else>
            <div v-if="!isFiltered" class="table-title text-h6">
              <slot name="title">{{ $t($route.meta?.breadcrumb ?? '') }}</slot>
            </div>

            <!--View Filter Parameters-->
            <template v-else>
              <q-btn-dropdown
                size="12px"
                split
                unelevated
                color="positive"
                :label="!$q.screen.xs ? $t('Clear Filters') : ''"
                :icon="mdiFilterRemove"
                :content-style="{ padding: '6px 12px' }"
                :menu-offset="[0, 10]"
                menu-self="top start"
                menu-anchor="bottom start"
                @click="clearFilter"
              >
                <div class="flex column items-start">
                  <q-chip
                    v-for="(val, type, index) in filterValues"
                    :key="`filter-${type}-${index}`"
                    clickable
                    @click="unsetFilter(type)"
                    square
                    class="q-pr-sm q-pl-sm q-mr-sm"
                  >
                    <q-tooltip>{{ $t('Click to remove') }}</q-tooltip>
                    <q-avatar :color="$q.dark.isActive ? 'primary' : 'secondary'" text-color="white" class="full-w q-px-sm">{{ type }}</q-avatar>
                    {{ val }}
                  </q-chip>
                </div>
              </q-btn-dropdown>
            </template>
          </div>

          <div class="q-table__separator col"></div>

          <!--Table Actions-->
          <div class="q-table__control">
            <template v-if="header">
              <div class="row q-gutter-sm">
                <q-btn-group flat v-if="!$q.screen.xs">
                  <q-btn color="primary" v-if="refreshButton" v-close-menu size="12px" :icon="mdiRefresh" @click="refresh(true, false)"
                  ><q-tooltip>{{ $t('Refresh') }}</q-tooltip>
                  </q-btn>
                  <q-btn
                    v-if="exportButton && getExportedColumns.length > 0"
                    color="primary"
                    v-close-menu
                    :icon="mdiFileExportOutline"
                    size="12px"
                    @click="($refs.exporter as any).toggle()"
                  ><q-tooltip>{{ $t('Export') }}</q-tooltip>
                  </q-btn>
                  <slot name="tableActions"></slot>
                </q-btn-group>
                <q-btn-dropdown
                  v-else
                  :dropdown-icon="mdiDotsVertical"
                  content-class="shadow-0 transparent-dropdown"
                  size="12px"
                  outline
                  padding="5px 6px"
                  rounded
                  :menu-offset="[0, 10]"
                >
                  <div class="column q-gutter-sm">
                    <q-btn v-if="refreshButton" v-close-menu color="primary" :icon="mdiRefresh" size="12px" @click="refresh">
                      <q-tooltip>{{ $t('Refresh') }}</q-tooltip>
                    </q-btn>
                    <q-btn
                      v-if="exportButton && getExportedColumns.length > 0"
                      color="primary"
                      v-close-menu
                      :icon="mdiFileExportOutline"
                      size="12px"
                      @click="($refs.exporter as any).toggle()">
                      <q-tooltip>{{ $t('Export') }}</q-tooltip>
                    </q-btn>
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
          :dropdown-icon="mdiDotsHorizontal"
          menu-anchor="bottom start"
          color="primary" style="margin: -1px 0"
          menu-self="top left"
          size="13px" dense flat rounded autoClose
          @click.stop
        >
          <q-list style="min-width: 130px">
            <slot name="rowActions" :props="props"></slot>
            <q-item
              clickable class="text-red-5"
              v-if="deleteProp && $authStore.hasPermission(deletePermission as any)"
              @click="onActionRemoveItem(props)"
            >
              <q-item-section side><q-icon color="red-5" :name="mdiDeleteOutline" /></q-item-section>
              <q-item-section>{{ $t('Delete') }}</q-item-section>
            </q-item>
          </q-list>
        </q-btn-dropdown>
      </q-td>
    </template>

    <!--Column Config-->
    <template #header-cell-actions>
      <q-th class="actions-column">
        <q-btn dense flat size="12px" @click="($refs.tableConfig as any).toggle()" :icon="mdiFilterCogOutline" style="opacity: .6"></q-btn>
      </q-th>
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
        <template v-if="columnSlots.has(props.col.name)">
          <slot :name="'column_' + props.col.name" :props="props"></slot>
        </template>
        <template v-else>{{ props.value }}</template>
      </q-td>
    </template>

    <!--Header Cell -->
    <template #header-cell="props">
      <q-th :props="props">
        <div class="inline-flex no-wrap items-center vertical-middle" :class="getColumnFilter.hasOwnProperty(props.col.name) ? 'hasfilter' : ''">
          <!--Filters-->
          <q-btn
            v-if="getColumnFilter.hasOwnProperty(props.col.name) || $slots['filter_' + props.col.name]"
            size="9px"
            class="filters"
            flat
            dense
            rounded
            @click.stop
            v-bind="bindFilterAttr(props.col)"
          >
            <q-popup-proxy :breakpoint="600" style="padding: 0 !important">
              <TableFilter
                v-if="$slots['filter_' + props.col.name]"
                :filter="getColumnFilter[props.col.name]"
                :column="props.col"
                v-model="filterValues[props.col.name]"
                @onSearch="refresh"
              >
                <slot :name="'filter_' + props.col.name" :column="props.col" :values="filterValues" :refresh="refresh"></slot>
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

          <span class="text">{{ props.col.label }}</span>
        </div>
      </q-th>
    </template>

    <!--Bottoms-->
    <template #pagination="props" v-if="isPaginate">
      <!--View Total-->
      <div class="q-mr-md" v-if="props.pagination.isTotal">
        {{ Math.min(props.pagination.page * props.pagination.rowsPerPage, props.pagination.rowsNumber) }} /
        {{ props.pagination.rowsNumber }}
      </div>

      <div class="pageCount q-mr-md">
        <q-select
          dense
          emit-value
          borderless
          hide-dropdown-icon
          hide-bottom-space
          map-options
          hide-hint
          style="height: 30px; margin-top: -7px"
          :options="['Auto', 10, 20, 40, 50, 100]"
          v-model="perPageState"
          @update:modelValue="(v) => onPerPage(v, true)"
        />
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

  <!--Context Actions-->
  <div>
    <q-menu autoClose touchPosition :transitionDuration="150"  ref="contextMenu" v-if="contextActions || $slots.getRowActions">
      <q-list style="min-width: 130px">
        <slot name="rowActions" :props="contextMenuProps"></slot>
        <q-item
          clickable
          v-close-popup
          class="text-red-5"
          v-if="deleteProp && $authStore.hasPermission(deletePermission as string)"
          @click="onActionRemoveItem(contextMenuProps)"
        >
          <q-item-section side><q-icon color="red-5" :name="mdiDeleteOutline" /></q-item-section>
          <q-item-section>{{ $t('Delete') }}</q-item-section>
        </q-item>
      </q-list>
    </q-menu>
  </div>

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
      <q-separator vertical spaced inset />
      <q-btn flat label="Csv" color="primary" :icon="mdiFileDelimited" v-close-popup @click="onExport('csv')" />
      <div>
        <q-btn flat label="Excel" color="green" :icon="mdiFileExcel" v-close-popup @click="onExport('xls')" />
      </div>
    </template>
  </SimpleDialog>

  <!--Table Config Dialog-->
  <SimpleDialog ref="tableConfig" clean>
    <template #header>
      <q-avatar :icon="mdiFilterCogOutline" color="primary" text-color="white" />
      <h6 class="q-ml-sm q-ma-none">{{ $t('Column Config') }}</h6>
    </template>
    <template #content>
      <div class="row q-mb-sm">
        <div v-for="col in columns" :key="col.name" class="q-mb-none col-12 col-sm-6">
          <q-checkbox :modelValue="!excludedColumns.includes(col.name)"
                      @update:modelValue="(val) => {
            if (val) {
              excludedColumns = excludedColumns.filter((c) => c !== col.name);
            } else {
              excludedColumns.push(col.name);
            }
          }"
          >{{ $tt(transKey, col.label) }}</q-checkbox>
        </div>
      </div>
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
  mdiFilterCogOutline,
  mdiClose
} from '@quasar/extras/mdi-v7';
import SimpleDialog from 'components/SimpleDialog/Index.vue';
import TableFilter from 'components/SimpleTable/TableFilter.vue';
import { AxiosResponse } from 'axios';
import { deFlatten, flatten } from '@api/flatten';
import NavigationToggle from 'components/Layout/NavigationToggle.vue';
import { LocalStorage } from 'quasar';

export default defineComponent({
  name: 'SimpleTable',
  components: { NavigationToggle, SimpleDialog, TableFilter },
  emits: ['rowClick', 'rowRightClick', 'rowDblclick'],
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
    mdiFilterRemove,
    mdiCheckAll,
    mdiDeleteOutline,
    mdiFilterCogOutline,
    mdiClose
  }),
  props: {
    uId: String,
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
    exportColumns: {
      type: [Array, String],
      default: null,
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
    excludedCols: {
      type: [Array, String],
      default: null,
    },
    titleCloseButton: {
      type: Boolean,
      default: false,
    },
    isPaginate: {
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
    perPageState: LocalStorage.getItem('rowsPerPage') ?? 'Auto',
    excludedColumns: ['id'],
    contextMenuProps: null as any,
    columnSlotsCache: null as Map<string, boolean> | null,
  }),
  watch: {
    'excludedColumns': {
      handler(val) {
        if (!this.isMounted) return;
        LocalStorage.setItem(this.uId + '_excols', val);
      },
      deep: true
    }
  },
  computed: {
    columnSlots() {
      if (this.columnSlotsCache) return this.columnSlotsCache;

      const slotMap = new Map<string, boolean>();
      if (this.$slots) {
        Object.keys(this.$slots).forEach(slotName => {
          if (slotName.startsWith('column_')) {
            slotMap.set(slotName.replace('column_', ''), true);
          }
        });
      }

      this.columnSlotsCache = slotMap;
      return slotMap;
    },
    getRowActions() {
      if (!this.rowActions) {
        return false;
      }

      return !this.$q.screen.lt.md;
    },
    getColumns() {
      const all = [];

      if (this.getRowActions) {
        all.push({
          name: 'actions',
          label: '',
          style: 'width: 10px',
        } as never);
      }

      const mappedCols = this.columns.filter(c => !this.excludedColumns.includes(c.name)).map((c) => {
        const col = {
          ...c,
          field: c.name,
          label: this.$tt(this.transKey, c.label)
        };

        if (c.name.endsWith('_at') && !c.format) {
          col.format = (val) => this.$appStore.formatDate(val);
        }

        return col;
      });

      return all.concat(mappedCols);
    },
    getExportedColumns() {
      return (this.exportColumns || this.columns)
        .filter((c) => c.hasOwnProperty('export') && c.export)
        .map((c) => {
          return {
            label: this.$tt(this.transKey, c.label),
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
      return this.columns.find((c) => c.name === this.getDefaultSortBy)?.sortable_desc || false;
    },
    isFiltered() {
      return Object.values(this.filterValues).filter((item: any) => ![undefined, null, ''].includes(item)).length > 0;
    },
  },
  beforeMount() {
    if (this.excludedCols) {
      this.excludedColumns = this.excludedCols;
    }

    this.loadColumnConfig();
  },
  mounted() {
    this.onPerPage(this.perPageState);
    this.pagination.descending = !this.getDefaultSortDescending;
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
    onRowContextMenu(event: MouseEvent, row: any, index: number) {
      if (!this.contextActions && !this.$slots.rowActions) return;
      event.preventDefault();
      event.stopPropagation();

      this.contextMenuProps = {row, rowIndex: index, cols: this.getColumns,};

      this.$nextTick(() => {
        //(this.$refs.contextMenu as any).hide(event);
        (this.$refs.contextMenu as any).show(event);
      });
    },

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
        { responseType: 'blob' },
      ).then((r) => this.$appStore.axiosDownloadFile(r));
    },

    /**
     * Convert Table Parameter to Backend Parameters
     */
    getQuery() {
      let data = {
        sort_by: this.pagination.sortBy,
        sort: this.pagination.descending ? 'ASC' : 'DESC',
      } as any;

      if (this.isPaginate) {
        data.max = this.pagination.rowsPerPage;
        data.page = this.pagination.page;
      }

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
      if (this.isPaginate && r.data.pager) {
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
      } else {
        this.pagination.rowsNumber = this.rows.length;
        this.pagination.rowsPerPage = 0;
        this.pagination.isTotal = true;
      }
    },

    /**
     * Refresh Current Request
     */
    refresh(checkPagination = true, pullRefresh) {
      if (checkPagination && this.rows.length === 0 && this.pagination.page > 1) {
        this.pagination.page--;
      }

      this.onRequest({ pagination: this.pagination, pullRefresh });
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

    /**
     * Update Item
     */
    updateItem(item, key) {
      const index = this.rows.findIndex((row) => row[key] === item[key]);
      if (index !== -1) {
        this.rows[index] = item;
        return true;
      }

      return false;
    },

    /**
     * Update Partial Item Properties
     */
    updatePartial(item, key) {
      const index = this.rows.findIndex((row) => row[key] === item[key]);
      if (index !== -1) {
        Object.keys(item).forEach((prop) => {
          if (this.rows[index].hasOwnProperty(prop)) {
            this.rows[index][prop] = item[prop];
          }
        });
        return true;
      }

      return false;
    },

    addOrUpdateItem(items, key: string | undefined = 'id') {
      if (Array.isArray(items)) {
        items.forEach((i) => this.addOrUpdateItem(i, key));
      }

      if (!this.updateItem(items, key)) {
        this.addFirst(items);
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
      if (this.exportedColumns.length === this.getExportedColumns.length) {
        this.exportedColumns = [];
      } else {
        this.getExportedColumns.forEach((c) => {
          if (!this.exportedColumns.includes(c.value)) {
            this.exportedColumns.push(c.value);
          }
        });
      }
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
     * Request Params to URL String
     */
    loadQueryString(updateHash) {
      if (!this.updateHistory || this.backEvent) {
        return;
      }

      if (updateHash) {
        return this.$router.push({ query: flatten(this.getQuery()), hash: new URL(location.href).hash });
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

    /**
     * Remove Filter Item
     */
    addFilter(type, data) {
      this.pagination.page = 1;
      this.filterValues[type] = data;
      this.refresh();
    },

    /**
     * Load PerPage Count
     */
    onPerPage(val: number | string, triggerRequest: boolean = false) {
      if (!['Auto', 10, 20, 40, 50, 100].includes(val)) {
        val = 'Auto';
        this.perPageState = val;
      }

      const dialogPadding = this.$el.parentNode.closest('.q-dialog') ? 48 : 0;
      const headerHeight = document.querySelector('.page-header')?.clientHeight ?? 52;
      this.pagination.rowsPerPage = val === 'Auto' ? Math.ceil(((document.body.clientHeight - (headerHeight + dialogPadding + 40 + 40)) / 40) - 1) : val;
      LocalStorage.setItem('rowsPerPage', val);

      if (triggerRequest) {
        this.pagination.page = 1;
        this.refresh(false);
      }
    },

    /**
     * Bind Filter Attributes
     */
    bindFilterAttr(col) {
      let result = ![null, undefined, ''].includes(this.filterValues[col.name]);
      if (col.hasOwnProperty('filter_cols')) {
        result = col.filter_cols.some((colName) => ![null, undefined, ''].includes(this.filterValues[colName]));
      }

      return {
        icon: result ? mdiFilter : mdiFilterOutline,
        color: result ? 'primary' : 'default',
        style: [result ? 'opacity: 1' : 'opacity: .6'],
      };
    },

    /**
     * Load Column Config from Local Storage
     */
    loadColumnConfig() {
      // Excluded Columns
      const excluded = LocalStorage.getItem(this.uId + '_excols');
      if (excluded) {
        this.excludedColumns = excluded;
      }
    },
  },
});
</script>

<style lang="scss">
.table-title {
  overflow: hidden;
  text-overflow: ellipsis;
  font-size: 1.15rem;
}

.q-table__top {
  min-height: var(--header-size);
  flex-wrap: nowrap;
  background: var(--q-light);
  z-index: 3;
  padding-bottom: 6px;
  padding-top: max(env(safe-area-inset-top), 6px);
  padding-left: calc(env(safe-area-inset-left) / 2 + 16px);
  padding-right: calc(env(safe-area-inset-right) / 2 + 16px);

  .body--dark & {
    //border-bottom: 1px solid rgba(255, 255, 255, 0.13);
    background: var(--q-dark);
  }
}
.screen--xl,
.screen--lg {
  .q-table__top {
    padding-left: calc(env(safe-area-inset-left) / 2 + 24px);
    padding-right: calc(env(safe-area-inset-right) / 2 + 24px);
  }
}

.table-exporter {
  display: grid;
  grid-template-columns: 1fr;
  @media (min-width: 500px) {
    grid-template-columns: repeat(2, minmax(170px, 1fr));
  }
  @media (min-width: $breakpoint-sm-min) {
    grid-template-columns: repeat(3, minmax(170px, 1fr));
  }
}
</style>

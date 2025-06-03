// assets/js/app.js
import { createApp } from 'vue';

// Create Vue application
const app = createApp({
  data() {
    return {
      selectedCustomer: null,
      selectedDevice: null,
      debugOn: true
    };
  },
  methods: {
    onCustomerSelected(code) {
      this.selectedCustomer = code;
      this.selectedDevice = null; // Reset drilldown when customer changes
    },
    onDeviceSelected(deviceId) {
      this.selectedDevice = deviceId;
    }
  }
});

// Blank Module
app.component('blank-module', {
  template: `
    <div style="padding:1rem; margin:1rem; background: var(--surface); box-shadow: var(--glass-shadow);">
      <h2>Blank Module Loaded</h2>
      <p>If you see this, module wiring works.</p>
    </div>
  `
});

// CustomerSelect Module
app.component('customer-select', {
  template: \`
    <div>
      <label>Select Customer:</label>
      <select v-model="current" @change="emitSelection" style="margin-left:0.5rem; padding:0.25rem;">
        <option disabled value="">-- Choose --</option>
        <option v-for="cust in customers" :key="cust.CustomerCode" :value="cust.CustomerCode">
          {{ cust.Name }} ({{ cust.CustomerCode }})
        </option>
      </select>
    </div>
  \`,
  data() {
    return {
      current: '',
      customers: []
    };
  },
  mounted() {
    fetch('/modules/CustomerSelect/CustomerSelect.php')
      .then(r => r.json())
      .then(data => {
        this.customers = data.customers || [];
      })
      .catch(err => console.error('CustomerFetchError:', err));
  },
  methods: {
    emitSelection() {
      this.$emit('selected', this.current);
    }
  }
});

// DeviceList Module
app.component('device-list', {
  props: ['customer'],
  template: \`
    <div>
      <h2>Devices for {{ customer }}</h2>
      <column-toggle v-if="columnsToggled">
        <label v-for="col in allColumns" :key="col.key">
          <input type="checkbox" v-model="visibleColumns" :value="col.key" /> {{ col.label }}
        </label>
      </column-toggle>

      <table class="mpsm-table">
        <thead>
          <tr>
            <th v-for="col in displayedColumns" @click="sortBy(col.key)" class="sortable">
              {{ col.label }}
              <span class="sort-indicator" v-if="sortKey === col.key">
                {{ sortDir === 'asc' ? '▲' : '▼' }}
              </span>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="device in devices" :key="device.Id">
            <td v-for="col in displayedColumns" @click="col.key==='SEID' && selectDevice(device.Id)">
              {{ displayCell(device, col.key) }}
            </td>
          </tr>
        </tbody>
      </table>

      <div class="pagination">
        <button @click="changePage(page - 1)" :disabled="page <= 1">Prev</button>
        <span>Page {{ page }} / {{ totalPages }}</span>
        <button @click="changePage(page + 1)" :disabled="page >= totalPages">Next</button>
      </div>
    </div>
  \`,
  data() {
    return {
      devices: [],
      page: 1,
      rows: 10,
      total: 0,
      sortKey: '',
      sortDir: 'asc',
      columnsToggled: true,
      allColumns: [
        { key: 'SEID', label: 'SEID' },
        { key: 'Brand', label: 'Brand' },
        { key: 'Model', label: 'Model' },
        { key: 'SerialNumber', label: 'Serial Number' },
        { key: 'IpAddress', label: 'IP Address' }
      ],
      visibleColumns: ['SEID', 'Brand', 'Model', 'SerialNumber', 'IpAddress']
    };
  },
  computed: {
    displayedColumns() {
      return this.allColumns.filter(c => this.visibleColumns.includes(c.key));
    },
    totalPages() {
      return Math.ceil(this.total / this.rows) || 1;
    }
  },
  watch: {
    customer: 'fetchDevices',
    page:     'fetchDevices',
    rows:     'fetchDevices',
    sortKey:  'fetchDevices',
    sortDir:  'fetchDevices'
  },
  methods: {
    fetchDevices() {
      const params = new URLSearchParams({
        customer: this.customer,
        page: this.page,
        rows: this.rows,
        sortCol: this.sortKey,
        sortDir: this.sortDir
      });
      fetch(\`/modules/DeviceList/DeviceList.php?\${params.toString()}\`)
        .then(r => r.json())
        .then(data => {
          this.devices = data.devices || [];
          this.total   = data.total   || 0;
        })
        .catch(err => console.error('DeviceListFetch:', err));
    },
    displayCell(device, key) {
      if (key === 'SEID') {
        return device.AssetNumber || device.ExternalIdentifier || '';
      }
      return device[key] ?? '';
    },
    sortBy(key) {
      if (this.sortKey === key) {
        this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortKey = key;
        this.sortDir = 'asc';
      }
    },
    changePage(p) {
      if (p < 1 || p > this.totalPages) return;
      this.page = p;
    },
    selectDevice(id) {
      this.$emit('view-device', id);
    }
  },
  mounted() {
    this.fetchDevices();
  }
});

// DeviceDrill Module
app.component('device-drill', {
  props: ['deviceId'],
  template: \`
    <div class="glass-panel" v-if="details">
      <h3>Device Details: {{ deviceId }}</h3>
      <pre>{{ JSON.stringify(details, null, 2) }}</pre>
    </div>
  \`,
  data() {
    return {
      details: null
    };
  },
  watch: {
    deviceId: 'fetchDetails'
  },
  methods: {
    fetchDetails() {
      fetch(\`/modules/DeviceDrill/DeviceDrill.php?deviceId=\${this.deviceId}\`)
        .then(r => r.json())
        .then(data => {
          this.details = data.details || {};
        })
        .catch(err => console.error('DeviceDrillFetch:', err));
    }
  },
  mounted() {
    this.fetchDetails();
  }
});

// ColumnToggle Helper
app.component('column-toggle', {
  template: \`<div class="column-toggle"><slot /></div>\`
});

// Debug Panel
app.component('debug-panel', {
  template: \`
    <div id="debug-panel">
      <h4>Debug Log</h4>
      <pre>{{ logText }}</pre>
    </div>
  \`,
  data() {
    return {
      logText: ''
    };
  },
  methods: {
    fetchLogs() {
      fetch('/modules/DebugPanel/DebugPanel.php')
        .then(r => r.text())
        .then(txt => {
          this.logText = txt;
        })
        .catch(err => console.error('DebugFetch:', err));
    }
  },
  mounted() {
    this.fetchLogs();
    setInterval(this.fetchLogs, 5000);
  }
});

app.mount('#app');

// modules/customers.js
// v2.2.3 [Fix: Use debug, store, eventBus, dom; render dropdown on load]

import debug from '../core/debug.js';
import { eventBus } from '../core/event-bus.js';
import { store } from '../core/store.js';
import { get } from '../core/dom.js';

export async function loadCustomers() {
  debug.log('Customers: loading list');
  try {
    const res = await fetch('./get_customers.php');
    const data = await res.json();
    if (!Array.isArray(data.Result)) {
      throw new Error('Invalid customer list');
    }
    const customers = data.Result;
    store.set('customers', customers);
    eventBus.emit('customers:loaded', customers);
    debug.log(`Customers: loaded ${customers.length}`);
    renderDropdown(customers);
  } catch (err) {
    debug.error(`Customers: load failed: ${err.message}`);
  }
}

function renderDropdown(customers) {
  const app = get('app');
  app.innerHTML = '';

  const wrapper = document.createElement('div');
  wrapper.className = 'dropdown';

  const input = document.createElement('input');
  input.placeholder = 'Type to search customers…';
  wrapper.appendChild(input);

  const clearBtn = document.createElement('button');
  clearBtn.textContent = '✕';
  clearBtn.className = 'clear-btn';
  clearBtn.onclick = () => { input.value = ''; updateList(); input.focus(); };
  wrapper.appendChild(clearBtn);

  const list = document.createElement('ul');
  wrapper.appendChild(list);

  let filtered = [...customers];
  let selectedIndex = -1;

  function updateList() {
    filtered = customers.filter(c =>
      c.Description.toLowerCase().includes(input.value.toLowerCase())
    );
    list.innerHTML = '';
    filtered.forEach((c, i) => {
      const li = document.createElement('li');
      li.textContent = c.Description;
      li.className = i === selectedIndex ? 'active' : '';
      li.onclick = () => selectCustomer(c);
      list.appendChild(li);
    });
    list.style.display = filtered.length ? 'block' : 'none';
  }

  function selectCustomer(cust) {
    input.value = cust.Description;
    list.style.display = 'none';
    store.set('customerId', cust.Code);
    eventBus.emit('customer:selected', cust.Code);
    debug.log(`Customer selected: ${cust.Code}`);
  }

  input.addEventListener('input', () => { selectedIndex = -1; updateList(); });
  input.addEventListener('keydown', e => {
    if (!filtered.length) return;
    if (e.key === 'ArrowDown') {
      selectedIndex = (selectedIndex + 1) % filtered.length;
      updateList();
    } else if (e.key === 'ArrowUp') {
      selectedIndex = (selectedIndex - 1 + filtered.length) % filtered.length;
      updateList();
    } else if (e.key === 'Enter' && selectedIndex >= 0) {
      selectCustomer(filtered[selectedIndex]);
    }
  });

  app.appendChild(wrapper);
  input.focus();
}


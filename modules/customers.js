/**
 * v2.2.3 [Export named loadCustomers()]
 */
import debug from '../core/debug.js';
import { eventBus } from '../core/event-bus.js';
import { store } from '../core/store.js';
import { get } from '../core/dom.js';

export async function loadCustomers() {
  debug.log('Customers: loading');
  try {
    const res = await fetch('get_customers.php');
    const data = await res.json();
    if (!Array.isArray(data.Result)) throw new Error('Bad');
    store.set('customers', data.Result);
    eventBus.emit('customers:loaded', data.Result);
    debug.log(`Customers: ${data.Result.length}`);
    render(data.Result);
  } catch (e) { debug.error(`Customers: ${e.message}`); }
}

function render(list) {
  const app = get('app');
  app.innerHTML = '';
  const dd  = document.createElement('div');
  dd.className = 'dropdown';
  const inp = document.createElement('input');
  inp.placeholder = 'Search customers…';
  dd.append(inp);
  const ul = document.createElement('ul'); dd.append(ul);

  let arr=list, idx=-1;
  function update() {
    arr = list.filter(c=>c.Description.toLowerCase().includes(inp.value.toLowerCase()));
    ul.innerHTML=''; arr.forEach((c,i)=> {
      const li=document.createElement('li');
      li.textContent=c.Description;
      li.classList.toggle('active',i===idx);
      li.onclick=()=>select(c);
      ul.append(li);
    });
    ul.style.display=arr.length?'block':'none';
  }
  function select(c) {
    inp.value=c.Description; ul.style.display='none';
    store.set('customerId',c.Code);
    eventBus.emit('customer:selected',c.Code);
    debug.log(`Customer:${c.Code}`);
  }
  inp.oninput=()=>{idx=-1;update()};
  inp.onkeydown=e=>{if(!arr.length)return;
    if(e.key==='ArrowDown'){idx=(idx+1)%arr.length;update();}
    if(e.key==='ArrowUp'){idx=(idx-1+arr.length)%arr.length;update();}
    if(e.key==='Enter'&&idx>=0)select(arr[idx]);
  };
  app.append(dd);
  inp.focus();
}

/**
 * v1.1.1 [Export named + default eventBus()]
 */
import debug from './debug.js';

const eventBus = (() => {
  const h = {};
  return {
    on(evt, fn)   { (h[evt]=h[evt]||[]).push(fn); debug.log(`Bus:on ${evt}`); },
    off(evt,fn)   { h[evt]= (h[evt]||[]).filter(x=>x!==fn); debug.log(`Bus:off ${evt}`); },
    emit(evt,p)   { debug.log(`Bus:emit ${evt}`); (h[evt]||[]).forEach(f=>{ try{f(p)}catch(e){debug.error(`${evt}: ${e}`)} }); }
  };
})();
export { eventBus };
export default eventBus;

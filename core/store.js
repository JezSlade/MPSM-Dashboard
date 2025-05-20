/**
 * v1.1.1 [Export named + default store()]
 */
import debug from './debug.js';

const store = (() => {
  const d={}, s={};
  return {
    set(k,v) { d[k]=v; debug.log(`Store:set ${k}`); (s[k]||[]).forEach(f=>{try{f(v)}catch(e){debug.error(`${k} sub: ${e}`)}}); },
    get(k)    { return d[k] },
    subscribe(k,f){ (s[k]=s[k]||[]).push(f); debug.log(`Store:sub ${k}`); }
  };
})();
export { store };
export default store;

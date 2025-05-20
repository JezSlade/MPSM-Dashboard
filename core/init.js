/**
 * core/init.js
 * v1.0.1 [Init: Refactor load order & error handling]
 */

import debug from './debug.js';
import dom from './dom.js';
import eventBus from './event-bus.js';
import store from './store.js';
import auth from './auth.js';

// IMPORT YOUR MODULE INITS HERE
// e.g. import { initCustomerModule } from '../modules/customers.js';
//       import { initDevicesModule } from '../modules/devices.js';
//       import overlay from '../overlay.js';

async function bootstrap() {
  debug.log('🚀 Starting application bootstrap');
  try {
    // 1. Fetch token
    const token = await auth.getToken();
    debug.log('✅ Token acquired');

    // 2. Emit core initialization
    eventBus.emit('core:init', {
      version: '1.0.1',
      time: new Date().toISOString()
    });

    // 3. Initialize modules in dependency order
    // initCustomerModule({ auth, store, eventBus, debug, dom });
    // initDevicesModule({ auth, store, eventBus, debug, dom });
    // overlay.init({ auth, store, eventBus, debug, dom });

    debug.log('🎉 Bootstrap complete');
  } catch (err) {
    debug.error('❌ Bootstrap error: ' + err);
  }
}

document.addEventListener('DOMContentLoaded', bootstrap);

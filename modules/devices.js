/**
 * v2.2.3 [Export named initDevices()]
 */
import debug from '../core/debug.js';
import { eventBus } from '../core/event-bus.js';

export function initDevices() {
  debug.log('Devices: init started');
  eventBus.on('customer:selected', code => {
    debug.log(`Devices: customer ${code}`);
    // … your fetch & render logic here …
  });
}

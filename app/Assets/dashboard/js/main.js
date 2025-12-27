import { setupGroupHandlers } from './group.js';
import { setupEntityHandlers } from './entity.js';
import { setupModalHandlers } from './modal.js';
import { setupDemoHandlers } from './demo.js';

document.addEventListener('DOMContentLoaded', () => {
    setupGroupHandlers();
    setupEntityHandlers();
    setupModalHandlers();
    setupDemoHandlers();
});

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WIP Workflow Visualization</title>
    <!-- Use Tailwind CSS for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
        .workflow-card {
            background-color: #ffffff;
            border-radius: 1.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .process-node {
            background-color: #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.5rem;
            min-width: 10rem;
            min-height: 16rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            height: 100%;
            transition: background-color 0.3s ease-in-out;
        }
        .queue {
            border: 2px dashed #9ca3af;
            background-color: #f9fafb;
            min-height: 5rem;
            width: 100%;
            border-radius: 0.5rem;
            display: flex;
            flex-direction: column-reverse;
            align-items: center;
            padding: 0.5rem;
            position: relative;
            gap: 0.5rem;
        }
        .part {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            transition: transform 1.5s ease-in-out;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .material-A { background-color: #3b82f6; }
        .material-B { background-color: #10b981; }
        .material-C { background-color: #ef4444; }

        .machine-icon {
            width: 4rem;
            height: 4rem;
            margin-bottom: 0.5rem;
        }
        .connection-line {
            width: 2rem;
            height: 2px;
            background-color: #9ca3af;
            position: relative;
        }
        .connection-arrow {
            width: 0;
            height: 0;
            border-top: 8px solid transparent;
            border-bottom: 8px solid transparent;
            border-left: 8px solid #9ca3af;
            position: absolute;
            right: -2px;
            top: -7px;
        }
    </style>
</head>
<body class="flex flex-col items-center p-8 min-h-screen">
    <div class="workflow-card p-10 w-full max-w-full">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-gray-800">MES WIP Workflow</h1>
            <p class="text-gray-500 mt-2">Visualizing the flow of work-in-progress (WIP) through production machines.</p>
        </div>

        <div class="flex items-stretch justify-center">
            <!-- Planner Node -->
            <div class="flex flex-col items-center">
                <div class="process-node">
                    <div class="text-center mb-4">
                        <h2 class="text-xl font-semibold text-gray-700">Planner</h2>
                        <p class="text-gray-500 text-sm mb-4">Create a new work item</p>
                        <button id="create-wip-btn" class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium py-1.5 px-3 rounded-md shadow transition-colors">
                            Create Work To Process
                        </button>
                    </div>
                    <div class="queue" id="queue0">
                        <p class="text-xs text-gray-400">Queue</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col items-center justify-center space-y-2">
                <div class="flex items-center">
                    <div class="connection-line"><div class="connection-arrow"></div></div>
                </div>
                <div class="flex items-center">
                    <div class="connection-line"><div class="connection-arrow"></div></div>
                </div>
            </div>

            <!-- Primary Production Line -->
            <div class="flex flex-col items-center space-y-4">
                <div class="flex items-center justify-center space-x-4">
                    <!-- Milling Machine -->
                    <div class="flex flex-col items-center">
                        <div class="process-node" id="node-MillingMachine">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-700"> Machine - 1</h2>
                                <p class="text-gray-500 text-sm mb-4">PVD process</p>
                                <p id="status-MillingMachine" class="text-xs font-semibold text-green-500"></p>
                                <button id="maint-MillingMachine-btn" class="bg-orange-500 hover:bg-orange-600 text-white text-xs font-medium py-1 px-2 rounded-md shadow transition-colors mt-2">
                                    MAINT
                                </button>
                                <div class="machine-icon text-blue-500 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-4m-4-4l4-4m-4-4l4-4" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16" />
                                    </svg>
                                </div>
                            </div>
                            <div class="queue" id="queue1">
                                <p class="text-xs text-gray-400">Queue</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <div class="connection-line"><div class="connection-arrow"></div></div>
                    </div>

                    <!-- Lathe Machine -->
                    <div class="flex flex-col items-center">
                        <div class="process-node" id="node-LatheMachine">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-700"> Machine - 3</h2>
                                <p class="text-gray-500 text-sm mb-4">Etching process</p>
                                <p id="status-LatheMachine" class="text-xs font-semibold text-green-500"></p>
                                <button id="maint-LatheMachine-btn" class="bg-orange-500 hover:bg-orange-600 text-white text-xs font-medium py-1 px-2 rounded-md shadow transition-colors mt-2">
                                    MAINT
                                </button>
                                <div class="machine-icon text-blue-500 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="queue" id="queue2">
                                <p class="text-xs text-gray-400">Queue</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <div class="connection-line"><div class="connection-arrow"></div></div>
                    </div>

                    <!-- Finishing Station -->
                    <div class="flex flex-col items-center">
                        <div class="process-node" id="node-FinishingStation">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-700">Machine-5 </h2>
                                <p class="text-gray-500 text-sm mb-4">Testing and inspection</p>
                                <p id="status-FinishingStation" class="text-xs font-semibold text-green-500"></p>
                                <button id="maint-FinishingStation-btn" class="bg-orange-500 hover:bg-orange-600 text-white text-xs font-medium py-1 px-2 rounded-md shadow transition-colors mt-2">
                                    MAINT
                                </button>
                                <div class="machine-icon text-blue-500 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 13l2 2m0 0l4-4m-4 4V7" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="queue" id="queue3">
                                <p class="text-xs text-gray-400">Queue</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <div class="connection-line"><div class="connection-arrow"></div></div>
                    </div>

                    <!-- Store -->
                    <div class="flex flex-col items-center">
                        <div class="process-node">
                            <div class="text-center mb-4">
                                <h2 class="text-xl font-semibold text-gray-700">Store</h2>
                                <p class="text-gray-500 text-sm mb-4">Final storage</p>
                                <button id="release-btn" class="bg-red-500 hover:bg-red-600 text-white text-sm font-medium py-1.5 px-3 rounded-md shadow transition-colors mb-4">
                                    Release
                                </button>
                            </div>
                            <div class="queue" id="queue4">
                                <p class="text-xs text-gray-400">Storage</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alternate Production Line -->
                <div class="flex items-center justify-center space-x-4">
                    <!-- Alternate Milling Machine -->
                    <div class="flex flex-col items-center ml-[calc(10rem+2rem)]">
                        <div class="process-node" id="node-AlternateMillingMachine">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-700">Machine -2</h2>
                                <p class="text-gray-500 text-sm mb-4">PVD Process</p>
                                <p id="status-AlternateMillingMachine" class="text-xs font-semibold text-green-500"></p>
                                <button id="maint-AlternateMillingMachine-btn" class="bg-orange-500 hover:bg-orange-600 text-white text-xs font-medium py-1 px-2 rounded-md shadow transition-colors mt-2">
                                    MAINT
                                </button>
                                <div class="machine-icon text-blue-500 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-4m-4-4l4-4m-4-4l4-4" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16" />
                                    </svg>
                                </div>
                            </div>
                            <div class="queue" id="queue5">
                                <p class="text-xs text-gray-400">Queue</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <div class="connection-line"><div class="connection-arrow"></div></div>
                    </div>

                    <!-- Alternate Lathe Machine -->
                    <div class="flex flex-col items-center">
                        <div class="process-node" id="node-AlternateLatheMachine">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-700">Machine-4</h2>
                                <p class="text-gray-500 text-sm mb-4">Etching Process</p>
                                <p id="status-AlternateLatheMachine" class="text-xs font-semibold text-green-500"></p>
                                <button id="maint-AlternateLatheMachine-btn" class="bg-orange-500 hover:bg-orange-600 text-white text-xs font-medium py-1 px-2 rounded-md shadow transition-colors mt-2">
                                    MAINT
                                </button>
                                <div class="machine-icon text-blue-500 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="queue" id="queue6">
                                <p class="text-xs text-gray-400">Queue</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <div class="connection-line"><div class="connection-arrow"></div></div>
                    </div>

                    <!-- Alternate Finishing Station -->
                    <div class="flex flex-col items-center">
                        <div class="process-node" id="node-AlternateFinishingStation">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-700">Machine-6</h2>
                                <p class="text-gray-500 text-sm mb-4">Testin and inspection</p>
                                <p id="status-AlternateFinishingStation" class="text-xs font-semibold text-green-500"></p>
                                <button id="maint-AlternateFinishingStation-btn" class="bg-orange-500 hover:bg-orange-600 text-white text-xs font-medium py-1 px-2 rounded-md shadow transition-colors mt-2">
                                    MAINT
                                </button>
                                <div class="machine-icon text-blue-500 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 13l2 2m0 0l4-4m-4 4V7" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="queue" id="queue7">
                                <p class="text-xs text-gray-400">Queue</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="flex justify-center mt-10">
            <button id="toggle-btn" class="bg-blue-600 text-white font-medium py-3 px-8 rounded-full shadow-lg hover:bg-blue-700 transition-colors">
                Start Simulation
            </button>
        </div>

        <div class="mt-8 bg-gray-50 p-6 rounded-xl border border-gray-200">
            <h2 class="text-xl font-semibold text-gray-700">System Log</h2>
            <ul id="log-list" class="mt-4 space-y-2 text-sm text-gray-600 font-mono">
                <!-- Log entries will appear here -->
            </ul>
        </div>
    </div>

    <script>
        const machineGroups = {
            milling: {
                primary: {
                    name: 'Milling Machine',
                    queue: document.getElementById('queue1'),
                    nodeEl: document.getElementById('node-MillingMachine'),
                    statusEl: document.getElementById('status-MillingMachine')
                },
                alternate: {
                    name: 'Alternate Milling Machine',
                    queue: document.getElementById('queue5'),
                    nodeEl: document.getElementById('node-AlternateMillingMachine'),
                    statusEl: document.getElementById('status-AlternateMillingMachine')
                }
            },
            lathe: {
                primary: {
                    name: 'Lathe Machine',
                    queue: document.getElementById('queue2'),
                    nodeEl: document.getElementById('node-LatheMachine'),
                    statusEl: document.getElementById('status-LatheMachine')
                },
                alternate: {
                    name: 'Alternate Lathe Machine',
                    queue: document.getElementById('queue6'),
                    nodeEl: document.getElementById('node-AlternateLatheMachine'),
                    statusEl: document.getElementById('status-AlternateLatheMachine')
                }
            },
            finishing: {
                primary: {
                    name: 'Finishing Station',
                    queue: document.getElementById('queue3'),
                    nodeEl: document.getElementById('node-FinishingStation'),
                    statusEl: document.getElementById('status-FinishingStation')
                },
                alternate: {
                    name: 'Alternate Finishing Station',
                    queue: document.getElementById('queue7'),
                    nodeEl: document.getElementById('node-AlternateFinishingStation'),
                    statusEl: document.getElementById('status-AlternateFinishingStation')
                }
            }
        };

        const plannerQueue = document.getElementById('queue0');
        const storeQueue = document.getElementById('queue4');
        const logList = document.getElementById('log-list');
        const toggleBtn = document.getElementById('toggle-btn');
        const createWIPBtn = document.getElementById('create-wip-btn');
        const releaseBtn = document.getElementById('release-btn');

        let partCounter = 0;
        let simulationLoop = null;

        const delays = {
            transition: 1500,
            processingCheck: 500
        };

        const processingTimes = {
            'Milling Machine': { 'A': 2000, 'B': 2000, 'C': 2000 },
            'Lathe Machine': { 'A': 2000, 'B': 3000, 'C': 4000 },
            'Finishing Station': { 'A': 1500, 'B': 2000, 'C': 1000 },
            'Alternate Milling Machine': { 'A': 2500, 'B': 2500, 'C': 2500 },
            'Alternate Lathe Machine': { 'A': 2500, 'B': 3500, 'C': 4500 },
            'Alternate Finishing Station': { 'A': 2000, 'B': 2500, 'C': 1500 },
            'Store': { 'A': 500, 'B': 500, 'C': 500 }
        };

        const materials = ['A', 'B', 'C'];
        const machineStatus = {
            'Milling Machine': 'idle',
            'Lathe Machine': 'idle',
            'Finishing Station': 'idle',
            'Alternate Milling Machine': 'idle',
            'Alternate Lathe Machine': 'idle',
            'Alternate Finishing Station': 'idle'
        };

        const getStatusElement = (machineName) => {
            return document.getElementById(`status-${machineName.replace(/\s/g, '')}`);
        };

        const getNodeElement = (machineName) => {
            return document.getElementById(`node-${machineName.replace(/\s/g, '')}`);
        };

        const addLogEntry = (message) => {
            const li = document.createElement('li');
            li.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
            logList.prepend(li);
        };

        const createPart = (id, material) => {
            const partEl = document.createElement('div');
            partEl.id = `part-${id}`;
            partEl.classList.add('part', `material-${material}`);
            partEl.textContent = `${material}-${id}`;
            partEl.dataset.material = material;
            return partEl;
        };

        const movePart = async (part, fromQueue, toQueue, machineName) => {
            const partRect = part.getBoundingClientRect();
            const toQueueRect = toQueue.getBoundingClientRect();
            
            part.style.position = 'fixed';
            part.style.top = `${partRect.top}px`;
            part.style.left = `${partRect.left}px`;
            
            const endX = toQueueRect.left + (toQueueRect.width / 2) - (part.offsetWidth / 2);
            const endY = toQueueRect.bottom - (part.offsetHeight / 2) - 10;
            
            part.style.transition = `all ${delays.transition / 1000}s ease-in-out`;
            part.style.transform = `translate(${endX - partRect.left}px, ${endY - partRect.top}px)`;
            
            await new Promise(resolve => setTimeout(resolve, delays.transition));
            
            part.style.position = '';
            part.style.top = '';
            part.style.left = '';
            part.style.transform = '';
            toQueue.appendChild(part);
            addLogEntry(`Part #${part.textContent} moving to ${machineName} queue.`);
        };
        
        const processMachine = async (machineConfig, nextQueue, nextMachineName) => {
            const queue = machineConfig.queue;
            const statusEl = machineConfig.statusEl;
            const machineName = machineConfig.name;

            if (machineStatus[machineName] === 'idle' && queue.children.length > 1) {
                const partToProcess = queue.children[1];
                const materialId = partToProcess.dataset.material;
                const processingDuration = processingTimes[machineName][materialId];

                machineStatus[machineName] = 'busy';
                if (statusEl) {
                    statusEl.textContent = 'BUSY';
                    statusEl.classList.remove('text-green-500');
                    statusEl.classList.add('text-red-500');
                }

                addLogEntry(`Part #${partToProcess.textContent} is being processed at ${machineName}.`);

                partToProcess.style.opacity = 0.5;
                await new Promise(resolve => setTimeout(resolve, processingDuration));
                partToProcess.style.opacity = 1;
                
                machineStatus[machineName] = 'idle';
                if (statusEl) {
                    statusEl.textContent = 'IDLE';
                    statusEl.classList.remove('text-red-500');
                    statusEl.classList.add('text-green-500');
                }
                
                if (nextQueue) {
                    await movePart(partToProcess, queue, nextQueue, nextMachineName);
                }
            }
        };
        
        const processWorkflows = () => {
            // Processing from Planner to Milling
            const partFromPlanner = plannerQueue.children.length > 1 ? plannerQueue.children[1] : null;
            if (partFromPlanner) {
                if (machineStatus['Milling Machine'] === 'idle') {
                    movePart(partFromPlanner, plannerQueue, machineGroups.milling.primary.queue, 'Milling Machine');
                } else if (machineStatus['Alternate Milling Machine'] === 'idle') {
                    movePart(partFromPlanner, plannerQueue, machineGroups.milling.alternate.queue, 'Alternate Milling Machine');
                }
            }
            
            // Processing Milling to Lathe
            const partFromMilling = machineGroups.milling.primary.queue.children.length > 1 ? machineGroups.milling.primary.queue.children[1] : null;
            if (partFromMilling) {
                if (machineStatus['Lathe Machine'] === 'idle') {
                    processMachine(machineGroups.milling.primary, machineGroups.lathe.primary.queue, 'Lathe Machine');
                } else if (machineStatus['Alternate Lathe Machine'] === 'idle') {
                    processMachine(machineGroups.milling.primary, machineGroups.lathe.alternate.queue, 'Alternate Lathe Machine');
                }
            }
            const partFromAltMilling = machineGroups.milling.alternate.queue.children.length > 1 ? machineGroups.milling.alternate.queue.children[1] : null;
            if (partFromAltMilling) {
                if (machineStatus['Lathe Machine'] === 'idle') {
                    processMachine(machineGroups.milling.alternate, machineGroups.lathe.primary.queue, 'Lathe Machine');
                } else if (machineStatus['Alternate Lathe Machine'] === 'idle') {
                    processMachine(machineGroups.milling.alternate, machineGroups.lathe.alternate.queue, 'Alternate Lathe Machine');
                }
            }
            
            // Processing Lathe to Finishing
            const partFromLathe = machineGroups.lathe.primary.queue.children.length > 1 ? machineGroups.lathe.primary.queue.children[1] : null;
            if (partFromLathe) {
                if (machineStatus['Finishing Station'] === 'idle') {
                    processMachine(machineGroups.lathe.primary, machineGroups.finishing.primary.queue, 'Finishing Station');
                } else if (machineStatus['Alternate Finishing Station'] === 'idle') {
                    processMachine(machineGroups.lathe.primary, machineGroups.finishing.alternate.queue, 'Alternate Finishing Station');
                }
            }
            const partFromAltLathe = machineGroups.lathe.alternate.queue.children.length > 1 ? machineGroups.lathe.alternate.queue.children[1] : null;
            if (partFromAltLathe) {
                if (machineStatus['Finishing Station'] === 'idle') {
                    processMachine(machineGroups.lathe.alternate, machineGroups.finishing.primary.queue, 'Finishing Station');
                } else if (machineStatus['Alternate Finishing Station'] === 'idle') {
                    processMachine(machineGroups.lathe.alternate, machineGroups.finishing.alternate.queue, 'Alternate Finishing Station');
                }
            }
            
            // Processing Finishing to Store
            const partFromFinishing = machineGroups.finishing.primary.queue.children.length > 1 ? machineGroups.finishing.primary.queue.children[1] : null;
            if (partFromFinishing) {
                processMachine(machineGroups.finishing.primary, storeQueue, 'Store');
            }
            const partFromAltFinishing = machineGroups.finishing.alternate.queue.children.length > 1 ? machineGroups.finishing.alternate.queue.children[1] : null;
            if (partFromAltFinishing) {
                processMachine(machineGroups.finishing.alternate, storeQueue, 'Store');
            }
        };

        const handleMaintClick = (machineName) => {
            const statusEl = getStatusElement(machineName);
            const nodeEl = getNodeElement(machineName);

            if (machineStatus[machineName] === 'idle' || machineStatus[machineName] === 'busy') {
                machineStatus[machineName] = 'maint';
                statusEl.textContent = 'MAINT';
                statusEl.classList.remove('text-green-500', 'text-red-500');
                statusEl.classList.add('text-orange-500');
                nodeEl.style.backgroundColor = '#fdba74';
                addLogEntry(`${machineName} is now in maintenance.`);
            } else if (machineStatus[machineName] === 'maint') {
                machineStatus[machineName] = 'idle';
                statusEl.textContent = 'IDLE';
                statusEl.classList.remove('text-orange-500');
                statusEl.classList.add('text-green-500');
                nodeEl.style.backgroundColor = '#e5e7eb';
                addLogEntry(`${machineName} maintenance completed.`);
            }
        };

        const startSimulation = () => {
            if (simulationLoop) return;

            toggleBtn.textContent = 'Stop Simulation';
            addLogEntry('Simulation started.');

            simulationLoop = setInterval(processWorkflows, delays.processingCheck);
        };

        const stopSimulation = () => {
            if (simulationLoop) {
                clearInterval(simulationLoop);
                simulationLoop = null;
                toggleBtn.textContent = 'Start Simulation';
                addLogEntry('Simulation stopped.');
            }
        };

        toggleBtn.addEventListener('click', () => {
            if (simulationLoop) {
                stopSimulation();
            } else {
                startSimulation();
            }
        });

        createWIPBtn.addEventListener('click', () => {
            partCounter++;
            const randomMaterial = materials[Math.floor(Math.random() * materials.length)];
            const newPart = createPart(partCounter, randomMaterial);
            plannerQueue.appendChild(newPart);
            addLogEntry(`New part #${newPart.textContent} created in Planner queue.`);
        });

        releaseBtn.addEventListener('click', () => {
            while (storeQueue.children.length > 1) {
                storeQueue.removeChild(storeQueue.lastElementChild);
            }
            addLogEntry("All parts released from the Store.");
        });

        document.getElementById('maint-MillingMachine-btn').addEventListener('click', () => handleMaintClick('Milling Machine'));
        document.getElementById('maint-LatheMachine-btn').addEventListener('click', () => handleMaintClick('Lathe Machine'));
        document.getElementById('maint-FinishingStation-btn').addEventListener('click', () => handleMaintClick('Finishing Station'));
        document.getElementById('maint-AlternateMillingMachine-btn').addEventListener('click', () => handleMaintClick('Alternate Milling Machine'));
        document.getElementById('maint-AlternateLatheMachine-btn').addEventListener('click', () => handleMaintClick('Alternate Lathe Machine'));
        document.getElementById('maint-AlternateFinishingStation-btn').addEventListener('click', () => handleMaintClick('Alternate Finishing Station'));

        document.getElementById('status-MillingMachine').textContent = 'IDLE';
        document.getElementById('status-LatheMachine').textContent = 'IDLE';
        document.getElementById('status-FinishingStation').textContent = 'IDLE';
        document.getElementById('status-AlternateMillingMachine').textContent = 'IDLE';
        document.getElementById('status-AlternateLatheMachine').textContent = 'IDLE';
        document.getElementById('status-AlternateFinishingStation').textContent = 'IDLE';
    </script>
</body>
</html>

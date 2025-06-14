<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Proxmox</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --card-vm: #20c997;
            --card-lxc: #6f42c1;
            --card-node: #0d6efd;
        }
        .card-machine {
            border-left: 4px solid var(--card-color);
            transition: all 0.3s ease;
            height: 100%;
        }
        .card-machine:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        }
        .card-vm {
            --card-color: var(--card-vm);
        }
        .card-lxc {
            --card-color: var(--card-lxc);
        }
        .card-node {
            --card-color: var(--card-node);
        }
        .progress-thin {
            height: 8px;
            border-radius: 4px;
        }
        .status-badge {
            font-size: 0.75rem;
        }
        .resource-chip {
            background-color: #f8f9fa;
            border-radius: 16px;
            padding: 2px 8px;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            margin-right: 4px;
        }
        .filter-active {
            background-color: #0d6efd !important;
            color: white !important;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">
                <i class="bi bi-hdd-stack me-2"></i>FastRabbit Dashboard
            </h1>
            <div>
                <button id="refreshBtn" class="btn btn-primary me-2">
                    <i class="bi bi-arrow-clockwise"></i> Actualizar
                </button>
                <div class="btn-group">
                    <button class="btn btn-outline-secondary filter-btn active" data-type="all">
                        <i class="bi bi-collection"></i> Todos
                    </button>
                    <button class="btn btn-outline-secondary filter-btn" data-type="vm">
                        <i class="bi bi-pc-display"></i> VMs
                    </button>
                    <button class="btn btn-outline-secondary filter-btn" data-type="lxc">
                        <i class="bi bi-box-seam"></i> LXC
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Bar -->
        <div class="row mb-4" id="statsBar">
            <div class="col-md-4">
                <div class="card card-node">
                    <div class="card-body">
                        <h5 class="card-title" id="nodeName">Nodo: atenper</h5>
                        <div class="d-flex justify-content-between">
                            <span class="resource-chip">
                                <i class="bi bi-cpu me-1"></i>
                                <span id="cpuUsage">0%</span>
                            </span>
                            <span class="resource-chip">
                                <i class="bi bi-memory me-1"></i>
                                <span id="ramUsage">0GB/0GB</span>
                            </span>
                            <span class="resource-chip">
                                <i class="bi bi-hdd me-1"></i>
                                <span id="diskUsage">0GB/0GB</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card bg-white">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-4 text-center">
                                <h6 class="mb-0"><i class="bi bi-pc-display"></i> Máquinas Virtuales</h6>
                                <h2 id="vmCount">0</h2>
                            </div>
                            <div class="col-4 text-center">
                                <h6 class="mb-0"><i class="bi bi-box-seam"></i> Contenedores</h6>
                                <h2 id="lxcCount">0</h2>
                            </div>
                            <div class="col-4 text-center">
                                <h6 class="mb-0"><i class="bi bi-check-circle"></i> Activos</h6>
                                <h2 id="activeCount">0</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div id="loadingIndicator" class="text-center py-5">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
            <p class="mt-3">Cargando infraestructura...</p>
        </div>

        <!-- Error Alert -->
        <div id="errorAlert" class="alert alert-danger d-none">
            <i class="bi bi-exclamation-triangle-fill"></i> <span id="errorText"></span>
        </div>

        <!-- Machines Grid -->
        <div id="machinesGrid" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4 d-none">
            <!-- Las tarjetas se generarán dinámicamente aquí -->
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para cargar datos desde el backend
        async function loadData() {
            try {
                console.log("Iniciando carga de datos...");
                const response = await fetch('../backEnd/gt_vms.php');
                console.log("Respuesta HTTP:", response.status);

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error("Error en la respuesta:", errorText);
                    throw new Error(`Error ${response.status}: ${errorText}`);
                }

                const data = await response.json();
                console.log("Datos recibidos:", data);

                if (!data.success) {
                    throw new Error(data.error || "Error en la estructura de datos");
                }

                renderDashboard(data.data);
                document.getElementById('machinesGrid').classList.remove('d-none');
            } catch (err) {
                console.error("Error completo:", err);
                document.getElementById('errorText').textContent = err.message;
                document.getElementById('errorAlert').classList.remove('d-none');
            } finally {
                document.getElementById('loadingIndicator').classList.add('d-none');
            }
        }

        // Función para renderizar el dashboard
        function renderDashboard(data) {
            console.log("Renderizando dashboard con datos:", data);

            // Actualizar stats del nodo
            const node = data.node;
            document.getElementById('nodeName').textContent = `Nodo: ${node.node}`;
            document.getElementById('cpuUsage').textContent = `${(node.cpu * 100).toFixed(2)}%`;
            document.getElementById('ramUsage').textContent = `${(node.mem / 1024 / 1024 / 1024).toFixed(2)}GB/${(node.maxmem / 1024 / 1024 / 1024).toFixed(2)}GB`;
            document.getElementById('diskUsage').textContent = `${(node.disk / 1024 / 1024 / 1024).toFixed(2)}GB/${(node.maxdisk / 1024 / 1024 / 1024).toFixed(2)}GB`;

            // Actualizar contadores
            const vms = (data.vms || []).filter(vm => vm.vmid !== 200); // Excluir máquina con ID 200
            const lxcs = (data.containers || []).filter(lxc => lxc.vmid !== 200); // Excluir contenedor con ID 200
            const activeMachines = [...vms, ...lxcs].filter(m => m.status === 'running').length;

            document.getElementById('vmCount').textContent = vms.length;
            document.getElementById('lxcCount').textContent = lxcs.length;
            document.getElementById('activeCount').textContent = activeMachines;

            // Ordenar las máquinas por nombre
            const sortedVms = vms.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
            const sortedLxcs = lxcs.sort((a, b) => (a.name || '').localeCompare(b.name || ''));

            // Renderizar tarjetas
            const grid = document.getElementById('machinesGrid');
            grid.innerHTML = '';

            // Renderizar tarjetas de máquinas virtuales y contenedores
            sortedVms.forEach(vm => grid.appendChild(createMachineCard(vm, 'vm')));
            sortedLxcs.forEach(lxc => grid.appendChild(createMachineCard(lxc, 'lxc')));

            if (sortedVms.length === 0 && sortedLxcs.length === 0) {
                grid.innerHTML = '<div class="col text-center"><p>No se encontraron máquinas virtuales ni contenedores.</p></div>';
            }

            // Ejecutar automáticamente getMachineIP para cada tarjeta
            const cards = document.querySelectorAll('#machinesGrid .col');
            cards.forEach(card => {
                const id = card.dataset.vmid;
                const type = card.dataset.type;
                getMachineIP({ id, type, card }); // Llamar a la función para obtener la IP
            });
        }

        // Función para crear la tarjeta del nodo
        function createNodeCard(node) {
            const card = document.createElement('div');
            card.className = 'col';
            card.innerHTML = `
                <div class="card card-node">
                    <div class="card-body">
                        <h5 class="card-title">Nodo: ${node.node}</h5>
                        <div class="d-flex justify-content-between">
                            <span class="resource-chip">
                                <i class="bi bi-cpu me-1"></i> ${(node.cpu * 100).toFixed(2)}%
                            </span>
                            <span class="resource-chip">
                                <i class="bi bi-memory me-1"></i> ${(node.mem / 1024 / 1024 / 1024).toFixed(2)}GB/${(node.maxmem / 1024 / 1024 / 1024).toFixed(2)}GB
                            </span>
                            <span class="resource-chip">
                                <i class="bi bi-hdd me-1"></i> ${(node.disk / 1024 / 1024 / 1024).toFixed(2)}GB/${(node.maxdisk / 1024 / 1024 / 1024).toFixed(2)}GB
                            </span>
                        </div>
                    </div>
                </div>
            `;
            return card;
        }

        // Función para crear tarjetas de máquinas virtuales y contenedores
        function createMachineCard(machine, type) {
            const card = document.createElement('div');
            card.className = 'col';
            card.dataset.type = type; // Tipo de máquina (VM o LXC)
            card.dataset.vmid = machine.vmid; // ID de la máquina
            card.dataset.status = machine.status; // Estado de la máquina

            const isRunning = machine.status === 'running';

            card.innerHTML = `
                <div class="card card-machine card-${type}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="card-title">
                                    <i class="bi ${type === 'vm' ? 'bi-pc-display' : 'bi-box-seam'}"></i> 
                                    ${machine.name || (type === 'vm' ? `VM ${machine.vmid}` : `CT ${machine.vmid}`)}
                                </h5>
                                <span class="badge ${isRunning ? 'bg-success' : 'bg-secondary'} status-badge">
                                    ${isRunning ? 'En ejecución' : 'Detenido'}
                                </span>
                            </div>
                            <span class="badge bg-light text-dark">${type === 'vm' ? 'VM' : 'LXC'}</span>
                        </div>
                        <div class="mt-3">
                            <div class="d-flex flex-wrap mb-2">
                                <span class="resource-chip">
                                    <i class="bi bi-123 me-1"></i> ID: ${machine.vmid}
                                </span>
                                <span class="resource-chip">
                                    <i class="bi bi-clock-history me-1"></i> ${Math.floor(machine.uptime / 60)} min
                                </span>
                                <span class="resource-chip">
                                    <i class="bi bi-globe me-1"></i> IP: <span class="ip-placeholder">Cargando...</span>
                                </span>
                            </div>
                            <div class="mb-2">
                                <small>RAM: ${(machine.mem / 1024 / 1024).toFixed(2)}/${(machine.maxmem / 1024 / 1024).toFixed(2)} MB</small>
                                <div class="progress progress-thin mt-1">
                                    <div class="progress-bar" style="width: ${(machine.mem / machine.maxmem * 100).toFixed(2)}%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 text-center">
                            <button class="btn ${isRunning ? 'btn-secondary' : 'btn-success'} btn-sm me-2" 
                                onclick="startMachine(this)" ${isRunning ? 'disabled' : ''}>
                                <i class="bi bi-play-fill"></i> Encender
                            </button>
                            <button class="btn ${isRunning ? 'btn-warning' : 'btn-secondary'} btn-sm me-2" 
                                onclick="stopMachine(this)" ${isRunning ? '' : 'disabled'}>
                                <i class="bi bi-stop-fill"></i> Apagar
                            </button>
                            <button class="btn ${isRunning ? 'btn-secondary' : 'btn-danger'} btn-sm me-2" 
                                onclick="deleteMachine(this)" ${isRunning ? 'disabled' : ''}>
                                <i class="bi bi-trash-fill"></i> Borrar
                            </button>
                        </div>
                    </div>
                </div>
            `;
            return card;
        }

        // Cargar datos al iniciar la página
        document.addEventListener('DOMContentLoaded', () => {
            console.log("DOMContentLoaded disparado");
            loadData(); // Asegúrate de que esta línea esté después de la definición de loadData
        });

        // Agregar evento al botón de actualización
        document.getElementById('refreshBtn').addEventListener('click', () => {
            console.log("Botón de actualización presionado");
            document.getElementById('loadingIndicator').classList.remove('d-none');
            document.getElementById('machinesGrid').classList.add('d-none');
            document.getElementById('errorAlert').classList.add('d-none');
            loadData();
        });

        // Agregar eventos a los botones de filtro
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', () => {
                const type = button.dataset.type;
                console.log(`Filtro seleccionado: ${type}`);

                // Actualizar estilos de los botones
                document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('filter-active'));
                button.classList.add('filter-active');

                // Filtrar las tarjetas
                const cards = document.querySelectorAll('#machinesGrid .col');
                cards.forEach(card => {
                    if (type === 'all' || card.dataset.type === type) {
                        card.classList.remove('d-none');
                    } else {
                        card.classList.add('d-none');
                    }
                });
            });
        });

        // Funciones para manejar acciones en las máquinas
        function startMachine(button) {
            const card = button.closest('.col'); // Obtener la tarjeta asociada al botón
            const vmid = card.dataset.vmid; // Obtener la ID del contenedor desde la tarjeta
            const type = card.dataset.type; // Obtener el tipo desde la tarjeta
            const status = card.dataset.status; // Obtener el estado actual de la máquina

            if (status === 'running') {
                alert(`La máquina ${type} con ID ${vmid} ya está encendida.`);
                return;
            }

            console.log(`Encendiendo máquina ${type} con ID ${vmid}`);
            const body = JSON.stringify({ ct_id: vmid });
            console.log("Cuerpo de la solicitud:", body); // Registrar el cuerpo de la solicitud

            fetch('../backEnd/encender_lxc.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: body
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload(); // Recargar la vista
                } else {
                    alert(`Error: ${data.error}`);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function stopMachine(button) {
            const card = button.closest('.col'); // Obtener la tarjeta asociada al botón
            const vmid = card.dataset.vmid; // Obtener la ID del contenedor desde la tarjeta
            const type = card.dataset.type; // Obtener el tipo desde la tarjeta
            const status = card.dataset.status; // Obtener el estado actual de la máquina

            if (status === 'stopped') {
                alert(`La máquina ${type} con ID ${vmid} ya está apagada.`);
                return;
            }

            console.log(`Apagando máquina ${type} con ID ${vmid}`);
            const body = JSON.stringify({ ct_id: vmid }); // Construir el cuerpo de la solicitud
            console.log("Cuerpo de la solicitud:", body); // Registrar el cuerpo de la solicitud

            fetch('../backEnd/apagar_lxc.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: body
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Máquina ${vmid} apagada correctamente.`);
                    location.reload(); // Recargar la vista
                } else {
                    alert(`Error al apagar la máquina: ${data.error}`);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function deleteMachine(button) {
            const card = button.closest('.col'); // Obtener la tarjeta asociada al botón
            const vmid = card.dataset.vmid; // Obtener la ID del contenedor desde la tarjeta
            const type = card.dataset.type; // Obtener el tipo desde la tarjeta

            console.log(`Borrando máquina ${type} con ID ${vmid}`);
            const body = JSON.stringify({ ct_id: vmid }); // Construir el cuerpo de la solicitud
            console.log("Cuerpo de la solicitud:", body); // Registrar el cuerpo de la solicitud

            if (confirm(`¿Estás seguro de que deseas borrar la máquina ${vmid}? Esta acción no se puede deshacer.`)) {
                fetch('../backEnd/borrar_lxc.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: body
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`Máquina ${vmid} borrada correctamente.`);
                        location.reload(); // Recargar la vista
                    } else {
                        alert(`Error al borrar la máquina: ${data.error}`);
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }

        function getMachineIP({ id, type, card }) {
            console.log(`Obteniendo IP para máquina ${type} con ID ${id}`);

            fetch('../backEnd/get_ip.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, type })
            })
            .then(response => {
                console.log("Respuesta HTTP:", response.status);
                return response.json();
            })
            .then(data => {
                console.log("Datos recibidos:", data);
                const ipElement = card.querySelector('.ip-placeholder');
                if (data.success) {
                    ipElement.textContent = data.ip || 'No disponible';
                } else {
                    ipElement.textContent = 'Error';
                    console.error(`Error al obtener IP: ${data.error}`);
                }
            })
            .catch(error => {
                const ipElement = card.querySelector('.ip-placeholder');
                ipElement.textContent = 'Error';
                console.error('Error:', error);
            });
        }

        // Función para buscar máquinas
        function searchMachines() {
            fetch('../backEnd/buscar_maquinas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ search: 'Maquina1' })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Resultados:', data.results);
                } else {
                    console.error('Error:', data.error);
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>
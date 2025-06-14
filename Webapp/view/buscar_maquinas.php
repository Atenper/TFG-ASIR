<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Máquinas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <h1 class="text-center mb-4">Resultados de la Búsqueda</h1>

        <!-- Loading Indicator -->
        <div id="loadingIndicator" class="text-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
            <p class="mt-3">Cargando...</p>
        </div>

        <!-- Error Alert -->
        <div id="errorAlert" class="alert alert-danger d-none">
            <p id="errorText"></p>
        </div>

        <!-- Search Results -->
        <div id="searchResults" class="row g-4">
            <!-- Aquí se mostrarán los resultados -->
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const urlParams = new URLSearchParams(window.location.search);
            const searchTerm = urlParams.get('search'); // Obtener el término de búsqueda desde la URL

            console.log(`Término de búsqueda obtenido: ${searchTerm}`); // Depuración

            if (!searchTerm) {
                showError('No se proporcionó un término de búsqueda.');
                return;
            }

            await realizarBusqueda(searchTerm); // Realizar la búsqueda automáticamente
        });

        async function realizarBusqueda(searchTerm) {
            const loadingIndicator = document.getElementById('loadingIndicator');
            const searchResults = document.getElementById('searchResults');
            const errorAlert = document.getElementById('errorAlert');
            const errorText = document.getElementById('errorText');

            // Mostrar indicador de carga
            loadingIndicator.classList.remove('d-none');
            searchResults.innerHTML = '';
            errorAlert.classList.add('d-none');

            try {
                const response = await fetch('../backEnd/buscar_maquinas.php', {
                    method: 'GET',
                    headers: { 'Content-Type': 'application/json' }
                });

                console.log("Respuesta HTTP:", response.status); // Depuración

                if (!response.ok) {
                    throw new Error(`Error ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                console.log("Datos recibidos del backend:", data); // Depuración

                if (!data.success) {
                    throw new Error(data.error || "Error en la búsqueda.");
                }

                // Filtrar resultados en el frontend
                const containers = data.data.containers || [];
                const vms = data.data.vms || [];
                const allMachines = [...containers, ...vms];

                const filteredMachines = allMachines.filter(machine => 
                    machine.name.toLowerCase().includes(searchTerm.toLowerCase())
                );

                // Renderizar resultados
                if (filteredMachines.length === 0) {
                    searchResults.innerHTML = '<div class="col text-center"><p>No se encontraron máquinas con ese nombre.</p></div>';
                } else {
                    filteredMachines.forEach(machine => {
                        const card = document.createElement('div');
                        card.className = 'col-md-4';
                        card.innerHTML = `
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">${machine.name || `Máquina ${machine.vmid}`}</h5>
                                    <p class="card-text">Estado: ${machine.status === 'running' ? 'En ejecución' : 'Detenido'}</p>
                                    <p class="card-text">Tipo: ${machine.type.toUpperCase()}</p>
                                    <p class="card-text">ID: ${machine.vmid}</p>
                                </div>
                            </div>
                        `;
                        searchResults.appendChild(card);
                    });
                }
            } catch (err) {
                showError(err.message);
            } finally {
                loadingIndicator.classList.add('d-none');
            }
        }

        function showError(message) {
            const errorAlert = document.getElementById('errorAlert');
            const errorText = document.getElementById('errorText');
            errorText.textContent = message;
            errorAlert.classList.remove('d-none');
        }
    </script>
</body>
</html>
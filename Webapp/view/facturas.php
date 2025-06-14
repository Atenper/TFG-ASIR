<?php
include("../backEnd/list.php");
$search = $_GET['search'] ?? '';
$facturas = getFacturas($search);
?>

<!-- Añade un indicador de búsqueda -->
<div class="container mb-3">
	<?php if(!empty($search)): ?>
		<div class="alert alert-info">
			Mostrando resultados para: <strong><?php echo htmlspecialchars($search) ?></strong>
			<a href="main.php?vista=facturas" class="btn btn-sm btn-danger ms-3">Limpiar búsqueda</a>
		</div>
	<?php endif; ?>
</div>

<!-- Tu listado actual de tarjetas -->
<!-- En tu listado de facturas -->
<div class="container my-4">
    <ul class="list-group">
        <?php foreach ($facturas as $factura): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div class="flex-grow-1">
                    <h5 class="mb-1"><?php echo htmlspecialchars($factura['nombre']); ?></h5>
                    <p class="mb-0 text-muted"><?php echo htmlspecialchars($factura['cliente_name']); ?></p>
                </div>
                
                <div class="text-end me-3" style="min-width: 150px;">
                    <?php if (!empty($factura['fecha_creacion'])): ?>
                        <small class="d-block text-muted">Creado: <?php 
                            echo date('d/m/Y', strtotime($factura['fecha_creacion'])); 
                        ?></small>
                    <?php endif; ?>
                    
                    <?php if (!empty($factura['fecha_descarga'])): ?>
                        <small class="d-block text-muted">Descargado: <?php 
                            echo date('d/m/Y', strtotime($factura['fecha_descarga'])); 
                        ?></small>
                    <?php endif; ?>
                </div>
                
                <div class="btn-group" style="flex-shrink: 0;">
                    <a href="backEnd/download.php?id=<?php echo $factura['id']; ?>" class="btn btn-primary btn-sm">Descargar</a>
                    <button onclick="confirmDelete(<?php echo $factura['id']; ?>)" class="btn btn-secondary btn-sm">Borrar</button>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<script>
function confirmDelete(id) {
    if (confirm('¿Estás seguro de que quieres borrar esta factura?')) {
        window.location.href = 'backEnd/delete.php?id=' + id;
    }
}
</script>
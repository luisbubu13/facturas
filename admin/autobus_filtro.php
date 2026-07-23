<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - PANEL DE ADMINISTRACIÓN
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/autobus_filtro.php
 * @description: Componente parcial (Fragmento de interfaz). Construye la barra
 * de herramientas de búsqueda y filtros para el catálogo de
 * autobuses, manteniendo la persistencia de datos tras el submit.
 * @dependencies: 
 * - Contexto: Requiere que el archivo contenedor ya tenga inicializada e 
 * instanciada la variable de conexión activa ($con).
 * -----------------------------------------------------------------------------
 */

/* =============================================================================
   1. CAPTURA Y CONSERVACIÓN DEL ESTADO DE BUSQUEDA (MÉTOPDO GET)
   ============================================================================= */
$unidad = isset($_GET['unidad']) ? trim($_GET['unidad']) : '';
$id_empresa = isset($_GET['id_empresa']) ? intval($_GET['id_empresa']) : 0;

/* =============================================================================
   2. CONSULTA AL CATÁLOGO DE EMPRESAS (ENTIDAD FORÁNEA)
   ============================================================================= */
// Recupera únicamente los registros vigentes (eliminado = 0) para poblar el elemento <select>
$sqlEmpresas = "SELECT
                    id,
                    nombre
                FROM empresa
                WHERE eliminado = 0
                ORDER BY nombre";

$resEmpresas = $con->query($sqlEmpresas);
?>

<div class="filtro">

    <form method="GET">

        <div class="grupo">
            <label>Unidad</label>
            <input
                type="text"
                name="unidad"
                placeholder="AM, TM, 001..."
                value="<?= htmlspecialchars($unidad); ?>">
        </div>

        <div class="grupo">
            <label>Empresa</label>
            <select name="id_empresa">
                <option value="0">Todas las empresas</option>

                <?php while($empresa = $resEmpresas->fetch_assoc()){ ?>
                    <option
                        value="<?= $empresa['id']; ?>"
                        <?= ($id_empresa == $empresa['id']) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($empresa['nombre']); ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="botones">
            <button type="submit">Buscar</button>

            <a href="autobuses_lista.php" class="btnLimpiar">Limpiar</a>
        </div>

    </form>

</div>
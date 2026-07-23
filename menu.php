<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS
 * -----------------------------------------------------------------------------
 * @file: facturas/menu.php
 * @description: Componente de barra lateral/superior de navegación y filtros.
 * Permite buscar facturas por rango de fechas, proveedor, empresa
 * y folio, además de contener los disparadores de acciones.
 * @dependencies:
 * - Requiere que la sesión ya esté iniciada (session_start() en el archivo padre).
 * - Requiere un objeto de conexión activo '$con' (inyectado desde el archivo padre).
 * - CSS: css/menu.css
 * -----------------------------------------------------------------------------
 */

// Recupera el nombre del usuario guardado en la sesión activa
$nombre = $_SESSION['nombreUser'];
?>

<div class="menu">

    <div class="fila1">

        <div class="usuario">
            <img src="admin/fotos/logo.png" class="logo" alt="Logo Sistema">

            <span>
                <?= htmlspecialchars($nombre) ?>
            </span>
        </div>

        <div class="accionesUsuario">

            <a
                href="funciones/respaldo.php"
                class="btnBackup">

                Copia de seguridad

            </a>

            <a
                href="funciones/logout.php"
                class="btnSalir">

                Cerrar sesión

            </a>

        </div>

    </div>

    <div class="fila2">
        Facturas Registradas
    </div>

    <div class="fila3">

        <a href="factura_alta.php" class="btnMenu">
            Nueva
        </a>

        <a href="#" class="btnMenu" id="btnImprimir">
            Imprimir
        </a>

        <a href="#" class="btnMenu" id="btnEntregado">
            Entregado
        </a>

        <a href="#" class="btnMenu" id="btnCancelar">
            Cancelar
        </a>

    </div>

    <div class="fila4">

        <form action="facturas.php" method="GET" class="formFiltros">

            <div>
                <label>Desde</label>
                <input
                    type="date"
                    name="fecha_inicio"
                    value="<?= $_GET['fecha_inicio'] ?? '' ?>">
            </div>

            <div>
                <label>Hasta</label>
                <input
                    type="date"
                    name="fecha_fin"
                    value="<?= $_GET['fecha_fin'] ?? '' ?>">
            </div>

            <div>
                <label>Proveedor</label>
                <select name="id_provedor" class="filtroSelect">
                    <option value="">Todos</option>

                    <?php
                    // Consulta los proveedores que no han sido dados de baja de manera lógica (eliminado = 0)
                    $sql = "SELECT id, nombre 
                            FROM provedores 
                            WHERE eliminado = 0 
                            ORDER BY nombre";
                    
                    $res = $con->query($sql);

                    // Ciclo para renderizar cada proveedor en el elemento select
                    while($prov = $res->fetch_assoc()){
                    ?>
                        <option
                            value="<?= $prov['id'] ?>"
                            <?= (
                                isset($_GET['id_provedor']) &&
                                $_GET['id_provedor'] == $prov['id']
                            ) ? 'selected' : '' ?>>
                            
                            <?= htmlspecialchars($prov['nombre']) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div>
                <label>Empresa</label>
                <select name="id_empresa" class="filtroSelect">
                    <option value="">Todas</option>

                    <?php
                    // Consulta las empresas que no han sido dadas de baja de manera lógica (eliminado = 0)
                    $sql = "SELECT id, nombre 
                            FROM empresa 
                            WHERE eliminado = 0 
                            ORDER BY nombre";
                    
                    $res = $con->query($sql);

                    // Ciclo para renderizar cada empresa en el elemento select
                    while($emp = $res->fetch_assoc()){
                    ?>
                        <option
                            value="<?= $emp['id'] ?>"
                            <?= (
                                isset($_GET['id_empresa']) &&
                                $_GET['id_empresa'] == $emp['id']
                            ) ? 'selected' : '' ?>>
                            
                            <?= htmlspecialchars($emp['nombre']) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div>
                <label>Estado</label>

                <select name="estado">

                    <option value="">Todos</option>

                    <option
                        value="0"
                        <?= (isset($_GET['estado']) && $_GET['estado'] === '0') ? 'selected' : '' ?>>
                        Registradas
                    </option>

                    <option
                        value="1"
                        <?= (isset($_GET['estado']) && $_GET['estado'] === '1') ? 'selected' : '' ?>>
                        En proceso
                    </option>

                    <option
                        value="2"
                        <?= (isset($_GET['estado']) && $_GET['estado'] === '2') ? 'selected' : '' ?>>
                        Entregadas
                    </option>

                    <option
                        value="3"
                        <?= (isset($_GET['estado']) && $_GET['estado'] === '3') ? 'selected' : '' ?>>
                        Canceladas
                    </option>

                </select>

            </div>

            <div>
                <label>Folio</label>
                <input
                    type="text"
                    name="folio"
                    placeholder="Buscar folio"
                    value="<?= $_GET['folio'] ?? '' ?>">
            </div>

            <button type="submit" class="btnMenu">
                Filtrar
            </button>

            <a href="facturas.php" class="btnMenu">
                Limpiar
            </a>

        </form>

    </div>

</div>
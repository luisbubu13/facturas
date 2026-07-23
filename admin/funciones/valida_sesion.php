<?php
/**
 * -----------------------------------------------------------------------------
 * SISTEMA DE CONTROL DE FACTURAS - FILTROS DE SEGURIDAD INTERNOS
 * -----------------------------------------------------------------------------
 * @file: facturas/admin/funciones/valida_sesion.php
 * @description: Interceptor de sesión global (Middleware). Se incluye en la 
 * cabecera de todas las vistas del panel de administración 
 * para restringir el acceso a usuarios no autenticados.
 * @dependencies:
 * - Origen: Cualquiera de los catálogos principales del sistema.
 * - Redirección de Expulsión: index.php (Formulario de Login)
 * -----------------------------------------------------------------------------
 */

// Inicializa o reanuda la sesión web basada en el identificador único de cookie (PHPSESSID)
session_start();

/* =============================================================================
   1. CONTROL DE ACCESO PERIMETRAL (VERIFICACIÓN DE TOKEN DE AUTENTICACIÓN)
   ============================================================================= */
/**
 * CLÁUSULA DE SALVAGUARDA DE SESIÓN:
 * Evalúa si la variable súper global $_SESSION['id'] NO se encuentra declarada.
 * La ausencia de este índice dictamina que el cliente actual no ha completado el 
 * flujo de login de manera válida en 'validar_login.php'.
 */
if(!isset($_SESSION['id'])){

    /**
     * EXPULSIÓN INMEDIATA:
     * Redirige al navegador de vuelta a la raíz del entorno administrativo.
     * Al estar este script incluido mediante require_once() en carpetas del mismo
     * nivel (como 'admin/'), la ruta relativa directa a 'index.php' es correcta.
     */
    header("Location: index.php");
    
    // Frena de golpe la ejecución del backend, protegiendo las consultas e información HTML del archivo hijo
    exit;
}
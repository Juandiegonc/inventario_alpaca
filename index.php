<?php

require_once "controladores/plantilla.controlador.php";
require_once "controladores/usuarios.controlador.php";
require_once "controladores/categorias.controlador.php";
require_once "controladores/productos.controlador.php";
require_once "controladores/ventas.controlador.php";
require_once "controladores/devolucion.controlador.php";
require_once "controladores/cambios.controlador.php";
require_once "controladores/mermas.controlador.php";
require_once "controladores/kardex.controlador.php";




require_once "modelos/usuarios.modelo.php";
require_once "modelos/categorias.modelo.php";
require_once "modelos/productos.modelo.php";
require_once "modelos/ventas.modelo.php";
require_once "modelos/devolucion.modelo.php";
require_once "modelos/cambios.modelo.php";
require_once "modelos/mermas.modelo.php";
require_once "modelos/kardex.modelo.php";


$plantilla = new ControladorPlantilla();
$plantilla -> ctrPlantilla();
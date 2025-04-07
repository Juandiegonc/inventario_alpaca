<?php
class ControladorCambios {
    /* =============================================
    MOSTRAR CAMBIOS
    ============================================= */
    static public function ctrMostrarCambios($item, $valor) {
        $tabla = "cambios";
        return ModeloCambio::mdlMostrarCambio($tabla, $item, $valor);
    }

    /* =============================================
    CREAR CAMBIO
    ============================================= */
    static public function ctrCrearCambio() {
        if (isset($_POST["nuevoCambio"])) {
            if ($_POST["cantidad_devuelta"] <= 0 || $_POST["cantidad_entregada"] <= 0) {
                echo '<script>alert("Las cantidades deben ser mayores a cero");</script>';
                return;
            }

            $tablaProductos = "productos";
            $productoEntregar = ModeloProductos::mdlMostrarProductos($tablaProductos, "id", $_POST["id_producto_entregado"], null);
            
            if ($productoEntregar["stock"] < $_POST["cantidad_entregada"]) {
                echo '<script>alert("No hay suficiente stock del producto a entregar");</script>';
                return;
            }

            $tabla = "cambios";
            $datos = array(
                "id_producto_devuelto" => $_POST["id_producto_devuelto"],
                "cantidad_devuelta" => $_POST["cantidad_devuelta"],
                "id_producto_entregado" => $_POST["id_producto_entregado"],
                "cantidad_entregada" => $_POST["cantidad_entregada"],
                "estado" => "Pendiente",
                "motivo" => $_POST["motivo"],
                "usuario" => $_SESSION["id"]
            );

           $respuesta = ModeloCambio::mdlIngresarCambio($tabla, $datos);

            if ($respuesta == "ok") {
                // Ya no necesitamos actualizar el stock aquí porque el estado siempre será "Pendiente"
                echo '<script>
                    swal({
                        type: "success",
                        title: "El Cambio ha sido guardado correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if (result.value) {
                            window.location = "cambio";
                        }
                    });
                </script>';
            } else {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error al guardar el cambio",
                        text: "Por favor, intenta nuevamente.",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /* =============================================
    EDITAR CAMBIO (APROBAR CAMBIO Y ACTUALIZAR STOCK)
============================================= */
static public function ctrEditarCambio() {
    if (isset($_POST["editarCambio"])) {
        $tabla = "cambios";
        $item = "id";
        $valor = $_POST["id"];
        $cambio = ModeloCambio::mdlMostrarCambio($tabla, $item, $valor);

        if (!is_array($cambio) || empty($cambio)) {
            echo '<script>alert("Error: No se encontró el cambio.");</script>';
            return;
        }

        $tablaProductos = "productos";
        $productoRecibido = ModeloProductos::mdlMostrarProductos($tablaProductos, "id", $cambio["id_producto_devuelto"], null);
        $productoEntregado = ModeloProductos::mdlMostrarProductos($tablaProductos, "id", $cambio["id_producto_entregado"], null);

        if (!is_array($productoRecibido) || empty($productoRecibido)) {
            echo '<script>alert("Error: Producto devuelto no encontrado.");</script>';
            return;
        }

        if (!is_array($productoEntregado) || empty($productoEntregado)) {
            echo '<script>alert("Error: Producto entregado no encontrado.");</script>';
            return;
        }

        // Capturar valores
        $cantidadDevuelta = intval($_POST["cantidad_devuelta"]);
        $cantidadEntregada = intval($_POST["cantidad_entregada"]);
        $motivo = trim($_POST["motivo"]);
        $nuevoEstado = $_POST["estado"] ?? "";
        $estadoAnterior = $cambio["estado"];

        if (!in_array($nuevoEstado, ["Pendiente", "Aprobado", "Rechazado"])) {
            echo '<script>alert("Error: Estado no válido.");</script>';
            return;
        }

        $pdo = Conexion::conectar();
        $pdo->beginTransaction();

        try {
            // Solo actualizar stocks si el cambio pasa de Pendiente a Aprobado
            if ($nuevoEstado === "Aprobado" && $estadoAnterior !== "Aprobado") {
                // Verificar stock suficiente antes de aprobar
                if ($productoEntregado["stock"] < $cantidadEntregada) {
                    throw new Exception("No hay suficiente stock del producto a entregar");
                }

                // Actualizar stock del producto recibido (sumar)
                $nuevoStockRecibido = $productoRecibido["stock"] + $cantidadDevuelta;
                $resultadoRecibido = ModeloProductos::mdlActualizarStockProducto(
                    $tablaProductos, 
                    $cambio["id_producto_devuelto"], 
                    $nuevoStockRecibido
                );

                // Actualizar stock del producto entregado (restar)
                $nuevoStockEntregado = $productoEntregado["stock"] - $cantidadEntregada;
                $resultadoEntregado = ModeloProductos::mdlActualizarStockProducto(
                    $tablaProductos, 
                    $cambio["id_producto_entregado"], 
                    $nuevoStockEntregado
                );

                if (!$resultadoRecibido || !$resultadoEntregado) {
                    throw new Exception("Error al actualizar el stock de los productos");
                }
            }
            // Si el cambio pasa de Aprobado a otro estado, revertir los cambios en el stock
            else if ($estadoAnterior === "Aprobado" && $nuevoEstado !== "Aprobado") {
                // Revertir stock del producto recibido (restar)
                $nuevoStockRecibido = $productoRecibido["stock"] - $cantidadDevuelta;
                $resultadoRecibido = ModeloProductos::mdlActualizarStockProducto(
                    $tablaProductos, 
                    $cambio["id_producto_devuelto"], 
                    $nuevoStockRecibido
                );

                // Revertir stock del producto entregado (sumar)
                $nuevoStockEntregado = $productoEntregado["stock"] + $cantidadEntregada;
                $resultadoEntregado = ModeloProductos::mdlActualizarStockProducto(
                    $tablaProductos, 
                    $cambio["id_producto_entregado"], 
                    $nuevoStockEntregado
                );

                if (!$resultadoRecibido || !$resultadoEntregado) {
                    throw new Exception("Error al revertir el stock de los productos");
                }
            }

            // Actualizar el cambio en la base de datos
            $resultadoCambio = ModeloCambio::mdlEditarCambio($tabla, array(
                "id" => $cambio["id"],
                "cantidad_devuelta" => $cantidadDevuelta,
                "cantidad_entregada" => $cantidadEntregada,
                "motivo" => $motivo,
                "estado" => $nuevoEstado
            ));

            if (!$resultadoCambio) {
                throw new Exception("Error al actualizar el cambio");
            }

            $pdo->commit();
            echo '<script>
                swal({
                    type: "success",
                    title: "El cambio ha sido actualizado correctamente",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                }).then(function(result){
                    if (result.value) {
                        window.location = "cambio";
                    }
                });
            </script>';
        } catch (Exception $e) {
            $pdo->rollBack();
            echo '<script>
                swal({
                    type: "error",
                    title: "Error al actualizar el cambio",
                    text: "' . $e->getMessage() . '",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
        }
    }
}


     /* =============================================
    BORRAR CAMBIO
    ============================================= */
    static public function ctrBorrarCambio() {
        if (isset($_GET["id"])) {
            try {
                $tabla = "cambios";
                $id = $_GET["id"];

                // Verificar que el cambio no esté aprobado
                $cambio = ModeloCambio::mdlMostrarCambio($tabla, "id", $id);
                if ($cambio["estado"] == "aprobado") {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "No se puede eliminar un cambio aprobado",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                    return;
                }

                $respuesta = ModeloCambio::mdlBorrarCambio($tabla, $id);

                if ($respuesta == "ok") {
                    echo '<script>
                        swal({
                            type: "success",
                            title: "El cambio ha sido eliminado correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if (result.value) {
                                window.location = "cambio";
                            }
                        });
                    </script>';
                } else {
                    throw new Exception("Error al eliminar el cambio");
                }

            } catch (Exception $e) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error al eliminar el cambio",
                        text: "' . $e->getMessage() . '",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }
}
   

    


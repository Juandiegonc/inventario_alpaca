<?php

require_once "conexion.php";

class ModeloKardex {

    /*=============================================
    OBTENER DATOS PARA EL KARDEX
    =============================================*/
    static public function mdlObtenerDatosKardex($fechaInicio, $fechaFin, $idProducto = null) {
        $conexion = Conexion::conectar();
        
        // Array para almacenar todos los movimientos
        $movimientos = [];
        
        // Consulta SQL base - Filtramos por fechas
        $condicionFecha = "WHERE fecha BETWEEN :fechaInicio AND :fechaFin";
        $condicionProducto = "";
        
        // Si se especifica un producto, añadimos el filtro
        if ($idProducto !== null) {
            $condicionProducto = " AND id_producto = :idProducto";
        }
        
        // Obtener entradas de productos
        $sqlEntradas = "SELECT 
                            p.id as id_producto,
                            p.descripcion,
                            p.codigo,
                            p.talla,
                            'Entrada' as tipo_movimiento,
                            p.ubicacion,
                            '' as motivo,
                            p.precio_produccion as costo_unitario,
                            p.stock as cantidad,
                            p.fecha
                        FROM productos p
                        " . $condicionFecha . $condicionProducto;
                        
        $stmtEntradas = $conexion->prepare($sqlEntradas);
        $stmtEntradas->bindParam(":fechaInicio", $fechaInicio, PDO::PARAM_STR);
        $stmtEntradas->bindParam(":fechaFin", $fechaFin, PDO::PARAM_STR);
        
        if ($idProducto !== null) {
            $stmtEntradas->bindParam(":idProducto", $idProducto, PDO::PARAM_INT);
        }
        
        $stmtEntradas->execute();
        $entradas = $stmtEntradas->fetchAll(PDO::FETCH_ASSOC);
        $movimientos = array_merge($movimientos, $entradas);
        
        // Obtener ventas (salidas)
        $sqlVentas = "SELECT 
                            p.id as id_producto,
                            p.descripcion,
                            p.codigo,
                            p.talla,
                            'Venta' as tipo_movimiento,
                            p.ubicacion,
                            '' as motivo,
                            dv.precio_unitario as precio_venta,
                            dv.cantidad,
                            v.fecha
                        FROM detalle_venta dv
                        INNER JOIN productos p ON dv.id_producto = p.id
                        INNER JOIN ventas v ON dv.id_venta = v.id
                        " . $condicionFecha . $condicionProducto;
        
        $stmtVentas = $conexion->prepare($sqlVentas);
        $stmtVentas->bindParam(":fechaInicio", $fechaInicio, PDO::PARAM_STR);
        $stmtVentas->bindParam(":fechaFin", $fechaFin, PDO::PARAM_STR);
        
        if ($idProducto !== null) {
            $stmtVentas->bindParam(":idProducto", $idProducto, PDO::PARAM_INT);
        }
        
        $stmtVentas->execute();
        $ventas = $stmtVentas->fetchAll(PDO::FETCH_ASSOC);
        $movimientos = array_merge($movimientos, $ventas);
        
        // Obtener devoluciones
        $sqlDevoluciones = "SELECT 
                                p.id as id_producto,
                                p.descripcion,
                                p.codigo,
                                p.talla,
                                'Devolución' as tipo_movimiento,
                                p.ubicacion,
                                d.motivo,
                                p.precio_produccion as costo_unitario,
                                d.cantidad,
                                d.fecha
                            FROM devolucion d
                            INNER JOIN productos p ON d.id_producto = p.id
                            " . $condicionFecha . " AND d.estado = 'aprobado'" . $condicionProducto;
        
        $stmtDevoluciones = $conexion->prepare($sqlDevoluciones);
        $stmtDevoluciones->bindParam(":fechaInicio", $fechaInicio, PDO::PARAM_STR);
        $stmtDevoluciones->bindParam(":fechaFin", $fechaFin, PDO::PARAM_STR);
        
        if ($idProducto !== null) {
            $stmtDevoluciones->bindParam(":idProducto", $idProducto, PDO::PARAM_INT);
        }
        
        $stmtDevoluciones->execute();
        $devoluciones = $stmtDevoluciones->fetchAll(PDO::FETCH_ASSOC);
        $movimientos = array_merge($movimientos, $devoluciones);
        
        // Obtener cambios
        $sqlCambios = "SELECT 
                            p.id as id_producto,
                            p.descripcion,
                            p.codigo,
                            p.talla,
                            'Cambio Salida' as tipo_movimiento,
                            p.ubicacion,
                            c.motivo,
                            p.precio_produccion as costo_unitario,
                            c.cantidad_devuelta as cantidad,
                            c.fecha
                        FROM cambios c
                        INNER JOIN productos p ON c.id_producto_devuelto = p.id
                        " . $condicionFecha . " AND c.estado = 'aprobado'" . $condicionProducto;
        
        $stmtCambiosSalida = $conexion->prepare($sqlCambios);
        $stmtCambiosSalida->bindParam(":fechaInicio", $fechaInicio, PDO::PARAM_STR);
        $stmtCambiosSalida->bindParam(":fechaFin", $fechaFin, PDO::PARAM_STR);
        
        if ($idProducto !== null) {
            $stmtCambiosSalida->bindParam(":idProducto", $idProducto, PDO::PARAM_INT);
        }
        
        $stmtCambiosSalida->execute();
        $cambiosSalida = $stmtCambiosSalida->fetchAll(PDO::FETCH_ASSOC);
        $movimientos = array_merge($movimientos, $cambiosSalida);
        
        // Cambios - Entradas
        $sqlCambiosEntrada = "SELECT 
                                p.id as id_producto,
                                p.descripcion,
                                p.codigo,
                                p.talla,
                                'Cambio Entrada' as tipo_movimiento,
                                p.ubicacion,
                                c.motivo,
                                p.precio_produccion as costo_unitario,
                                c.cantidad_entregada as cantidad,
                                c.fecha
                            FROM cambios c
                            INNER JOIN productos p ON c.id_producto_entregado = p.id
                            " . $condicionFecha . " AND c.estado = 'aprobado'" . $condicionProducto;
        
        $stmtCambiosEntrada = $conexion->prepare($sqlCambiosEntrada);
        $stmtCambiosEntrada->bindParam(":fechaInicio", $fechaInicio, PDO::PARAM_STR);
        $stmtCambiosEntrada->bindParam(":fechaFin", $fechaFin, PDO::PARAM_STR);
        
        if ($idProducto !== null) {
            $stmtCambiosEntrada->bindParam(":idProducto", $idProducto, PDO::PARAM_INT);
        }
        
        $stmtCambiosEntrada->execute();
        $cambiosEntrada = $stmtCambiosEntrada->fetchAll(PDO::FETCH_ASSOC);
        $movimientos = array_merge($movimientos, $cambiosEntrada);
        
        // Obtener mermas
        $sqlMermas = "SELECT 
                        p.id as id_producto,
                        p.descripcion,
                        p.codigo, 
                        p.talla,
                        'Merma' as tipo_movimiento,
                        p.ubicacion,
                        m.motivo,
                        p.precio_produccion as costo_unitario,
                        m.cantidad,
                        m.fecha
                    FROM mermas m
                    INNER JOIN productos p ON m.id_producto = p.id
                    " . $condicionFecha . $condicionProducto;
        
        $stmtMermas = $conexion->prepare($sqlMermas);
        $stmtMermas->bindParam(":fechaInicio", $fechaInicio, PDO::PARAM_STR);
        $stmtMermas->bindParam(":fechaFin", $fechaFin, PDO::PARAM_STR);
        
        if ($idProducto !== null) {
            $stmtMermas->bindParam(":idProducto", $idProducto, PDO::PARAM_INT);
        }
        
        $stmtMermas->execute();
        $mermas = $stmtMermas->fetchAll(PDO::FETCH_ASSOC);
        $movimientos = array_merge($movimientos, $mermas);
        
        // Ordenar todos los movimientos por fecha (ascendente)
        usort($movimientos, function($a, $b) {
            return strtotime($a['fecha']) - strtotime($b['fecha']);
        });
        
        return $movimientos;
    }
    
    /*=============================================
    OBTENER SALDO INICIAL
    =============================================*/
    static public function mdlObtenerSaldoInicial($fechaInicio, $idProducto = null) {
        $conexion = Conexion::conectar();
        
        // Obtenemos los datos del producto
        $sql = "SELECT id, descripcion, codigo, talla, stock, precio_produccion
                FROM productos ";
                
        if ($idProducto !== null) {
            $sql .= "WHERE id = :idProducto";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(":idProducto", $idProducto, PDO::PARAM_INT);
        } else {
            $stmt = $conexion->prepare($sql);
        }
        
        $stmt->execute();
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Para cada producto, calculamos su saldo inicial antes de la fecha de inicio
        $saldosIniciales = [];
        
        foreach ($productos as $producto) {
            // Entradas antes de la fecha inicial
            $sqlEntradas = "SELECT COALESCE(SUM(cantidad), 0) as total_entradas
                            FROM (
                                SELECT id_producto, p.stock as cantidad 
                                FROM productos p
                                WHERE p.id = :idProducto AND p.fecha < :fechaInicio
                                
                                UNION ALL
                                
                                SELECT c.id_producto_entregado as id_producto, c.cantidad_entregada as cantidad
                                FROM cambios c
                                WHERE c.id_producto_entregado = :idProducto 
                                AND c.fecha < :fechaInicio
                                AND c.estado = 'aprobado'
                                
                                UNION ALL
                                
                                SELECT d.id_producto, d.cantidad
                                FROM devolucion d
                                WHERE d.id_producto = :idProducto
                                AND d.fecha < :fechaInicio
                                AND d.estado = 'aprobado'
                            ) as entradas";
            
            $stmtEntradas = $conexion->prepare($sqlEntradas);
            $stmtEntradas->bindParam(":idProducto", $producto['id'], PDO::PARAM_INT);
            $stmtEntradas->bindParam(":fechaInicio", $fechaInicio, PDO::PARAM_STR);
            $stmtEntradas->execute();
            $totalEntradas = $stmtEntradas->fetch(PDO::FETCH_ASSOC)['total_entradas'] ?: 0;
            
            // Salidas antes de la fecha inicial
            $sqlSalidas = "SELECT COALESCE(SUM(cantidad), 0) as total_salidas
                            FROM (
                                SELECT dv.id_producto, dv.cantidad
                                FROM detalle_venta dv
                                INNER JOIN ventas v ON dv.id_venta = v.id
                                WHERE dv.id_producto = :idProducto
                                AND v.fecha < :fechaInicio
                                
                                UNION ALL
                                
                                SELECT c.id_producto_devuelto as id_producto, c.cantidad_devuelta as cantidad
                                FROM cambios c
                                WHERE c.id_producto_devuelto = :idProducto
                                AND c.fecha < :fechaInicio
                                AND c.estado = 'aprobado'
                                
                                UNION ALL
                                
                                SELECT m.id_producto, m.cantidad
                                FROM mermas m
                                WHERE m.id_producto = :idProducto
                                AND m.fecha < :fechaInicio
                            ) as salidas";
            
            $stmtSalidas = $conexion->prepare($sqlSalidas);
            $stmtSalidas->bindParam(":idProducto", $producto['id'], PDO::PARAM_INT);
            $stmtSalidas->bindParam(":fechaInicio", $fechaInicio, PDO::PARAM_STR);
            $stmtSalidas->execute();
            $totalSalidas = $stmtSalidas->fetch(PDO::FETCH_ASSOC)['total_salidas'] ?: 0;
            
            // Calcular saldo inicial
            $cantidadInicial = max(0, $totalEntradas - $totalSalidas);
            $valorInicial = $cantidadInicial * $producto['precio_produccion'];
            
            $saldosIniciales[$producto['id']] = [
                'id_producto' => $producto['id'],
                'descripcion' => $producto['descripcion'],
                'codigo' => $producto['codigo'],
                'talla' => $producto['talla'],
                'cantidad' => $cantidadInicial,
                'costo_unitario' => $producto['precio_produccion'],
                'valor_total' => $valorInicial
            ];
        }
        
        return $idProducto !== null ? ($saldosIniciales[$idProducto] ?? null) : $saldosIniciales;
    }
    
    /*=============================================
    LISTAR PRODUCTOS
    =============================================*/
    static public function mdlListarProductos() {
        $stmt = Conexion::conectar()->prepare("SELECT id, codigo, descripcion, talla FROM productos ORDER BY descripcion, talla");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
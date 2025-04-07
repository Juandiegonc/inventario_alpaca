<?php


require_once "conexion.php";

class ModeloDevolucion{
/*=============================================
	MOSTRAR DEVOLUCIÓN
	=============== ==============================*/
    static public function mdlMostrarDevolucion($tabla, $item, $valor){
        if($item !=null){
            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");
            $stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);
            $stmt -> execute();
            return $stmt -> fetch();
        }else{
            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla");
            $stmt -> execute();
            return $stmt -> fetchAll();
        }
        $stmt -> close();
        $stmt = null;
    }





    /*=============================================
	CREAR DEVOLUCIÓN
	=============================================*/


    static public function mdlIngresarDevolucion($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla (id_producto, cantidad, motivo, usuario, estado) 
                                               VALUES (:id_producto, :cantidad, :motivo, :usuario, :estado)");
        $stmt->bindParam(":id_producto", $datos["id_producto"], PDO::PARAM_INT);
        $stmt->bindParam(":cantidad", $datos["cantidad"], PDO::PARAM_INT);
        $stmt->bindParam(":motivo", $datos["motivo"], PDO::PARAM_STR);
        $stmt->bindParam(":usuario", $datos["usuario"], PDO::PARAM_INT);
        $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
    
        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    
        $stmt->close();
        $stmt = null;
    }

        /* ==============================================
        EDITAR DEVOLUCIÓN
        ============================================== */
        static public function mdlEditarDevolucion($tabla, $datos) {
            try {
                // Actualizar la fecha automáticamente
                $fecha = date("Y-m-d H:i:s"); // Fecha y hora actual
    
                $stmt = Conexion::conectar()->prepare("
                    UPDATE $tabla 
                    SET cantidad = :cantidad, 
                        motivo = :motivo, 
                        estado = :estado,
                        fecha = :fecha 
                    WHERE id = :id
                ");
                $stmt->bindParam(":cantidad", $datos["cantidad"], PDO::PARAM_INT);
                $stmt->bindParam(":motivo", $datos["motivo"], PDO::PARAM_STR);
                $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
                $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR); // Fecha automática
                $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
    
                if ($stmt->execute()) {
                    return "ok";
                } else {
                    return "error";
                }
            } catch (Exception $e) {
                error_log("Error en mdlEditarDevolucion: " . $e->getMessage());
                return "error";
            }
        }
        static public function mdlMostrarDevolucionConUsuarioYProducto($tabla, $item, $valor) {
            try {
                if ($item != null && $valor != null) {
                    $stmt = Conexion::conectar()->prepare("
                        SELECT d.*, u.nombre AS nombre_usuario, p.descripcion AS descripcion_producto 
                        FROM $tabla d
                        INNER JOIN usuarios u ON d.usuario = u.id
                        INNER JOIN productos p ON d.id_producto = p.id
                        WHERE d.$item = :$item
                    ");
                    $stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);
                    $stmt->execute();
                    return $stmt->fetch(PDO::FETCH_ASSOC); // Devuelve un array asociativo
                } else {
                    $stmt = Conexion::conectar()->prepare("
                        SELECT d.*, u.nombre AS nombre_usuario, p.descripcion AS descripcion_producto 
                        FROM $tabla d
                        INNER JOIN usuarios u ON d.usuario = u.id
                        INNER JOIN productos p ON d.id_producto = p.id
                    ");
                    $stmt->execute();
                    return $stmt->fetchAll(PDO::FETCH_ASSOC); // Devuelve todos los resultados
                }
            } catch (Exception $e) {
                error_log("Error en mdlMostrarDevolucionConUsuarioYProducto: " . $e->getMessage());
                return [];
            }
        }
        /*=============================================
	BORRAR DEVOLUCIÓN
	=============================================*/

    static public function mdlBorrarDevolucion($tabla, $datos){
        $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");
        $stmt -> bindParam(":id", $datos, PDO::PARAM_INT);

		if($stmt -> execute()){

			return "ok";
		
		}else{

			return "error";	

		}

		$stmt -> close();

		$stmt = null;

	}
	
	    static public function mdlRangoFechasDevoluciones($tabla, $fechaInicial, $fechaFinal) {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE fecha BETWEEN :fechaInicial AND :fechaFinal");
        $stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
        $stmt->bindParam(":fechaFinal", $fechaFinal, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    }


}
?>
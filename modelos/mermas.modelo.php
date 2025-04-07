<?php


require_once "conexion.php";

class ModeloMermas{
/*=============================================
	MOSTRAR MERMAS
	=============== ==============================*/
    static public function mdlMostrarMermas($tabla, $item, $valor) {
        if ($item != null) {
            $stmt = Conexion::conectar()->prepare("
                SELECT m.*, p.codigo  
                FROM $tabla m 
                INNER JOIN productos p ON m.id_producto = p.id 
                WHERE m.$item = :$item
            ");
            $stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch();
        } else {
            $stmt = Conexion::conectar()->prepare("
                SELECT m.*, p.codigo  
                FROM $tabla m 
                INNER JOIN productos p ON m.id_producto = p.id
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        }
    }
    





    /*=============================================
	CREAR MERMA
	=============================================*/


    static public function mdlIngresarMerma($tabla, $datos) {

        $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla (id_producto, cantidad, motivo, usuario) 
        VALUES (:id_producto, :cantidad, :motivo, :usuario)");
    
        $stmt->bindParam(":id_producto", $datos["id_producto"], PDO::PARAM_INT);
        $stmt->bindParam(":cantidad", $datos["cantidad"], PDO::PARAM_INT);
        $stmt->bindParam(":motivo", $datos["motivo"], PDO::PARAM_STR);
        $stmt->bindParam(":usuario", $datos["usuario"], PDO::PARAM_INT);
    
        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    
        $stmt->close();
        $stmt = null;
    }
    


    
    /*=============================================
	EDITAR MERMA
	=============================================*/


    static public function mdlEditarMerma($tabla, $datos) {
        try {
            // Validar que la tabla no esté vacía
            if (empty($tabla)) {
                throw new Exception("El nombre de la tabla no puede estar vacío");
            }
    
            // Preparar la consulta SQL
            $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET cantidad = :cantidad, motivo = :motivo WHERE id = :id");
            $stmt->bindParam(":cantidad", $datos["cantidad"], PDO::PARAM_INT);
            $stmt->bindParam(":motivo", $datos["motivo"], PDO::PARAM_STR);
            $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
    
            // Ejecutar la consulta
            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            // Manejar errores de la base de datos
            return "error: " . $e->getMessage();
        } finally {
            // Cerrar la conexión
            $stmt = null;
        }
    }

        /*=============================================
	BORRAR MERMA
	=============================================*/

    static public function mdlBorrarMerma($tabla, $datos){
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
	
	   
        static public function mdlRangoFechasMermas($tabla, $fechaInicial, $fechaFinal) {
            $pdo = Conexion::conectar();
    
            if ($fechaInicial == null) {
                $stmt = $pdo->prepare("SELECT * FROM $tabla ORDER BY id ASC");
            } elseif ($fechaInicial == $fechaFinal) {
                $stmt = $pdo->prepare("SELECT * FROM $tabla WHERE fecha LIKE :fecha");
                $stmt->bindValue(":fecha", '%' . $fechaFinal . '%', PDO::PARAM_STR);
            } else {
                $stmt = $pdo->prepare("SELECT * FROM $tabla WHERE fecha BETWEEN :fechaInicial AND :fechaFinal");
                $stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
                $stmt->bindParam(":fechaFinal", $fechaFinal, PDO::PARAM_STR);
            }
    
            $stmt->execute();
            return $stmt->fetchAll();
        }
    }

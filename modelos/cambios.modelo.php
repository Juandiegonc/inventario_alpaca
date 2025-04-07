<?php

require_once "conexion.php";

class ModeloCambio{
/*=============================================
	MOSTRAR CAMBIO
	=============== ==============================*/

static public function mdlMostrarCambio($tabla, $item, $valor){
    if($item !=null){
        $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");
        $stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);
        $stmt -> execute();
        return $stmt -> fetch(PDO::FETCH_ASSOC);

    }else{
        $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla");
        $stmt -> execute();
        return $stmt -> fetchAll(PDO::FETCH_ASSOC);

    }
    $stmt -> close();
    $stmt = null;
}

/*=============================================
	CREAR CAMBIO
	=============== ==============================*/

    static public function mdlIngresarCambio($tabla, $datos) {

        $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla (id_producto_devuelto, cantidad_devuelta, id_producto_entregado, cantidad_entregada, estado, motivo, usuario) 
        VALUES (:id_producto_devuelto, :cantidad_devuelta, :id_producto_entregado, :cantidad_entregada, :estado, :motivo, :usuario)");
    
        $stmt->bindParam(":id_producto_devuelto", $datos["id_producto_devuelto"], PDO::PARAM_INT);
        $stmt->bindParam(":cantidad_devuelta", $datos["cantidad_devuelta"], PDO::PARAM_INT);
        $stmt->bindParam(":id_producto_entregado", $datos["id_producto_entregado"], PDO::PARAM_INT);
        $stmt->bindParam(":cantidad_entregada", $datos["cantidad_entregada"], PDO::PARAM_INT);
        $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
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
	EDITAR CAMBIO
	=============================================*/


    static public function mdlEditarCambio($tabla, $datos){
       
        $stmt = Conexion::conectar()->prepare("
            UPDATE $tabla 
            SET cantidad_devuelta = :cantidad_devuelta, 
                cantidad_entregada = :cantidad_entregada, 
                estado = :estado, 
                motivo = :motivo
            WHERE id = :id");
    
        $stmt->bindParam(":cantidad_devuelta", $datos["cantidad_devuelta"], PDO::PARAM_INT);
        $stmt->bindParam(":cantidad_entregada", $datos["cantidad_entregada"], PDO::PARAM_INT);
        $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
        $stmt->bindParam(":motivo", $datos["motivo"], PDO::PARAM_STR);
        $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
    
        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }
    
    
    

 /*=============================================
	BORRAR CAMBIO
	=============================================*/
    static public function mdlBorrarCambio($tabla, $datos){
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
}
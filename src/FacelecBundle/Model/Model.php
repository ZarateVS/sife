<?php

namespace FacelecBundle\Model;

use Symfony\Component\HttpFoundation\Session\Session;
use FacelecBundle\Controller\DefaultController;

class Model{
    private $conexion;
    private $dns;
    private $usuario;
    private $password;
    private $numErr;
    private $desErr;

    public function __construct(){
        $this->dns      = $this->getDSN();
        $this->usuario  = "";
        $this->password = "";
        if(!isset($this->conexion)){
            $this->conexion = (odbc_connect($this->dns, $this->usuario, $this->password)) or die ('En Mantenimiento...');
            $sql="set names 'utf8'";
            //$this->query($sql);
        }
    }

    function getDSN(){
        $session = new Session();
        return $session->get('dsn');
    }

    public function getConexion(){
        if(isset($this->conexion)){
            return $this->conexion;
        }
        return "Desconectado";
    }

    public function autocommit(){
        $resultado = odbc_autocommit($this->conexion, false);
        return $resultado;
    }

    public function commit(){
        $resultado = odbc_commit($this->conexion);
        return $resultado;
    }

    public function rollback(){
        $resultado = odbc_rollback($this->conexion);
        return $resultado;
    }

    public function query($consulta){
        $resultado = odbc_exec($this->conexion, utf8_decode($consulta));
        if( $resultado === false ){
            //$this->setError( odbc_error( $this->conexion ), odbc_errormsg( $this->conexion ), $consulta );
            $log = new DefaultController();
            $error = "".odbc_error( $this->conexion ).", ".odbc_errormsg( $this->conexion ).", En la consulta: ".utf8_decode($consulta)." ";
            $log->log($error);
        }
        return $resultado;
    }

    public function fetch_array($consulta){
        if( !$consulta ){
            return 0;
        }
        return odbc_fetch_array($consulta);
    }

    public function num_rows($consulta){
        if( !$consulta ){
            return 0;
        }
        return odbc_num_rows($consulta);
    }

    public function num_fields($consulta){
        if( !$consulta ){
            return 0;
        }
        return odbc_num_fields($consulta);
    }

    public function result_cam($consulta,$val){
        if( !$consulta ){
            return 0;
        }
        return odbc_result($consulta,$val);
    }

    public function close(){
        if(isset($this->conexion)){
            odbc_close($this->conexion);
        }
    }

    public function setError($no_err, $err, $qry){
        $this->numErr = $no_err;
        $this->desErr = $err;
    }

    public function getnumErr(){
        return $this->numErr;
    }

    public function getdesErr(){
        return $this->desErr;
    }

    public function free($r){
        odbc_free_result($r);
    }

}
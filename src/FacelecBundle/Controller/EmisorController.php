<?php

namespace FacelecBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Finder\Finder;
use FacelecBundle\Controller\GenerarMenuController;
use FacelecBundle\Model\ModelCorporativo;
use FacelecBundle\Model\Model;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class EmisorController extends Controller
{
    /**
     * @Route("/Emisor", name="Emisor")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function EmisorAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("Emisor")) {
                return $this->render('FacelecBundle:Catalogos/Generales/Emisor:Emisor.html.twig', $session->get('menu'));
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/ObtenerClientes", name="ObtenerClientes")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ObtenerClientesAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("Emisor")) {
                $params = [
                    'clientes'=>$this->ObtenerClientes()
                ];
                $array = array_merge($params, $session->get('menu'));
                return $this->render('FacelecBundle:Catalogos/Generales/Emisor:TablaClientes.html.twig', $array);
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/ModificarEmisor", name="ModificarEmisor")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ModificarEmisorAction(SessionInterface $session, Request $request) {
        if ($session->has("Userid")) {
            if ($session->has("Emisor")) {
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $getdata = new DefaultController();
                    $datos = $getdata->getData($request);
                    if (!isset($datos['id_cliente'])) {
                        $id = $_POST['id'];
                        $cp = $_POST['cp'];
                        $tp = $_POST['tp'];
                        $params = [
                            'regs'=>$this->ObtenerRegimenesFiscales($tp),
                            'estados'=>$this->ObtenerEstados(),
                            'municipios'=>$this->ObtenerMunicipios(),
                            'clientes'=>$this->ObtenerCliente($id),
                            'colonias'=>$this->ObtenerColoniasPorCp($cp)
                        ];
                        $array = array_merge($params, $session->get('menu'));
                        return $this->render('FacelecBundle:Catalogos/Generales/Emisor:ModificarEmisor.html.twig', $array);
                    }
                    else {
                        if($this->validarInfo($datos)) {
                            if (!$this->validarNombre($datos)) {
                                $Userid = $session->get('Userid');
                                if($this->ActualizarCliente($datos, $Userid))
                                    die("1"); // Operación completa
                                else
                                    die("3");
                            }
                            else {
                                die("2"); // Nombre existente
                            }
                        }
                        else {
                            die("3"); // No se pudo realizar la operación
                        }
                    }
                } else return $this->render('FacelecBundle:Catalogos/Generales/Emisor:Emisor.html.twig', $session->get('menu'));
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/ObtenerInfoCampos", name="ObtenerInfoCampos")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ObtenerInfoCamposAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("Emisor")) {
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $fn = $_POST['fn'];
                    $id = $_POST['id'];
                    switch ($fn) {
                        case "1":
                            die($this->ObtenerMunicipiosPorEstado($id));
                        case "2":
                            die($this->ObtenerEstadoPorMunicipio($id));                           
                        case "3":
                            die(json_encode($this->ObtenerColoniasPorMunicipio($id)));
                        case "4":
                            die($this->ObtenerCiudad($id));
                        case "5":
                            die($this->ObtenerEdoMpio($id));
                        case "6":
                            die(json_encode($this->ObtenerColoniasPorCp($id)));
                        case "7":
                            die(json_encode($this->ObtenerMunicipios()));
                        case "8":
                            die(json_encode($this->ObtenerRegimenesFiscales($id)));
                    }
                } else return $this->render('FacelecBundle:Catalogos/Generales/Emisor:Emisor.html.twig', $session->get('menu'));
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/ActivarDesactivarCliente", name="ActivarDesactivarCliente")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ActivarDesactivarClienteAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("Emisor")) {
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $valor = $_POST['valor'];
                    $id = $_POST['idcte'];
                    $rs = $_POST['rs'];
                    $Userid = $session->get('Userid');
                    if ($this->ActivarDesactivarCliente($id, $rs, $valor, $Userid))
                        die("1");
                    else
                        die("2");
                } else return $this->render('FacelecBundle:Catalogos/Generales/Emisor:Emisor.html.twig', $session->get('menu'));
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    public function ActualizarCliente ($datos, $Userid) {
        $conn = new Model();
        $sql_select = "SELECT razonsocial, tipoPer, rfc, correoE, telefono1, calle, numExt, numInt, cp, estado, D.municipio, C.colonia AS colonia, A.ciudad, E.descripcion
                        FROM clientes A INNER JOIN estado B ON A.id_estado=B.id_estado INNER JOIN colonia C ON A.id_colonia=C.id_colonia INNER JOIN municipio D ON A.id_municipio=D.id_municipio INNER JOIN c_regimenfiscal E ON A.regimenFiscal=E.regimenFiscal
                        WHERE id_cliente=".$datos['id_cliente']."";
        $result1 = $conn->query($sql_select);
        $info = $this->obtenerEdoColMpioReg($datos['colonia'], $datos['reg_fiscal'], $conn);
        while ($row = $conn->fetch_array($result1)) {
            $tp = ($row['tipoPer'] == 0) ? "Moral" : "Física";
            $tipo_p = ($datos['tipo_p'] == 0) ? "Moral" : "Física";
            $detalle = "Actualizó el siguiente cliente de: Nombre/Razón Social: ".utf8_encode($row['razonsocial']).", Persona: ".$tp.", RFC: ".$row['rfc'].", Correo: ".$row['correoE'].", Teléfono: ".$row['telefono1'].", Calle: ".utf8_encode($row['calle']).", Num Ext: ".$row['numExt'].", Num Int: ".$row['numInt'].", CP: ".$row['cp'].", Estado: ".utf8_encode($row['estado']).", Municipio: ".utf8_encode($row['municipio']).", Colonia: ".utf8_encode($row['colonia']).", Ciudad: ".utf8_encode($row['ciudad']).", Régimen Fiscal: ".utf8_encode($row['descripcion'])."-- A: Nombre/Razón Social: ".$datos['rs'].", Persona: ".$tipo_p.", RFC: ".$datos['rfc'].", Correo: ".$datos['correo'].", Teléfono: ".$datos['tel'].", Calle: ".$datos['calle'].", Num Ext: ".$datos['num_ext'].", Num Int: ".$datos['num_int'].", CP: ".$datos['cp'].", Estado: ".$info['edo'].", Municipio: ".$info['mpio'].", Colonia: ".$info['col'].", Ciudad: ".$datos['ciudad'].", Régimen Fiscal: ".$info['reg'];
        }
        $sql_update = "UPDATE clientes SET razonsocial='".addslashes($datos['rs'])."', tipoPer=".$datos['tipo_p'].", rfc='".$datos['rfc']."', correoE='".$datos['correo']."', telefono1='".$datos['tel']."', calle='".addslashes($datos['calle'])."', numExt='".$datos['num_ext']."', numInt='".$datos['num_int']."', cp='".$datos['cp']."', id_estado=".$datos['estado'].", id_municipio=".$datos['municipio'].", id_colonia=".$datos['colonia'].", ciudad='".addslashes($datos['ciudad'])."', regimenFiscal=".$datos['reg_fiscal']." WHERE id_cliente=".$datos['id_cliente'].";";
        $result2 = $conn->query($sql_update);
        if ($conn->num_rows($result2) > 0) {
            $accion = "Actualizar cliente";
            $this->registrarBitacora($accion, $detalle, $Userid, $conn);
        }
        $conn->close();
        return $result2;
    }
    
    public function ActivarDesactivarCliente($id, $rs, $valor, $Userid) {
        $conn = new Model();
        $sql = "UPDATE clientes SET activo=$valor WHERE id_cliente=$id";
        $result = $conn->query($sql);
        if ($conn->num_rows($result) > 0) {
            if ($valor == 1) {
                $accion = "Activar cliente";
                $detalle = "Activó el cliente: $rs";
            }
            else {
                $accion = "Desactivar cliente";
                $detalle = "Desactivó el cliente: $rs";
            }
            $this->registrarBitacora($accion, $detalle, $Userid, $conn);
        }
        $conn->close();
        return $result;
    }
    
    public function ObtenerClientes() {
        $conn = new Model();
        $sql = "SELECT id_cliente, rfc, razonsocial, estado, C.colonia AS colonia, A.activo, A.cp, tipoPer
                FROM clientes A INNER JOIN estado B ON A.id_estado=B.id_estado INNER JOIN colonia C ON A.id_colonia=C.id_colonia
                ORDER BY razonsocial";
        $clientes = $this->setCodingUtf8($sql, $conn);
        /*$result = $conn->query($sql);
        while ($row=$conn->fetch_array($result)) {
            $clientes[] = array(
                'id_cliente'=>$row['id_cliente'],
                'rfc'=>$row['rfc'],
                'razonsocial'=>utf8_encode($row['razonsocial']),
                'estado'=>utf8_encode($row['estado']),
                'colonia'=>utf8_encode($row['colonia']),
                'activo'=>$row['activo'],
                'cp'=>$row['cp'],
                'tipoPer'=>$row['tipoPer']
            );
        }*/
        $conn->close();
        return $clientes;
    }
    
    public function ObtenerCliente($id) {
        $conn = new Model();
        $sql = "SELECT id_cliente, razonsocial, tipoPer, rfc, correoE, telefono1, calle, numExt, numInt, cp, id_estado, id_municipio, id_colonia, ciudad, regimenFiscal FROM clientes WHERE id_cliente=$id";
        $cliente = $this->setCodingUtf82($sql, $conn, 2);
        /*$result= $conn->query($sql);
        $cliente=[];
        while ($row = $conn->fetch_array($result)) {
            $cliente [] = array(
                'id_cliente'=>$row['id_cliente'],
                'razonsocial'=>utf8_encode($row['razonsocial']),
                'tipoPer'=>$row['tipoPer'],
                'rfc'=>$row['rfc'],
                'correoE'=>$row['correoE'],
                'telefono1'=>$row['telefono1'],
                'calle'=>utf8_encode($row['calle']),
                'numExt'=>$row['numExt'],
                'numInt'=>$row['numInt'],
                'cp'=>$row['cp'],
                'id_estado'=>$row['id_estado'],
                'id_municipio'=>$row['id_municipio'],
                'id_colonia'=>$row['id_colonia'],
                'ciudad'=>utf8_encode($row['ciudad']),
                'regimenFiscal'=>$row['regimenFiscal']
            );
        }*/
        $conn->close();
        return $cliente;
    }
    
    public function ObtenerRegimenesFiscales($tp) {
        $conn = new Model();
        $sql = "SELECT regimenFiscal, descripcion FROM c_regimenfiscal WHERE tipo_persona IN ('$tp',2) ORDER BY descripcion";
        $regs = $this->setCodingUtf8($sql, $conn);
        /*$result = $conn->query($sql);
        while ($row = $conn->fetch_array($result)) {
            $regs[] = array(
                'regimenFiscal'=>$row['regimenFiscal'],
                'descripcion'=>utf8_encode($row['descripcion'])
            );
        }*/
        $conn->close();
        return $regs;
    }
    
    public function ObtenerEstados() {
        $conn = new Model();
        $sql = "SELECT id_estado, estado FROM estado WHERE id_estado!=0 ORDER BY estado";
        $estados = $this->setCodingUtf8($sql, $conn);
        /*$result = $conn->query($sql);
        while ($row = $conn->fetch_array($result)) {
            $estados[] = array(
                'id_estado'=>$row['id_estado'],
                'estado'=>utf8_encode($row['estado'])
            );
        }*/
        $conn->close();
        return $estados;
    }
    
    public function ObtenerEstadoPorMunicipio($id) {
        $conn = new Model();
        $sql = "SELECT A.id_estado
                FROM estado A INNER JOIN municipio B ON A.id_estado=B.id_estado
                WHERE id_municipio='$id'";
        $edo = $this->setCodingUtf82($sql, $conn, 1);
        /*$result = $conn->query($sql);
        $edo = [];
        while ($row = $conn->fetch_array($result)) {
            $edo = [
                'id_estado'=>$row['id_estado']
            ];
        }*/
        $conn->close();
        return json_encode($edo);
    }
    
    public function ObtenerEdoMpio($cp) {
        $conn = new Model();
        $sql = "SELECT A.id_estado, B.id_municipio
                FROM estado A INNER JOIN municipio B ON A.id_estado=B.id_estado INNER JOIN colonia C ON B.id_municipio=C.id_municipio
                WHERE codigoPostal='$cp'
                GROUP BY A.id_estado;";
        $result = $conn->query($sql);
        $info = [];
        while ($row = $conn->fetch_array($result)) {
            $info = [
                'id_edo'=>$row['id_estado'],
                'id_mpio'=>$row['id_municipio']
            ];
        }
        $conn->close();
        return json_encode($info);
    }
    
    public function ObtenerMunicipios() {
        $conn = new Model();
        $sql = "SELECT id_municipio, municipio FROM municipio WHERE id_municipio!=0 ORDER BY municipio";
        $municipios = $this->setCodingUtf8($sql, $conn);
        /*$result = $conn->query($sql);
        while ($row = $conn->fetch_array($result)) {
            $municipios[] = array(
                'id_municipio'=>$row['id_municipio'],
                'municipio'=>utf8_encode($row['municipio'])
            );
        }*/
        $conn->close();
        return $municipios;
    }
    
    public function ObtenerMunicipiosPorEstado($id) {
        $conn = new Model();
        $sql = "SELECT id_municipio, municipio FROM municipio WHERE id_estado='$id' ORDER BY municipio";
        $mpios = $this->setCodingUtf8($sql, $conn);
        /*$result = $conn->query($sql);
        $mpios = [];
        while ($row = $conn->fetch_array($result)) {
            $mpios[] = array(
                'id_municipio'=>$row['id_municipio'],
                'municipio'=>utf8_encode($row['municipio'])
            );
        }*/
        $conn->close();
        return json_encode($mpios);
    }
    
    public function ObtenerColoniasPorCp($cp) {
        $conn = new Model();
        $sql = "SELECT id_colonia, colonia FROM colonia WHERE codigoPostal='$cp' ORDER BY colonia";
        $colonias = $this->setCodingUtf8($sql, $conn);
        /*$result = $conn->query($sql);
        $colonias = [];
        while ($row = $conn->fetch_array($result)) {
            $colonias[] = array(
                'id_colonia'=>$row['id_colonia'],
                'colonia'=>utf8_encode($row['colonia'])
            );
        }*/
        $conn->close();
        return $colonias;
    }
    
    public function ObtenerColoniasPorMunicipio($id) {
        $conn = new Model();
        $sql = "SELECT id_colonia, colonia FROM colonia WHERE id_municipio='$id' ORDER BY colonia";
        $colonias = $this->setCodingUtf8($sql, $conn);
        /*$result = $conn->query($sql);
        $colonias = [];
        while ($row = $conn->fetch_array($result)) {
            $colonias [] = array(
                'id_colonia'=>$row['id_colonia'],
                'colonia'=>utf8_encode($row['colonia'])
            );
        }*/
        //die(print_r($colonias));
        $conn->close();
        return $colonias;
    }
    
    public function ObtenerCiudad($id) {
        $conn = new Model();
        $sql = "SELECT ciudad, codigoPostal FROM colonia WHERE id_colonia='$id'";
        $result = $conn->query($sql);
        $ciudad = "";
        $info = [];
        while ($row = $conn->fetch_array($result)) {
            //$ciudad = utf8_encode($row['ciudad']);
            $info = [
                'ciudad'=>utf8_encode($row['ciudad']),
                'codigoPostal'=>$row['codigoPostal']
            ];
        }
        $conn->close();
        return json_encode($info);
    }
    
    public function validarInfo($datos) {
        
        $a = array_values($datos);
        
        // Validar campos vacios
        $no_nulos = [$a[0], $a[1], $a[2], $a[3], $a[4], $a[5], $a[7], $a[8], $a[10], $a[11], $a[12], $a[13]];
        for ($i = 0; $i < sizeof($no_nulos); $i++) {
            if ($no_nulos[$i] === "" )
                return 0;
        }
        
        // Validar enteros
        if ($a[6]=="")
            $a[6] = "0";
        $enteros = [$a[0], $a[1], $a[2], $a[6], $a[10], $a[11], $a[12], $a[13]];
        for ($i = 0; $i < sizeof($enteros); $i++) {
            if (!ctype_digit($enteros[$i]))
                return 0;
        }
        
        // Validar cadenas
        $cadenas = [$a[3], $a[4], $a[5], $a[6], $a[7],$a[8], $a[9], $a[10],$a[14]];
        $longs = [13, 120, 100, 15, 100, 10, 10, 10, 50];
        for ($i = 0; $i < sizeof($cadenas); $i++) {
            if (strlen($cadenas[$i]) > $longs[$i])
                return 0;   
        }
        
        // Validar apostrofe
        $sin_apostrofe = [$a[3], $a[5], $a[6], $a[8], $a[9], $a[10]];
        for ($i = 0; $i< sizeof($sin_apostrofe); $i++) {
            if (strpos($sin_apostrofe[$i],"'") !== false)
                return 0;
        }
        
        // Validar RFC
        if ($a[2] === "0")
            $expr_rfc = '/^([A-Z,Ñ,&]{3}([0-9]{2})(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[A-Z|\d]{3})$/'; // RFC Persona Moral
        else 
            $expr_rfc = '/^([A-Z,Ñ,&]{4}([0-9]{2})(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[A-Z|\d]{3})$/'; // RFC Persona Fisica
        
        if(!preg_match($expr_rfc,$a[3]))
            return 0;
        
        // Validar Correo
        $expr_correo = '/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/';
        if(!preg_match($expr_correo,$a[5]))
            return 0;
        
        return 1;
    }
    
    public function validarNombre($datos) {
        $conn = new Model();
        $sql = "SELECT * FROM clientes WHERE razonsocial LIKE '".addslashes($datos['rs'])."' AND id_cliente NOT IN (".$datos['id_cliente'].")";
        $result = $conn->query($sql);
        $cliente = [];
        while ($row = $conn->fetch_array($result)) {
            $cliente[] = $row;
        }
        $conn->close();
        return $cliente;
    }
    
    public function obtenerEdoColMpioReg($id, $reg,$conn) {
        $sql = "SELECT estado, municipio, colonia, (SELECT descripcion FROM c_regimenfiscal WHERE regimenFiscal='$reg') AS regimen
                FROM estado A INNER JOIN municipio B ON A.id_estado=B.id_estado INNER JOIN colonia C ON B.id_municipio=C.id_municipio
                WHERE id_colonia=$id";
        $r = $conn->query($sql);
        $info = [];
        while ($row = $conn->fetch_array($r)) {
            $info = [
                'edo'=> utf8_encode($row['estado']),
                'mpio'=> utf8_encode($row['municipio']),
                'col'=> utf8_encode($row['colonia']),
                'reg'=> utf8_encode($row['regimen'])
            ];
        }
        return $info;
    }
    
    public function registrarBitacora ($accion, $detalle, $Userid, $conn) {
        $idmnu = $this->container->getParameter('Emisor');
        $sql = "INSERT INTO sis_bitacora (idusr, id_mnu, fch_bitacora, accion, detalle) VALUES ($Userid, $idmnu, now(), '$accion', '".addslashes($detalle)."')";
        $conn->query($sql);
    }
    
    public function setCodingUtf8($sql, $conn) {
        $r = $conn->query($sql);
        $array = [];
        while ($row = $conn->fetch_array($r)) {
            $keys = array_keys($row);
            for ($i = 0; $i < sizeof($row); $i++) {
                $a[$keys[$i]] = utf8_encode($row[$keys[$i]]);
            }
            $array [] = $a;
        }
        return $array;
    }
    
    public function setCodingUtf82($sql, $conn, $format) {
        $r = $conn->query($sql);
        $array = [];
        while ($row = $conn->fetch_array($r)) {
            $keys = array_keys($row);
            for ($i = 0; $i < sizeof($row); $i++) {
                $a[$keys[$i]] = utf8_encode($row[$keys[$i]]);
            }
            $array [] = $a;
        }
        if ($format == 1)
            return $a;
        else
            return $array;
    }
}
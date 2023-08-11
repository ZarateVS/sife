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

class FacturaController extends Controller
{     
    /**
     * @Route("/FacturaManual", name="FacturaManual")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function FacturaManualAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("FacturaManual")) {
                $Userid = $session->get('Userid');
                $params = [
                    'factura' => $this->VerificarFactura($Userid),
                    'receptores' => $this->ObtenerReceptores(),
                    'series' => $this->ObtenerSeries($Userid),
                    'seriesR' => $this->ObtenerSeriesR($Userid),
                    'tiposRel' => $this->ObtenerTipoRelacion(),
                    'metodosPago' => $this->ObtenerMetodoPago(),
                    'formasPago' => $this->ObtenerFormaPago(),
                    'tiposComp' => $this->ObtenerTipoComprobante(),
                    'monedas' => $this->ObtenerMonedas()
                ];
                $array = array_merge($params, $session->get('menu'));
                return $this->render('FacelecBundle:Facturacion/FacturaManual:RealizarFactura.html.twig', $array);
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/AccionesFactura", name="AccionesFactura")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function AccionesFacturaAction(SessionInterface $session, Request $request) {
        if ($session->has("Userid")) {
            if ($session->has("FacturaManual")) {
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $Userid = $session->get('Userid');
                    $getdata = new DefaultController();
                    $datos = $getdata->getData($request);
                    
                    switch ($datos['fn']) {
                        case 'actDato':
                            die($this->ActualizarDato($Userid, $datos));
                        case 'actRec':
                            if ($this->ActualizarReceptor($Userid, $datos))
                                die($this->ObtenerUsoCfdi($datos));
                            else
                                die("0");
                        case 'actSerie':
                            die($this->ActualizarSerie($Userid, $datos['serie']));
                        case 'actLugExp':
                            die($this->ActualizarLugarExpedicion($Userid, $datos['cp']));
                        case 'rmvItem':
                            die($this->removeItem($Userid, $datos));
                        case 'getProdCod':
                            die($this->ObtenerProductoPorCodigo($Userid, $datos));
                        case 'getProdNom':
                            die($this->ObtenerProductoPorNombre($Userid, $datos));
                        case 'getUsoCfdi':
                            die($this->ObtenerUsoCfdi($datos));
                        case 'getCfdiR':
                            if (isset($datos['uuid_r']))
                                die($this->ObtenerDatosCfdiRuuid($Userid, $datos));
                            else
                                die($this->ObtenerDatosCfdiR($Userid, $datos));
                        case 'borrarSRUG':
                            die($this->borrSRUG($Userid));
                        case 'elIt':
                            //die(print_r($datos));
                            die(print($this->elIt($Userid, $datos)));
                        case 'celIt':
                            die($this->celIt($Userid, $datos));
                        case 'genFac':
                            die($this->genFac($Userid, $datos));
                        default:
                            break;
                    }
                } return $this->render('FacelecBundle:Facturacion/FacturaManual:RealizarFactura.html.twig', $session->get('menu'));
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }

    /**
     * @Route("/ItemsFactura", name="ItemsFactura")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ItemsFacturaAction(SessionInterface $session, Request $request) {
        if ($session->has("Userid")) {
            if ($session->has("FacturaManual")) {
                $Userid = $session->get('Userid');
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $getdata = new DefaultController();
                    $datos = $getdata->getData($request);                    
                    $item = $this->addItem($Userid, $datos);
                    /*$params = [
                        'items' => $this->getItem($Userid, $item),
                        'datos' => $this->hdrFac($Userid)
                    ];*/
                }
                /*else {
                    $params = [
                        'items' => $this->obtItems($Userid),
                        'datos' => $this->hdrFac($Userid)
                    ];
                }*/
                    $params = [
                        'items' => $this->obtItems($Userid),
                        'datos' => $this->hdrFac($Userid)
                    ];
                //die(print_r($params));
                $array = array_merge($params, $session->get('menu'));
                return $this->render('FacelecBundle:Facturacion/FacturaManual:TablaProductosFactura.html.twig', $array);
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }

    /**
     * @Route("/GenerarFactura", name="GenerarFactura")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function GenerarFacturaAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("FacturaManual")) {
                $Userid = $session->get('Userid');
                $datRep = $this->datRep($Userid);
                $session->set('datRep', $datRep);
                //print_r($session->get('datRep'));
                //die("<b>Error:</b><br>");
                die($this->generarFactura($Userid));
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/ReporteFactura", name="ReporeFactura")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ReporeFacturaAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("FacturaManual")) {
                $datos = $session->get('datRep');
                //die(print_r($datos));
                $resp = $this->genReporte($datos);
                $params = [ 'datos' => $resp ];
                //die(print_r($params));
                $array = array_merge($params, $session->get('menu'));
                return $this->render('FacelecBundle:Facturacion/FacturaManual:ReporteFactura.html.twig', $array);
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/DescargaFactura", name="DescargaFactura")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function DescargaFacturaAction(SessionInterface $session, Request $request) {
        if ($session->has("Userid")) {
            if ($session->has("FacturaManual")) {
                $enlace = $_REQUEST["src"];
                $nomb = explode("/", $_REQUEST["src"]);
                header ("Content-Disposition: attachment; filename=".$nomb[6]." ");
                header ("Content-Type: application/octet-stream");
                header ("Content-Length: ".filesize($enlace));
                readfile($enlace);
                exit;
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /* ----------------------- FUNCIONES ------------------------- */

    public function VerificarFactura($idusr) {
        $conn = new Model();
        $sql_sel = "SELECT * FROM fctmnl WHERE idUsr = $idusr";
        $result = $conn->query($sql_sel);
        $resp = [];
        while ($row = $conn->fetch_array($result)) {
            $resp [] = $row;
        }
        if (!$resp) {
            $sql_ins = "INSERT INTO fctmnl (idUsr) VALUES ($idusr)";
            $conn->query($sql_ins);
            $result = $conn->query($sql_sel);
            while ($row = $conn->fetch_array($result)) {
                $resp [] = $row;
            }
            $accion = "Realizar Factura Manual";
            $detalle = "Comenzó a realizar una factura manual";
            $this->registrarBitacora($accion, $detalle, $idusr, $conn);
        }
        $resp[0]['fecha'] = substr($resp[0]['fecha'], 0, 10);
        $conn->close();
        $resp[0]['estado'] = $this->ObtenerLugarExpedicion($resp[0]['lugarExpedicion']);
        return $resp;
    }
    
    /*public function getCliente( $idu ){
        $sql = "SELECT id_cliente FROM sis_usr WHERE idUsr = $idu ";
        $conn = new Model();
        $result = $conn->query( $sql );
        $rows = $conn->fetch_array($result);
        return $rows["id_cliente"];
    }*/

    public function ObtenerReceptores() {
        $conn = new Model();
        $sql = "SELECT id_receptor, nombreRazon FROM receptor WHERE activo = 1 ORDER BY nombreRazon";
        $resp = $this->setCodingUtf8($sql, $conn);
        $conn->close();
        return $resp;
    }

    public function ObtenerSeries($Userid) {
        $conn = new Model();
        $sql = "SELECT s.serie, s.id_tiposerie FROM series s INNER JOIN sis_usrcli uc ON s.id_cliente = uc.id_cliente WHERE uc.idUsr = $Userid AND s.manual = 1 AND s.activo = 1 ORDER BY serie";
        $resp = $this->setCodingUtf8($sql, $conn);
        $conn->close();
        return $resp;
    }
    
    public function ObtenerSeriesR($Userid) {
        $conn = new Model();
        $sql = "SELECT serie FROM series s INNER JOIN sis_usrcli uc ON s.id_cliente = uc.id_cliente WHERE uc.idUsr = $Userid AND s.activo = 1 AND s.id_tiposerie = 1 ORDER BY serie";
        $resp = $this->setCodingUtf8($sql, $conn);
        $conn->close();
        return $resp;
    }

    public function ObtenerUsoCfdi($datos) {
        $conn = new Model();
        $sql = "SELECT tipoPer FROM receptor WHERE id_receptor = ".$datos['recep'].";";
        $result = $conn->query($sql);
        $resp = "";
        while ($row = $conn->fetch_array($result)) {
            $campo = ($row['tipoPer'] == 1) ? 'fisica' : 'moral';
            $sql = "SELECT usocfdi, descripcion FROM c_usocfdi WHERE $campo=1 ORDER BY descripcion";
            $resp = $this->setCodingUtf8($sql, $conn);
        }
        $conn->close();
        return json_encode($resp);
    }
    
    public function ObtenerTipoRelacion() {
        $conn = new Model();
        $sql = "SELECT tiporelacion, descripcion FROM c_tiporelacion ORDER BY descripcion";
        $resp = $this->setCodingUtf8($sql, $conn);
        $conn->close();
        return $resp;
    }
    
    public function ObtenerMetodoPago() {
        $conn = new Model();
        $sql = "SELECT metodopago, descripcion FROM c_metodopago ORDER BY descripcion";
        $resp = $this->setCodingUtf8($sql, $conn);
        $conn->close();
        return $resp;
    }
    
    public function ObtenerFormaPago() {
        $conn = new Model();
        $sql = "SELECT formaPago, descripcion FROM c_formapago ORDER BY descripcion";
        $resp = $this->setCodingUtf8($sql, $conn);
        $conn->close();
        return $resp;
    }
    
    public function ObtenerTipoComprobante() {
        $conn = new Model();
        $sql = "SELECT tipodecomprobante, descripcion FROM c_tipodecomprobante WHERE activo = 1 ORDER BY descripcion";
        $resp = $this->setCodingUtf8($sql, $conn);
        $conn->close();
        return $resp;
    }
    
    public function ObtenerMonedas() {
        $conn = new Model();
        $sql = "SELECT moneda, descripcion FROM c_moneda ORDER BY descripcion";
        $resp = $this->setCodingUtf8($sql, $conn);
        $conn->close();
        return $resp;
    }
    
    /**********************************************************************************************/
    /**********************************************************************************************/

    public function ActualizarDato($idusr, $datos) {
        $conn = new Model();
        if ($datos['campo'] == 'fecha' && $datos['valor'] == '')
            $sql = "UPDATE fctmnl SET ".$datos['campo']." = NULL WHERE idUsr = $idusr";
        else
            $sql = "UPDATE fctmnl SET ".$datos['campo']." = '".$datos['valor']."' WHERE idUsr = $idusr";
        $result = $conn->query($sql);
        $resp = ($conn->num_rows($result) > 0) ? 1 : 0;
        $conn->close();
        return $resp;
    }
    
    public function ActualizarReceptor($idusr, $datos) {
        $conn = new Model();
        $sql = "SELECT rfc, nombreRazon, correoE FROM receptor WHERE id_receptor = ".$datos['recep'].";";
        $recep = $this->setCodingUtf8($sql, $conn);
        $rfc = '';  $nom = '';  $correo = '';
        if ($recep) {
            $rfc = $recep[0]['rfc'];
            $nom = $recep[0]['nombreRazon'];
            $correo = $recep[0]['correoE'];
        }
        $sql = "UPDATE fctmnl
                SET id_receptor = ".$datos['recep'].", rfc_receptor = '".$rfc."', receptor = '".$nom."', correoenvio = '".$correo."', usoCFDI = ''
                WHERE idUsr = $idusr";
        $result = $conn->query($sql);
        $resp = ($conn->num_rows($result) > 0) ? 1 : 0;
        $conn->close();
        return $resp;
    }
    
    public function ActualizarSerie($idusr, $serie) {
        $conn = new Model();
        $sql = "SELECT id_tiposerie, id_sucursal, id_cliente FROM series WHERE serie = '$serie' AND activo = 1";
        $result = $conn->query($sql);
        $row = $conn->fetch_array($result);
        $folio = $this->ObtenerFolio($row['id_cliente'], $row['id_sucursal'], $serie);
        $tipo = $row['id_tiposerie'];
        $sql = "UPDATE fctmnl
                SET id_sucursal = ".$row['id_sucursal'].", serie = '".$serie."', id_tipocomprobante = ".$tipo.", folio = $folio, uuid_r = '', id_cliente = ".$row['id_cliente']."
                WHERE idUsr = $idusr";
        $result = $conn->query($sql);
        if ($conn->num_rows($result) < 1)
            $tipo = 0;
        return $tipo;
    }

    public function borrSRUG($idusr){
        $conn = new Model();
        $sql = "SELECT uuid_r, tipoRelacion FROM fctmnl WHERE idUsr = $idusr";
        $result = $conn->query($sql);
        $rows = $conn->fetch_array($result);
        if($rows["uuid_r"] != "" || $rows["tipoRelacion"] != ""){
            $sql = "UPDATE fctmnl SET uuid_r = '', tipoRelacion = '' WHERE idUsr = $idusr";
            $result = $conn->query($sql);
        }
        $resp = ($conn->num_rows($result) < 1) ? 0 : 1;
        $conn->close();
        return $resp;
    }
    
    public function ObtenerFolio($cli, $suc, $serie) {
        $conn = new Model();
        $sql = "SELECT MAX(folio) AS fol FROM documentos WHERE id_sucursal = $suc AND serie = '$serie'";
        $res = $conn->query($sql);
        $row = $conn->fetch_array($res);
        $fol = $row["fol"] == "" ? 0 : $row["fol"];
        return $fol;
    }
    
    public function ObtenerLugarExpedicion($cp) {
        $conn = new Model();
        $sql = "SELECT codigopostal, c_estado FROM c_codigopostal WHERE codigopostal = '$cp';";
        $result = $conn->query($sql);
        $row = $conn->fetch_array($result);
        $estado = $row['c_estado'];
        $conn->close();
        return $estado;
    }
    
    public function ActualizarLugarExpedicion($idusr, $cp) {
        $estado = $this->ObtenerLugarExpedicion($cp);
        $conn = new Model();
        if ($estado != "" || $cp == "") {
            $sql = "UPDATE fctmnl SET lugarExpedicion = '".$cp."' WHERE idUsr = $idusr";
            $result = $conn->query($sql);
            if ($conn->num_rows($result) < 1)
                $estado = '0'; // No se actualizó
        }
        else {
            $estado = '2'; // No existe CP
        }
        $conn->close();
        return $estado;
    }
    // serie=SCB folio=2707 -> c/uuid
    // serie=ADENC folio=144 -> c/uuid_r
    public function ObtenerDatosCfdiR ($idusr, $datos) {
        $conn = new Model();
        $sql = "SELECT B.sucursal, B.nombre, A.serie, A.folio, A.total, A.receptor, A.uuid
                FROM documentos A INNER JOIN sucursales B ON (A.id_sucursal = B.id_sucursal)
                WHERE A.serie = '".$datos['serie']."' AND A.folio = '".$datos['folio']."';";
        //$resp = $this->setCodingUtf82($sql, $conn, 1);
        $result = $conn->query($sql);
        $rows = $conn->fetch_array($result);
        if ($conn->num_rows($result)){
            $info = $this->setCodingUtf82($sql, $conn, 1);
            $resp = json_encode($info);
        } else
            $resp = "SD"; // No se pudo obtener informacion del folio.
        
        $uuid = $rows["uuid"];
        $tot = $rows["total"];
        $sql = "SELECT total FROM documentos WHERE uuid_r = '$uuid'";
        $res = $conn->query($sql);
        $totdr = 0;
        while( $row = $conn->fetch_array($res) ){
                $totdr += $row["total"];
        }
        if( ($totdr != 0) && ($totdr == $tot) ){
            $resp = "CN"; // Ya existe una nota de credito para este folio.
        }
        if( $uuid == "" )
            $resp = "FT";
        if( $resp != "CN" && $resp != "SD" && $resp != "FT" ){
            $sql = "UPDATE fctmnl SET uuid_r = '$uuid' WHERE idUsr = $idusr";
            $res = $conn->query( $sql );
            if( $conn->num_rows($res) < 1) $resp = "er";
        }
        $conn->close();
        return $resp;
    }
    
    public function ObtenerDatosCfdiRuuid ($idusr, $datos) {
        if($datos['uuid_r'] == "")
            return "";
        $conn = new Model();
        $sql = "SELECT B.sucursal, B.nombre, A.serie, A.folio, A.total, A.receptor
                FROM documentos A INNER JOIN sucursales B ON (A.id_sucursal = B.id_sucursal)
                WHERE A.uuid = '".$datos['uuid_r']."' AND A.uuid <> '';";
        $info = $this->setCodingUtf82($sql, $conn, 1);
        $resp = json_encode($info);
        $conn->close();
        return $resp;
    }
    
    public function ObtenerProductoPorCodigo ($idusr, $datos) {
        $conn = new Model();
        $sql = "SELECT * FROM fctmnldet
                WHERE codigoproducto = '". $datos["cod"] ."' AND idUsr = ". $idusr ." AND catalogo = ". $datos["cat"];
        $result = $conn->query($sql);
        $res = "";
        while($row = $conn->fetch_array($result)) $res = "2";
        if ($res == "") {
            $catalogo = ($datos['cat'] == 1) ? 'cat_producto' : 'cat_producto_gen';
            $sql = "SELECT Nombre, Precio, Unidad, IVA FROM $catalogo WHERE Id_Producto = " . $datos['cod'] . " AND activo = 1;";
            $producto = $this->setCodingUtf82($sql, $conn, 1);
            $res = json_encode($producto);
        }
        $conn->close();
        return $res;
    }
    
    public function ObtenerProductoPorNombre ($idusr, $datos) {
        $conn = new Model();
        $sql = "SELECT * FROM fctmnldet
                WHERE codigoproducto = '". $datos["nom"] ."' AND idUsr = ". $idusr ." AND catalogo = ". $datos["cat"];
        $result = $conn->query($sql);
        $resp = "";
        while($row = $conn->fetch_array($result)) $resp = "2";
        if ($resp == "") {
            $catalogo = ($datos['cat'] == 1) ? 'cat_producto' : 'cat_producto_gen';
            $sql = "SELECT Id_Producto, Nombre, Precio, Unidad, IVA FROM $catalogo WHERE Nombre='".$datos['nom']."' AND activo=1;";
            $prod = $this->setCodingUtf82($sql, $conn, 1);
            $resp = json_encode($prod);
        }
        $conn->close();
        return $resp;
    }
    
    public function ObtenerProducto ($idusr, $datos) {
        $conn = new Model();
        $catalogo = ($datos['cat'] == 1) ? 'cat_producto' : 'cat_producto_gen';
        $sql = "SELECT claveProdServ, claveUnidad, c_impuesto, tipofactor, valor, tipoImpuesto FROM $catalogo WHERE Id_Producto=".$datos['cod'].";";
        $prod = $this->setCodingUtf82($sql, $conn, 1);
        $conn->close();
        return json_encode($prod);
    }

    public function addItem($idusr, $datos) {
        $conn = new Model();
        /*if ($datos['cat'] == 1)
            $sql = "SELECT claveProdServ, claveUnidad, c_impuesto, tipofactor, valor, tipoImpuesto FROM cat_producto WHERE Id_Producto=".$datos['codprod'].";";
        else
            $sql = "SELECT claveProdServ, claveUnidad, c_impuesto, tipofactor, valor, tipoImpuesto FROM cat_producto_gen WHERE Id_Producto = '". $datos["codprod"] ."';";
         */
        
        $cat = ($datos['cat'] == 1) ? 'cat_producto' : 'cat_producto_gen' ;
        $sql = "SELECT claveProdServ, claveUnidad, c_impuesto, tipofactor, valor, tipoImpuesto
                FROM $cat WHERE Id_Producto = '".$datos['codprod']."';";
        $id = $this->getIdItem($idusr) + 1;
        if($datos["iva"] > 0 ){
            //if( $post["tpo"] == 1 ){
            $datos["precio"] = $datos["precio"] / (1 + ($datos["iva"]/100));
            $mnt = $datos["cant"] * $datos["precio"];
            $datos["dcto"] = ($mnt * ($datos["dcto"]/100));
            $datos["iva"] = ($mnt - $datos["dcto"]) * ($datos["iva"]/100);
            /*}else{
                $mnt = $post["can"] * $post["pre"];
                $post["dsc"] = ($mnt * ($post["dsc"]/100));
                $post["iva"] = $mnt * ($post["iva"]/100);
            }*/
        }else{
            $mnt = $datos["cant"] * $datos["precio"];
            $datos["dcto"] = ($mnt * ($datos["dcto"]/100));
        }

        $res = $conn->query($sql);
        $row = $conn->fetch_array($res);
        $claveProdServ = $row["claveProdServ"];
        $claveUnidad = $row["claveUnidad"];
        $c_impuesto = $row["c_impuesto"];
        $tipofactor = $row["tipofactor"];
        $valor = $row["valor"];
        $tipoImpuesto = $row["tipoImpuesto"];

        $base = $mnt - $datos["dcto"];
        $sql = "INSERT INTO fctmnldet (idUsr, item, cantidad, unidad, codigoproducto, descproducto, valorunitario, importe,
                iva, descuento, claveProdServ, claveUnidad, base, impuesto, tipoFactor, tasaOCuota, tipoImpuesto, catalogo, ctapredial) VALUES(".
            $idusr .", ".
            $id .", ".
            $datos["cant"] .", '".
            $datos["um"] ."', '".
            $datos["codprod"] ."', '".
            $datos["concep"] ."', ". //pdt
            $datos["precio"] .",  ".
            $mnt .", ".
            $datos["iva"] .", ".
            $datos["dcto"] .", '".
            $claveProdServ ."', '".
            $claveUnidad ."', ".
            $base .", '".
            $c_impuesto ."', '".
            $tipofactor ."', '".
            $valor ."', ".
            $tipoImpuesto .", ".
            $datos["cat"] .", '".
            str_replace(" ", "", $datos["cta_pred"]) ."')";
        //die(print($sql));
        $conn->query($sql);
        $conn->close();
        return $id;
    }
    
    public function removeItem($idusr, $datos){
        $conn = new Model();
        $sql = "SELECT descproducto FROM fctmnldet WHERE idUsr = ". $idusr ." AND item IN(". $datos["items"] .")";
        $res = $conn->query($sql);
        if($conn->num_rows($res) > 0){
            $cadena = "Desea eliminar los siguientes productos:<br>";
            while($row = $conn->fetch_array($res)){
                if( $cadena != "" ) $cadena .= "<br>";
                $cadena .= $row["descproducto"];
            }
        }else $cadena = 0;
        return $cadena;
    }

    public function getIdItem($idusr){
        $conn = new Model();
        $sql = "SELECT MAX(item) id FROM fctmnldet WHERE idUsr = $idusr";
        $result = $conn->query($sql);
        $row = $conn->fetch_array($result);
        $id = $row["id"] == "" ? 0 : $row["id"];
        //$conn->close();
        return $id;
    }

    /*public function getItem($idusr, $item){
        $conn = new Model();
        $sql = "SELECT item, cantidad, descproducto, valorunitario, importe FROM fctmnldet WHERE idUsr = $idusr AND item = $item";
        $resp = $this->setCodingUtf8($sql, $conn);
        $conn->close();
        return $resp;
    }*/

    public function obtItems($idusr){
        $conn = new Model();
        $sql = "SELECT item, unidad, codigoproducto, cantidad, descproducto, valorunitario, importe, iva, descuento, tipoImpuesto
                FROM fctmnldet WHERE idUsr = $idusr ORDER BY item;";
        $resp = $this->setCodingUtf8($sql, $conn);
        $conn->close();
        return $resp;
    }
    
    public function hdrFac($idusr){
        $conn = new Model();
        $sql = "SELECT item, importe, iva, descuento, tipoImpuesto FROM fctmnldet WHERE idUsr = $idusr ORDER BY item;";
        $result = $conn->query($sql);
        $datos = [];
        $sbt = 0; $dsc = 0; $iva = 0; $tot = 0;
        while($row = $conn->fetch_array($result)){
            $sbt += $row["importe"];
            $iva += $row["iva"];
            $dsc += $row["descuento"];
        }
        $tot = $sbt - $dsc + $iva;
        $datos['sbt'] = number_format($sbt, 2);
        if( $dsc > 0 ){
            $dsc = number_format($dsc, 2);
        }
        if( $iva > 0 ){
            $iva = number_format($iva, 2);
        }
        $datos['dsc'] = $dsc;
        $datos['iva'] = $iva;
        $datos['tot'] = number_format($tot, 2);
        return $datos;
    }

    public function elIt($idusr, $datos){
        $conn = new Model();
        $sql = "SELECT descproducto FROM fctmnldet WHERE idUsr = ". $idusr ." AND item IN(". $datos["items"] .")";
        $res = $conn->query( $sql );
        if($conn->num_rows($res) > 0){
            $cadena = "Desea eliminar los siguientes productos:<br>";
            while($row = $conn->fetch_array($res)){
                if( $cadena != "" ) $cadena .= "<br>";
                $cadena .= $row["descproducto"];
            }
        }else $cadena = "er";
        return $cadena;
    }

    public function celIt($idusr, $datos){
        $sql = "DELETE FROM fctmnldet WHERE idUsr = ". $idusr ." AND item IN(". $datos["items"] .")";
        $conn = new Model();
        $result = $conn->query($sql);
        $resp = 2;
        if ($conn->num_rows($result) > 0)
            $resp = 1;
        return $resp;
    }
    
    public function clrReg( $clr ){
        if( $clr == 0 ) $r = "";
        else $r = 'bgcolor="#E7EEFA"';
        return $r;
    }
    
    public function numItems($idusr){
        $sql = "SELECT COUNT(*) AS numr FROM fctmnldet WHERE idUsr = $idusr";
        $conn = new Model();
        $res = $conn->query($sql);
        $rows = $conn->fetch_array($res);
        return $rows["numr"];
    }

    public function registrarBitacora($accion, $detalle, $Userid, $conn) {
        $idmnu = $this->container->getParameter('FacturaManual'); // El id del menu se encuentra definido en el archivo parameters.yml
        $sql = "INSERT INTO sis_bitacora (idusr, id_mnu, fch_bitacora, accion, detalle) VALUES ($Userid, $idmnu, now(), '$accion', '$detalle')";
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
        $a = "";
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

    public function genFac($idusr, $datos){
        $conn = new Model();
        $sql = "SELECT item, importe, iva, descuento, tipoImpuesto FROM fctmnldet WHERE idUsr = $idusr ORDER BY item;";
        $result = $conn->query($sql);
        if($conn->num_rows($result) > 0){
            $sbt = 0; $dsc = 0; $iva = 0; $rete = 0; $tot = 0; $vta0 = 0; $vta16 = 0;
            while($rows = $conn->fetch_array($result)){
                $sbt += $rows["importe"];
                if( $rows["iva"] > 0 ){
                    if( $rows["tipoImpuesto"] == 1 ){
                        $iva += $rows["iva"];
                        $vta16 += $rows["importe"] - $rows["descuento"];
                    }else{
                        $rete += $rows["iva"];
                    }
                }else{
                    $vta0 += $rows["importe"] - $rows["descuento"];
                }
                $dsc += $rows["descuento"];
            }
            $tot = $sbt - $dsc + $iva + $rete;
            $cc = $this->verifMtoR($idusr);
            $val = explode("|", $cc);
            $vd = $val[0];
            $dr = $val[1];
            $e = "";
            if( $vd != "" ){
                if( round($tot,2) > round($vd,2) )
                    $e = "error";
                else
                    $e = "ok";
                
                //$e = (round($tot,2) > round($vd,2)) ? "error" : "ok" ;
            }
            else
                $e = "ok";
            
            if( $e == "ok" ){
                if( $datos['mtpg'] == "01" || $datos['mtpg'] == "99" )
                    $crit = ", cuentadepago = ''";
                else
                    $crit = ", cuentadepago = '". $datos['cta'] ."'";
                
                //$crit = ", cuentadepago = '";
                //$crit = ($datos['mtpg'] == "01" || $datos['mtpg'] == "99") ? "'": $datos['cta'] ."'";
                
                $ti = $this->genDetImp($idusr);
                if( $ti == "ok" ){
                    $sql = "UPDATE fctmnl SET ".
                        "metododepago = '". $datos['mtpg'] ."' $crit,".
                        "subtotal = ".  $sbt    .", ".
                        "descuento = ". $dsc    .", ".
                        "total = ".     $tot    .", ".
                        "iva = ".       $iva    .", ".
                        "vtaiva0 = ".   $vta0   .", iva0 = 0, ".
                        "vtaiva16 = ".  $vta16 .", ".
                        "iva16 = ".     $iva   .", ".
                        "retenidos = ". $rete   ." ".
                        "WHERE idUsr = $idusr";
                    $result = $conn->query($sql);
                    if(1/*$conn->num_rows($result) > 0*/){
                        $cad = "ok";
                    }else{
                        $cad = "Error al verificar datos.";
                        $sql = "DELETE FROM fctmnl_imp WHERE idUsr = $idusr;";
                        $conn->query($sql);
                    }
                }else{
                    $cad = $ti;
                }
            }else{
                $cad = "El total de la factura es mayor al monto del folio relacionado.";
                if($dr == 1) $cad .= " Ya existen NC relacionadas a este documento.";
            }
        }else{
            $cad = "No existen conceptos para facturar.";
        }
        $conn->close();
        return $cad;
    }
    
    public function verifMtoR($idusr){
        $conn = new Model();
        $sql = "SELECT d.total, d.uuid FROM documentos d INNER JOIN fctmnl f ON d.uuid = f.uuid_r WHERE f.idUsr = $idusr AND d.uuid <> '';";
        $res = $conn->query($sql);
        $row = $conn->fetch_array($res);
        $tot = $row["total"];
        $uuid = $row["uuid"];
        $dr = 0;
        if( $tot != "" ){
            $sql = "SELECT total FROM documentos WHERE uuid_r = '$uuid'";
            $res = $conn->query($sql);
            while( $rw = $conn->fetch_array($res) ){
                if( $rw["total"] != "" ){
                    $tot -= $rw["total"];
                    $dr = 1;
                }
            }
        }
        return $tot."|".$dr;
    }
    
    public function genDetImp($idusr){
        $conn = new Model();
        $sql = "SELECT impuesto, tipoFactor, tasaOCuota, sum(iva) as importe, tipoImpuesto ".
                "FROM fctmnldet WHERE idUsr = $idusr ".
                "GROUP BY impuesto, tipoFactor, tasaOCuota, tipoImpuesto ".
                "ORDER BY tipoImpuesto ASC;";
        $res = $conn->query($sql);
        $cadena = "ok";
        if( $conn->num_rows($res) > 0 ){
            $rt = 0;
            $ra = 0;
            $iva0 = 0;
            $iva16 = 0;
            $sa = "";
            while($row = $conn->fetch_array($res)){
                if( $row["tipoImpuesto"] == 0 ){
                    $iva0 = 1;
                    $row["importe"] = 0;
                }
                if( $row["tipoImpuesto"] == 1 ) $iva16 = 1;
                $sql = "INSERT INTO fctmnl_imp(idUsr, impuesto, tipoFactor, tasaOCuota, importe, tipoImpuesto) VALUES (". $idusr .", '".
                        $row["impuesto"] ."', '". $row["tipoFactor"] ."', '". $row["tasaOCuota"] ."', ". $row["importe"] .", ". $row["tipoImpuesto"] .");";				
                //$sa .= $sql;
                $result = $conn->query($sql);
                $rt++;
                if($conn->num_rows($result) > 0) $ra++;
            }
            if( $rt != $ra ){
                $sql = "DELETE FROM fctmnl_imp WHERE idUsr = $idusr;";
                $conn->query($sql);
                $cadena = "Error al generar detalle de impuestos";
            }else{
                if( $iva0 == $iva16 ){
                    $sql = "DELETE FROM fctmnl_imp WHERE idUsr = $idusr AND tipoImpuesto = 0;";
                    $conn->query($sql);
                }
            }
        }else $cadena = "Error al obtener detalle de impuestos";
        return $cadena;
    }

    public function generarFactura($usr){
        $res = $this->datFact( $usr );
        $conn = new Model();
        $error = "<b>Error:</b><br>";
        if( $conn->num_rows($res) > 0 ){
            $row = $conn->fetch_array($res);
            $rsl = $this->datEmisor( $row["id_cliente"] );
            $rem = $conn->fetch_array($rsl);
            $ruta = $rem["nombrecorto"];
            //echo "<br>nombrecorto: ". $ruta;
            if( !is_dir($ruta) ){
                mkdir($ruta);
                if( is_dir($ruta) ){
                    try{ chmod($ruta, 0777); }catch( Exception $e ){}
                    $ruta .= "/". $row["serie"];
                    //echo "<br> serie:". $ruta;
                    if( !is_dir($ruta) ){
                        mkdir($ruta);
                        if( is_dir($ruta) ){
                            try{ chmod($ruta, 0777); }catch( Exception $e ){}
                        }
                    }
                }
            }else{
                $ruta .= "/". $row["serie"];
                //echo "<br> serie1:". $ruta;
                if( !is_dir($ruta) ){
                    mkdir($ruta);
                    if( is_dir($ruta) ){
                        try{ chmod($ruta, 0777); }catch( Exception $e ){}
                    }
                }
            }
            $fch = substr($row["fecha"], 0, 10);
            $hora = date("H:i:s");
            $seg = strtotime($fch) - strtotime('now');
            $dif_d = intval($seg/60/60/24);
            if( $dif_d < 0 ){
                $hora = strtotime ( '+1 hour' , strtotime ( $hora ) ) ;
                $hora = date ( "H:i:s" , $hora );
            }else{
                $hora = strtotime ( '-5 minute' , strtotime ( $hora ) ) ;
                $hora = date ( "H:i:s" , $hora );
            }
            $this->actFecHr( $fch ." ". $hora, $usr);
            $folact = $this->ObtenerFolio( $row["id_cliente"], $row["id_sucursal"], $row["serie"]) + 1;
            if( $row["folio"] != $folact ){
                $this->actFolio($usr, $folact);
                $row["folio"] = $folact;
            }
            $nmaO = "f#". $row["folio"] ."_". str_replace("-", "", $fch) ."_andrmd.txt";
            $nma = $ruta ."/". $nmaO;
            //echo "<br>archivo: ". $nma;
            $ar=fopen("$nma","a") or die("No se pudo crear el archivo");
            //************* CFDI 3.3 ******************//
            // -------------------------- DC - Detalle Comprobante
            // DC|Version|Serie|Folio|Fecha|FormaPago|Subtotal|Descuento|
            $DC = "DC|3.3|". $row["serie"] ."|". $row["folio"] ."|". $fch ."T". $hora ."|". $row["metododepago"] ."|". $row["subtotal"] ."|". $row["descuento"] ."|";
            // Moneda|TipoCambio|Total|TipoDeComprobante|MetodoPago|
            $DC .= $row["moneda"] ."|". $row["tipoCambio"] ."|". $row["total"] ."|". $row["tipodeComprobante"] ."|". $row["formadepago"] ."|";
            // LugarExpedicion|CondicionesDePago|Confirmacion|IdComprobante|TipoRelacion
            $DC .= $row["lugarExpedicion"] ."|". $row["condicionesdepago"] ."|||". $row["tipoRelacion"];
            fputs($ar, $DC);
            fputs($ar,chr(13).chr(10));
            //--- CRL no deben de existir si no hay CFDI de relación ---
            if( $row["uuid_r"] != "" ){
                // -------------------------- CRL - CFDIs Relacionados
                // CRL|IdComprobante|UUID
                $CRL = "CRL||". $row["uuid_r"];
                fputs($ar, $CRL);
                fputs($ar,chr(13).chr(10));
            }
            // -------------------------- EM - Datos Emisor
            // EM|RFC|Nombre|Regimen Fiscal
            $EM = "EM|". $rem["rfc"] ."|". $rem["razonsocial"] ."|". $rem["regimenFiscal"];
            fputs($ar, $EM);
            fputs($ar,chr(13).chr(10));
            // -------------------------- RC - Datos Receptor
            $rsr = $this->datReceptor( $row["id_receptor"] );
            $rcp = $conn->fetch_array( $rsr );
            if( $rcp["residenciaFiscal"] == "MEX" ) $rcp["residenciaFiscal"] = "";
            // RC|RFC|Nombre|UsoCFDI|ResidenciaFiscal|NumRegIdTrib
            $RC = "RC|". trim($rcp["rfc"]) ."|". $rcp["nombreRazon"] ."|". $row["usoCFDI"] ."|". $rcp["residenciaFiscal"] ."|". $rcp["regIdentFiscal"];
            fputs($ar, $RC);
            fputs($ar,chr(13).chr(10));
            $rit = $this->obtItemsG( $usr );
            while($itm = $conn->fetch_array( $rit )){
                // -------------------------- CN - Detalle Concepto
                //CN|ClaveProdServ|Cantidad|ClaveUnidad|Descripcion|
                $CN = "CN|". $itm["claveProdServ"] ."|". $itm["cantidad"] ."|". $itm["claveUnidad"] ."|". $itm["descproducto"] ."|";
                $ttdes = "";
                if( $itm["descuento"] > 0 ) $ttdes = $itm["descuento"];
                //ValorUnitario|Importe|Descuento|NoIdentificacion|Unidad
                $CN .= $itm["valorunitario"] ."|". $itm["importe"] ."|$ttdes|". $itm["codigoproducto"] ."|". $itm["unidad"];
                fputs($ar, $CN);
                fputs($ar,chr(13).chr(10));
                if( $itm["tipoImpuesto"] == 0 || $itm["tipoImpuesto"] == 1 ){// Trasladados
                    // -------------------------- ICT - Concepto Traslados
                    //ICT|Base|Impuesto|TipoFactor|TasaOCuota|Importe
                    $ICT = "ICT|". $itm["base"] ."|". $itm["impuesto"] ."|". $itm["tipoFactor"] ."|". $itm["tasaOCuota"] ."|". $itm["iva"];
                    fputs($ar, $ICT);
                }else{// Retenidos
                    // -------------------------- ICR - Concepto Retenciones
                    //ICR|Base|Impuesto|TipoFactor|TasaOCuota|Importe
                    $ICR = "ICR|". $itm["base"] ."|". $itm["impuesto"] ."|". $itm["tipoFactor"] ."|". $itm["tasaOCuota"] ."|". $itm["iva"];
                    fputs($ar, $ICR);
                }
                fputs($ar,chr(13).chr(10));
                if( $itm["ctapredial"] != "" ){
                    // -------------------------- CPC - Concepto Cuenta Predial
                    //CPC|Numero
                    $CPC = "CPC|". $itm["ctapredial"];
                    fputs($ar, $CPC);
                    fputs($ar,chr(13).chr(10));
                }
                // -------------------------- PC - Concepto Parte
                //PC|ClaveProdServ|NoIdentificacion|Cantidad|Unidad|Descripcion|
                $PC = "PC|". $itm["claveProdServ"] ."|". $itm["codigoproducto"] ."|". $itm["cantidad"] ."|". $itm["unidad"] ."|". $itm["descproducto"] ."|";
                //ValorUnitario|Importe
                $PC .= $itm["valorunitario"] ."|". $itm["importe"];
                fputs($ar, $PC);
                fputs($ar,chr(13).chr(10));
            }
            // -------------------------- IT - Total de Impuestos
            //IT|Total de Impuestos retenidos|Total de impuestos Trasladados
            $ret = "";
            if( $row["retenidos"] > 0 ) $ret = $row["retenidos"];
            $IT = "IT|$ret|". $row["iva"];
            fputs($ar, $IT);
            fputs($ar,chr(13).chr(10));
            $oti = $this->obtTotI($usr);
            while($otid = $conn->fetch_array( $oti )){
                if( $otid["tipoImpuesto"] == 0 || $otid["tipoImpuesto"] == 1 ){
                    // -------------------------- TI - Detalle Trasladados
                    //TI|Impuesto|TipoFactor|TasaOCuota|Importe
                    $TI = "TI|". $otid["impuesto"] ."|". $otid["tipoFactor"] ."|". $otid["tasaOCuota"] ."|". $otid["importe"];
                    fputs($ar, $TI);
                }else{
                    // -------------------------- RI - Detalle Retenciones
                    //RI|mpuesto|Importe
                    $RI = "RI|". $otid["impuesto"] ."|". $otid["importe"];
                    fputs($ar, $RI);
                }
                fputs($ar,chr(13).chr(10));
            }
            // -------------------------- OP - Opcional
            //OP|Opcionales
            $OP = "OP|DESGLOSE DEL IVA:    VENTA CON IVA: ". number_format($row["vtaiva0"], 2) ." AL 0.00%  $";
            $OP .= number_format($row["iva0"], 2) ."    VENTA CON IVA: ". number_format($row["vtaiva16"], 2) ." AL 16.00%  $";
            $OP .= number_format($row["iva16"], 2) ."|    ". $row["observaciones"]."|    ". $row["motivodescuento"];
            fputs($ar, $OP);
            fputs($ar,chr(13).chr(10));
            //************* CFDI 3.3 ******************//
            //************* CFDI 3.2 ******************//
            /*
            // -------------------------- DC Información del Comprobante a ser Timbrado
            // DC|version|serie|folio|fecha|formaPago|
            $DC = "DC|3.2|". $row["serie"] ."|". $row["folio"] ."|". $fch ."T". $hora ."|". $row["formadepago"] ."|";
            $dtp = $bl->desTC( $row["id_tipocomprobante"] );
            //subtotal|descuento|total|metodoPago|tipodeComprobante|tipoCambio|moneda|
            $DC .= $row["subtotal"] ."|". $row["descuento"] ."|". $row["total"] ."|". $row["metododepago"] ."|". $dtp ."||MXN|";
            // motivoDescuento|condicionesDePago|LugarExpedicion|
            $DC .= $row["motivodescuento"] ."|". $row["condicionesdepago"] ."|". $rem["municipio"] .", ". $rem["estado"] ."|";
            // NumCtaPago|FolioFiscalOrig|SerieFolioFiscalOrig|FechaFolioFiscalOrig|MontoFolioFiscalOrig
            $DC .= $row["cuentadepago"] ."||||";
            fputs($ar, $DC);
            fputs($ar,chr(13).chr(10));
            // -------------------------- EM Datos del Emisor
            // EM|RFC|nombreEmisor
            $EM = "EM|". $rem["rfc"] ."|". $rem["razonsocial"];
            fputs($ar, $EM);
            fputs($ar,chr(13).chr(10));
            // -------------------------- EE Expedido En
            // EE|calle|noExterior|noInterior|colonia|localidad|referencia|
            $EE = "EE|". $rem["calle"] ."|". $rem["numExt"] ."|". $rem["numInt"] ."|". $rem["colonia"] ."|". $rem["ciudad"] ."||";
            // municipio|estado|pais|CP
            $EE .= $rem["municipio"] ."|". $rem["estado"] ."|Mexico|". $rem["cp"];
            fputs($ar, $EE);
            fputs($ar,chr(13).chr(10));
            // -------------------------- RC Datos del Receptor
            $rsr = $bl->datReceptor( $row["id_receptor"] );
            $rcp = $conn->fetch_array( $rsr );
            // RC|RFC|nombreReceptor
            $RC = "RC|". $rcp["rfc"] ."|". $rcp["nombreRazon"];
            fputs($ar, $RC);
            fputs($ar,chr(13).chr(10));
            // -------------------------- DE Dirección del Receptor
            // DE|calle|noExterior|noInterior|colonia|localidad|referencia|
            $DE = "DE|". $rcp["calle"] ."|". $rcp["numExt"] ."|". $rcp["numInt"] ."|". $rcp["colonia"] ."|". $rcp["ciudad"] ."||";
            // municipio|estado|pais|CP
            $DE .= $rcp["municipio"] ."|". $rcp["estado"] ."|Mexico|". $rcp["cp"];
            fputs($ar, $DE);
            fputs($ar,chr(13).chr(10));
            //  -------------------------- CN Información del concepto
            $rit = $bl->obtItems( $usr );
            // CN|cantidad|unidad|noIdentificacion|descripcion|
            while($itm = $conn->fetch_array( $rit )){
                $CN = "CN|". $itm["cantidad"] ."|". $itm["unidad"] ."|". $itm["codigoproducto"] ."|". $itm["descproducto"] ."|";
                // valorUnitario|importe
                $CN .= $itm["valorunitario"] ."|". $itm["importe"];
                fputs($ar, $CN);
                fputs($ar,chr(13).chr(10));
            }
            // -------------------------- OC Campos opcionales del concepto
            // OC|

            // -------------------------- AR Campo opcional de Arrendamiento
            // AR|cuenta predial

            // -------------------------- IT Total de impuestos en el comprobante, tanto retenidos como Trasladados
            // IT|retenidos|trasladados
            $IT = "IT|0.00|". $row["iva"];
            fputs($ar, $IT);
            fputs($ar,chr(13).chr(10));
            // -------------------------- TI Impuestos Trasladados
            // TI|impuesto|importe|tasa
            $TI = "TI|IVA|". $row["iva16"] ."|16.00";
            fputs($ar, $TI);
            fputs($ar,chr(13).chr(10));
            // -------------------------- RI Impuestos Retenidos
            // RI|impuesto|importe

            // -------------------------- Ml Lista de correos separados por coma
            // Ml|para|cc|cco

            // -------------------------- OP Campos opcionales
            // OP|
            $OP = "OP|DESGLOSE DEL IVA:    VENTA CON IVA: ". number_format($row["vtaiva0"], 2) ." AL 0.00%  $";
            $OP .= number_format($row["iva0"], 2) ."    VENTA CON IVA: ". number_format($row["vtaiva16"], 2) ." AL 16.00%  $";
            $OP .= number_format($row["iva16"], 2) ."|    ". $row["observaciones"];
            fputs($ar, $OP);
            fputs($ar,chr(13).chr(10));
            */
            //************* CFDI 3.2 ******************//
            fclose($ar);
            $e = $this->agrDoc( $usr );
            $dat = explode("|", $e);
            if( $dat[0] != "ok" ){
                $error .= $e;
                unlink($nma);
            }else{
                $accion = "Realizar Factura Manual";
                $detalle = "Terminó de realizar la factura manual";
                $this->registrarBitacora($accion, $detalle, $usr, $conn);
                /*
                //echo "<br>copia a: C:\AccRed\Respaldos\\". $rem["nombrecorto"] ."\Conversion\\". $row["serie"] ."\\". $nmaO;
                $val = @copy( $nma, "C:\AccRed\Respaldos\\". $rem["nombrecorto"] ."\Conversion\\". $row["serie"] ."\\". $dat[1]);
                if( !$val ) $error .= "No se pudo respaldar el archivo.";
                //echo "<br>mueve a: C:\AndromedaIN\\". $nmaO;
                $val = @copy( $nma, "C:\AndromedaIN\\". $dat[1] );
                if( !$val ) $error .= "No se pudo mandar el archivo para facturar.";
                */
            }
        }else{
            $error .= "No se pudo obtener los datos para facturar.<br>";
        }
        return $error;
    }

    public function datFact($idusr){
        $sql = "SELECT * FROM fctmnl WHERE idUsr = $idusr;";
        $conn = new Model();
        $res = $conn->query($sql);
        return $res;
    }

    public function datEmisor($idc){
        $conn = new Model();
        $sql = "SELECT r.tipoPer, r.rfc, r.razonsocial, r.correoE, r.telefono1, r.calle, r.numExt, r.numInt, r.cp, e.estado, ".
                "m.municipio, c.colonia, r.ciudad, r.nombrecorto, r.regimenFiscal FROM clientes r INNER JOIN estado e ON e.id_estado = r.id_estado ".
                "INNER JOIN  municipio m ON m.id_municipio = r.id_municipio INNER JOIN colonia c ON c.id_colonia = r.id_colonia ".
                "WHERE r.id_cliente = $idc ";
        $result = $conn->query($sql);
        return $result;
    }
    
    public function datReceptor($idr){
        $conn = new Model();
        $sql = "SELECT r.tipoPer, r.rfc, r.nombreRazon, r.correoE, r.telefono, r.calle, r.numExt, r.numInt, r.cp, e.estado, ".
                "m.municipio, c.colonia, r.ciudad, r.residenciaFiscal, r.regIdentFiscal FROM receptor r INNER JOIN estado e ON e.id_estado = r.estado ".
                "INNER JOIN  municipio m ON m.id_municipio = r.mpioDel INNER JOIN colonia c ON c.id_colonia = r.idCol ".
                "WHERE r.id_receptor = $idr ";
        $result = $conn->query($sql);
        return $result;
    }

    public function actFecHr($fch, $idu){
        $conn = new Model();
        $sql = "UPDATE fctmnl SET fecha = '$fch' WHERE idUsr = $idu";
        $res = $conn->query($sql);
        return $conn->num_rows($res);
    }
    
    public function actFolio($idusr, $fol){
        $conn = new Model();
        $sql = "UPDATE fctmnl SET folio = ". $fol ." WHERE idUsr = ". $idusr;
        $res = $conn->query($sql);
        $tps = "";
        if( $conn->num_rows($res) < 1 ) $tps = "er";
        return $tps;
    }
    
    public function obtItemsG( $idu ){
        $conn = new Model();
        $sql = "SELECT * FROM fctmnldet WHERE idUsr = $idu ORDER BY item;";
        $res = $conn->query($sql);
        //$this->setAfectados($conn->affected_rows());
        return $res;
    }
    
    public function obtTotI( $idu ){
        $conn = new Model();
        $sql = "SELECT * FROM fctmnl_imp WHERE idUsr = $idu ORDER BY tipoImpuesto DESC;";
        $res = $conn->query($sql);
        //$this->setAfectados($conn->affected_rows());
        return $res;
    }
    
    public function agrDoc( $usr ){
        $res = $this->datFact( $usr );
        $conn = new Model();
        $cad = "";
        $row = $conn->fetch_array( $res );
        $cli = $row["id_cliente"];
        $ser = $row["serie"];
        $fol = $row["folio"];
        $sql = "INSERT INTO documentos(id_cliente, id_sucursal, serie, folio, rfc_receptor, receptor, fecha, ".
                "formadepago, condicionesdepago, subtotal, descuento, motivodescuento, total, metododepago, ".
                "id_tipocomprobante, iva, vtaiva0, iva0, vtaiva16, iva16, correoenvio, ".
                "usrC, fchC, observaciones, moneda, tipoCambio, tipodeComprobante, lugarExpedicion, tipoRelacion, ".
                "uuid_r, usoCFDI, retenidos) VALUES (".
                $row["id_cliente"] .", ".
                $row["id_sucursal"] .", '".
                $row["serie"] ."', ".
                $row["folio"] .", '".
                $row["rfc_receptor"] ."', '".
                $row["receptor"] ."', '".
                $row["fecha"] ."', '".
                $row["formadepago"] ."', '".
                $row["condicionesdepago"] ."', ".
                $row["subtotal"] .", ".
                $row["descuento"] .", '".
                $row["motivodescuento"] ."', ".
                $row["total"] .", '".
                $row["metododepago"] ."', ".
                $row["id_tipocomprobante"] .", ".
                $row["iva"] .", ".
                $row["vtaiva0"] .", ".
                $row["iva0"] .", ".
                $row["vtaiva16"] .", ".
                $row["iva16"] .", '".
                $row["correoenvio"] ."', $usr, NOW(), '".
                $row["observaciones"] ."', '".
                $row["moneda"] ."', ".
                $row["tipoCambio"] .", '".
                $row["tipodeComprobante"] ."', '".
                $row["lugarExpedicion"] ."', '".
                $row["tipoRelacion"] ."', '".
                $row["uuid_r"] ."', '".
                $row["usoCFDI"] ."', ".
                $row["retenidos"] .")";
        $res = $conn->query( $sql );
        if( $conn->num_rows($res) > 0 ){
            $rit = $this->obtItemsG( $usr );
            $afc = 0;
            while( $itm = $conn->fetch_array($rit) ){
                $sql = "INSERT INTO documento_detalle(id_cliente, id_sucursal, serie, folio, item, cantidad, unidad, ".
                        "codigoproducto, descproducto, valorunitario, importe, ".
                        "iva, descuento, claveProdServ, claveUnidad, base, impuesto, tipoFactor, tasaOCuota, tipoImpuesto) VALUES (".
                        $row["id_cliente"] .", ".
                        $row["id_sucursal"] .", '".
                        $row["serie"] ."', ".
                        $row["folio"] .", ".
                        $itm["item"] .", ".
                        $itm["cantidad"] .", '".
                        $itm["unidad"] ."', '".
                        $itm["codigoproducto"] ."', '".
                        $itm["descproducto"] ."', ".
                        $itm["valorunitario"] .", ".
                        $itm["importe"] .", ".
                        $itm["iva"] .", ".
                        $itm["descuento"] .", '".
                        $itm["claveProdServ"] ."', '".
                        $itm["claveUnidad"] ."', ".
                        $itm["base"] .", '".
                        $itm["impuesto"] ."', '".
                        $itm["tipoFactor"] ."', '".
                        $itm["tasaOCuota"] ."', ".
                        $itm["tipoImpuesto"] .")";
                $res = $conn->query( $sql );
                if( $conn->num_rows($res) > 0 ) $afc++;
                if( $itm["ctapredial"] != "" ){
                    $sql = "INSERT INTO documento_detalle_complemento(id_cliente, id_sucursal, serie, folio, item, cuentaPredial) VALUES (".
                            $row["id_cliente"] .", ".
                            $row["id_sucursal"] .", '".
                            $row["serie"] ."', ".
                            $row["folio"] .", ".
                            $itm["item"] .", '".
                            $itm["ctapredial"] ."')";
                    $conn->query($sql);
                }
            }
            if( $afc > 0 ){
                $rit = $this->obtTotI( $usr );
                $afc = 0;
                while( $itm = $conn->fetch_array($rit) ){
                    $sql = "INSERT INTO documento_imp(id_cliente, id_sucursal, serie, folio, ".
                            "impuesto, tipoFactor, tasaOCuota, importe) VALUES (".
                            $row["id_cliente"] .", ".
                            $row["id_sucursal"] .", '".
                            $row["serie"] ."', ".
                            $row["folio"] .", '".
                            $itm["impuesto"] ."', '".
                            $itm["tipoFactor"] ."', '".
                            $itm["tasaOCuota"] ."', ".
                            $itm["importe"] .")";
                            $res = $conn->query( $sql );
                    if( $conn->num_rows($res) > 0 ) $afc++;
                }
                if( $afc > 0 ){
                    $cad = "ok";
                    if( $row["id_tipocomprobante"] == 2 ){
                        $this->verifSttDR( $usr );
                    }
                    $sql = "DELETE FROM fctmnldet WHERE idUsr = $usr";
                    $conn->query( $sql );
                    $sql = "DELETE FROM fctmnl WHERE idUsr = $usr";
                    $conn->query( $sql );
                    $sql = "DELETE FROM fctmnl_imp WHERE idUsr = $usr";
                    $conn->query( $sql );				
                }else{
                    $cad = "No se pudo obtener el detalle de impuestos.";
                    $sql = "DELETE FROM documentos WHERE id_cliente = ". $row["id_cliente"] ." AND ".
                            "id_sucursal = ". $row["id_sucursal"] ." AND serie = '". $row["serie"] ."' ".
                            "AND folio = ". $row["folio"];
                    $conn->query( $sql );
                }
            }else{
                $cad = "No se pudo obtener el detalle de la factura.";
                $sql = "DELETE FROM documentos WHERE id_cliente = ". $row["id_cliente"] ." AND ".
                        "id_sucursal = ". $row["id_sucursal"] ." AND serie = '". $row["serie"] ."' ".
                        "AND folio = ". $row["folio"];
                $conn->query( $sql );
                $sql = "DELETE FROM documento_detalle WHERE id_cliente = ". $row["id_cliente"] ." AND ".
                        "id_sucursal = ". $row["id_sucursal"] ." AND serie = '". $row["serie"] ."' ".
                        "AND folio = ". $row["folio"];
                $conn->query( $sql );
            }
        }else $cad = "No se pudo obtener la informacion de la factura.";
        if( $cad == "ok" ){
            $sql = "SELECT lower(rfc) AS rfc FROM clientes where id_cliente = $cli;";
            $rs = $conn->query( $sql );
            $itm = $conn->fetch_array( $rs );
            $na = $itm["rfc"] ."-". $ser.$fol .".txt";
            $cad .= "|".$na;
        }else $cad .= "|error";
        return $cad;
    }
    
    public function verifSttDR( $usr ){
        $sql = "SELECT d.uuid, d.total AS td, f.total AS tr FROM documentos d INNER JOIN fctmnl f ON d.uuid = f.uuid_r WHERE f.idUsr = $usr;";
        $conn = new Model();
        $res = $conn->query($sql);
        $row = $conn->fetch_array($res);
        if( $row["td"] == $row["tr"] ){
            $sql = "UPDATE documentos SET estatus = 0 WHERE uuid = '". $row["uuid"] ."';";
            $conn->query( $sql );
        }
    }
    
    public function genReporte($datos) {
        $conn = new Model();
        $result = $this->reporte( $datos , $conn );
        //$afc = $this->getAfectados();
        $resp = "Sin registros";
        while( $rows = $conn->fetch_array($result) ){
            //$rows["nombrearchivo"] = 'Documento';
            $ruta = $this->rutaf( $rows["id_cliente"], $rows["serie"], $rows["nombrearchivo"] , $conn);
            $info [] = array(
                'sucursal' => $rows['sucursal'],
                'nombre' => utf8_encode($rows['nombre']),
                'serie' => $rows['serie'],
                'folio' => $rows['folio'],
                'receptor' => utf8_encode($rows['receptor']),
                'total' => number_format( $rows["total"], 2 ),
                'fechafacturacion' => $rows['fechafacturacion'],
                'ruta' => $ruta
            );
            $resp = $info;
        }
        $conn->close();
        return $resp;
    }

    public function reporte( $datos , $conn ){
        $sql = "SELECT s.sucursal, s.nombre, d.* FROM documentos d
                INNER JOIN sucursales s ON s.id_sucursal = d.id_sucursal
                WHERE d.id_cliente = ". $datos["cli"] ." AND d.id_sucursal = ". $datos["suc"]. "
                AND d.serie = '". $datos["ser"] ."' AND d.folio = ". $datos["nof"] ." AND d.uuid <> ''";
        //$conn = new Model();
        //die(print($sql));
        $result = $conn->query( $sql );
        //$this->setAfectados( $conn->affected_rows() );
        //$conn->close();
        return $result;
    }

    public function rutaf( $clien, $serie, $file , $conn){
        $sql = "SELECT nombrecorto FROM clientes WHERE id_cliente = $clien ";
        $res = $conn->query( $sql );
        if( $conn->num_rows( $res ) > 0 ){
            $row = $conn->fetch_array($res);
            $ruta = "C:/AccRed/Clientes/". $row["nombrecorto"] ."/Out/". $serie ."/". $file;
            //$ruta = $this->get('kernel')->getWebDir().$row["nombrecorto"] ."/". $serie ."/". $file;
        }else $ruta = "Error";
        return $ruta;
    }

    public function datRep ($idusr) {
        $res = $this->datFact($idusr);
        $conn = new Model();
        $row = $conn->fetch_array($res);
        $datos = [
            'cli' => $row["id_cliente"],
            'suc' => $row["id_sucursal"],
            'ser' => $row["serie"],
            'nof' => ($row["folio"]+1)
        ];
        return $datos;
    }
}
<?php

namespace FacelecBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use FacelecBundle\Model\Model;

class GenerarMenuController extends Controller
{

    /**
     * Devuelve un arreglo con los permisos que pesee un usuario sobre el SIIF
     * Realiza:
     * 1.- Ejecuta procedimiento almacenado para obtener accesos al SIIF
     * 2.- Genera un arrelo con los datos de menu accesables por el usuario
     * 3.- Establece en sesión la viariable menú
     * Es ejecutada por:
     * 1.- InicioAction
     * @param $Usuarios_id id de usuario que inicia sesion en el sistema
     * @param $Usuarios_nombre nombre de usuario que inicia sesión en el sistema
     * @return array arreglo con todos los datos de menú al que el usuario tiene acceso
     */
    public function Generamenu($Usuarios_id, $Usuarios_nombre)
    {
        $session = new Session();
        $conn = new Model();
        $sql = "call spcrearmenu($Usuarios_id)";//llama al procedimiento almacenado para generar el menu en base al ide del usuraio.
        $result = $conn->query($sql);
        $menu = [];
        while($row = $conn->fetch_array($result))
        {
            $session->set($row['mnu_url'], $row['mnu_url']);
            //$menu[] = $row;
            $menu[] = array(
                'mnu_id'=>$row['mnu_id'],
                'mnu_url'=>$row['mnu_url'],
                'mnu_nom'=>utf8_encode($row['mnu_nom']),
                'mnu_cat'=>utf8_encode($row['mnu_cat']),
                'cat_nom'=>utf8_encode($row['cat_nom']),
                'icono'=>$row['icono'],
                'submenu'=>utf8_encode($row['submenu']),
                'rol_insert'=>$row['rol_insert'],
                'rol_update'=>$row['rol_update'],
                'rol_delete'=>$row['rol_delete']
            );
        }
        $menu1 = [
            'menu' => $menu,
            'usuarios_nombre' => $Usuarios_nombre,
        ];
        $session->set('menu', $menu1);
        $conn->close();
        return $menu1;
    }

}
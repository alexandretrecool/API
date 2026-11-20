<?php

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('display_startup_erros', 0);

$cChave = "MIIDRzCCArCgAwIBAgICVYkwDQYJKoZIhvcNAQEFBQAwgb8xCzAJBgNVBAYTAi0t
MRIwEAYDVQQIDAlTb21lU3RhdGUxETAPBgNVBAcMCFNvbWVDaXR5MRkwFwYDVQQK
DBBTb21lT3JnYW5pemF0aW9uMR8wHQYDVQQLDBZTb21lT3JnYW5pemF0aW9uYWxV
bml0MSAwHgYDVQQDDBdzZXJ2ZXIxMC5jb25zb2Z0LmNvbS5weTErMCkGCSqGSIb3
DQEJARYccm9vdEBzZXJ2ZXIxMC5jb25zb2Z0LmNvbS5weTAeFw0xNDEwMDMwODMw
NTRaFw0xNTEwMDMwODMwNTRaMIG/MQswCQYDVQQGEwItLTESMBAGA1UECAwJU29t
ZVN0YXRlMREwDwYDVQQHDAhTb21lQ2l0eTEZMBcGA1UECgwQU29tZU9yZ2FuaXph
dGlvbjEfMB0GA1UECwwWU29tZU9yZ2FuaXphdGlvbmFsVW5pdDEgMB4GA1UEAwwX
c2VydmVyMTAuY29uc29mdC5jb20ucHkxKzApBgkqhkiG9w0BCQEWHHJvb3RAc2Vy
dmVyMTAuY29uc29mdC5jb20ucHkwgZ8wDQYJKoZIhvcNAQEBBQADgY0AMIGJAoGB
ALhnCHk3loDXqe5NvM+tgMd+v/cXKDWzYHfSd2LHQXcbSa/I+rxhpVKaM+K2bgkl
+9j/2CaPTBus1mX+jZvi6l2HGIRC3CE/TobwzRmmhvU5zQ+0jd5BYvpz+x8Pbuea
NZCw+/2bUFFbc9Vb8gpIt6nCvfXzb0uqn6ZFb7iadHw5AgMBAAGjUDBOMB0GA1Ud
DgQWBBRW+z1SUPl8E60dkWa8kKWABpI6mzAfBgNVHSMEGDAWgBRW+z1SUPl8E60d
kWa8kKWABpI6mzAMBgNVHRMEBTADAQH/MA0GCSqGSIb3DQEBBQUAA4GBAJD4hfVN
OozCi/OTM8f7ze/YWq7QUECSkCkhfWNtFq3rxzHrx3fwmu3HSfmf4RSVd5CuY3Zr
jVfrG63rVZDRU6lZuBURxoVysgB1obEHjZjJuFAYhv+6deCMjuUa+H+3fwt8t1LB
/r9yRP+6lOB8UHwvR2Gpx9Cauf5IhaNJAVA7
";


$ctimezone = "America/Asuncion";
define("SP", "&nbsp;");
define("CR", "<br>");
define("AZUL", "#000080");
define("CIAN", "#7FFFD4");
define("VERM", "#FF0000");
define("BRAN", "#FFFFFF");
define("PRET", "#000000");
define("VERD", "#00FF00");
define("MAGE", "#FF00FF");
define("MARR", "#800000");
define("CINZ", "#808080");
define("AMAR", "#FFFF00");
define("OLIV", "#808000");
define("AZUF", "#0000FF");
define("LILA", "#800080");
require_once "mysql.php";
require_once "classes.php";
require_once "classes_1.php";
require_once "language.php";
require_once "Mobile_Detect.php";
require_once "mediator.php";

$crosto = "./consoft.css";
$versao = "0.13";
$clanguage = "BR";
$cempresa = "Abba";
$data_min = "01/01/2000";
$data_max = "31/12/2019";
$ausuario = array(0, "", 0);
$SitePrincipal = "http://www.cellshop.com.py";

$dir_relatorios_pdf = "/home/sist/sistema/gerente/impress/web";
$refresh_pdf_segundos = 10;
$limite_ler_pdf = 10;
$pdf_em_nova_janela = 1;
$local_tamanho = 5;

global $db;
global $sid;


define("cfg_max", 14);
define("cfg_tlimite", 1);
define("cfg_tlimpa", 2);
define("cfg_idioma", 3);
define("cfg_mserver", 4);
define("cfg_mssl", 5);
define("cfg_muser", 6);
define("cfg_mpassword", 7);
define("cfg_mport", 8);
define("cfg_mzone", 9);
define("cfg_mfrom", 10);
define("cfg_mname", 11);
define("cfg_mcabecalho", 12);
define("cfg_mrodape", 13);
define("cfg_tpendente", 14);


$db = '';
date_default_timezone_set($ctimezone);

$atiposnomes = array("RC" => "Recepcionado",
    "AN" => "Em Análise",
    "CT" => "Análise de Custos",
    "AP" => "Em Aprovação",
    "AV" => "Aprovado",
    "EX" => "Em Execução",
    "PC" => "Aguardando Peças",
    "ET" => "Executado",
    "CK" => "Em Checagem",
    "EN" => "Entregue",
    "OT" => "Outros",
    "RJ" => "Rejeitado",
    "ST" => "Substituição"
);

function is_ssl() {
    if (isset($_SERVER['HTTPS'])) {
        if ('on' == strtolower($_SERVER['HTTPS']))
            return true;
        if ('1' == $_SERVER['HTTPS'])
            return true;
    } elseif (isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] )) {
        return true;
    }
    return false;
}

function velocalvendas() {
    $aipspermitidos = array("192.168.12.", "192.168.3.", "10.10.0.", "200.195.155.178", "192.168.9.");

    $lok = false;
    foreach ($aipspermitidos as $value) {
        if (substr($_SERVER['REMOTE_ADDR'], 0, strlen($value)) == $value) {
            $lok = true;
            break;
        }
    }
    if (!$lok) {
        die($_SERVER['REMOTE_ADDR'] . " Acesso nao autorizado deste local");
        return false;
    }

    return true;
}

//return;
function velocal() {
    $aipspermitidos = array("192.168.12.", "192.168.3.", "10.10.0.", "200.195.155.178");

    $lok = false;
    foreach ($aipspermitidos as $value) {
        if (substr($_SERVER['REMOTE_ADDR'], 0, strlen($value)) == $value) {
            $lok = true;
            break;
        }
    }
    if (!$lok) {
        die($_SERVER['REMOTE_ADDR'] . " Acesso nao autorizado deste local");
        return false;
    }

    return true;
}

function mysqli_myquery($cstr) {
    global $db;
    $otableret = mysqli_query($db, $cstr);
    if (mysqli_error($db) != '') {
        echo "<br><b>******************* ERRO NA REQUISICAO SQL   *******************<BR>";
        echo $cstr . "<br>";
        echo mysqli_error($db);
        echo "<br><b>****************************************************************<BR>";
    }
    return $otableret;
}

function mysql_myquery($cstr) {
    return mysqli_myquery($cstr);
}

function ve_ssl() {
    global $cSemSsl;

    require("mysql.php");

    $lSemSsl = 1;

    $aip = array();
    $cip = strtok($cSemSsl, " ");
    while ($cip) {
        $aip[] = $cip;
        $cip = strtok(" ");
    }

    $cip = getenv("REMOTE_ADDR");
    for ($i = 0; $i < count($aip); $i++) {
        if (!strncmp($aip[$i], $cip, strlen($aip[$i]))) {
            $lSemSsl = 1;
            break;
        }
    }
    if ($lSemSsl)
        return 1;

    if (getenv("SSL_SESSION_ID") == "") {
        men("Esta não é uma conexão segura.<br><br><b>Incie com https://....", ($paginainicial ? "window.location.assign('$paginainicial')" : ""), '', '', 'Ir para a página');
//        erro(idm("Esta nao e uma conexao segura.<br><br><b>Incie com https://...."));
        return 0;
    }
    return 1;
}

function cifra($a = '', $b = '') {
    $var = "";

    if (empty($b)) {
        $b = 3;
    }

    for ($i = 1; $i <= strlen($a); $i++) {
        $var = $var . chr(255 + $i - (($b * $i) + ord(substr($a, $i - 1, 1))));
    }

    return $var;
}

function data2sql($ddata, $lSemAspas = false) {
    $ddata = str_replace("-", "", $ddata);
    $ddata = str_replace("/", "", $ddata);
    $ddata = str_replace(".", "", $ddata);
    $ddata = str_replace(" ", "", $ddata);
    $aux = ($lSemAspas ? "" : "'") . substr($ddata, 4, 4) . "-" . substr($ddata, 2, 2) . "-" . substr($ddata, 0, 2) . ($lSemAspas ? "" : "'");
    return $aux;
}

function hora2sql($chora, $lSemAspas = false) {
    $aaux = explode(":", $chora);
    while (count($aaux) < 3) {
        $aaux[] = "00";
    }
    for ($i = 0; $i < 3; $i++) {
        while (strlen($aaux[$i]) < 2) {
            $aaux[$i] = "0" . $aaux[$i];
        }
    }
    $aux = ($lSemAspas ? "" : "'") . $aaux[0] . ":" . $aaux[1] . ":" . $aaux[2] . ($lSemAspas ? "" : "'");
    return $aux;
}

function sql2time($ddata) {
    if ($ddata == '1000-01-01')
        return '';
    $aux = substr($ddata, 11, 2) . ":" . substr($ddata, 14, 2) . ":" . substr($ddata, 17, 2);
    return $aux;
}

function sql2data($ddata) {
    if ($ddata == '1000-01-01' || $ddata == '0000-00-00')
        return ' ';
    $aux = substr($ddata, 8, 2) . "/" . substr($ddata, 5, 2) . "/" . substr($ddata, 2, 2);
    return $aux;
}

function c2sql($str) {
    return carac2sql($str);
}

function carac2sql($str) {
    $aux = "'" . $str . "'";
    return $aux;
}

function nHtml2Sql($cstr) {
    $cstr = strtok($cstr, ",");
    $cstr = str_replace(".", "", $cstr);
    return $cstr;
}

function erro($cmen = '', $ctitulo = '') {

    require("mysql.php");

    if (!$ctitulo)
        $ctitulo = idm("Error del Sistema");
    echo HtmlCabOn($ctitulo, "", "inicio", "./index.php");
    echo HtmlTableOn(0, 0, 1);

    if ($cImagem)
        abrelinha(HtmlImage($cImagem, 0), "H", "", "", 2);
    abrelinha(idm("ERROR DEL SISTEMA"), "H", "", "", 2);
    abrelinha(SP, "C", "", "", 2);
    abrelinha($cmen, "C", true, "", 2);
    abrelinha(SP, "C", true, "", 2);
    abrelinha();
    abrecel("", "C", "", "", 2);
    echo criabotao("submit");
    echo HtmlTableOff();
    echo HtmlCabOff();

    return 0;
}

function men($cmen, $clink, $lfecha, $ctitulo, $cbotao) {

    require("mysql.php");

    if (empty($ctitulo))
        $ctitulo = idm("Mensaje Del Sistema");
    echo HtmlCabOn($ctitulo);
    echo HtmlTableOn(0, 0, 1);
    if ($cImagem)
        abrelinha(HtmlImage($cImagem, 0), "H", "", "", 2);
    abrelinha(idm("MENSAJE DEL SISTEMA"), "H", "", "", 2);
    abrelinha(SP, "C", "", "", 2);
    abrelinha($cmen, "C", true, "", 2);
    abrelinha(SP, "C", "", "", 2);

    if ($lfecha) {
        $cclick = "javascript:window.close()";
    } elseif (empty($clink)) {
        $cclick = "javascript:history.go(-1)";
    } else {
        $cclick = $clink;
    }

    if (!$cbotao)
        $cbotao = "Ok";
    echo abrelinha(criabotao("ok", $cbotao, $cclick), "C", "", "", 2);

    echo HtmlTableOff();
    echo HtmlCabOff();

    return 0;
}

function conecta($server = '', $port = '', $user = '', $password = '', $database = '') {
    global $db;
    if (!ve_ssl())
        return 0;
    require("mysql.php");
    $db = mysqli_connect($server, $user, $password, $database, $port);
    if (!$db) {
        erro(idm("Error accendendo al servidor SQL") . " <br>" . mysqli_error($db));
        return 0;
    }
    mysqli_set_charset($db, "latin1");
    return $db;
}

function gera_sid($user) {
    global $versao;
    global $idioma;
    global $db;

    $caux = "10q2wertyu3iopasd4fghj5klzx6cvbnm7poiu9ytre0wqmn1bvc2xzlkj3hgf4dsa51235fg86773";
    $caux1 = getdate();
    $caux1 = rand(999999, 99999999999999);
    $caux1 = $caux1 . rand(999999, 99999999999999);
    $caux1 = $caux1 . rand(999999, 99999999999999);
    $caux1 = $caux1 . rand(999999, 99999999999999);
    $caux1 = $caux1 . rand(999999, 99999999999999);
    $caux1 = $caux1 . rand(999999, 99999999999999);
    $sid = "";
    for ($i = 1; $i < strlen($caux1); $i++) {
        $sid = $sid . substr($caux, substr($caux1, $i, 1) + $i, 1);
    }


    $sid = $idioma . $sid;
    $olinha = mysql_linha("OPERADOR_OPE", "codigo,nome,degrau", "codigo=$user");

    $caux = "insert php_wc_session set codigo=" . $user . ",nome='" . $olinha[1] . "',nivel=" . cifra($olinha[2]) . ",data_in=now(),sid='" . $sid . "',ip='" . getenv("REMOTE_ADDR") . "'";
    mysqli_query($db, "update php_wc_session set data_out=now() where codigo=" . $user . " and data_out= '0000-00-00' ");
    mysqli_query($db, $caux);
//controle de versao
    mysqli_query($db, "create table if not exists php_wc_config (
	    versao int(10) not null default 0,
	    data datetime not null default '0000-00-00 00:00:00'
	     )");
    $otable = mysqli_query($db, "select versao from php_wc_config order by data desc limit 1");
    $linha = mysqli_fetch_row($otable);

    if ($linha && ceil($linha[0]) == ceil($versao * 100)) {
        ve_sid($sid);
        return $sid;
    }
    if (!$linha || ceil($linha[0]) < ceil($versao * 100)) {
        mysqli_query($db, "insert php_config set data=now(),versao=" . ceil($versao * 100));
        ve_sid($sid);
        return $sid;
    }
    erro(idm("La version instalada es inferior a la actual.<br>Instalada: ") . number_format($linha[0] / 100, 2) . idm("<br>Utilizada: ") . number_format($versao, 2));
    return;
//return false;
}

function ve_sid(&$sid) {
    global $codven;
    global $nomven;
    global $lojven;
    global $ausuario;
    global $idioma;
    global $db;
    if (!velocal()) {
        return;
    }
    $idioma = substr($sid, 0, 2);
    if (!$db) {
        $db = conecta();
        if (!$db)
            return;
    }

    if (empty($sid)) {
//        $sid = $_GET['sid'];
//        if (empty($sid))
//            $sid = $_POST['sid'];
        $sid = PegaVar("sid");
    }
    $idioma = substr($sid, 0, 2);

    $tempo = 12000;

    if (empty($sid)) {
        erro(idm("Identificador no valido"));
        return 0;
    }

    $caux = "select * from php_wc_session where sid='" . $sid . "'";
    $otable = mysqli_query($db, $caux);


    if (mysqli_num_rows($otable) == 0) {
        erro(idm("Identificador no valido"));
        return 0;
    }

    $olinha = mysqli_fetch_row($otable);

    if (!($olinha[4] == '0000-00-00 00:00:00')) {
        fecha_sid($sid);
        erro(idm("Sistema accesado en otro local o") . "<br>" . idm("Identificador no valido"));
        return 0;
    }

    $otable = mysqli_query($db, "select now()-data_in from php_wc_session where sid='" . $sid . "'");
    $olinha = mysqli_fetch_row($otable);
    if ($olinha[0] > $tempo) {
        fecha_sid($sid);
        erro(idm("Tiempo expirado"));
        return 0;
    }

    mysqli_query($db, "update php_wc_session set data_in=now() where sid='" . $sid . "'");
    $otable = mysqli_query($db, "select codigo,nome,nivel from php_wc_session where sid='" . $sid . "'");
    $olinha = mysqli_fetch_row($otable);
    $ausuario = array($olinha[0], $olinha[1], $olinha[2]);
    return array($olinha[0], $olinha[1], $olinha[2]);
}

function fecha_sid($sid) {
    global $db;
    mysqli_query($db, "update php_wc_session set data_out=now() where sid='" . $sid . "'");
    return 0;
}

function multi_post_item($input_item_name) {
    $array_output = array();
    $in_handle = fopen("php://input", "r");
    $raw_input_items = split("&", urldecode(fread($in_handle, 100000)));
    foreach ($raw_input_items as $input_item) {
        // split this item into name/value pair
        $item = split("=", $input_item);
        // form item name
        $item_name = $item[0];
        // form item value
        $item_value = $item[1];
        if ($item_name == $input_item_name) {
            $array_output[] = $item_value;
        }
    }

    if ($array_output) {
        return $array_output;
    } else {
        return multi_get_item($input_item_name);
    }
}

function ve_nivel($ausu, $nniv) {
    global $db;
    $otable = mysqli_query($db, "select * from php_menu where codigo=" . $nniv);
    $olinha = mysqli_fetch_row($otable);
    if ($ausu[2] < $olinha[3])
        return 0;
    return 1;
}

function men_java($cmen) {

    $cjava = "function fecha(eu){
    alert('" . $cmen . "');
    eu.close();
    return true;
  }";

    echo HtmlCabOn(idm("Mensaje Del Sistema"), $cjava, "", "", "fecha(this)");
    echo HtmlCabOff();
    return;
}

function FirstUpper($cstr, $primeiro = '') {
    $cret = "";
    $caux = strtok($cstr, " ");
    while ($caux) {
        $cret = $cret . " " . strtoupper(substr($caux, 0, 1)) . strtolower(substr($caux, 1));
        $caux = strtok(" ");
    }
    $cstr = ltrim(rtrim($cret));

    $cret = "";
    $caux = strtok($cstr, ".");
    while ($caux) {
        $cret = $cret . "." . strtoupper(substr($caux, 0, 1)) . substr($caux, 1);
        $caux = strtok(".");
    }
    $cstr = substr($cret, 1);

    $cret = "";
    $caux = strtok($cstr, "-");
    while ($caux) {
        $cret = $cret . "." . strtoupper(substr($caux, 0, 1)) . substr($caux, 1);
        $caux = strtok(".");
    }

    return substr($cret, 1);
}

function digito($ncod) {
    $ncod = str_pad($ncod, 12, "0", STR_PAD_LEFT);
    $apar = 0;
    $aimp = 0;
    for ($i = 0; $i < 12; $i++) {
        if ($i % 2 == 0) {
            $aimp = $aimp + substr($ncod, $i, 1);
        } else {
            $apar = $apar + substr($ncod, $i, 1);
        }
    }
    $nret = (3 * $apar) + $aimp;
    $nret = $nret % 10;
    $nret = 10 - $nret;
    if ($nret > 9)
        $nret = 0;

    return $nret;
}

function poedigito($ncod) {
    return $ncod * 10 + digito($ncod);
}

function tiradigito($ncod) {
    return floor($ncod / 10);
}

function vedigito($ncod) {
    $nint = substr($ncod, 0, -1);
    //$nint = floor($ncod / 10);
    //$ndig = $ncod % 10;
    $ndig = substr($ncod, -1, 1);
    //return $ndig."-".$nint."-".digito($nint);
    return ($ndig == digito($nint));
}

function html2num($cnum) {
    return str_replace(",", ".", str_replace(".", "", $cnum));
}

function fechacel() {
    echo "</td>";
}

// mostra,posicao(E,D,C,H),negrito,fonte,span colunas,span linhas)
function abrelinha($cstr = '', $cpos = '', $lneg = false, $ntamfonte = '', $ncolspan = '', $nrowspan = '', $ccor = '', $clarg = '') {
    echo "<tr>";
    if ($cstr || $cpos || $ncolspan || $ncolspan)
        impcel($cstr, $cpos, $lneg, $ntamfonte, $ncolspan, $nrowspan, false, $ccor, $clarg);
}

function fechalinha() {
    echo "</tr>";
}

function abrecel($cstr = '', $cpos = '', $lneg = '', $ntamfonte = '', $ncolspan = '', $nrowspan = '', $ccor = '', $clarg = '') {
    impcel($cstr, $cpos, $lneg, $ntamfonte, $ncolspan, $nrowspan, true, $ccor, $clarg);
}

function impcel($cstr = '', $cpos = '', $lneg = '', $ntamfonte = '', $ncolspan = '', $nrowspan = '', $lsemfechar = '', $ccor = '', $clarg = '') {
    $lcent = 0;
    echo "<td class=\"";
    if ($cpos == "D" || $cpos == "d") {
        echo "right";
        $lcent = 2;
    } elseif ($cpos == "C" || $cpos == "c") {
        echo "cent";
        $lcent = 1;
    } elseif ($cpos == "H" || $cpos == "h") {
        echo "header";
        $lcent = 1;
    } elseif ($cpos == "H1" || $cpos == "h1") {
        echo "headers";
        $lcent = 1;
    } else {
        echo "default";
    }
    echo "\" ";
    if ($ncolspan > 1)
        echo " colspan=\"" . $ncolspan . "\" ";
    if ($nrowspan > 1)
        echo " rowspan=\"" . $nrowspan . "\" ";
    if (!empty($clarg))
        echo " width=\"" . $clarg . "\"";

    echo ">";
    if ($lcent == 2) {
        echo "<p align=right>";
    } elseif ($lcent == 1) {
        echo "<p align=center>";
    }
    if ($ntamfonte || $ccor) {
        echo "<font ";
        if ($ntamfonte)
            echo "size=\"" . $ntamfonte . "\" ";
        if ($ccor)
            echo "color=\"" . $ccor . "\" ";
        echo ">";
    }
    if ($lneg)
        echo "<b>";
    if ($cstr)
        echo $cstr;
    if (!$lsemfechar)
        echo "</td>";
}

function my_number_format($p1 = 0, $p2 = 0, $p3 = ",", $p4 = ".") {
    if ($p1 == 0)
        return "&nbsp;";
    if (!$p3) {
        $p3 = ",";
        $p4 = ".";
    }
    if (!$p4) {
        $p3 = ",";
        $p4 = ".";
    }
    if (!$p2)
        $p2 = 0;
    return number_format($p1, $p2, $p3, $p4);
}

/*
  none 			-> nenhuma formatacao
  integer numeric  	-> numerico inteiro
  float 			-> decimal com 2
  date 			-> data dd/mm/aaaa
  time 			-> hora hh:mm
  month_year 		-> mes e ano mm/aaaa
  cep 			-> cep 99999-999
  percent 		-> percentual 6 int e 2 dec
  cpf 			-> apenas 11 numeros sem checagem
  cnpj			-> apenas 14 numeros sem checagem
  text 			-> somente letras
  uppercase 		-> qq letras e numeros para maiusculo
  textnumber		-> letras e numeros
  full_name		-> letras e espacos
  text_entry		-> qq texto
  password 		-> nenhuma formatacao com senha
  email 		-> email
 */

function CriaCampo($cnome, $ctipo, $ntam = '', $default = '', $ctexto = '', $nlinha = 1, $csaifoco = '', $ldisabled = false, $nfontepadrao) {
    if ($default && $ctipo == "money") {
        $default = str_replace(".", ",", $default);
    }
    if ($ctipo == "livre") {
        if (empty($nlinha))
            $nlinha = 1;
        if (empty($ntam))
            $ntam = 10;
        $cstr = "<textarea ";
        if ($nfontepadrao) {
            $cstr.=" style=\"font-size:" . $nfontepadrao . "pt; \"";
        }
        $cstr.=" name=\"$cnome\" id=\"$cnome\" cols=\"$ntam\" rows=\"$nlinha\" " . ($ldisabled ? " disabled " : "") . " >" . $default . "</textarea>";

        if (!empty($ctexto))
            $cstr = $ctexto . $cstr;
        return $cstr;
    }
    $cstr = "<input ";
    if ($nfontepadrao) {
        $cstr.=" style=\"font-size:" . $nfontepadrao . "pt; \"";
    }
    $cstr.=" type=\"text\" name=\"[[0]]\" id=\"[[0]]\"  size=\"[[4]]\" maxlength=\"[[1]]\" value=\"[[2]]\"  onkeypress=\"return(validaConteudo(event,this,'[[3]]'))\" onFocus=\"removeCaracs(this,'[[3]]')\" onblur=\"formatCamp(this,'[[3]]')";
    if ($csaifoco)
        $cstr.=";$csaifoco";
    $cstr.="\" onkeyup=\"saltaCampo(event,this,'[[3]]',[[1]] )\" " . ($ldisabled ? " disabled " : "") . " > ";
    $areplace = array();

    $areplace[0] = $cnome;
    if (!$ntam)
        $ntam = 10;
    $areplace[1] = $ntam;
    $areplace[2] = $default;
    $areplace[3] = $ctipo;

    if ($areplace[3] == 'date') {
        if (!$areplace[2])
            $areplace[2] = date('d/m/Y');
        $areplace[1] = 10;
    }elseif ($areplace[3] == 'time') {
        if (!$areplace[2])
            $areplace[2] = date('H:i');
        $areplace[1] = 5;
    }elseif ($areplace[3] == 'month_year') {
        if (!$areplace[2])
            $areplace[2] = date('m/Y');
        $areplace[1] = 7;
    }elseif ($areplace[3] == 'cep') {
        if (!$areplace[2])
            $areplace[2] = "";
        $areplace[1] = 9;
    }elseif ($areplace[3] == 'password') {
        $areplace[3] = "none";
        $cstr = str_replace("\"text\"", "\"password\"", $cstr);
        if (!$areplace[2])
            $areplace[2] = "";
    }elseif ($areplace[3] == 'percent') {
        if (!$areplace[2])
            $areplace[2] = "0";
        $areplace[1] = 9;
    }elseif ($areplace[3] == 'numeric' || $areplace[3] == 'integer' || $areplace[3] == 'float' || $areplace[3] == 'money') {
        if (!$areplace[2])
            $areplace[2] = "0";
    }elseif ($areplace[3] == 'text' || $areplace[3] == 'textnumber' || $areplace[3] == 'uppercase' ||
            $areplace[3] == 'full_name' || $areplace[3] == 'none') {
        if (!$areplace[2])
            $areplace[2] = "";
    }else {
        $areplace[3] = "text_entry";
        if (!$areplace[2])
            $areplace[2] = "";
    }


    $areplace[4] = $areplace[1];

    if ($areplace[3] == 'float' || $areplace[3] == 'money')
        $areplace[4]+=3;

    for ($i = 0; $i < count($areplace); $i++) {
        $cstr = str_replace("[[" . $i . "]]", $areplace[$i], $cstr);
    }
    if ($ctexto)
        $cstr = $ctexto . $cstr;
    return $cstr;
}

function CriaBotoesFinal($lvoltar = false, $llimpar = false, $lenviar = false, $lcongelar = false, $nvoltar = '', $nfontepadrao) {
    if (!$nvoltar)
        $nvoltar = -1;
    $cstr = "";
    if ($lvoltar) {
        if ($lvoltar == 1) {
            $cstr.=criabotao("voltar", idm("Volver"), "javascript:history.go(" . $nvoltar . ")", '', '', $nfontepadrao);
        } else {
            $cstr.=criabotao("voltar", idm("Volver"), "window.location.href='" . $lvoltar . "'", '', '', $nfontepadrao);
        }
    }
    if ($llimpar) {
        $cstr.=criabotao("reset", idm("Limpar"), "", SP . SP, '', $nfontepadrao);
    }
    if ($lenviar) {
        $cstr.=criabotao("submeter", idm("Enviar"), iif($lcongelar, "this.disabled=true;document.forms[0].submit()", ""), SP . SP, '', $nfontepadrao);
    }
    return $cstr;
}

function CriaBotao($cnome, $cescrita = '', $cexec = '', $ctexto = '', $cimagem = '', $nfontsize) {
    if ($cnome == "submit" || $cnome == "submeter") {
        $ctipo = "submit";
    } elseif ($cnome == "reset") {
        $ctipo = "reset";
    } else {
        $ctipo = "button";
    }
    if (!$cescrita)
        $cescrita = "Ok";
    if (!$cimagem) {
        $caux = "<input ";
        if ($nfontsize) {
            $caux.=' style="font-size: ' . $nfontsize . 'pt;"';
        }
        $caux.=" type=\"" . $ctipo . "\"  name=\"" . $cnome . "\"  id=\"" . $cnome . "\" value=\"" . $cescrita . "\" ";
    } else {
        $caux = "<input ";
        if ($nfontsize) {
            $caux.=' style="font-size: ' . $nfontsize . 'pt;"';
        }
        $caux.=" type=\"image\" src=\"$cimagem\"  name=\"" . $cnome . "\"  id=\"" . $cnome . "\" value=\"" . $cescrita . "\" ";
    }
    if ($cexec)
        $caux = $caux . " onclick=\"" . $cexec . "\" ";
    if ($ctexto)
        $caux = $ctexto . $caux;

    $caux = $caux . ">";
    return $caux;
}

function CriaCombo($cnome, $ctodos = '', $ctabela = '', $ccampos = '', $cretorno = '', $cwhere = '', $cordem = '', $nlinha = 1, $aselect = '', $cexec = '', $csql = '', $ldisabled = false, $nfontepadrao) {
//echo $csql.CR;
    global $db;

    if ($csql) {
        $caux = $csql;
    } else {
        $caux = "select concat_ws('-'," . $ccampos . ") as nome," . $cretorno . " as codigo  from " . $ctabela . " where is_deleted='N' ";
    }
    MyDefault($nlinha, 1);
    if ($cwhere)
        $caux = $caux . " and " . $cwhere;
    if ($cordem)
        $caux = $caux . " order by " . $cordem;

    $otable = mysqli_query($db, $caux);

    $caux = "<select";
    if ($nfontepadrao) {
        $caux.=" style=\"font-size: " . $nfontepadrao . "pt \" ";
    }
    $caux.=" size=\"" . $nlinha . "\" ";
    if ($nlinha > 1)
        $caux.=" multiple ";
    $caux.=" name=\"" . $cnome . "\" ";
    $caux.=" id=\"" . $cnome . "\" ";
    if ($cexec) {
        $caux.=" onchange=\"" . $cexec . "\" ";
    }
    $caux.=($ldisabled ? " disabled " : "") . " > ";
    if ($ctodos) {
        $caux.="<option ";
        if (!$aselect)
            $caux.="selected ";
        $caux.="value=\"" . strtok($ctodos, ",") . "\">" . strtok(',') . "</option>";
    }

    while ($linha = mysqli_fetch_row($otable)) {
        $caux.="<option ";
        if ($aselect) {
            for ($i = 0; $i < count($aselect); $i++) {
                if ($linha[1] == $aselect[$i])
                    $caux.=" selected ";
            }
        }
        $caux.=" value=\"" . $linha[1] . "\">" . $linha[0] . "</option>";
    }
    $caux = $caux . "</select>";
    return $caux;
}

function CriaComboBox($cnome, $ctodos, $acampos, $nlinha, $aselect, $cfunselect, $nfontsize) {
    MyDefault($nlinha, 1);

    $caux = "<select ";
    if ($nfontsize) {
        $caux.=' style="font-size: ' . $nfontsize . 'pt;"';
    }
    $caux.=" size=\"" . $nlinha . "\" ";
    if ($nlinha > 1)
        $caux.=" multiple ";
    if ($cfunselect)
        $caux.=" onchange=\"" . $cfunselect . "\" ";
    $caux.=" name=\"" . $cnome . "\" id=\"" . $cnome . "\" >";

    if ($ctodos) {
        $caux.="<option ";
        if (!$aselect)
            $caux.="selected ";
        $caux.="value=\"" . strtok($ctodos, ",") . "\">" . strtok(',') . "</option>";
    }

    for ($j = 0; $j < count($acampos); $j++) {
        $caux.="<option ";
        $ccod = strtok($acampos[$j], ",");
        $cnom = strtok(",");
        for ($i = 0; $i < count($aselect); $i++) {
            if ($ccod == $aselect[$i])
                $caux.=" selected ";
        }
        $caux.=" value=\"" . $ccod . "\">" . $cnom . "</option>";
    }
    $caux = $caux . "</select>";
    return $caux;
}

// aop=array("c,Codigo,1" ,...)
function CriaRadio($cnome, $aop, $lcomsp = false, $conclick = '') {
    $caux = "";
    for ($i = 0; $i < count($aop); $i++) {
        if ($lcomsp)
            $caux = $caux . SP;
        $caux = $caux . "<input type=\"radio\" name=\"" . $cnome . "\" value=\"" . strtok($aop[$i], ",") . "\"";
        $cstr = strtok(",");
        if (strtok(","))
            $caux = $caux . " checked";
        if ($conclick)
            $caux.=" onclick=\"" . $conclick . "\" ";

        $caux = $caux . ">" . $cstr;
    }
    return $caux;
}

function CriaCheck($cnome, $lcheck = false, $cstr = '', $conclick = '', $nfontsize) {
    $caux = "<input ";
    if ($nfontsize) {
        $caux.=' style="width: ' . ($nfontsize + 2) . 'px;height: ' . ($nfontsize + 2) . 'px;"';
    }
    $caux.=" type=\"checkbox\" name=\"" . $cnome . "\" id=\"" . $cnome . "\" ";
    if ($lcheck)
        $caux.="checked ";
    if ($conclick)
        $caux.=" onclick=\"" . $conclick . "\" ";
    $caux.="> ";
    if ($cstr)
        $caux.=$cstr;
    return $caux;
}

function ReCriaHidden($cnome) {
    return CriaHidden($cnome, PegaVar($cnome));
}

function AReCriaHidden($anome) {
    $caux = "";
    for ($i = 0; $i < count($anome); $i++) {
        $caux.=CriaHidden($anome[$i], PegaVar($anome[$i]));
    }
    return $caux;
}

function CriaHidden($cnome, $uval) {
    return $caux = "<input type=\"hidden\" name=\"" . $cnome . "\" id=\"" . $cnome . "\" value=\"" . $uval . "\" >";
}

function HtmlCabOn($ctitulo = '', $cjava = '', $cform = '', $cform_action = '', $conload = '', $consubmit = '', $chead = '') {
    global $crosto;

    echo mobilecabecalho();
    $cstr = '';
    $cstr.="<html><head>
<meta HTTP-EQUIV=\"PRAGMA\" CONTENT=\"NO-CACHE\">     
<meta http-equiv=\"expires\" content = \"-1\" />
<meta http-equiv=\"Content-Language\" content=\"pt-br\">
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">
<title>" . $ctitulo . "</title>
  <link href=\"" . $crosto . "\" type=text/css rel=stylesheet>
$chead
 <script type=\"text/javascript\" src=\"consoft.js\"></script> ";
    if ($cjava) {
        if ($cjava == "|") {
            $cstr.="|";
        } else {
            $cstr.="<script language=\"JavaScript\">" . $cjava . "</script>";
        }
    }
    $cstr.="</head>
<body";

    if ($conload)
        $cstr.=" onload=\"" . $conload . "\" ";
    $cstr.="><div id=\"popsen\"></div>";

    if ($cform) {
        $cstr.="<form method=\"post\" name=\"" . $cform . "\" ";
        if ($cform_action)
            $cstr.="  action=\"" . $cform_action . "\" ";
        if ($consubmit)
            $cstr.=" onSubmit=\"" . $consubmit . "\" ";
        $cstr.=">";
    }
    $cstr.="
<center>
";
    return $cstr;
}

function HtmlCabOnXX($ctitulo, $cjava, $cform, $cform_action, $conload, $consubmit, $nrefresh) {
    global $crosto;

    $cstr = "<html><head>";
    if ($nrefresh) {
        $cstr.="<meta http-equiv=\"refresh\" content=\"$nrefresh\" >
     ";
    }
    $cstr.="<meta http-equiv=\"Content-Language\" content=\"pt-br\">
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<title>" . $ctitulo . "</title>
  <link href=\"" . $crosto . "\" type=text/css rel=stylesheet>
  <script language=\"JavaScript\" src=\"./consoft.js\" type=\"text/javascript\" ></script>
    <script language=\"JavaScript\">
    <!--
" . $cjava . "
    -->
    </script>
</head>
<body";
    if ($conload)
        $cstr.=" onload=\"" . $conload . "\" ";
    $cstr.=">";

    if ($cform) {
        $cstr.="<form method=\"post\" name=\"" . $cform . "\" ";
        if ($cform_action)
            $cstr.="  action=\"" . $cform_action . "\" ";
        if ($consubmit)
            $cstr.=" onSubmit=\"" . $consubmit . "\"; ";
        $cstr.=">";
    }
    $cstr.="
<div align=\"center\"> <center>
";
    return $cstr;
}

function MyDefault(&$uvar, $uret) {
    if (!($uvar))
        $uvar = $uret;
    return $uvar;
}

function HtmlImage($cimagem, $nborder, $nwidth, $nheight) {
    global $cImagemLink;
    MyDefault($nborder, 0);
    $caux = "";
    if ($cImagemLink)
        $caux.="<a href=\"$cImagemLink\">";
    $caux.="<img border=\"" . $nborder . "\" src=\"" . $cimagem . "\" ";
    if ($nwidth && $nheight)
        $caux.=" width=\"" . $nwidth . "\" height=\"" . $nheight . "\" ";
    $caux.=">
  ";
    if ($cImagemLink)
        $caux.="</a>";
    return $caux;
}

function HtmlTableOff() {
    return "</table>";
}

function HtmlCabOff($cjava = '', $chtml = '') {
    $caux = "</center></div></form></body>";
    if ($chtml)
        $caux.=$chtml;
    if ($cjava)
        $caux.="<script language=\"JavaScript\" >" . $cjava . "</script>";
    $caux.="</html>";
    return $caux;
}

function HtmlTableOn($nborder = '', $npad = '', $nspace = '', $cwidth = '') {

    MyDefault($nborder, 0);
    MyDefault($npad, 0);
    MyDefault($nspace, 0);
    MyDefault($cwidth, "100%");
    if ($nborder > 998)
        return "</table>
	";

    return "<table border=\"" . $nborder . "\" cellpadding=\"" . $npad . "\" cellspacing=\"" . $nspace . "\" width=\"" . $cwidth . "\" >
  ";
}

function mysqli_linha($cTableString, $ccampos = '', $cwhere = '', $lSemIsDeleted = false) {
    return mysql_linha($cTableString, $ccampos, $cwhere, $lSemIsDeleted);
}

function mysql_linha($cTableString, $ccampos = '', $cwhere = '', $lSemIsDeleted = false) {
    global $db;
    if ($ccampos) {
        $caux = "select " . $ccampos . " from " . $cTableString . " where 1 ";
        if (!$lSemIsDeleted)
            $caux.=" and is_deleted='N' ";
        if ($cwhere)
            $caux.=" and " . $cwhere;
    }else {
        $caux = $cTableString;
    }
    return mysqli_fetch_row(mysqli_query($db, $caux));
}

function data_dif_sql($dataini, $datafim) {
//echo $datafim."/".$dataini;
    return ceil((mktime(0, 0, 0, substr($datafim, 5, 2), substr($datafim, 8, 2), substr($datafim, 0, 4)) - mktime(0, 0, 0, substr($dataini, 5, 2), substr($dataini, 8, 2), substr($dataini, 0, 4)) ) / 86400);
}

function data_dif($dataini, $datafim) {
    return ceil((mktime(0, 0, 0, substr($datafim, 3, 2), substr($datafim, 0, 2), substr($datafim, 6, 4)) - mktime(0, 0, 0, substr($dataini, 3, 2), substr($dataini, 0, 2), substr($dataini, 6, 4)) ) / 86400);
}

function hoje_sql() {
    return date("Y-m-d", time());
}

function hoje_html() {
    return date("d/m/Y", time());
}

function PegaVar($cvar, $lpost = false, $lget = false) {
    if (!$lpost && !$lget) {
        $lpost = true;
        $lget = true;
    }
    if ($lpost && array_key_exists($cvar, $_POST)) {
        return $_POST[$cvar];
    }
    if ($lget && array_key_exists($cvar, $_GET)) {
        return $_GET[$cvar];
    }
    return "";
}

function tira($cstr, $alista, $crep) {
    if (strlen($crep) == 0) {
        $crep = "";
    }
    for ($i = 0; $i < count($alista); $i++) {
        $cstr = str_replace($alista[$i], "", $cstr);
    }
    return $cstr;
}

function GravaSql($ctabela, $acampos, $lupdate, $cwhere) {
    global $db;
    if ($lupdate) {
        $cstr = "update ";
    } else {
        $cstr = "insert ";
    }
    $cstr.=$ctabela . " set ";
    for ($i = 0; $i < count($acampos); $i++) {
        if ($i > 0)
            $cstr.=",";
        if ($acampos[$i][2]) {
            $cstr.=$acampos[$i][0] . "=" . $acampos[$i][1];
        } else {
            $cstr.=$acampos[$i][0] . "='";
//	    $cstr.=$acampos[$i][1]."'";
            $cstr.=str_replace("'", "\\'", $acampos[$i][1]) . "'";
        }
    }
    if ($cwhere) {
        $cstr.=" where " . $cwhere;
    }
    mysql_myquery($cstr);
//    men($cstr);
    $nid = mysqli_fetch_row(mysqli_query($db, "select last_insert_id()"));
    return $nid[0];
}

function bom($dat) {
    return mktime(0, 0, 0, date('m', $dat), '01', date('Y', $dat));
}

function eom($dat) {
    return mktime(0, 0, 0, date('m', $dat) + 1, 0, date('Y', $dat));
}

function bom_html() {
    return date('d/m/y', mktime(0, 0, 0, date('m'), '01', date('Y')));
}

function eom_html() {
    return date('d/m/y', mktime(0, 0, 0, date('m') + 1, 0, date('Y')));
}

function str2date($cdata, &$aRetMDY) {
    $cdata = str_replace("-", "", str_replace(" ", "", str_replace("/", "", $cdata)));
    $cdia = substr($cdata, 0, 2);
    $cmes = substr($cdata, 2, 2);
    $cano = substr($cdata, 4, 4);

    if ($cano < 100 && $cano > 20)
        $cano+=1900;
    if ($cano < 100 && $cano <= 20)
        $cano+=2000;
    $aRetMDY = array($cmes, $cdia, $cano);
    $cdia = mktime(0, 0, 0, $cmes, $cdia, $cano);
    return $cdia;
}

function str2data($ddata) {
    $cdia = date('d/m/Y', str2date($ddata));
    return $cdia;
}

function somadata($ddata, $ndias, $ctipo) {
    $adata = array();
    str2date($ddata, $adata);
    if (strtoupper($ctipo) == "Y") {
        $adata[2]+=$ndias;
    } elseif (strtoupper($ctipo) == "M") {
        $adata[0]+=$ndias;
    } else {
        $adata[1]+=$ndias;
    }
    return date("d/m/Y", mktime(0, 0, 0, $adata[0], $adata[1], $adata[2]));
}

function somadate($ddata, $ndias, $ctipo) {
    $adata = array(date("m", $ddata), date("d", $ddata), date("Y", $ddata));

    if (strtoupper($ctipo) == "Y") {
        $adata[2]+=$ndias;
    } elseif (strtoupper($ctipo) == "M") {
        $adata[0]+=$ndias;
    } else {
        $adata[1]+=$ndias;
    }
    return mktime(0, 0, 0, $adata[0], $adata[1], $adata[2]);
}

function flink($clink, $sid, $avar, $ldireto) {
    if (!$clink)
        return "</a>";

    if ($ldireto) {
        $caux = "parent.location='$clink";
    } else {
        $caux = "<a href='$clink";
    }
    $caux.= ( strpos($clink, "?", 1) ? "&" : "?" ) . "sid=$sid";
    for ($i = 0; $i < count($avar); $i++) {
        $caux.="&" . $avar[$i][0] . "=" . urlencode($avar[$i][1]);
    }
    if ($ldireto) {
        $caux.="'";
    } else {
        $caux.="'>";
    }
    return $caux;
}

function EncBrowser($nnum = '', $ldecifra = false) {
    $caux = "~A!B@C#D$E%F^G&H*I(J_K+L-M=N[O]P{Q}R<S>T?U,V.W/X|Y";
    $cret = "";
    if (!$ldecifra) {
        for ($i = 0; $i < strlen($nnum); $i++) {
            $naux = substr($nnum, $i, 1);
            $cret.=substr($caux, $i + $naux, 1);
        }
    } else {
        for ($i = 0; $i < strlen($nnum); $i++) {
            $naux = substr($nnum, $i, 1);
            $npos = strpos($caux, $naux);
            $cret.=($npos - $i);
        }
    }

    return $cret;
}

function VeAcesso($noperador, $ncodigo) {
    global $db;
    if ($noperador == 1) {
        return true;
    }
    $otable = mysqli_query($db, "select * from php_wc_menu3 where codigo=$ncodigo and operador=$noperador ");
    if (mysqli_num_rows($otable) == 0) {
        return false;
    }
    $olinha = mysqli_fetch_array($otable);

    return $olinha["liberado"] > 0;
}

function VeAcesso1($noperador, $ncodigo) {
    global $db;
    $otable = mysqli_query($db, "select * from php_wc_menu1 where codigo=$ncodigo");
    $olinha = mysqli_fetch_array($otable);
    $nnivel = $olinha["nivel"];

    $otable = mysqli_query($db, "select degrau as nivel from OPERADOR_OPE where codigo=$noperador and is_deleted='N'");
    $olinha = mysqli_fetch_array($otable);

    if ($nnivel > cifra($olinha["nivel"])) {
        return 0;
    }

    $otable = mysqli_query($db, "select * from php_wc_menu2 where (operador=$noperador 
    or operador=999999) and
    codigo=$ncodigo");
    return mysqli_num_rows($otable) == 0;
}

function iif($lcondicao, $rtrue, $rfalse) {
    if ($lcondicao) {
        return $rtrue;
    } else {
        return $rfalse;
    }
}

function assql($uvar, $ctipo = '') {
    if (strtoupper($ctipo) == 'D') {
        return "'" . date("Y-m-d", $uvar) . "'";
    } elseif (strtoupper($ctipo) == 'N') {
        return $uvar;
    } elseif (strtoupper($ctipo) == 'L') {
        return iff($uvar, 1, 0);
    } else {
        return "'" . $uvar . "'";
    }
}

function html_ir_para($curl, $lsemsid, $aparametros) {
    global $sid;
    $caux = "<html><body onload=\"location='" . $curl . "?";
    if (!$lsemsid) {
        $caux.="sid=" . $sid;
    } else {
        $caux.="no_sid=Y";
    }

    for ($i = 0; $i < count($aparametros); $i++) {
        $caux.="&" . $aparametros[$i][0] . "=" . rawurlencode($aparametros[$i][1]);
    }

    $caux.="'\" ></body></html>";
    echo $caux;
}

function multi_get_item($cvar) {
    strtok(getenv("REQUEST_URI"), "?");
    $caux = strtok("?");
    $aaux = split("&", $caux);
    $aret = array();
    for ($i = 0; $i < count($aaux); $i++) {
        $caux = strtok($aaux[$i], "=");
        if ($caux == $cvar) {
            $aret[] = strtok("=");
        }
    }
    return $aret;
}

function Num2Base($nNum, $nBase) {
    $cRet = "";

    while ($nNum > 0) {
        $cRet = SubStr("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ,./;[]\-=<>?:{}|!@#$%^&*()_+", ($nNum % $nBase), 1) . $cRet;
        $nNum = floor($nNum / $nBase);
    }

    return $cRet;
}

function VeConfigRma($ccampo) {
    global $db;

    $otable = mysqli_query($db, "select $ccampo from CONFIG_$cLojaRma ");
    $olinha = mysqli_fetch_row($otable);
    return $olinha[0];
}

function Conv850($cstr) {
    return iconv("cp850", "utf-8", $cstr);
}

function ConvWin($cstr) {
    return iconv("ISO-8859-1", "utf-8", $cstr);
}

function StrFile($cfile, $cstr,$lAppend=false) {
    $farq = fopen($cfile, ($lAppend?"a":"w") );
    $nret = fwrite($farq, $cstr);
    fclose($farq);
    return ($nret == strlen($cstr));
}

function FileStr($cfile) {
    $farq = fopen($cfile, "r");
    $conteudo = fread($farq, filesize($cfile));
    fclose($farq);
    return ($conteudo);
}

function veconfig($ccampo = '', $noperador = 0) {
    global $db;
    global $server;
    global $port;
    global $user;
    global $password;
    global $database;

    if (!$db)
        conecta($server, $port, $user, $password, $database);
    if (!$ccampo) {
        mysqli_query($db, "create table if not exists php_wc_config_geral (codigo int(10) not null auto_increment primary key,valor text)");
        $otable = mysqli_query($db, "select count(*) as total from php_wc_config_geral");
        $olinha = mysqli_fetch_array($otable);
        for ($i = $olinha["total"]; $i < cfg_max; $i++) {
            mysqli_query($db, "insert php_wc_config_geral set valor=''");
        }
        return true;
    }
    if (!$noperador) {
        $otable = mysqli_query($db, "select valor from php_wc_config_geral where codigo=$ccampo");
        $olinha = mysqli_fetch_row($otable);
        return $olinha[0];
    } else {
        $otable = mysqli_query($db, "select $ccampo from php_wc_config_operador where usuario=$noperador");
        $olinha = mysqli_fetch_row($otable);
        return $olinha[0];
    }
}

function CriaSelect($cnome, $aselect, $nlinha) {
    MyDefault($nlinha, 1);

    $caux = "<select size=\"" . $nlinha . "\" ";
    if ($nlinha > 1)
        $caux.=" multiple ";
    $caux.=" id=\"$cnome\"  name=\"" . $cnome . "\" >";

    for ($i = 0; $i < count($aselect); $i++) {
        $caux.="<option ";
        $aaux = explode(",", $aselect[$i]);
        if ($aaux[2])
            $caux.=" selected ";
        $caux.=" value=\"" . $aaux[1] . "\">" . $aaux[0] . "</option>";
    }
    $caux = $caux . "</select>";

    return $caux;
}

function VeSaldoProduto($nproduto, $nloja) {
    global $db;

    if (!$nproduto || !$nloja) {
        return 0;
    }
    $otab = mysqli_query($db, "select qualoc from LOCAL_LOC where is_deleted='N' and LOCLOC=$nloja and PROLOC=$nproduto ");
    $olin = mysqli_fetch_row($otab);
    if (!$olin || !$olin[0]) {
        return 0;
    }
    return $olin[0];
}

/*
 * $aselect = array( array(<codigo>,<nome>,[<marcado>],[<desabilitado>]), ....)
 * $amark = vetor com os itens marcados
 * $adisabled = vetor com os itens desabilitados
 * $nmarcados = 0 - todos    1 - Apenas sem selecionar    2 - Apenas selecionados
 * 
 */

function CriaSelectCheck($cnome, $aselect, $nlargura = 300, $naltura = 100, $ctabela, $ccampos, $cwhere, $amark, $adisabled, $nmarcados = 0) {
    global $db;

    if ($ctabela) {
        $cstr = "select $ccampos from $ctabela ";
        if ($cwhere) {
            $cstr.=" where $cwhere ";
        }
        $otable = mysqli_query($db, $cstr);
        $aselect = array();
        while ($olinha = mysql_fetch_array($otable)) {
            $aselect[] = array($olinha[0], $olinha[1]);
        }
    }

    for ($i = 0; $i < count($aselect); $i++) {
        if (count($aselect[$i]) == 2) {
            $aselect[$i] = array($aselect[$i][0], $aselect[$i][1], false);
        }
        if (count($aselect[$i]) == 3) {
            $aselect[$i] = array($aselect[$i][0], $aselect[$i][1], $aselect[$i][2], false);
        }
        $lmark = false;
        foreach ($amark as $value) {
            if ($value == $aselect[$i][0]) {
                $lmark = true;
            }
        }
        $aselect[$i][2] = $lmark;

        $lmark = false;
        foreach ($adisabled as $value) {
            if ($value == $aselect[$i][0]) {
                $lmark = true;
            }
        }
        $aselect[$i][3] = $lmark;
    }

    if ($amark) {
        $asaida = array();
        if ($nmarcados == 2 || $nmarcados == 0) {
            foreach ($aselect as $avalue) {
                if ($avalue[2]) {
                    $asaida[] = $avalue;
                }
            }
        }
        if ($nmarcados == 1 || $nmarcados == 0) {
            foreach ($aselect as $avalue) {
                if (!$avalue[2]) {
                    $asaida[] = $avalue;
                }
            }
        }
        $aselect = $asaida;
    }


    $caux = "
     <style>
    #$cnome  { height: " . $naltura . "px; overflow: auto; width: " . $nlargura . "px; border: 1px solid #000; }
    #$cnome { list-style-type: none; margin: 0; padding: 0; overflow-x: hidden; }
    #$cnome li { margin: 0; padding: 0; }
    #$cnome label { display: block; color: WindowText; background-color: Window; margin: 0; padding: 0; width: 100%; }
    #$cnome label:hover { background-color: Highlight; color: HighlightText; }
</style>    


<ul id=\"$cnome\">
    ";

    $i = 0;
    foreach ($aselect as $avalue) {
        ++$i;
        $caux.="<li><label for=\"$cnome" . $i . "\"><input type=\"checkbox\" name=\"$cnome" . $avalue[0] . "\" id=\"$cnome" . $i . "\" " . ($avalue[2] ? " checked  " : "") . ($avalue[3] ? " disabled  " : "") . ">" . $avalue[1] . "-" . $avalue[0] . "</label></li>
        ";
    }

    $caux.="</ul>
    ";

    return $caux;
}

function img2html($ctexto) {
    $ctexto = str_replace(chr(141), CR, $ctexto);
    $ctexto = str_replace(chr(13) . chr(10), CR, $ctexto);
    $ctexto = str_replace(chr(10), CR, $ctexto);
    $ctexto = utf8_encode($ctexto);

    return $ctexto;
}

function Date2Unix($cData = '', $cHora = '', $lsql = false, $lutc = false) {
    global $ctimezone;
    if (!$cData) {
        $cData = date("d/m/Y");
        $lsql = false;
    }

    if (!$cHora) {
        $cHora = date("H:i:s");
    }

    $ahora = explode(":", $cHora . ":00:00");
    $adata = array(0, 0, 0);
    if ($lsql) {
        $aaux = explode("-", $cData);
        $adata[0] = $aaux[2];
        $adata[1] = $aaux[1];
        $adata[2] = $aaux[0];
    } else {
        $aaux = explode("/", $cData);
        $adata[0] = $aaux[0];
        $adata[1] = $aaux[1];
        $adata[2] = $aaux[2];
    }
    $nhora = mktime($ahora[0], $ahora[1], $ahora[2], $adata[1], $adata[0], $adata[2]);

    if (!$lutc) {
        $dateTimeZoneUTC = new DateTimeZone("UTC");
        $dateTimeZoneLOCAL = new DateTimeZone($ctimezone);

        $dateTimeUTC = new DateTime("now", $dateTimeZoneUTC);
        $dateTimeLOCAL = new DateTime("now", $dateTimeZoneLOCAL);

        $timeOffset = $dateTimeZoneLOCAL->getOffset($dateTimeUTC);
        $nhora+=$timeOffset;
    }




    return $nhora;
}

function Unix2Date($nHora, $lsql = false, $lutc = false) {
echo "1"."<br>";
    global $ctimezone;
    if (!$lutc) {
        $dateTimeZoneUTC = new DateTimeZone("UTC");
        $dateTimeZoneLOCAL = new DateTimeZone($ctimezone);

        $dateTimeUTC = new DateTime("now", $dateTimeZoneUTC);
        $dateTimeLOCAL = new DateTime("now", $dateTimeZoneLOCAL);

        $timeOffset = $dateTimeZoneLOCAL->getOffset($dateTimeUTC);
        $nHora-=$timeOffset;
    }

    $adata = array("", "", "");
    $adata[2] = date("H:i:s", $nHora);

    if ($lsql) {
        $adata[0] = date("Y-m-d H:i:s", $nHora);
        $adata[1] = date("Y-m-d", $nHora);
    } else {
        $adata[0] = date("d/m/Y H:i:s", $nHora);
        $adata[1] = date("d/m/Y", $nHora);
    }
    return $adata;
}

function Sec2Time($nHora) {
    $nsegundos = $nHora % 60;
    $nminutos = floor($nHora / 60) % 60;
    $nhora = floor($nHora / 60 / 60);

    if ($nhora > 99) {
        $cret = $nhora;
    } else {
        $cret = str_pad($nhora, 2, "0", STR_PAD_LEFT);
    }
    $cret.=":";
    $cret.= str_pad($nminutos, 2, "0", STR_PAD_LEFT);
    $cret.=":";
    $cret.= str_pad($nsegundos, 2, "0", STR_PAD_LEFT);
    return $cret;
}

function GeraSenha($ntam) {
    //alert(parseInt((Math.random()*10000)%62));
    $caux = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
    while (true) {
        $csenha = "";
        for ($i = 0; $i < $ntam; $i++) {
            $csenha.= substr($caux, rand(0, 61), 1);
        }
        preg_match("/[0-9]/", $csenha, $cret);
        if (count($cret) == 0) {
            continue;
            ;
        }
        preg_match("/[A-Z]/", $csenha, $cret);
        if (count($cret) == 0) {
            continue;
            ;
        }
        preg_match("/[a-z]/", $csenha, $cret);
        if (count($cret) == 0) {
            continue;
            ;
        }
        break;
    }
    return $csenha;
}

function randString($size) {
    //String com valor possíveis do resultado, os caracteres pode ser adicionado ou retirados conforme sua necessidade
    $basic = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    $return = "";

    for ($count = 0; $size > $count; $count++) {
        //Gera um caracter aleatorio
        $return.= $basic[rand(0, strlen($basic) - 1)];
    }

    return $return;
}

//Imprime uma String randônica com 20 caracteres

function LimpaRg($crg, $lSemLetras = false) {
    $crg = strtoupper($crg);
    $cret = '';

    for ($ni = 0; $ni < strlen($crg); $ni++) {
        $naux = ord($crg[$ni]);
        if ($lSemLetras) {
            if (($naux >= 48 && $naux <= 57)) {
                $cret.=substr($crg, $ni, 1);
            }
        } else {
            if (($naux >= 65 && $naux <= 90) || ($naux >= 48 && $naux <= 57)) {
                $cret.=substr($crg, $ni, 1);
            }
        }
    }
    return $cret;
}

function Commit($afile) {
    global $ccomimit;
    $caux = "";
    foreach ($afile as $value) {
        $caux.=$value;
    }
    $caux = substr($caux, 1);
    if ($caux) {
        file($ccomimit . " \"refresh=$caux\" ");
    }
}

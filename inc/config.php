<?php

$lgerente = false;
$lhomologa = false;
$aip = array("255.255.255.255", "200.195.155.178", "181.1.154.169", "69.46.2.51", "192.168.12.1", "89.116.51.29");
$acadena = array();
/* vetor de preco cadena/loja Ex: 
  $acadena[1]=2 ; //cadena 1 e loja 2
  $acadena[2]=5 ; //cadena 2 e loja 5
 *  *  *  */
$acadena[1] = 1;
$acadena[2] = 2;

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
require_once "idioma.php";
require_once "Mobile_Detect.php";
require_once "mediator.php";
require_once "mediator3.php";

$crosto = "./consoft.css";
$versao = "1.10";
$cempresa = "";
$data_min = "01/01/2000";
$data_max = "31/12/2029";
$sem_canal = 1;
$ldecimais = 2;
global $lSemSsl;
$lSemSsl = 0;
$cpathcommit = "";
//$nSaldoEstoque="if(isnull(l.proloc),0,l.qualoc)";
$nSaldoEstoque = "p.estoque";
//$TEMPO_DE_EXPIRACAO = 60; //MINUTOS PARA EXPIRAR
global $idioma;
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

return;

function StrFile($cfile, $cstr, $lAppend = false) {
    $farq = fopen($cfile, ($lAppend ? "a" : "w"));
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

function padr($cstr, $ntam = 10, $cpad = " ") {
    return str_pad($cstr, $ntam, $cpad, STR_PAD_RIGHT);
}

function padl($cstr, $ntam = 10, $cpad = " ") {
    return str_pad($cstr, $ntam, $cpad, STR_PAD_LEFT);
}

function padc($cstr, $ntam = 10, $cpad = " ") {
    return str_pad($cstr, $ntam, $cpad, STR_PAD_BOTH);
}

function mysql_myquery($cstr) {
    return mysqli_myquery($cstr);
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

function ve_ssl() {
    global $lSemSsl;
//    if (file_exists("mysql.php")){
    require_once("mysql.php");
//    }else{
    require_once("./inc/mysql.php");
//    }
//   $lSemSsl = 0;

    $aip = array();
    /*    $cip = strtok($cSemSsl, " ");
      while ($cip) {
      $aip[] = $cip;
      $cip = strtok(" ");
      }
     */
    $cip = getenv("REMOTE_ADDR");
    for ($i = 0; $i < count($aip); $i++) {
        if (!strncmp($aip[$i], $cip, strlen($aip[$i]))) {
            $lSemSsl = 1;
            break;
        }
    }
    if ($lSemSsl)
        return 1;

//if ( getenv("SSL_SESSION_ID") == "" ){
    if (!isset($_SERVER['HTTPS'])) {
//    $caux=$url="<a href=\"https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."\"> https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."</a>";
//    erro("Esta nao e uma conexao segura.<br><br><b>Incie com https://....".CR.$caux);
        $caux = "parent.location='https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "'";
        die("Esta não é uma conexao segura.<br><br><b>Incie com https://....<br>");
        return 0;
    }
    return 1;
}

function cifra($a, $b = '') {
    $var = "";

    if (empty($b)) {
        $b = 3;
    }

    for ($i = 1; $i <= strlen($a); $i++) {
        $var = $var . chr(255 + $i - (($b * $i) + ord(substr($a, $i - 1, 1))));
    }

    return $var;
}

function data2sql($ddata) {
    $ddata = str_replace("/", "", $ddata);
    $ddata = str_replace("-", "", $ddata);
    $ddata = str_replace(" ", "", $ddata);
    $cdia = substr($ddata, 0, 2);
    $cmes = substr($ddata, 2, 2);
    $cano = substr($ddata, 4, 4);
    if ($cano < 1000) {
        if ($cano < 30) {
            $cano = "20" . $cano;
        } else {
            $cano = "19" . $cano;
        }
    }
    //$aux="'".substr($ddata,6,4)."-".substr($ddata,3,2)."-".substr($ddata,0,2)."'";
    return "'" . $cano . "-" . $cmes . "-" . $cdia . "'";
}

function sql2data($ddata) {
    if ($ddata == '1000-01-01')
        return '';
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

function erro($cmen, $ctitulo, $cexec) {

    require("mysql.php");
    $cmen = id($cmen);
    $ctitulo = id($ctitulo);
    if (!$ctitulo)
        $ctitulo = id("Erro do Sistema");
    echo HtmlCabOn($ctitulo, "", "inicio", "./index.php");
    echo HtmlTableOn(0, 0, 1);

    if ($cImagem)
        abrelinha(HtmlImage($cImagem, 0), "H", "", "", 2);
    abrelinha(id("Erro do Sistema"), "H", "", "", 2);
    abrelinha(SP, "C", "", "", 2);
    abrelinha($cmen, "C", true, "", 2);
    abrelinha(SP, "C", true, "", 2);
    abrelinha();
    abrecel("", "C", "", "", 2);
    if (!$cexec) {
        echo criabotao("submit");
    } else {
        echo CriaBotao("exec", "Ok", $cexec);
    }
    echo HtmlTableOff();
    echo HtmlCabOff();

    return 0;
}

function men($cmen, $clink, $lfecha, $ctitulo, $cbotao) {

    require("mysql.php");

    if (empty($ctitulo))
        $ctitulo = id("Mensagem do sistema");
    echo HtmlCabOn($ctitulo);
    echo HtmlTableOn(0, 0, 1);
    if ($cImagem)
        abrelinha(HtmlImage($cImagem, 0), "H", "", "", 2);
    abrelinha(id("Mensagem do sistema"), "H", "", "", 2);
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

function conecta($server = '', $port = '', $user = '', $password = '', $database = '', &$cerrosql = '') {
    if (!ve_ssl()) {
        return 0;
    }
    if (!$server) {
        require("mysql.php");
        if (file("connect.php")) {
            require("connect.php");
        }
        if (file("inc/connect.php")) {
            require("inc/connect.php");
        }
    }
    $db = mysqli_connect($server, $user, $password, $database, $port);
    if (!$db) {
        $cerrosql = "Error accendendo al servidor SQL <br>" . mysqli_connect_error();
        return 0;
    }
    return $db;
}

function gera_sid($user) {
    global $versao;
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
    $aope = mysqli_linha("OPERADOR_OPE", "CODIGO,NOME,DEGRAU", "CODIGO=$user");
    $caux = "insert php_wc_session set language='" . (PegaVar("lang") == "E" ? 2 : (PegaVar("lang") == "S" ? 1 : 0) ) . "',codigo=" . $user . ",nome='" . $aope[1] . "',nivel=" . cifra($aope[2]) . ",data_in=now(),sid='" . $sid . "',ip='" . getenv("REMOTE_ADDR") . "'";
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
        //ve_sid($sid);
        return $sid;
    }
    if (!$linha || ceil($linha[0]) < ceil($versao * 100)) {
        mysqli_query($db, "insert php_config set data=now(),versao=" . ceil($versao * 100));
        //ve_sid($sid);
        return $sid;
    }
    erro("La version instalada es inferior a la actual.<br>Instalada: " . number_format($linha[0] / 100, 2) . "<br>Utilizada: " . number_format($versao, 2));
//    break;
//return false;
}

function ve_sid(&$sid, $nSenha = 0) {
    global $codven;
    global $nomven;
    global $lojven;
    global $TEMPO_DE_EXPIRACAO;
    global $idioma;
    global $db;

    if (!$db) {
        $db = conecta();
        if (!$db)
            return;
    }

    $sid = pegavar("sid");
    /*
      if (empty($sid)) {
      $sid = $_GET['sid'];
      if (empty($sid))
      $sid = $_POST['sid'];
      }
     */
//    $tempo = $TEMPO_DE_EXPIRACAO * 60;
    $ntempo = veconfig(cfg_tlimite);
    if (!$ntempo)
        $ntempo = 10;
    $tempo = $ntempo * 60;
//$tempo=10*60;

    if (empty($sid)) {
        erro("Identificador de operação não informado");
        exit;
        return 0;
    }

    $caux = "select * from php_wc_session where sid='" . $sid . "'";
    $otable = mysqli_query($db, $caux);

    if (mysqli_num_rows($otable) == 0) {
        erro("Identificador de operação inexistente ou inválido");
        exit;
        return 0;
    }

    $olinha = mysqli_fetch_row($otable);

    /*
      cho $olinha[1].CR;
      echo $olinha[2].CR;
      echo $olinha[3].CR;
      echo $olinha[4].CR;
      echo $olinha[5].CR;
      echo $olinha[6].CR;
     */

//$otabven=mysqli_query($db,"select codven,nomven,lojaweb from VENDEDOR_VEN where codven=".$olinha[0]);

    $otabven = mysqli_query($db, "select codigo,nome,grupo from OPERADOR_OPE WHERE is_deleted='N' and codigo=" . $olinha[0]);
    $olinhaven = mysqli_fetch_row($otabven);
    $codven = $olinhaven[0];
    $nomven = $olinhaven[1];
    $lojven = 1;

    if (!($olinha[4] == '0000-00-00 00:00:00')) {
        fecha_sid($sid);
        erro(id("Sistema acessado em outro local ou Identificador de operação inválido"));
        exit;
        return 0;
    }

    $otable = mysqli_query($db, "select time_to_sec(now())-time_to_sec(data_in) as tempo from php_wc_session where sid='" . $sid . "'");
    $olinha = mysqli_fetch_row($otable);
    if ($olinha[0] > $tempo) {
        fecha_sid($sid);
        erro("Tempo expirado");
        exit;
        return 0;
    }

// VERIFICA OS HORARIOS DE TRABALHO
    if ($codven != 1) {
        $otable = mysqli_query($db, "select left(i_" . date("w") . ",5) as ini,left(f_" . date("w") . ",5) as fim,bloqueado from php_wc_config_operador where usuario=" . $codven);
        $olinha = mysqli_fetch_array($otable);
        if ($olinha["bloqueado"]) {
            fecha_sid($sid);
            erro("Usuário está bloqueado", "Usuário está bloqueado");
            exit;
            return 0;
        }

        if (date("H:i") < $olinha["ini"] || date("H:i") > $olinha["fim"]) {
            fecha_sid($sid);
            erro("Horário de utilização não autorizado", "Tempo expirado");
            exit;
            return 0;
        }
    }

    mysqli_query($db, "update php_wc_session set data_in=now() where sid='" . $sid . "'");
    $otable = mysqli_query($db, "select codigo,nome,nivel,language from php_wc_session where sid='" . $sid . "'");
    $olinha = mysqli_fetch_row($otable);
    $ngrupo = cifra($olinhaven[2]);
    if (VeCfg("GRUPOPE") != "S") {
        $ngrupo = 999999;
    }
    $idioma = $olinha[3];
    if ($nSenha) {
        if (!vesenha($olinha[0], $nSenha)) {
            fecha_sid($sid);
            erro("Tentativa de violação do sistema");
            return 0;
        }
    }

    return array($olinha[0], $olinha[1], $olinha[2], $ngrupo);
}

function fecha_sid($sid) {
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
    return $array_output;
}

function ve_nivel($ausu, $nniv) {
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
    $cmen = id($cmen);
    echo HtmlCabOn(id("Mensagem do Sistema"), $cjava, "", "", "fecha(this)");
    echo HtmlCabOff();
    return;
}

function FirstUpper($cstr, $primeiro) {
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
    $nint = floor($ncod / 10);
    $ndig = $ncod % 10;
    return ($ndig == digito($nint));
}

function html2num($cnum) {
    return str_replace(",", ".", str_replace(".", "", $cnum));
}

function fechacel() {
    echo "</td>";
}

// mostra,posicao(E,D,C,H),negrito,fonte,span colunas,span linhas)
function abrelinha($cstr, $cpos = '', $lneg = false, $ntamfonte = '', $ncolspan = '', $nrowspan = '', $ccor = '', $clarg = '') {
    echo "<tr>";
    if ($cstr || $cpos || $ncolspan || $ncolspan)
        impcel($cstr, $cpos, $lneg, $ntamfonte, $ncolspan, $nrowspan, false, $ccor, $clarg);
}

function fechalinha() {
    echo "</tr>";
}

function abrecel($cstr, $cpos = '', $lneg = false, $ntamfonte = '', $ncolspan = '', $nrowspan = '', $ccor = '', $clarg = '') {
    impcel($cstr, $cpos, $lneg, $ntamfonte, $ncolspan, $nrowspan, true, $ccor, $clarg);
}

function impcel($cstr, $cpos, $lneg, $ntamfonte, $ncolspan, $nrowspan, $lsemfechar, $ccor, $clarg) {
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

function my_number_format($valor, $decimais = 0, $separador_dec = ',', $separador_milhar = '.', $npad = '', $lcomzeros = false) {
    global $ldecimais;
    $decimais = ($decimais || $decimais == '0') ? $decimais : $ldecimais;
    if (!$npad) {
        return my_number_format1($valor, $decimais, $separador_dec, $separador_milhar);
    } else {
        return padl(my_number_format1($valor, $decimais, $separador_dec, $separador_milhar), $npad, ($lcomzeros ? "0" : " "));
    }
}

function my_number_format1($p1, $p2, $p3, $p4) {
// valor,decimais,sep_dec,sep_milhar
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

function CriaCampo($cnome, $ctipo = 'text_entry', $ntam = 10, $default = '', $ctexto = '', $nlinha = 1, $lsemid = false, $cOnBlur = '') {
// date time month_year cep password percent numeric integer
// float money text upper_case full_name none text_entry
    if ($ctipo == "livre") {
        if (empty($nlinha))
            $nlinha = 1;
        if (empty($ntam))
            $ntam = 10;
        $cstr = "<textarea ";
        if (!$lsemid) {
            $cstr .= "id=\"$cnome\" ";
        }
        $cstr .= "name=\"$cnome\" cols=\"$ntam\" rows=\"$nlinha\" >" . $default . "</textarea>";

        if (!empty($ctexto))
            $cstr = $ctexto . $cstr;
        return $cstr;
    }
    $cstr = "<input type=\"text\" ";
    if (!$lsemid) {
        $cstr .= "id=\"[[0]]\" ";
    }
    if ($cOnBlur)
        $cOnBlur = ";$cOnBlur";
    $cstr .= "name=\"[[0]]\" size=\"[[4]]\" maxlength=\"[[1]]\" value=\"[[2]]\"  onkeypress=\"return(validaConteudo(event,this,'[[3]]'))\" onFocus=\"removeCaracs(this,'[[3]]')\" onblur=\"formatCamp(this,'[[3]]')$cOnBlur \" onkeyup=\"saltaCampo(event,this,'[[3]]',[[1]] )\" > ";
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
    } elseif ($areplace[3] == 'time') {
        if (!$areplace[2])
            $areplace[2] = date('H:i');
        $areplace[1] = 5;
    } elseif ($areplace[3] == 'month_year') {
        if (!$areplace[2])
            $areplace[2] = date('m/Y');
        $areplace[1] = 7;
    } elseif ($areplace[3] == 'cep') {
        if (!$areplace[2])
            $areplace[2] = "";
        $areplace[1] = 9;
    } elseif ($areplace[3] == 'password') {
        $areplace[3] = "none";
        $cstr = str_replace("\"text\"", "\"password\"", $cstr);
        if (!$areplace[2])
            $areplace[2] = "";
    } elseif ($areplace[3] == 'percent') {
        if (!$areplace[2])
            $areplace[2] = "0";
        $areplace[1] = 9;
    } elseif ($areplace[3] == 'numeric' || $areplace[3] == 'integer' || $areplace[3] == 'float' || $areplace[3] == 'money') {
        if (!$areplace[2])
            $areplace[2] = "0";
    } elseif ($areplace[3] == 'text' || $areplace[3] == 'textnumber' || $areplace[3] == 'uppercase' ||
            $areplace[3] == 'full_name' || $areplace[3] == 'none') {
        if (!$areplace[2])
            $areplace[2] = "";
    } elseif ($areplace[3] == 'cpf') {
        if (!$areplace[2])
            $areplace[2] = "";
    } elseif ($areplace[3] == 'cnpj') {
        if (!$areplace[2])
            $areplace[2] = "";
    } elseif ($areplace[3] == 'email') {
        if (!$areplace[2])
            $areplace[2] = "";
    } else {
        $areplace[3] = "text_entry";
        if (!$areplace[2])
            $areplace[2] = "";
    }


    $areplace[4] = $areplace[1];

    if ($areplace[3] == 'float' || $areplace[3] == 'money')
        $areplace[4] += 3;

    for ($i = 0; $i < count($areplace); $i++) {
        $cstr = str_replace("[[" . $i . "]]", $areplace[$i], $cstr);
    }
    if ($ctexto)
        $cstr = $ctexto . $cstr;
    return $cstr;
}

function CriaBotao($cnome, $cescrita = '', $cexec = '', $ctexto = '', $cclass = '') {
    if ($cnome == "submit") {
        $ctipo = "submit";
        $cnome = "enviar";
    } elseif ($cnome == "reset") {
        $ctipo = "reset";
        $cnome = "limpar";
    } else {
        $ctipo = "button";
    }
    if (!$cescrita)
        $cescrita = "Ok";
    $caux = "<input type=\"" . $ctipo . "\" id=\"$cnome\" name=\"" . $cnome . "\" value=\"" . $cescrita . "\" " . ($cclass ? " class=\"$cclass\"" : "");
    if ($cexec)
        $caux = $caux . " onclick=\"" . $cexec . "\" ";
    if ($ctexto)
        $caux = $ctexto . $caux;

    $caux = $caux . ">";
    return $caux;
}

//criaselect("teste",array("Portuques,P,1","Espanhol,E,0","English,E,0"),1)
function CriaSelect($cnome, $aselect, $nlinha) {
    MyDefault($nlinha, 1);

    $caux = "<select size=\"" . $nlinha . "\" ";
    if ($nlinha > 1)
        $caux .= " multiple ";
    $caux .= " id=\"$cnome\"  name=\"" . $cnome . "\" >";

    for ($i = 0; $i < count($aselect); $i++) {
        $caux .= "<option ";
        $aaux = explode(",", $aselect[$i]);
        if ($aaux[2])
            $caux .= " selected ";
        $caux .= " value=\"" . $aaux[1] . "\">" . $aaux[0] . "</option>";
    }
    $caux = $caux . "</select>";

    return $caux;
}

function CriaCombo($cnome, $ctodos = '', $ctabela, $ccampos, $cretorno = '', $cwhere = '', $cordem = '', $nlinha = 1, $aselect = array(), $cOnBlur = '', $lComOptiopnId = false) {
    $caux = "select concat_ws('-'," . $ccampos . ") as nome," . $cretorno . " as codigo  from " . $ctabela . " where is_deleted='N' ";
    MyDefault($nlinha, 1);
    if ($cwhere)
        $caux = $caux . " and " . $cwhere;
    if ($cordem)
        $caux = $caux . " order by " . $cordem;

    $otable = mysqli_query($db, $caux);
//echo $caux;
    $caux = "<select size=\"" . $nlinha . "\" ";
    if ($nlinha > 1)
        $caux .= " multiple ";
    $caux .= " id=\"$cnome\"  name=\"" . $cnome . "\"";
    if ($cOnBlur) {
        $caux .= " onBlur=\"$cOnBlur\"";
    }
    $caux .= ">";
    if ($ctodos) {
        $caux .= "<option ";
        if (!$aselect && mysqli_num_rows($otable) != 1)
            $caux .= "selected ";
        $caux .= "value=\"" . strtok($ctodos, ",") . "\">" . strtok(',') . "</option>";
    }

    while ($linha = mysqli_fetch_row($otable)) {
        $caux .= "<option ";
        if ($lComOptiopnId)
            $caux .= " id=\"" . $cnome . "_" . $linha[1] . "\"";
        for ($i = 0; $i < count($aselect); $i++) {
            if ($linha[1] == $aselect[$i])
                $caux .= " selected ";
        }
        if (mysqli_num_rows($otable) == 1)
            $caux .= " selected ";
        $caux .= " value=\"" . $linha[1] . "\">" . $linha[0] . "</option>";
    }
    $caux = $caux . "</select>";

    return $caux;
}

// criaradio("teste",array("a,opcao 1,1","b,opcao 2,0),true,"alert('ok'))                      
function CriaRadio($cnome, $aop, $lcomsp, $conclick) {
    $caux = "";
    for ($i = 0; $i < count($aop); $i++) {
        if ($lcomsp)
            $caux = $caux . SP;
        $caux = $caux . "<input type=\"radio\" name=\"" . $cnome . "\" id=\"" . $cnome . "_$i" . "\" value=\"" . strtok($aop[$i], ",") . "\"";
        $cstr = strtok(",");
        if (strtok(","))
            $caux = $caux . " checked ";
        if ($conclick)
            $caux .= " onclick=\"" . $conclick . "\" ";

        $caux = $caux . ">" . $cstr;
    }
    return $caux;
}

function CriaCheck($cnome, $lcheck, $cstr, $conclick) {
    $caux = "<input type=\"checkbox\" name=\"" . $cnome . "\" id=\"$cnome\"";
    if ($lcheck)
        $caux .= "checked ";
    if ($conclick)
        $caux .= " onclick=\"" . $conclick . "\" ";
    $caux .= "> ";
    if ($cstr)
        $caux .= $cstr;
    return $caux;
}

function ReCriaHidden($cnome) {
    return CriaHidden($cnome, PegaVar($cnome));
}

function AReCriaHidden($anome) {
    $caux = "";
    for ($i = 0; $i < count($anome); $i++) {
        $caux .= CriaHidden($anome[$i], PegaVar($anome[$i]));
    }
    return $caux;
}

function CriaHidden($cnome, $uval, $lsemid = false) {
    return $caux = "<input type=\"hidden\" " . (!$lsemid ? "id=\"$cnome\" " : "") . " name=\"" . $cnome . "\" value=\"" . $uval . "\" >";
}

function HtmlCabOn($ctitulo = '', $cjava = '', $cform = '', $cform_action = '', $conload = '', $consubmit = '', $chead = '') {
    global $crosto;

    echo mobilecabecalho();
    $cstr = '';
    $cstr .= "<html><head>
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
            $cstr .= "|";
        } else {
            $cstr .= "<script language=\"JavaScript\">" . $cjava . "</script>";
        }
    }
    $cstr .= "</head>
<body";

    if ($conload)
        $cstr .= " onload=\"" . $conload . "\" ";
    $cstr .= "><div id=\"popsen\"></div>";

    if ($cform) {
        $cstr .= "<form method=\"post\" name=\"" . $cform . "\" ";
        if ($cform_action)
            $cstr .= "  action=\"" . $cform_action . "\" ";
        if ($consubmit)
            $cstr .= " onSubmit=\"" . $consubmit . "\"; ";
        $cstr .= ">";
    }
    $cstr .= "
<center>
";
    return $cstr;
}

function MyDefault(&$uvar, $uret) {
    if (!($uvar))
        $uvar = $uret;
    return $uvar;
}

function HtmlImage($cimagem, $nborder = 0, $nwidth = "", $nheight = "") {
    global $cImagemLink;
    global $cLogoPadrao;
    MyDefault($nborder, 0);
    $caux = "";
    if ($cLogoPadrao) {
        $caux .= "<img src=\"$cLogoPadrao\" border=0>";
    }
    if ($cImagemLink)
        $caux .= "<a href=\"$cImagemLink\">";
    $caux .= "<img border=\"" . $nborder . "\" src=\"" . $cimagem . "\" ";
    if ($nwidth && $nheight)
        $caux .= " width=\"" . $nwidth . "\" height=\"" . $nheight . "\" ";
    $caux .= ">
  ";
    if ($cImagemLink)
        $caux .= "</a>";
    return $caux;
}

function HtmlTableOff() {
    return "</table>";
}

function HtmlCabOff($cjava = '', $chtml = '') {
    $caux = "</center></form></body>";
    if ($chtml)
        $caux .= $chtml;
    if ($cjava)
        $caux .= "<script language=\"JavaScript\" >" . $cjava . "</script>";
    $caux .= "</html>";
    return $caux;
}

function HtmlTableOn($nborder = 0, $npad = 0, $nspace = 0, $cwidth = "100%", $cheight = '') {

    MyDefault($nborder, 0);
    MyDefault($npad, 0);
    MyDefault($nspace, 0);
    MyDefault($cwidth, "100%");
    if ($nborder > 998)
        return "</table>
	";

    $caux = "<table border=\"" . $nborder . "\" cellpadding=\"" . $npad . "\" cellspacing=\"" . $nspace . "\" width=\"" . $cwidth . "\" ";
    if ($cheight)
        $caux .= " height=\"$cheight\"";
    $caux .= ">";
    return $caux;
}

function mysql_linha($ctable, $ccampos, $cwhere = '', $lSemIsDeleted = false, $dbc) {
    return mysqli_linha($ctable, $ccampos, $cwhere = '', $lSemIsDeleted = false, $dbc);
}

function mysqli_linha($ctable, $ccampos, $cwhere = '', $lSemIsDeleted = false, $dbc = '') {
    global $db;
    $caux = "select " . $ccampos . " from " . $ctable . " where 1 ";
    $olinha = array();
    if (!$lSemIsDeleted)
        " and is_deleted='N' ";
    if ($cwhere)
        $caux .= " and " . $cwhere;
    $otable = mysqli_query($dbc ? $dbc : $db, $caux);
    if ($otable)
        $olinha = mysqli_fetch_array($otable);
    return $olinha;
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

function hora_sql() {
    return date("H:i:s", time());
}

function hoje_html() {
    return date("d/m/Y", time());
}

function PegaVar($cvar, $cmodo = '') {
    $cmodo = strtoupper($cmodo);
    if ($cmodo == "P") {
        foreach ($_POST as $key => $value) {
            if ($key == $cvar)
                return $value;
        }
    } else {
        if (isset($_REQUEST[$cvar])) {
            return $_REQUEST[$cvar];
        }
    }

    /*
      if (!($cmodo == "G")) {
      foreach ($_POST as $key => $value) {
      if ($key == $cvar)
      return $value;
      }
      }
      if (!($cmodo == "P")) {
      foreach ($_GET as $key => $value) {
      if ($key == $cvar)
      return $value;
      }
      }
     * 
     */
    return '';
    /*
      $cret = $_GET[$cvar];

      if (empty($cret))
      $cret = $_POST[$cvar];
      return $cret;
     */
}

function PegaCookie($cvar) {
    foreach ($_COOKIE as $key => $value) {
        if ($key == $cvar)
            return $value;
    }
    return '';
}

//pegavarmulti("id%" )
//pegavarmulti("%id" )
function PegaVarMulti($cvar) {
    $avariaveis = array();
    $aaux = array_keys($_GET);
    for ($i = 0; $i < count($aaux); $i++) {
        if (substr($cvar, -1) == "%") {
            $caux = substr($cvar, 0, -1);
            if ($caux == substr($aaux[$i], 0, strlen($caux))) {
                $avariaveis[] = array($aaux[$i], $_GET[$aaux[$i]]);
            }
        } elseif (substr($cvar, 0, 1) == "%") {
            $caux = substr($cvar, 1);
            if ($caux == substr($aaux[$i], strlen($caux) * -1)) {
                $avariaveis[] = array($aaux[$i], $_GET[$aaux[$i]]);
            }
        }
    }
    $aaux = array_keys($_POST);
    for ($i = 0; $i < count($aaux); $i++) {
        if (substr($cvar, -1) == "%") {
            $caux = substr($cvar, 0, -1);
            if ($caux == substr($aaux[$i], 0, strlen($caux))) {
                $avariaveis[] = array($aaux[$i], $_POST[$aaux[$i]]);
            }
        } elseif (substr($cvar, 0, 1) == "%") {
            $caux = substr($cvar, 1);
            if ($caux == substr($aaux[$i], strlen($caux) * -1)) {
                $avariaveis[] = array($aaux[$i], $_POST[$aaux[$i]]);
            }
        }
    }

    return $avariaveis;
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
    if ($lupdate) {
        $cstr = "update ";
    } else {
        $cstr = "insert ";
    }
    $cstr .= $ctabela . " set ";
    for ($i = 0; $i < count($acampos); $i++) {
        if ($i > 0)
            $cstr .= ",";
        if (count($acampos[$i]) > 2 && $acampos[$i][2]) {
            $cstr .= $acampos[$i][0] . "=" . $acampos[$i][1];
        } else {
            $cstr .= $acampos[$i][0] . "='";
//	    $cstr.=$acampos[$i][1]."'";
            $cstr .= str_replace("'", "\\'", $acampos[$i][1]) . "'";
        }
    }
    if ($cwhere) {
        $cstr .= " where " . $cwhere;
    }
    mysqli_myquery($cstr);
//    men($cstr);
    $nid = mysqli_fetch_row(mysqli_query($db, "select last_insert_id()"));
    return $nid[0];
}

function bom_html() {
    return date('d/m/y', mktime(0, 0, 0, '01', '01', date('Y')));
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
        $cano += 1900;
    if ($cano < 100 && $cano <= 20)
        $cano += 2000;
    $aRetMDY = array($cmes, $cdia, $cano);
    $cdia = mktime(0, 0, 0, $cmes, $cdia, $cano);
    return $cdia;
}

function str2data($ddata) {
    $cdia = date('d/m/Y', str2date($ddata));
    return $cdia;
}

function somadata($ddata, $ndias, $ctipo = '') {
    $adata = array();
    str2date($ddata, $adata);
    if (strtoupper($ctipo) == "Y") {
        $adata[2] += $ndias;
    } elseif (strtoupper($ctipo) == "M") {
        $adata[0] += $ndias;
    } else {
        $adata[1] += $ndias;
    }
    return date("d/m/Y", mktime(0, 0, 0, $adata[0], $adata[1], $adata[2]));
}

function flink($clink, $sid, $avar, $lparent) {
    if (!$clink)
        return (!$lparent) ? "</a>" : "";

    $caux = (!$lparent) ? "<a href='$clink" : "parent.location='$clink";
    $caux .= "?sid=$sid";

    for ($i = 0; $i < count($avar); $i++) {
        $caux .= "&" . $avar[$i][0] . "=" . urlencode($avar[$i][1]);
    }
    $caux .= (!$lparent) ? "'>" : "'";
    return $caux;
}

function EncBrowser($nnum, $ldecifra) {
    $caux = "~A!B@C#D$E%F^G&H*I(J_K+L-M=N[O]P{Q}R<S>T?U,V.W/X|Y";
    $cret = "";
    if (!$ldecifra) {
        for ($i = 0; $i < strlen($nnum); $i++) {
            $naux = substr($nnum, $i, 1);
            $cret .= substr($caux, $i + $naux, 1);
        }
    } else {
        for ($i = 0; $i < strlen($nnum); $i++) {
            $naux = substr($nnum, $i, 1);
            $npos = strpos($caux, $naux);
            $cret .= ($npos - $i);
        }
    }

    return $cret;
}

function vesenha($nusuario, $ncodigo) {
    if ($nusuario == 1) {
        return true;
    }
    $otable = mysqli_query($db, "select habilitado from php_wc_acessos_operador where usuario=$nusuario and codigo=$ncodigo");
    $olinha = mysqli_fetch_row($otable);
    return $olinha[0];
}

/*
  function AtualizaIndice($carquivo, $nrecno) {
  $afuncoes = array();
  $afuncoes[] = "drop function if exists ctod";
  $afuncoes[] = "create function ctod(cstr varchar(256))
  returns varchar(100)

  begin
  declare dia char(2);
  declare mes char(2);
  declare ano varchar(4);

  set dia=substring_index(cstr,'/',1);
  set mes=substring_index(cstr,'/',-2);
  set mes=substring_index(mes,'/',1);
  set ano=substring_index(cstr,'/',-1);

  set dia=lpad(dia,2,'0');
  set mes=lpad(mes,2,'0');
  if length(ano) = 2 then
  set ano=concat('20',ano);
  end if;
  set ano=lpad(ano,4,'0');

  return concat(ano,'-',mes,'-',dia);
  end
  ";
  $afuncoes[] = "drop function if exists dtoc";
  $afuncoes[] = "create function dtoc(cstr varchar(256))
  returns varchar(100)

  begin
  return concat(substr(cstr,9,2),'/',substr(cstr,6,2),'/',substr(cstr,3,2));
  end
  ";
  $afuncoes[] = "drop function if exists alltrim";
  $afuncoes[] = "create function alltrim(cstr varchar(256))
  returns varchar(100)

  begin
  return trim(cstr);
  end
  ";
  $afuncoes[] = "drop function if exists len";
  $afuncoes[] = "create function len(cstr varchar(256))
  returns varchar(100)

  begin
  return length(cstr);
  end
  ";
  $afuncoes[] = "drop function if exists str";
  $afuncoes[] = "create function str(numero DECIMAL(15,2),tam INT)
  returns varchar(100)

  begin
  return lpad(round(numero,0),tam,' ');
  end
  ";
  $afuncoes[] = "drop function if exists strzero";
  $afuncoes[] = "create function strzero(numero DECIMAL(15,2),tam INT)
  returns varchar(100)

  begin
  return lpad(round(numero,0),tam,'0');
  end
  ";
  $afuncoes[] = "drop function if exists dtos";
  $afuncoes[] = "create function dtos(ddata CHAR(10))
  returns varchar(30)

  begin
  return if(ddata='1000-01-01','        ',replace(ddata,'-',''));
  return 'lpad(\''+if(ddata='1000-01-01','        ',replace(ddata,'-',''))+'\',8,\'\')';
  end
  ";
  $afuncoes[] = "drop function if exists val";
  $afuncoes[] = "create function val(cstr VARCHAR(20))
  returns DECIMAL(12,2)

  begin
  return cstr+0;
  end
  ";
  $afuncoes[] = "drop function if exists descend";
  $afuncoes[] = "create function descend(cstr VARCHAR(20))
  returns varchar(20)

  begin
  declare i int default 0;
  declare cret varchar(20) default '';

  while (i<length(cstr)) DO
  set cret = concat(cret,char(256-ascii(substr(cstr,i+1,1))));
  set i = i+1;
  end while;
  return cret;
  end
  ";
  for ($i = 0; $i < count($afuncoes); $i++) {
  mysqli_query($db,$afuncoes[$i]);
  }

  $carquivo = str_replace(".", "_", $carquivo);
  $carquivo = strtoupper($carquivo);
  $otable = mysqli_query($db,"show tables like 'CLP_TAB_COLS'");
  if (mysqli_num_rows($otable) == 0)
  return;
  $otable = mysqli_query($db,"select col_name as nome,ind_clp_exp as indice from CLP_TAB_COLS where ind_clp_exp!='' and col_name like 'IE\$%' and  tab_name='$carquivo'");
  while ($olinha = mysqli_fetch_array($otable)) {
  $caux = str_replace('+', ',', $olinha["indice"]);
  $caux = str_replace('"', "'", $caux);
  $caux = str_replace('==', "=", $caux);
  $cstr = "update $carquivo set " . $olinha["nome"] . "=concat(" . $caux . ") where recno=$nrecno";
  //        echo $cstr.CR;
  mysqli_query($db,$cstr);
  }
  }

  function GravaSqlMedMemo($ctabela, $acampos, $arecno) {
  $ctabela = str_replace(".", "_", strtoupper($ctabela));

  for ($j = 0; $j < count($arecno); $j++) {
  $nrecno = $arecno[$j];
  for ($i = 0; $i < count($acampos); $i++) {
  if (strlen($acampos[$i][1]) == 0) {
  mysqli_query($db,"delete from " . $ctabela . "_MEMO where recno=$nrecno and col_name='" . strtoupper($acampos[$i][0]) . "'");
  mysqli_query($db,"update $ctabela set " . $acampos[$i][0] . "=0 where recno=$nrecno");
  } else {
  mysqli_query($db,"replace " . $ctabela . "_MEMO set recno=$nrecno,col_name='" . strtoupper($acampos[$i][0]) . "',memo='" . addslashes($acampos[$i][1]) . "'");
  mysqli_query($db,"update $ctabela set " . $acampos[$i][0] . "=" . strlen($acampos[$i][1]) . " where recno=$nrecno");
  }
  }
  }
  }

  function GravaSqlMed($ctabela, $acampos, $lupdate = false, $cwhere = '', $ltodosrecno = false) {
  $amemo = array();
  $ctabela = strtoupper($ctabela);
  $ctabela = str_replace(".", "_", $ctabela);
  if (substr($ctabela, -4) == "_DBF")
  $ctabela = substr($ctabela, 0, -4);

  if ($lupdate) {
  $cstr = "update ";
  } else {
  $cstr = "insert ";
  }
  $cstr.=$ctabela . " set ";
  for ($i = 0; $i < count($acampos); $i++) {
  if (count($acampos[$i]) > 3 && $acampos[$i][3]) {
  $amemo[] = array($acampos[$i][0], $acampos[$i][1]);
  continue;
  }
  if ($i > 0)
  $cstr.=",";
  if (count($acampos[$i]) > 2 && $acampos[$i][2]) {
  $cstr.=$acampos[$i][0] . "=" . $acampos[$i][1];
  } else {
  $cstr.=$acampos[$i][0] . "='";
  //	    $cstr.=$acampos[$i][1]."'";
  $cstr.=addslashes($acampos[$i][1]) . "'";
  }
  }
  $arecno = array();
  if (!$lupdate) {
  $otable = mysqli_query($db,"select max(recno) from $ctabela");
  $olinha = mysqli_fetch_row($otable);
  $cstr.=",recno=" . ($olinha[0] + 1);
  $arecno[] = ($olinha[0] + 1);
  }
  if ($cwhere) {
  $cstr.=" where " . $cwhere;
  $otable = mysqli_query($db,"select recno from $ctabela where $cwhere");
  while ($olinha = mysqli_fetch_row($otable)) {
  $arecno[] = $olinha[0];
  }
  }
  //echo $cstr.CR.CR;
  mysqli_myquery($cstr);
  for ($i = 0; $i < count($arecno); $i++) {
  AtualizaIndice($ctabela, $arecno[$i]);
  }
  //    men($cstr);
  //$arecno=mysqli_fetch_row(mysqli_query($db,"select last_insert_id()"));
  if (count($amemo) > 0)
  GravaSqlMedMemo($ctabela, $amemo, $arecno);
  if (!$ltodosrecno) {
  return $arecno[0];
  } else {
  return $arecno;
  }
  }

  function VeMedEstru($ctable) {
  $otable = mysqli_query($db,"DESC $ctable");
  $aret = array();
  while ($olinha = mysqli_fetch_row($otable)) {
  if ($olinha[0] == "RECNO" || $olinha[0] == "IS_DELETED" || substr($olinha[0], 0, 3) == "IE\$") {
  continue;
  }
  $aret[] = $olinha[0];
  }
  return $aret;
  }

  function token($cstr, $ctoken, $npos) {
  if (!$cstr)
  return "";
  if (!$ctoken)
  $ctoken = " ";
  if (!$npos)
  $npos = 1;

  $aaux = split($ctoken, $cstr);
  return $aaux[$npos - 1];
  }

  function numtoken($cstr, $ctoken) {
  if (!$cstr)
  return "";
  if (!$ctoken)
  $ctoken = " ";
  $aaux = split($ctoken, $cstr);
  return count($aaux);
  }

  function token2array($cstr, $ctoken) {
  $cstr=  trim($cstr);
  if (!$cstr)
  return array();
  if (!$ctoken)
  $ctoken = " ";
  $aaux = split($ctoken, $cstr);
  $aret = array();
  for ($i = 0; $i < count($aaux); $i++) {
  if (!($aaux[$i] == "")) {
  $aret[] = $aaux[$i];
  }
  }
  return $aret;
  }

  function array2token($astr, $ctoken) {
  if (!$astr)
  return "";
  if (!$ctoken)
  $ctoken = " ";
  $caux = "";
  $lprimeiro = true;
  for ($i = 0; $i < count($astr); $i++) {
  if (!($astr[$i] == "")) {
  if (!$lprimeiro) {
  $caux.="-";
  } else {
  $lprimeiro = false;
  }
  $caux.=$astr[$i];
  }
  }
  return $caux;
  }

  function VeCfg($cCampo, $nTela = '') {
  $olinha = mysqli_linha("CONFIG" . $nTela, $cCampo, "RECNO=1");
  return $olinha[0];
  }

  function VePar($cCampo) {
  $olinha = mysqli_linha("PARAMETR.CFG", $cCampo, "RECNO=1");
  return $olinha[0];
  }

  function VeImp($nTerminal, $cCampo) {
  $olinha = mysqli_linha("CONFIMP_IMP", $cCampo, "terminal=$nTerminal");
  return $olinha[0];
  }

  function VeCambio($nmoeda) {
  if (!$nmoeda)
  return 1;
  if ($lgerente) {
  $otable = mysqli_query($db,"select _$nmoeda from CAMB_CAM where is_deleted='N' order by data desc limit 1");
  } else {
  $otable = mysqli_query($db,"select MOEDA$nmoeda from CAMBIO_CAM where is_deleted='N' order by datcam desc limit 1");
  }
  $olinha = mysqli_fetch_row($otable);
  if (!$olinha) {
  return 1;
  }
  return $olinha[0];
  }


  function SendMail($cTo, $cFrom = '', $cMessage = '', $cSubject = '', $cToName = '', $cFromName = '', $cAttachment = '') {


  $mail_server = veconfig(cfg_mserver);
  $mail_ssl = (veconfig(cfg_mssl) ? "ssl" : "");
  $mail_user = veconfig(cfg_muser);
  $mail_password = veconfig(cfg_mpassword);
  $mail_port = veconfig(cfg_mport);
  $mail_zone = veconfig(cfg_mzone);
  $mail_from = veconfig(cfg_mfrom);
  $mail_name = veconfig(cfg_mname);

  if (!$cFrom)
  $cFrom = $mail_from;
  if (!$cFromName)
  $cFromName = $mail_name;
  if (!$cFromName)
  $cFromName = $cFrom;
  if (!$cToName)
  $cToName = $cTo;

  //error_reporting(E_ALL);
  //    error_reporting(E_STRICT);

  date_default_timezone_set($mail_zone);

  include("mailer/phpmailer/class.phpmailer.php");
  //include("class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded

  $mail = new PHPMailer();
  $mail->IsSMTP();
  $mail->CharSet = "utf-8";
  $mail->SMTPAuth = true;                  // enable SMTP authentication
  $mail->SMTPSecure = $mail_ssl;                 // sets the prefix to the servier
  $mail->Host = $mail_server;      // sets GMAIL as the SMTP server
  $mail->Port = $mail_port;                   // set the SMTP port for the GMAIL server

  $mail->Username = $mail_user;  // GMAIL username
  $mail->Password = $mail_password;            // GMAIL password

  $mail->AddReplyTo($cFrom, $cFromName);

  $mail->From = $cFrom;
  $mail->FromName = $cFromName;

  $mail->Subject = $cSubject;

  //$mail->Body       = "Hi,<br>This is the HTML BODY<br>";                      //HTML Body
  //$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
  //$mail->WordWrap   = 50; // set word wrap

  $mail->MsgHTML($cMessage);

  $aaux = explode(";", $cTo);
  for ($i = 0; $i < count($aaux); $i++) {
  if ($aaux[$i]) {
  $aaux1 = explode("<", $aaux[$i]);
  if (count($aaux1) > 1 && $aaux1[1]) {
  $cToName = str_replace('"', "", $aaux1[0]);
  $cTo = str_replace("<", "", str_replace(">", "", $aaux1[1]));
  } else {
  $cTo = $aaux1[0];
  $cToName = $aaux1[0];
  }
  $mail->AddAddress($cTo, $cToName);
  }
  }
  //    $mail->AddAddress($cTo, $cToName);
  if ($cAttachment)
  $mail->AddAttachment($cAttachment);             // attachment

  $mail->IsHTML(true); // send as HTML
  return $mail->Send();
  }

  function isbin($nNumero, $nPos) {
  $caux = "00000000000000000000" . decbin($nNumero);
  return substr($caux, $nPos * (-1), 1);
  }

  function PoeDigitoSql($cCampo, $cRetorno, $lSemAs = 0) {
  if (!$cRetorno)
  $cRetorno = "digito";

  return " " . $cCampo . "*10+Right(concat(@nDigitoSql:= " . $cCampo . " ,@nDigitoSql:=Right(concat('00000000000000',@nDigitoSql),12),(10-((3*(substring(@nDigitoSql,2,1)+substring(@nDigitoSql,4,1)+substring(@nDigitoSql,6,1)+substring(@nDigitoSql,8,1)+substring(@nDigitoSql,10,1)+substring(@nDigitoSql,12,1))+substring(@nDigitoSql,1,1)+substring(@nDigitoSql,3,1)+substring(@nDigitoSql,5,1)+substring(@nDigitoSql,7,1)+substring(@nDigitoSql,9,1)+substring(@nDigitoSql,11,1)) % 10 ) ) %10),1) " . ($lSemAs ? "" : "AS " . $cRetorno . " ");
  }

  function transform($val, $mask,$ntam=0) {
  if (!$ntam)
  $ntam=  strlen ($val);
  $val=  padl($val,$ntam);
  $maskared = '';
  $k = 0;
  for ($i = 0; $i <= strlen($mask) - 1; $i++) {
  if ($mask[$i] == '#' || $mask[$i] == '9' ) {
  if (isset($val[$k]))
  $maskared .= $val[$k++];
  }
  else {
  if (isset($mask[$i]))
  $maskared .= $mask[$i];
  }
  }
  return $maskared;
  }
 */

function Converter($cstr) {
    return iconv("cp850", "utf8", $cstr);
}

function Date2Unix($cData = '', $cHora = '', $lsql = false, $lutc = false) {
    global $ctimezone;
    $caux = preg_replace('/[0-9]/i', '', $cData);
    if ($caux == '-- ::') { //a data é sql format
        $lsql = true;
        $cData = substr($cData, 0, 10);
        $cHora = substr($cData, 11, 8);
    } else if ($caux == "--") {
        $lsql = true;
        $cData = substr($cData, 0, 10);
    }
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
        $nhora += $timeOffset;
    }




    return $nhora;
}

function Unix2Date($nHora, $lsql = false, $lutc = false) {
    global $ctimezone;
    if (!$lutc) {
        $dateTimeZoneUTC = new DateTimeZone("UTC");
        $dateTimeZoneLOCAL = new DateTimeZone($ctimezone);

        $dateTimeUTC = new DateTime("now", $dateTimeZoneUTC);
        $dateTimeLOCAL = new DateTime("now", $dateTimeZoneLOCAL);

        $timeOffset = $dateTimeZoneLOCAL->getOffset($dateTimeUTC);
        $nHora -= $timeOffset;
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
    $cret .= ":";
    $cret .= str_pad($nminutos, 2, "0", STR_PAD_LEFT);
    $cret .= ":";
    $cret .= str_pad($nsegundos, 2, "0", STR_PAD_LEFT);
    return $cret;
}

function LimpaChar($cstr) {
    $cret = "";
    $cstr = strtoupper($cstr);
    $areplace = array();
    $areplace[] = array("ñ", "N");
    $areplace[] = array("Ñ", "N");
    $areplace[] = array("Á", "A");
    $areplace[] = array("Ã", "A");
    $areplace[] = array("Â", "A");
    $areplace[] = array("Ä", "A");
    $areplace[] = array("À", "A");
    $areplace[] = array("á", "A");
    $areplace[] = array("ã", "A");
    $areplace[] = array("â", "A");
    $areplace[] = array("ä", "A");
    $areplace[] = array("à", "A");
    $areplace[] = array("É", "E");
    $areplace[] = array("È", "E");
    $areplace[] = array("Ê", "E");
    $areplace[] = array("Ë", "E");
    $areplace[] = array("é", "E");
    $areplace[] = array("è", "E");
    $areplace[] = array("ê", "E");
    $areplace[] = array("ë", "E");
    $areplace[] = array("Í", "I");
    $areplace[] = array("Ì", "I");
    $areplace[] = array("Î", "I");
    $areplace[] = array("Ï", "I");
    $areplace[] = array("í", "I");
    $areplace[] = array("ì", "I");
    $areplace[] = array("î", "I");
    $areplace[] = array("ï", "I");
    $areplace[] = array("Õ", "O");
    $areplace[] = array("Ó", "O");
    $areplace[] = array("Ò", "O");
    $areplace[] = array("Ô", "O");
    $areplace[] = array("Ö", "O");
    $areplace[] = array("õ", "O");
    $areplace[] = array("ó", "O");
    $areplace[] = array("ò", "O");
    $areplace[] = array("ô", "O");
    $areplace[] = array("ö", "O");
    $areplace[] = array("Ú", "U");
    $areplace[] = array("Ù", "U");
    $areplace[] = array("Û", "U");
    $areplace[] = array("Ü", "U");
    $areplace[] = array("ú", "U");
    $areplace[] = array("ù", "U");
    $areplace[] = array("û", "U");
    $areplace[] = array("ü", "U");
    $areplace[] = array("Ý", "Y");
    $areplace[] = array("ý", "Y");
    $areplace[] = array("Ç", "C");
    $areplace[] = array("ç", "C");
    $areplace[] = array("%", "/");

    foreach ($areplace as $value) {
        $cstr = str_replace($value[0], $value[1], $cstr);
    }
    $cstr = strtoupper($cstr);
    $cret = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $cstr);
    return $cret;
}

function assql($uvar, $ctipo = '') {
    if (strtoupper($ctipo) == 'D') {
        return "'" . date("Y-m-d", $uvar) . "'";
    } elseif (strtoupper($ctipo) == 'N') {
        return $uvar;
    } elseif (strtoupper($ctipo) == 'L') {
        return iff($uvar, 1, 0);
    } else {
        return "'" . addslashes($uvar) . "'";
    }
}

function ValidaMail($email) {
    return preg_match('/^[A-z0-9._%+-]+@[A-z0-9.-]+\.[A-z]{2,}$/', $email);
}

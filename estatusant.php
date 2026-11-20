<?php
//A-preven
//B-preco3 
//C-premin
ini_set('display_errors', 0);
error_reporting(E_ALL | E_STRICT);
set_time_limit(10 * 60);
/*
  1 Consulta de produtos
  2 Detalhes de Produto
  3 Imagem vinculada a produto
  4 Consulta de produtos por página
  6 Informar Voucher
  10 Versão da API
  11 Consulta de saldo e preço
  12 Enviar pedido
  13 Consultar pedido
  15 Faturar pedido
  16 Deletar pedido não faturado
  31 Consultar Pedidos não faturados
  71 Moedas e Câmbios
  81 Inserir Cliente para venda
  82 Alterar Cliente para venda
  83 Consultar Cliente para venda
  84 Incluir Cedulas
  85 Visualizar Cedulas
  86 Consultar Series
  87 Incluir cliente para factura
  88 Alterar cliente para factura
  89 Consultar cliente para factura
  91 Consultar Categorias
  92 Consultar Marcas
  93 Consultar Grupos do CEC
  94 Imagens JPEG em base-64
  98 Download de lista de produtos
  99 Consultar itens alterados
  111 Lista SQL
 */
require_once './inc/config.php';
require_once 'controles.php';

$lpost = "G";

$ncli = pegavar('cod', $lpost);
$nsub_cli = $afiltro[$ncli]["sub_cli"];
if (!$nsub_cli) {
    $nsub_cli = $ncli;
}


define("VERSAO", 406);
DEFINE("VERSAO_DATA", "2026-07-14 09:21:00");
define("CAMPOS_PRO", "p.RECNO,p.IS_DELETED,p.CODPRO,p.CODWEB,p.ATIVO,p.NOMELONG,p.CUSMED,p.ULTCUS,p.PREATAC,p.PRECO3,p.PRECO5,p.PREMIN,p.PREVEN,p.PRECOWEB,p.CODPROMO,p.LARGURA,p.ALTURA,p.PROFUND,p.PESPRO,p.GRUPRO,p.MARCPRO,p.GRADEPRO,p.KITPRO,p.SGRUPRO,p.GRUESP,p.FORPRO,p.NCM,p.ESTOQUE,p.DECRETO");
define("CAMPOS_WEB", "w.RECNO,w.IS_DELETED,w.CODIGO,w.BLOQUEADO,w.ALTERAPRO,w.ALTERAIMG,w.NOMEWEB,w.NOMEWEB2,w.NOMEWEB3,w.NIMAGEM,w.EMAX,w.PERMAX,w.LINK1,w.LINK2,w.GRADE,w.GRADE2,w.GRADE3,w.GRADE4,w.GRADE5,w.GRADE6,w.GRADE7,w.GRADE8,w.GRADE9,w.GRADE10,w.ETIQUETA,w.ECOMMERCE,w.ECOMMERC2,w.ECOMMERC3,w.ECOMMERC4,w.ECOMMERC5,w.ECOMMERC6,w.ECOMMERC7,w.ECOMMERC8,w.ECOMMERC9");
//define("CAMPOS_PRO","p.*");
//define("CAMPOS_WEB","w.*");

if (!$REFRESH_SEC) {
    $REFRESH_SEC = 25;
}
if ($afiltro[$ncli]["refresh"] > 0) {
    $REFRESH_SEC = $afiltro[$ncli]["refresh"] + 0;
}
/* * *****************RETIRAR****************** */
//$REFRESH_SEC=1;
$REFRESH_SEC = $REFRESH_SEC * 60;
$OP_22 = array("06:30:00", "19:00:00");

//define("PROD_PRO", "prod_api_$ncli");
//define("PROD_WEB", "prods_api_$ncli");
define("PROD_PRO", "prod_api_$nsub_cli");
define("PROD_WEB", "prods_api_$nsub_cli");
define("QUANT_ORDEM", 30);

if (count($afiltro[$ncli]) == 0) {
    $afiltro[$ncli]["homologa"] = true;
    $afiltro[$ncli]["ips_trabalho"] = array("");
    $afiltro[$ncli]["horario_trabalho_venda"] = array("00:00:00", "23:59:59");
    $afiltro[$ncli]["horario_trabalho_consulta"] = array("00:00:00", "23:59:59");
    $afiltro[$ncli]["config"] = "";
    $afiltro[$ncli]["lEnviarInativos"] = false;
    $afiltro[$ncli]["lApenasEcommerceSim"] = false;
    $afiltro[$ncli]["lMuitosItens"] = 10000;
    $afiltro[$ncli]["filtroativo"] = false;
    $afiltro[$ncli]["grupo"] = array();
    $afiltro[$ncli]["grupoesp"] = array();
    $afiltro[$ncli]["subgrupo"] = array();
    $afiltro[$ncli]["fornecedor"] = array();
    $afiltro[$ncli]["marca"] = array();

    $afiltro[$ncli]["ex_grupo"] = array();
    $afiltro[$ncli]["ex_grupoesp"] = array();
    $afiltro[$ncli]["ex_subgrupo"] = array();
    $afiltro[$ncli]["ex_fornecedor"] = array();
    $afiltro[$ncli]["ex_marca"] = array();
    $afiltro[$cliente]["naoenviasaldo"] = false;
    $afiltro[$cliente]["est_max"] = 0;
    $afiltro[$cliente]["ips_permitidos"] = array();
}

if ($afiltro[$ncli]["config"] == "1") {
    $afiltro[$ncli]["config"] = "";
}

if ($afiltro[$ncli]["CommitWeb"]) {
    $ccommit_web = $afiltro[$ncli]["CommitWeb"];
}

$idi = $afiltro[$ncli]["idioma"];

if ($idi !== 2) {
    $idi = "";
} else {
    $idi = "2";
}
if (pegavar("idm") == "P") {
    $idi = "";
} elseif (pegavar("idm") == "E") {
    $idi = "2";
}

date_default_timezone_set('America/Asuncion');
if ($afiltro[$ncli]["timezone"]) {
    date_default_timezone_set($afiltro[$ncli]["timezone"]);
}


$aret = array(array("ambiente" => ($afiltro[$ncli]["homologa"] ? "H" : "P"), "data_sol" => date('Y-m-d H:i:s'), "data_ret" => '', "estatus" => '', "cestatus" => '', "element" => 1));
$aret[0]["ver"] = number_format(VERSAO / 100, 2);
$aret[0]["dat"] = VERSAO_DATA;

global $lSemSsl;
global $db;

$lSemSsl = 1;
$cip = getenv("REMOTE_ADDR");
if ($afiltro[$ncli]["ips_permitidos"]) {
    $aip = $afiltro[$ncli]["ips_permitidos"];
    $nret = array_search($cip, $aip);

    if ($nret === false) {
        sleep(10);
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "5";
        $aret[0]["cestatus"] = "IP do solicitante nao aceito: " . $cip;
        echo json_encode($aret);
        logs($aret);
        return;
    }
}

$nope = pegavar("ope", $lpost);

//HORARIOS DE TRABALHO
if (array_search($nope, array(12, 15, 16)) !== false && $afiltro[$ncli]["horario_trabalho_venda"]) {
    $chora = date("H:i:s");
    if (!($chora >= $afiltro[$ncli]["horario_trabalho_venda"][0] && $chora <= $afiltro[$ncli]["horario_trabalho_venda"][1])) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "2";
        $aret[0]["cestatus"] = "Operacao nao permitida neste horario";
        echo json_encode($aret);
        return;
    }
}
if (array_search($nope, array(1, 2, 3, 4, 6, 11, 13, 31, 71, 81, 82, 83, 84, 85, 91, 93, 94, 98, 99, 111)) !== false && $afiltro[$ncli]["horario_trabalho_venda"]) {
    $chora = date("H:i:s");
    if (!($chora >= $afiltro[$ncli]["horario_trabalho_consulta"][0] && $chora <= $afiltro[$ncli]["horario_trabalho_consulta"][1])) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "2";
        $aret[0]["cestatus"] = "Operacao nao permitida neste horario.";
        echo json_encode($aret);
        return;
    }
}
if (!$aoperacoes["o" . $nope]) {
    $aret[0]["data_ret"] = date('Y-m-d H:i:s');
    $aret[0]["estatus"] = "3";
    $aret[0]["cestatus"] = $aoperacoes["o0"][0];
    echo json_encode($aret);
    logs($aret);
    return;
}

// operacoes em controles.php
if ($afiltro[$ncli]["operacao"] && ($afiltro[$ncli]["operacao"][$nope] === true || $afiltro[$ncli]["operacao"][$nope] === false)) {
    if (!$afiltro[$ncli]["operacao"][$nope]) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "3";
        $aret[0]["cestatus"] = "Operação Bloqueada =>" . $aoperacoes["o" . $nope][0];
        echo json_encode($aret);
        logs($aret);
        return;
    }
} else {
    if (!$aoperacoes["o" . $nope][1]) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "3";
        $aret[0]["cestatus"] = "Operação Bloqueada =>" . $aoperacoes["o" . $nope][0];
        echo json_encode($aret);
        logs($aret);
        return;
    }
}
if ($nope != 3) {
    if (!isset($_SERVER['HTTPS'])) {
        $lSemSsl = 1;
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "61";
        $aret[0]["cestatus"] = "Obrigatorio o uso de SSL";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $lSemSsl = 0;
} else {
    $lSemSsl = 1;
}

if ($nope == 10) {
    $aret[0]["data_ret"] = date('Y-m-d H:i:s');
    $aret[0]["estatus"] = "0";
    $aret[0]["cestatus"] = "Ok";
    $aret[0]["element"] = 2;
    $adet = array();
    $adet["ver"] = number_format(VERSAO / 100, 2);
    $adet["dat"] = VERSAO_DATA;
    $aret[] = $adet;
    echo json_encode($aret);
    logs($aret);
    return;
}

$asku = explode(",", pegavar("sku", $lpost));

$cerrosql = '';
if (!$db) {
    $db = conecta($server, $port, $user, $password, $database, $cerrosql);
    if (!$db) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "51";
        $aret[0]["cestatus"] = "Erro no Sql do Servidor. $cerrosql";
        echo json_encode($aret);
        logs($aret);
        return;
    }
}

$cstr="CREATE TABLE if not exists `cliente` (
  `server` varchar(100) NOT NULL DEFAULT '',
  `port` varchar(30) NOT NULL DEFAULT '',
  `user` varchar(30) NOT NULL DEFAULT '',
  `password` varchar(30) NOT NULL DEFAULT '',
  `data` varchar(30) NOT NULL DEFAULT '',
  `usuario` varchar(30) NOT NULL DEFAULT '',
  `senha` varchar(30) NOT NULL DEFAULT '',
  `codigo` varchar(30) NOT NULL DEFAULT '',
  `commit` varchar(150) NOT NULL DEFAULT '',
  `trab_server` varchar(100) NOT NULL DEFAULT '',
  `trab_user` varchar(100) NOT NULL DEFAULT '',
  `trab_password` varchar(100) NOT NULL DEFAULT '',
  `trab_data` varchar(100) NOT NULL DEFAULT '',
  `trab_dttmp` varchar(100) NOT NULL DEFAULT '',
  `trab_port` varchar(100) NOT NULL DEFAULT '',
  `trab_commit` varchar(100) NOT NULL DEFAULT '',
  `slave_server` varchar(100) NOT NULL DEFAULT '',
  `slave_user` varchar(100) NOT NULL DEFAULT '',
  `slave_password` varchar(100) NOT NULL DEFAULT '',
  `slave_data` varchar(100) NOT NULL DEFAULT '',
  `slave_port` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`codigo`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1";
mysqli_query($db,$cstr);

$otable = mysqli_query($db, "select codigo from cliente where codigo=" . pegavar("cod"));
if (mysqli_num_rows($otable) == 0) {
    $aret[0]["data_ret"] = date('Y-m-d H:i:s');
    $aret[0]["estatus"] = "1";
    $aret[0]["cestatus"] = "Cliente Invalido";
    echo json_encode($aret);
    logs($aret);
    return;
}

/* DATABASE DE CONTROLE */
/*
  $ctable = "cliente";
  $cengine = "INNODB";
  $acampos = array();
  $acampos["usuario"] = array("varchar(30)", true, "''");
  $acampos["senha"] = array("varchar(30)", true, "''");
  $acampos["codigo"] = array("varchar(30)", true, "'AUTO_INCREMENT'");

  $acampos["server"] = array("varchar(100)", true, "''");
  $acampos["port"] = array("varchar(100)", true, "''");
  $acampos["user"] = array("varchar(100)", true, "''");
  $acampos["password"] = array("varchar(100)", true, "''");
  $acampos["data"] = array("varchar(100)", true, "''");
  $acampos["commit"] = array("varchar(150)", true, "''");

  $acampos["trab_server"] = array("varchar(100)", true, "''");
  $acampos["trab_port"] = array("varchar(100)", true, "''");
  $acampos["trab_user"] = array("varchar(100)", true, "''");
  $acampos["trab_password"] = array("varchar(100)", true, "''");
  $acampos["trab_data"] = array("varchar(100)", true, "''");
  $acampos["trab_commit"] = array("varchar(150)", true, "''");
  $acampos["trab_dttmp"] = array("varchar(100)", true, "''");

  $acampos["slave_server"] = array("varchar(100)", true, "''");
  $acampos["slave_port"] = array("varchar(100)", true, "''");
  $acampos["slave_user"] = array("varchar(100)", true, "''");
  $acampos["slave_password"] = array("varchar(100)", true, "''");
  $acampos["slave_data"] = array("varchar(100)", true, "''");
  $acampos["slave_commit"] = array("varchar(150)", true, "''");

  $aindex = array();
  $aindex["PRIMARY"] = array("codigo");
  $aindex["server"] = array("server");
  $aindex["usuario"] = array("usuario", "password");
  CriaTabela($db, $ctable, $cengine, $acampos, $aindex);
  return;

  `server` varchar(100) NOT NULL,
  `port` varchar(30) NOT NULL,
  `user` varchar(30) NOT NULL,
  `password` varchar(30) NOT NULL,
  `data` varchar(30) NOT NULL,
  `usuario` varchar(30) NOT NULL,
  `senha` varchar(30) NOT NULL,
  `codigo` varchar(30) NOT NULL,
  `commit` varchar(150) NOT NULL,
  `trab_server` varchar(100) NOT NULL DEFAULT '',
  `trab_user` varchar(100) NOT NULL DEFAULT '',
  `trab_password` varchar(100) NOT NULL DEFAULT '',
  `trab_data` varchar(100) NOT NULL DEFAULT '',
  `trab_dttmp` varchar(100) NOT NULL DEFAULT '',
  `trab_port` varchar(100) NOT NULL DEFAULT '',
  `trab_commit` varchar(100) NOT NULL DEFAULT '',
  `slave_server` varchar(100) NOT NULL DEFAULT '',
  `slave_user` varchar(100) NOT NULL DEFAULT '',
  `slave_password` varchar(100) NOT NULL DEFAULT '',
  `slave_data` varchar(100) NOT NULL DEFAULT '',
  `slave_port` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`codigo`)
  ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
 * 
 */
mysqli_query($db, $cstr);
$otabcli = mysqli_query($db, "desc cliente");

$otable = mysqli_query($db, "select * from cliente where codigo='" . pegavar("cod") . "'  and senha='" . pegavar("pas") . "'");
if (mysqli_num_rows($otable) == 0) {
    $aret[0]["data_ret"] = date('Y-m-d H:i:s');
    $aret[0]["estatus"] = "2";
    $aret[0]["cestatus"] = "Senha Invalida";
    echo json_encode($aret);
    logs($aret);
    return;
}
$olinha = mysqli_fetch_array($otable);

$ccommit = $olinha["commit"];
if ($ccommit) {
    $ccommit_new = $ccommit;
}




/* MARCAR AQUI AS OPERACOES NO SERVER LOCAL */

$aOpeLocal = array(12, 13, 15, 16, 31, 71, 81, 82, 83, 84, 85, 87, 88, 89);
if (array_search($nope, $aOpeLocal) === false && trim($olinha["slave_server"])) {
    //echo "SLAVE<BR>";
    $ccserver = $olinha["slave_server"];
    $ccuser = $olinha["slave_user"];
    $ccpass = $olinha["slave_password"];
    $ccdata = $olinha["slave_data"];
    $ccport = !$olinha["slave_port"] ? 3306 : $olinha["slave_port"];
    $dbc = mysqli_connect($ccserver, $ccuser, $ccpass, $ccdata, $ccport);
} else {
    //echo "MASTER<BR>";
    //$dbc = mysqli_connect($olinha["server"], $olinha["user"], $olinha["password"], $olinha["data"], $olinha["port"]);
    $ccserver = $olinha["server"];
    $ccuser = $olinha["user"];
    $ccpass = $olinha["password"];
    $ccdata = $olinha["data"];
    $ccport = !$olinha["port"] ? 3306 : $olinha["port"];
    $dbc = mysqli_connect($ccserver, $ccuser, $ccpass, $ccdata, $ccport);
}
if (!$dbc) {
    $aret[0]["data_ret"] = date('Y-m-d H:i:s');
    $aret[0]["estatus"] = "7";
    $aret[0]["cestatus"] = "Cliente indisponivel";
    echo json_encode($aret);
    logs($aret);
    return;
}

$otcfg = mysqli_query($dbc, "select * FROM CONFIG" . $afiltro[$ncli]["config"] . "_ECM where recno=1");
$olcfg = mysqli_fetch_array($otcfg);
$caux = trim($olcfg["IP_AUT"]);
$aip1 = explode("\n", $caux);
$aip = array();
foreach ($aip1 as $value) {
    if ($value) {
        $aip[] = explode("|", $value)[0];
    }
}
if ($caux && $aip) {
    $nret = array_search($cip, $aip);
    if ($nret === false) {
        mysqli_close($dbc);
        mysqli_close($db);
        sleep(10);
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "5";
        $aret[0]["cestatus"] = "IP do solicitante nao aceito (2): " . $cip;
        echo json_encode($aret);
        logs($aret);
        return;
    }
}

//A-preven
//B-preco3
//C-premin
//T-Atacado
//W-preco WEB
//M-Custo Medio
//U-Ultimo custo  (em kit adota o custo medio)

switch ($olcfg["PRECO"]) {
    case "A":
        $preco_env = "PREVEN";
        $preco_envk = "VENKIT";
        break;
    case "B":
        $preco_env = "PRECO3";
        $preco_envk = "PR3KIT";
        break;
    case "C":
        $preco_env = "PREMIN";
        $preco_envk = "MINKIT";
        break;
    case "T":
        $preco_env = "PREATAC";
        $preco_envk = "ATAKIT";
        break;
    case "W":
        $preco_env = "PRECOWEB";
        $preco_envk = "VENKIT";
        break;
    case "M":
        $preco_env = "CUSMED";
        $preco_envk = "CUSKIT";
        break;
    case "U":
        $preco_env = "ULTCUS";
        $preco_envk = "CUSKIT";
        break;
    default:
        $preco_env = "PREVEN";
        $preco_envk = "VENKIT";
        break;
}
$lpreco1_env = trim($olcfg["PRECO1"]) != '';
switch ($olcfg["PRECO1"]) {
    case "A":
        $preco1_env = "PREVEN";
        $preco1_envk = "VENKIT";
        break;
    case "B":
        $preco1_env = "PRECO3";
        $preco1_envk = "PR3KIT";
        break;
    case "C":
        $preco1_env = "PREMIN";
        $preco1_envk = "MINKIT";
        break;
    case "T":
        $preco1_env = "PREATAC";
        $preco1_envk = "ATAKIT";
        break;
    case "W":
        $preco1_env = "PRECOWEB";
        $preco1_envk = "VENKIT";
        break;
    case "M":
        $preco1_env = "CUSMED";
        $preco1_envk = "CUSKIT";
        break;
    case "U":
        $preco1_env = "ULTCUS";
        $preco1_envk = "CUSKIT";
        break;
    default:
        $preco1_env = "";
        $preco1_envk = "";
        break;
}
$ntax = 0;
$preco_env = "round(p.$preco_env ,2)";
$preco_envk = "round(k.$preco_envk ,2)";
$preco1_env = "round(p.$preco1_env ,2)";
$preco1_envk = "round(k.$preco1_envk ,2)";

if ($afiltro[$ncli]["naoenviapreco"]) {
    $preco_env = "round(0 ,2)";
    $preco_envk = "round(0 ,2)";
    $preco1_env = "round(0 ,2)";
    $preco1_envk = "round(0 ,2)";
}

//CUSTO ADMINISTRATIVO
if ($olcfg["ADM"] > 0) {
    $ntax += $olcfg["ADM"];
}
//COMISSAO
if ($olcfg["COMISSAOP"] > 0) {
    $ntax += $olcfg["COMISSAOP"];
}
//SEGURO
if ($olcfg["SEGURO"] > 0) {
    $ntax += $olcfg["SEGURO"];
}
if ($ntax > 0) {
    $preco_env = "($preco_env*(1+" . ($ntax / 100) . "))";
    $preco_envk = "($preco_envk*(1+" . ($ntax / 100) . "))";
//    $preco1_env = "($preco1_env*(1+" . ($ntax / 100) . "))";
}
//FRETE PESO
if ($olcfg["FRETEP"] > 0) {
    $preco_env .= "+(p.pespro*" . $olcfg["FRETEP"] . ")";
    $preco_envk .= "+(p.pespro*" . $olcfg["FRETEP"] . ")";
//    $preco1_env .= "+(p.pespro*" . $olcfg["FRETEP"] . ")";
}
//FRETE VOLUME
if ($olcfg["FRETEV"] > 0) {
    $preco_env .= "+((p.largura*p.altura*p.profund)*" . $olcfg["FRETEV"] . ")";
    $preco_envk .= "+((p.largura*p.altura*p.profund)*" . $olcfg["FRETEV"] . ")";
//    $preco1_env .= "+((p.largura*p.altura*p.profund)*" . $olcfg["FRETEV"] . ")";
}
//COMISSAO VALOR
if ($olcfg["COMISSAOV"] > 0) {
    $preco_env .= "+" . $olcfg["COMISSAOV"];
    $preco_envk .= "+" . $olcfg["COMISSAOV"];
//    $preco1_env .= "+" . $olcfg["COMISSAOV"];
}

if (!$lpreco1_env) {
    $preco1_env = 0;
    $preco1_envk = 0;
}
$ocfgcec = mysqli_fetch_array(mysqli_query($dbc, "select * from CONFIG WHERE RECNO=1"));
$nloja = $olcfg["LOJA"];
$nrestar = 0;

if ($afiltro[$ncli]["naoenviasaldo"]) {
    $clojasal = "9999999";
} else {
    $clojasal = trim($olcfg["LOJASALDO"]);
}
$aret[0]["estatus"] = 0;
$aret[0]["cestatus"] = "ok";

//if (array_search($nope, array(1, 2,  3, 4, 11, 15,22, 91, 92, 93, 99)) !== false) {
if (array_search($nope, array(1, 2, 3, 4, 11, 22, 91, 92, 93, 99)) !== false) {
    GeraTempPro();
}

if ($nope == 101 || $nope == 102) { //ajustar restok
    $nsku = pegavar("sku") + 0;
    $nloja = pegavar("loja") + 0;

    if (!$nsku || !vedigito($nsku)) {
        $aret[0]["estatus"] = 99;
        $aret[0]["cestatus"] = "SKU inválido";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    if (!$nloja) {
        $aret[0]["estatus"] = 99;
        $aret[0]["cestatus"] = "Loja inválida";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $otabpro = mysqli_query($dbc, "select RESTOQ from PRODUTO_PRO WHERE IS_DELETED='N' and codpro=" . tiradigito($nsku));
    if (mysqli_num_rows($otabpro) == 0) {
        $aret[0]["estatus"] = 99;
        $aret[0]["cestatus"] = "SKU Não encontrado";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $olinpro = mysqli_fetch_row($otabpro);
    $aret["sku"] = $nsku;
    $aret["loja"] = $nloja;
    $aret["restoqger"] = round($olinpro[0]);
    $aret["restoq"] = mysqli_fetch_array(mysqli_query($dbc, "select quares from RESTOCK_PRO where is_deleted='N' and prores=" . tiradigito($nsku) . " and lojres=$nloja"))[0] + 0;
    if ($nope == 102) {
        $nqua = pegavar("quant") + 0;
        if ($nqua < 0) {
            $nqua = 0;
        }
        $nrecno = mysqli_fetch_row(mysqli_query($dbc, "select recno from  RESTOCK_PRO where is_deleted='N' and prores=" . tiradigito($nsku) . " and lojres=$nloja"))[0] + 0;
        if (!$nrecno) {
            $omed = new Mediator3("RESTOCK.PRO", W_CRIAR, '', '', $dbc);
            $omed->aadd("prores", tiradigito($nsku));
            $omed->aadd("lojres", $nloja);
            $omed->aadd("quares", $nqua);
            $omed->grava();
        } else {
            $omed = new Mediator3("RESTOCK.PRO", W_ALTERAR, '', $nrecno, $dbc);
            $omed->aadd("quares", $nqua);
            $omed->grava();
        }

        //mysqli_query($dbc, "update RESTOCK_PRO SET QUARES=$nqua where is_deleted='N' and lojres=$nloja and PRORES=" . tiradigito($nsku));
        $nger = mysqli_fetch_row(mysqli_query($dbc, "select sum(quares) from RESTOCK_PRO where  is_deleted='N' and PRORES=" . tiradigito($nsku)))[0] + 0;
        //mysqli_query($dbc, "update PRODUTO_PRO  SET RESTOQ=$nger where is_deleted='N' and codpro=" . tiradigito($nsku));
        $aret["newval"] = $nqua;
        //$aret["newvalger"] = $nger;
    }
    echo json_encode($aret);
    return;
} else if ($nope == 94) {
    if (pegavar("sku", $lpost) == "") {
        $aret[0]["estatus"] = 99;
        $aret[0]["cestatus"] = "SKU não passado";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $nimg = pegavar("img", $lpost);
    if (pegavar("img", $lpost) == "") {
        $nimg = 1;
    }


    $nsku = pegavar("sku", $lpost);
    if (!$nsku || !vedigito($nsku)) {
        $aret[0]["estatus"] = 99;
        $aret[0]["cestatus"] = "SKU inválido";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $nsku = tiradigito($nsku);
    $otable = mysqli_query($dbc, "select nimagem from " . PROD_WEB . " w WHERE 1 and w.codigo=" . $nsku);
    $olinha = mysqli_fetch_array($otable);
    $aret["tot"] = $olinha[0] + 0;
    $aret[0]["element"] = $aret[0]["element"] + 1;
    $aret["ord"] = $nimg;
    $aret[0]["element"] = $aret[0]["element"] + 1;
    $aret["tam"] = "";
    $aret[0]["element"] = $aret[0]["element"] + 1;
    $aret["t64"] = "";
    $aret[0]["element"] = $aret[0]["element"] + 1;
    $aret["img"] = "";
    $aret[0]["element"] = $aret[0]["element"] + 1;

    if ($nimg == 99 || mysqli_num_rows($otable) == 0) {
        echo json_encode($aret);
        logs($aret);
        return;
    }

    if ($olinha["nimagem"] < $nimg) {
        $aret[0]["estatus"] = 99;
        $aret[0]["cestatus"] = "Imagem não existe .$nimg." . $olinha["nimagem"];
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $otable = mysqli_query($dbc, "select i.ordem,m.memo from PRODUTOS_IMG i 
    inner join PRODUTOS_IMG_MEMO m on m.recno=i.recno and m.col_name='IMAGEM' 
    where i.codigo=" . $nsku . " and i.is_deleted='N' and i.ordem=$nimg order by i.ordem,i.parte");

    $aimagem = array();

    $nordem = 0;
    $npos = -1;
    while ($olinha = mysqli_fetch_array($otable)) {
        if ($nordem != $olinha["ordem"]) {
            $nordem = $olinha["ordem"];
            $aimagem[] = array($nordem, "");
            $npos++;
        }
        $aimagem[$npos][1] .= $olinha["memo"];
    }
    $aaux = array();
    $aaux[] = "\\";
    $aaux[] = chr(0);
    $aaux[] = chr(13);
    $aaux[] = chr(10);
    $aaux[] = '"';
    $aaux[] = "'";
    $aaux[] = "`";
    $aaux[] = chr(9);
    $aaux[] = chr(141);
    $aaux[] = chr(8);
    $aaux[] = chr(26);
    $aaux[] = "%";
    $aaux[] = "_";

    $cretorna = "";
    for ($j = 0;
            $j < count($aimagem);
            $j++) {
        $caux = $aimagem[$j][1];
        for ($i = count($aaux);
                $i > 0;
                $i--) {
            $caux = str_replace("/chr" . ( $i) . "/", $aaux[$i - 1], $caux);
        }
    }
    $cimg = base64_encode($caux);
    $aret["img"] = $cimg;
    $aret["tam"] = strlen($caux);
    $aret["t64"] = strlen($cimg);
    echo json_encode($aret);
    logs($aret);
    return;
} else if ($nope == 98) {
    $clink = $_SERVER["SCRIPT_NAME"];
    $aaux = explode("/", $clink);
    $clink = "https://" . $_SERVER["HTTP_HOST"];
    for ($i = 1;
            $i < count($aaux) - 1;
            $i++) {
        $clink .= "/" . $aaux[$i];
    }
    $clink .= "/File_";
    $nvezes = 12;
    $ncod = pegavar("cod");
    mysqli_query($db, "create table if not exists listadown_ecm (codigo int(10) not null default 0,data date ,numero int(10) not null default 0,arquivo varchar(100) not null default '')");
    $otab = mysqli_query($db, "select * from listadown_ecm where  data=current_date() and codigo=$ncod");
    if (mysqli_num_rows($otab) == 0) {
        mysqli_query($db, "delete from listadown_ecm where codigo=$ncod");
        mysqli_query($db, "insert listadown_ecm set codigo=$ncod,data=current_date()");
    }
    $olin = mysqli_fetch_array(mysqli_query($db, "select * from listadown_ecm WHERE codigo=$ncod"));
    if ($olin["numero"] < $nvezes) {
        foreach (glob("File_*") as $filename) {
            unlink($filename);
        }
        $arquivo = randString(30);
        $otpro = mysqli_query($dbc, "select p.codweb as codigo,$preco_env as preco,$preco1_env as preco1,if(p.ativo='N' or (!isnull(w.codigo) and w.bloqueado!='N'),'N','S') as ativo 
            from PRODUTO_PRO p 
            left join PRODUTOS_WEB w on w.is_deleted='N' and left(w.nomeweb,3)!='***' and w.codigo=p.codpro
            where p.is_deleted='N'
            and p.codpro!=0
            group by p.codpro
            order by codigo
                ");
        $farq = fopen("File_" . $arquivo . ".csv", "w");
        while ($otlin = mysqli_fetch_object($otpro)) {
            fwrite($farq, $otlin->codigo . "," . round($otlin->preco, 2) . ',"' . $otlin->ativo . '"' . chr(13) . chr(10));
        }
        fclose($farq);
        mysqli_query($db, "update listadown_ecm set arquivo='$arquivo',numero=" . ($olin["numero"] + 1) . " where codigo=$ncod");
        $olin = mysqli_fetch_array(mysqli_query($db, "select * from listadown_ecm WHERE codigo=$ncod"));
    }
    $clink .= $olin["arquivo"] . ".csv";
    $aret["num"] = $olin["numero"];
    $aret[0]["element"] = $aret[0]["element"] + 1;
    $aret["lnk"] = $clink;
    $aret[0]["element"] = $aret[0]["element"] + 1;
} else if ($nope == 6) {
    $apro = explode(",", pegavar("sku"));
    $aqua = explode(",", pegavar("qua"));
    $cvou = strtoupper(trim(pegavar("vou")));

    if (count($apro) !== count($aqua)) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = 40;
        $aret[0]["cestatus"] = "Parâmetros inválidos";
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $aretpro = array();
    $i = 0;
    foreach ($apro as $value) {
        
    }
} else if ($nope == 7) {
    if (!PegaVar("sku")) {
        $cstr = "select p.codpro as codigo from PRODUTO_PRO p
            inner join PRODUTOS_WEB w on w.is_deleted='N' and left(w.nomeweb,3)!='***' and w.codigo=p.codpro
            where p.kitpro='S' and p.ativo='S' and p.is_deleted='N' " . GeraFiltro("p", "w");
        $otab = mysqli_query($dbc, $cstr);
        $asku = array();
        while ($olin = mysqli_fetch_row($otab)) {
            $asku[] = poedigito($olin[0]);
        }
        $aret["qua"] = count($asku);
        $aret["sku"] = $asku;
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $apro = explode(",", pegavar("sku"));

    $aretpro = array();
    $i = 0;
    foreach ($apro as $value) {
        if (!vedigito($value)) {
            $aret["erro"] = 79;
            $aret["cerro"] = "SKU inválido => $value";
            echo json_encode($aret);
            return;
        }
        $csql = "select p.codpro as codigo,p.nomelong as nome,$preco_env as preco,$preco1_env as preco1,p.kitpro
,k.prokit,pk.nomelong,k.quakit,k.minkit,k.venkit,$preco_envk as valor,$preco1_envk as valor1
 from PROKIT_PRO k 
left join PRODUTO_PRO p on p.is_deleted='N' and k.CODKIT=p.CODPRO left join PRODUTO_PRO pk on pk.is_deleted='N' and k.proKIT=pk.CODPRO
WHERE k.IS_DELETED='N' AND k.CODkit=" . tiradigito($value);
        $otab = mysqli_query($dbc, $csql);
        if (mysqli_num_rows($otab) == 0) {
            $aret["erro"] = 79;
            $aret["cerro"] = "KIT parece não ser válido => $value";
            echo json_encode($aret);
            return;
        }
        $olin = mysqli_fetch_array($otab);
        mysqli_query($dbc, "drop temporary table if exist kitp");
        $cstr1 = "create temporary table kitp select p.codpro ,sum(if(isnull(l.qualoc),0,l.qualoc)) as qualoc,k.prokit,k.quakit 
                        from PRODUTO_PRO p 
                        inner join PROKIT_PRO k on k.is_deleted='N' and k.codkit=p.codpro
                        left  join LOCAL_LOC l on p.is_deleted='N' and l.qualoc>0 and k.prokit=l.proloc and p.kitpro='S' ";
        if ($clojasal && $clojasal !== "9999999") {
            $cstr1 .= " and find_in_set(l.locloc,'$clojasal')";
        }
        $cstr1 .= " WHERE p.IS_DELETED='N' and k.prokit=" . tiradigito($value) . " group by p.codpro,k.prokit";
        mysqli_query($dbc, $cstr1);
        $cstr1 = "update kitp set qualoc=qualoc/quakit";
        mysqli_query($dbc, $cstr1);
        $nsal = mysqli_fetch_array(mysqli_query($dbc, "select min(qualoc) from kitp"))[0];
        if (!$nsal) {
            $nsal = 0;
        }
        $aretpro = array("sku" => $olin["codigo"], "nom" => $olin["nome"], "pre" => $olin["preco"], "sal" => $nsal);
        while ($olin) {
            $aaux = array("sku" => $olin["prokit"], "nom" => $olin["nomelong"], "qua" => $olin["quakit"], "val" => $olin["valor"]);
            $aretpro["ite"][] = $aaux;
            $olin = mysqli_fetch_array($otab);
        }
        $aret[] = $aretpro;
        $aret[0]["element"] = $aret[0]["element"] + 1;
    }
    echo json_encode($aret);
    logs($aret);
    return;
} elseif ($nope == 87  //inclusão
        || $nope == 88 //alteracao
) {
    //adicionar em mysql.php

    $cerro = '';
    $codigo = PegaVar("clie");
    $ruc = PegaVar("ruc"); //se com ruc, contribuinte
    $doc = PegaVar("docu");
    $nasc = PegaVar("nasc");
    $nome = LimpaNome(trim(PegaVar("nome")));
    $ccid = trim(PegaVar("ccid"));
    $ncid = PegaVar("ncid");
    $cest = trim(PegaVar("depa"));
    $ende = LimpaNome(trim(PegaVar("ende")), "/,:;");
    $nume = PegaVar("nume");
    $fixo = PegaVar("tele");
    $celu = PegaVar("celu");
    $mail = trim(PegaVar("mail"));

    $npai = 600; //paraguay
    $nnat = 2; //não contribuinte
    $noper = 2; //Consumidor
    $ncon = 1; //pessoa fisica
    if (!$codigo) {
        $nerro = 101;
        $cerro .= "Codigo do cliente não passado";
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = $nerro;
        $aret[0]["cestatus"] = $cerro;
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $csql = "select nomcli,paiscli from CLIENTE_CLI WHERE IS_DELETED='N' AND CODCLI='$codigo'";
    $otabc = mysqli_query($dbc, $csql);
    if (mysqli_num_rows($otabc) == 0) {
        $nerro = 102;
        $cerro .= "Cliente não existe na base ";
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = $nerro;
        $aret[0]["cestatus"] = $cerro;
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $olinc = mysqli_fetch_array($otabc);
    if ($olinc["paiscli"] != 1) {
        $nerro = 117;
        $cerro .= "Cliente não é paraguayo";
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = $nerro;
        $aret[0]["cestatus"] = $cerro;
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }

    $csql = "select nome from CLIENTE_PGY WHERE IS_DELETED='N' AND CODIGO='$codigo'";
    $otab = mysqli_query($dbc, $csql);
    if ($nope == 87) {
        if (mysqli_num_rows($otab) !== 0) {
            $nerro = 103;
            $cerro .= "Cliente Ja cadastrado para Factura - Altere se necessário ";
            $aret[0]["data_ret"] = date('Y-m-d H:i:s');
            $aret[0]["estatus"] = $nerro;
            $aret[0]["cestatus"] = $cerro;
            $aret[0]["element"] = $aret[0]["element"] + 1;
            echo json_encode($aret);
            logs($aret);
            return;
        }
    } else {
        if (mysqli_num_rows($otab) == 0) {
            $nerro = 104;
            $cerro .= "Cliente não cadastrado para Factura - Inclua primeiro ";
            $aret[0]["data_ret"] = date('Y-m-d H:i:s');
            $aret[0]["estatus"] = $nerro;
            $aret[0]["cestatus"] = $cerro;
            $aret[0]["element"] = $aret[0]["element"] + 1;
            echo json_encode($aret);
            logs($aret);
            return;
        }
    }


    if (!$doc || strlen($doc) < 5) {
        $nerro = 105;
        $cerro .= "Documento inválido";
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = $nerro;
        $aret[0]["cestatus"] = $cerro;
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    if ($ruc && !VeDigitoRuc($ruc)) {
        $nerro = 106;
        $cerro .= "RUC Invalido";
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = $nerro;
        $aret[0]["cestatus"] = $cerro;
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    } elseif ($ruc) {
        $nnat = 1;
    }

    if ($nasc) {
        $format = "Y-m-d";
        $date = DateTime::createFromFormat($format, $nasc);

        if (!$date || $date->format($format) != $nasc) {
            $nerro = 107;
            $cerro .= "Data de nascimento inválida";
            $aret[0]["data_ret"] = date('Y-m-d H:i:s');
            $aret[0]["estatus"] = $nerro;
            $aret[0]["cestatus"] = $cerro;
            $aret[0]["element"] = $aret[0]["element"] + 1;
            echo json_encode($aret);
            logs($aret);
            return;
        }
    }

    if (!$nome) {
        $nerro = 108;
        $cerro .= "Nome vazio";
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = $nerro;
        $aret[0]["cestatus"] = $cerro;
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }


    $aaux = explode(" ", $nome);

    if (count($aaux) < 2) {
        $nerro = 109;
        $cerro .= "Sem sobrenome";
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = $nerro;
        $aret[0]["cestatus"] = $cerro;
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }

    if (strlen($nome) < 7) {
        $nerro = 110;
        $cerro .= "Nome Muito pequeno";
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = $nerro;
        $aret[0]["cestatus"] = $cerro;
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }

    if ($ncid || $ccid) {
        $cstr = "select c.codcid as ncidade,c.nomcid as ccidade,e.codest as nestado,e.nomest as cestado
      from CIDADE_PGY c
      INNER JOIN ESTADO_PGY e on e.is_deleted='N' and e.codest=c.codest
      where c.is_deleted='N' and "
        ;
        if ($ncid) {
            $cstr .= " c.codcid='$ncid'";
        } else {
            $cstr .= " c.nomcid='" . trim(strtoupper($ccid)) . "'";
        }
        $otab = mysqli_query($dbc, $cstr);
        if (mysqli_num_rows($otab) == 0) {
            $nerro = 111;
            $cerro .= "Cidade não localizada";
            $aret[0]["data_ret"] = date('Y-m-d H:i:s');
            $aret[0]["estatus"] = $nerro;
            $aret[0]["cestatus"] = $cerro;
            $aret[0]["element"] = $aret[0]["element"] + 1;
            echo json_encode($aret);
            logs($aret);
            return;
        }
        $olin = mysqli_fetch_array($otab);
        $ccid = $olin["ccidade"];
        $ncid = $olin["ncidade"];
        $nest = $olin["nestado"];
        $cest = $olin["cestado"];
    }
    if (!$ende || strlen($ende) < 5) {
        $nerro = 112;
        $cerro .= "Endereço inválido ou muito pequeno";
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = $nerro;
        $aret[0]["cestatus"] = $cerro;
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }

    if (!$fixo && !$celu) {
        $nerro = 113;
        $cerro .= "Um dos telefones não informado";

        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = $nerro;
        $aret[0]["cestatus"] = $cerro;
        $aret[0]["element"] = $aret[0]["element"] + 1;

        echo json_encode($aret);
        logs($aret);
        return;
    }

    if ($fixo) {
        $fixo = preg_replace('/[^0-9]/', "", $fixo);
        if (substr($fixo, 0, 1) == "0") {
            $fixo = substr($fixo, 1);
        }
        $cerro = "";

        if (substr($fixo, 0, 1) == "9") {
            $cerro .= "-Telefone fixo não pode começar com 9";
        }
        if (strlen($fixo) < 7) {
            $cerro .= "-Telefone fixo Muito pequeno";
        }
        if ($cerro) {
            $nerro = 114;
            $aret[0]["data_ret"] = date('Y-m-d H:i:s');
            $aret[0]["estatus"] = $nerro;
            $aret[0]["cestatus"] = $cerro;
            $aret[0]["element"] = $aret[0]["element"] + 1;
            echo json_encode($aret);
            logs($aret);
            return;
        }
        $fixo = "0" . $fixo;
    }

    if ($celu) {
        $celu = preg_replace('/[^0-9]/', "", $celu);
        if (substr($celu, 0, 1) == "0") {
            $celu = substr($celu, 1);
        }
        $cerro = "";

        if (substr($celu, 0, 1) !== "9") {
            $cerro .= "-Telefone Celular deve começar com 9";
        }
        if (strlen($celu) < 9) {
            $cerro .= "-Telefone Celular Muito pequeno";
        }
        if ($cerro) {
            $nerro = 115;
            $aret[0]["data_ret"] = date('Y-m-d H:i:s');
            $aret[0]["estatus"] = $nerro;
            $aret[0]["cestatus"] = $cerro;
            $aret[0]["element"] = $aret[0]["element"] + 1;
            echo json_encode($aret);
            logs($aret);
            return;
        }
        $celu = "0" . $celu;
    }

    if (!$mail || !ValidaMail($mail)) {
        $nerro = 116;
        $cerro .= "Email não informado ou inválido";
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = $nerro;
        $aret[0]["cestatus"] = $cerro;
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }








    if ($nope == 87) {
        if ($ccommit_web) {
            $omed = new Mediator3("CLIENTE.PGY", W_CRIAR, '', '', $dbc);
        } else {
            $omed = new Mediator();
            $omed->init($dbc, "CLIENTE.CLI", "");
        }
        $omed->aadd("CODIGO", $codigo);
    } else {
        if ($ccommit_web) {
            $omed = new Mediator3("CLIENTE.PGY", W_ALTERAR, '', "is_deleted='N' and CODIGO=$codigo", $dbc);
        } else {
            $omed = new Mediator();
            $omed->init($dbc, "CLIENTE.PGY", "codigo=$codigo");
        }
    }


    $omed->aadd("OPE_TIPO", $noper);
    $omed->aadd("CONT_TIPO", $ncon);
    $omed->aadd("NATUREZA", $nnat);

    $omed->aadd("BAIRRO", PegaVar("bair"));
    $omed->aadd("CELULAR", $celu);
    $omed->aadd("CEP", '');
    $omed->aadd("CIDADE", $ccid);
    $omed->aadd("CODCID", $ncid);
    $omed->aadd("CODEST", $nest);
    $omed->aadd("DOC_TIPO", pegavar("tdoc") == "4" ? 4 : 1);
    $omed->aadd("EMAIL", $mail);

    $omed->aadd("EMITIR", TRUE);
    $omed->aadd("ENDERECO", $ende);
    $omed->aadd("ESTADO", $cest);
    $omed->aadd("FANTASIA", '');
    $omed->aadd("IDEN_NUM", $doc);
    $omed->aadd("IDEN_TIPO", "Cédula Paraguaya");
    $omed->aadd("NOME", $nome);
    $omed->aadd("NUMERO", $nume ? $nume : "S/N");
    $omed->aadd("PAIS_NOM", "Paraguay");
    $omed->aadd("PAIS_NUM", 600);
    $omed->aadd("RUC", substr($ruc, 0, -1));
    $omed->aadd("RUCC", substr($ruc, 0, -1));
    $omed->aadd("RUC_DV", substr($ruc, -1));
    $omed->aadd("TELEFONE", $fixo);
    if ($nope == 88) {
        $omed->aadd("DAT_ALT", Date2Unix());
        $omed->aadd("OPE_ALT", 1);
        $omed->aadd("ORI_ALT", "API");
    } else {
        $omed->aadd("DAT_CAD", Date2Unix());
        $omed->aadd("OPE_CAD", 1);
        $omed->aadd("ORI_CAD", "API");
    }
    //$omed->aadd("RUCC", $ruc);
    $omed->aadd("CPF", '');

    if (!$omed->grava()) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "90";
        $aret[0]["cestatus"] = "Erro gravando dados CLI - " . $omed->cerro;
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    if (pegavar("nasc")) {
        mysqli_query($dbc, "update CLIENTE_CLI SET NASCLI=" . assql(PegaVar("nasc")) . " where is_deleted='N' and codcli=$codigo");
    }



    $nerro = 00;
    $cerro .= "ok";
    $aret[0]["data_ret"] = date('Y-m-d H:i:s');
    $aret[0]["estatus"] = $nerro;
    $aret[0]["cestatus"] = $cerro;
    $aret[0]["element"] = $aret[0]["element"] + 1;
    echo json_encode($aret);
    logs($aret);
    return;
} elseif ($nope == 89) {
    //adicionar em mysql.php

    $cerro = '';
    $codigo = PegaVar("clie");
    if (!$codigo) {
        $nerro = 101;
        $cerro .= "Codigo do cliente não passado";
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = $nerro;
        $aret[0]["cestatus"] = $cerro;
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $csql = "select * from CLIENTE_CLI WHERE IS_DELETED='N' AND CODCLI='$codigo'";
    $otabc = mysqli_query($dbc, $csql);
    if (mysqli_num_rows($otabc) == 0) {
        $nerro = 102;
        $cerro .= "Cliente não existe na base " . mysqli_error($dbc);
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = $nerro;
        $aret[0]["cestatus"] = $cerro;
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $csql = "select * from CLIENTE_PGY WHERE IS_DELETED='N' AND CODIGO='$codigo'";
    $otabf = mysqli_query($dbc, $csql);

    if (mysqli_num_rows($otabf) == 0) {
        $nerro = 104;
        $cerro .= "Cliente não cadastrado para Factura - Inclua primeiro ";
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = $nerro;
        $aret[0]["cestatus"] = $cerro;
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $olinf = mysqli_fetch_array($otabf);
    $olinc = mysqli_fetch_array($otabc);
    if ($olinf["PAIS_NUM"] != 600) {
        $nerro = 105;
        $cerro .= "Cliente não é Paraguayo";
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = $nerro;
        $aret[0]["cestatus"] = $cerro;
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }

    $acli = array();
    $acli["clie"] = $olinf["CODIGO"];
    $acli["nome"] = Converter($olinf["NOME"]);
    $acli["tdoc"] = $olinf["DOC_TIPO"];
    $acli["tdocc"] = $olinf["DOC_TIPO"] == 1 ? "Cédula Paraguaya" : "Carnet de Residencia";
    $acli["docu"] = $olinf["IDEN_NUM"];
    $acli["ruc"] = $olinf["RUCC"] . $olinf["RUC_DV"];
    $acli["celu"] = $olinf["CELULAR"];
    $acli["tele"] = $olinf["TELEFONE"];
    $acli["mail"] = Converter($olinf["EMAIL"]);
    $acli["nasc"] = $olinc["NASCLI"] > '1000-01-01' ? $olinc["NASCLI"] : "";
    $acli["ccid"] = Converter($olinf["CIDADE"]);
    $acli["ncid"] = $olinf["CODCID"];
    $acli["ende"] = Converter($olinf["ENDERECO"]);
    $acli["nume"] = $olinf["NUMERO"];
    $acli["bair"] = Converter($olinf["BAIRRO"]);
    $acli["dtcad"] = $olinf["DAT_CAD"] ? Unix2Date($olinf["DAT_CAD"], TRUE)[0] : "";
    $acli["oricad"] = $olinf["ORI_CAD"];
    $acli["dtalt"] = $olinf["DAT_ALT"] ? Unix2Date($olinf["DAT_ALT"], TRUE)[0] : "";
    $acli["orialt"] = $olinf["ORI_ALT"];

    $cfactura = "";

    if ($olinf["EMITIR"] != "Y") {
        $cfactura .= " - Cliente não autorizado a emitit DE";
    }
    if ($olinf["PAIS_NUM"] != 600) {
        $cfactura .= " - País inválido";
    }
    if (!$olinf["IDEN_NUM"] || $olinf["IDEN_NUM"] !== LIMPARG($olinf["IDEN_NUM"])) {
        $cfactura .= " - Documento inválido";
    }
    if ($olinf["EMAIL"] && !ValidaMail($olinf["EMAIL"])) {
        $cfactura .= " - Email inválido";
    }
    if (STRLEN($olinf["NOME"]) < 5) {
        $cfactura .= " - Nome inválido";
    }
    if ($olinf["NATUREZA"] < 1 || $olinf["NATUREZA"] > 2) {
        $cfactura .= " - Natureza inválido";
    }
    if ($olinf["OPE_TIPO"] != 2) {
        $cfactura .= " - Tipo de opração inválida";
    }
    if ($olinf["CONT_TIPO"] < 1 || $olinf["CONT_TIPO"] > 2) {
        $cfactura .= " - Tipo de contribuinte inválido";
    }
    if ($olinf["RUCC"] && !VeDigitoRuc($olinf["RUCC"]) != $olinf["RUC_DV"]) {
        $cfactura .= " - ruc INVÁLIDO";
    }
    if (!$olinf["CODCID"]) {
        $cfactura .= " - Cidade inválida";
    }
    if (!$olinf["ENDERECO"]) {
        $cfactura .= " - Endereço inválido";
    }
    if (trim($olinf["TELEFONE"]) && STRLEN($olinf["TELEFONE"]) < 9) {
        $cfactura .= " - telefone inválido";
    }
    if (trim($olinf["CELULAR"]) && STRLEN($olinf["CELULAR"]) < 10) {
        $cfactura .= " - celular inválido";
    }
    if (!$olinf["TELFEONE"] && !$olinf["CELULAR"]) {
        $cfactura .= " - Sem celular e sem celular";
    }

    $acli["falhas"] = $cfactura;

    $nerro = 0;
    $cerro = "";
    $aret["cliente"] = $acli;
    $aret[0]["data_ret"] = date('Y-m-d H:i:s');
    $aret[0]["estatus"] = $nerro;
    $aret[0]["cestatus"] = $cerro;
    $aret[0]["element"] = $aret[0]["element"] + 1;
    echo json_encode($aret);
    logs($aret);
    return;
} elseif ($nope == 86) {
    $cserie = strtoupper(trim(PegaVar("serie", $lpost)));
    if (!$cserie) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = 40;
        $aret[0]["cestatus"] = "Parâmetros inválidos";
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $adados = array();

    $csql = "select 
s.serie
,s.serie2
,s.codpro as codigo
,p.nomelong as nome
,s.notasai as nota
,n.datasai as data
,n.horasai as hora
,n.cliesai as cliente
,c.nomcli as ccliente

from PRODGAR s
inner join PRODUTO_PRO p on p.is_deleted='N' and p.codpro=s.codpro
inner join SAIDAS_SAI n on n.is_deleted='N' and n.recno=s.notasai
inner join CLIENTE_CLI c on c.is_deleted='N' and c.codcli=n.cliesai

where s.is_deleted='N' and (s.serie='$cserie' or s.serie2='$cserie')
";
    $otab = mysqli_query($dbc, $csql);
    while ($olin = mysqli_fetch_array($otab)) {
        $aaux = array();
        $aaux["serie"] = $olin["serie"];
        $aaux["serie2"] = $olin["serie2"];
        $aaux["sku"] = poedigito($olin["codigo"]);
        $aaux["produto"] = Converter($olin["nome"]);
        $aaux["nota"] = $olin["nota"] . "-" . digito($olin["nota"]);
        $aaux["emi_data"] = $olin["data"];
        $aaux["emi_hora"] = $olin["hora"];
        $aaux["cli_codigo"] = $olin["cliente"];
        $aaux["cli_nome"] = Converter($olin["ccliente"]);
        $adados[] = $aaux;
    }
    $aret["notas"] = $adados;

    $aret[0]["element"] = $aret[0]["element"] + 1;
} elseif ($nope == 85) { //ler cédula
    $ncodigo = PegaVar("cli");
    $nlado = pegavar("lado");
    $nmodo = pegavar("modo");

    if (!$ncodigo) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "40";
        $aret[0]["cestatus"] = "Código do cliente não informado";
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    if ($nlado !== "1" && $nlado !== "2") {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "40";
        $aret[0]["cestatus"] = "Parametro frente/verso inválido $nlado";
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $cstr = "select nomcli from CLIENTE_CLI WHERE IS_DELETED='N' AND CODCLI=$ncodigo";
    $otab = mysqli_query($dbc, $cstr);
    if (mysqli_num_rows($otab) == 0) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "40";
        $aret[0]["cestatus"] = "Cliente não existe";
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }

    $otab = mysqli_query($dbc, "select m.memo from CEDULA_CLI c inner join CEDULA_CLI_MEMO m on m.recno=c.recno and m.col_name='FOTO' where c.is_deleted='N' and c.codigo=$ncodigo and c.ordem=$nlado order by c.parte ");
    $caux = "";
    while ($olin = mysqli_fetch_row($otab)) {
        $caux .= $olin[0];
    }
    $aaux = array();
    $aaux[] = "\\";
    $aaux[] = chr(0);
    $aaux[] = chr(13);
    $aaux[] = chr(10);
    $aaux[] = '"';
    $aaux[] = "'";
    $aaux[] = "`";
    $aaux[] = chr(9);
    $aaux[] = chr(141);
    $aaux[] = chr(8);
    $aaux[] = chr(26);
    $aaux[] = "%";
    $aaux[] = "_";

    for ($i = 0; $i < count($aaux); $i++) {
        $caux = str_replace("/chr" . ($i + 1) . "/", $aaux[$i], $caux);
    }
    if ($nmodo == "B") {
        $aret["image"] = base64_encode($caux);
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "0";
        $aret[0]["cestatus"] = "Ok";
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $oimg = imagecreatefromstring($caux);
    header("Content-Type: image/jpeg");
    imagejpeg($oimg);
    imagedestroy($oimg);
    return;
} elseif ($nope == 84) { //inserir cédula
    $ncodigo = PegaVar("cli");
    $nlado = pegavar("lado");

    if (!$ncodigo) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "40";
        $aret[0]["cestatus"] = "Código do cliente não informado";
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    if ($nlado !== "1" && $nlado !== "2") {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "40";
        $aret[0]["cestatus"] = "Parametro frente/verso inválido $nlado";
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $cstr = "select nomcli from CLIENTE_CLI WHERE IS_DELETED='N' AND CODCLI=$ncodigo";
    $otab = mysqli_query($dbc, $cstr);
    if (mysqli_num_rows($otab) == 0) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "40";
        $aret[0]["cestatus"] = "Cliente não existe";
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }

    $carq = substr(strtoupper($_FILES['cedula']['name']), -4);
    $carq1 = substr(strtoupper($_FILES['cedula']['name']), -5);
    if ($carq !== ".JPG" && $carq1 !== ".JPEG") {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "40";
        $aret[0]["cestatus"] = "Arquivo de imagem inválido. Extensão inválida";
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }




    $cstr = file_get_contents($_FILES['cedula']['tmp_name'], FILE_BINARY);
    if (0 < $_FILES['fileUpload']['error'] || !$cstr) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "40";
        $aret[0]["cestatus"] = "Arquivo de imagem inválido ";
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }


    $aaux1 = array();
    $aaux1[] = "\\";
    $aaux1[] = chr(0);
    $aaux1[] = chr(13);
    $aaux1[] = chr(10);
    $aaux1[] = '"';
    $aaux1[] = "'";
    $aaux1[] = "`";
    $aaux1[] = chr(9);
    $aaux1[] = chr(141);
    $aaux1[] = chr(8);
    $aaux1[] = chr(26);
    $aaux1[] = "%";
    $aaux1[] = "_";

    $aaux = array();

    $i = 0;
    while (true) {
        $caux = substr($cstr, $i, 50000);
        if (strlen($caux) == 0) {
            break;
        }
        for ($k = 0; $k < count($aaux1); $k++) {
            $caux = str_replace($aaux1[$k], "/chr" . ($k + 1) . "/", $caux);
        }

        $aaux[] = $caux;
        $i += 50000;
    }



    $caux = $_FILES['fileUpload']['name'];

    mysqli_query($dbc, "update CEDULA_CLI SET IS_DELETED='Y' WHERE CODIGO=$ncodigo and ordem=$nlado");

    for ($i = 0; $i < count($aaux); $i++) {
        /*
          $agrava = array();
          $agrava[] = array("CODIGO", $ncli);
          $agrava[] = array("ORDEM", $nord);
          $agrava[] = array("PARTE", $i + 1);
          $agrava[] = array("FOTO", $aaux[$i], '', true);
          GravaSqlMed("CEDULA.CLI", $agrava);
         * 
         */
        if ($ccommit_web) {
            $omed = new Mediator3("CEDULA.CLI", W_CRIAR, '', "", $dbc);
        } else {
            $omed = new Mediator();
            $omed->init($dbc, "CEDULA.CLI");
        }

        $omed->aadd("CODIGO", $ncodigo);
        $omed->aadd("ORDEM", $nlado);
        $omed->aadd("PARTE", $i + 1);
        $omed->aadd("FOTO", $aaux[$i]);

        if (!$omed->grava()) {
            $aret[0]["data_ret"] = date('Y-m-d H:i:s');
            $aret[0]["estatus"] = "90";
            $aret[0]["cestatus"] = "Erro gravando dados CLI. - " . $omed->cerro;
            $aret[0]["element"] = $aret[0]["element"] + 1;
            echo json_encode($aret);
            logs($aret);
            return;
        }
    }
    mysqli_query($dbc, "update CLIENTE_CLI SET FOTO_CLI='S' WHERE IS_DELETED='N' AND CODCLI=$ncodigo");
    $aret[0]["data_ret"] = date('Y-m-d H:i:s');
    $aret[0]["estatus"] = "0";
    $aret[0]["cestatus"] = "Ok";
    $aret[0]["element"] = $aret[0]["element"] + 1;
    echo json_encode($aret);
    logs($aret);
    return;
} elseif ($nope == 83) {  //consultar cliente
    $adados = array();

    $codigo = PegaVar("codigo", $lpost);
    $ruc = limparg(strtoupper(trim(pegavar("docum", $lpost))));
    $email = strtoupper(trim(pegavar("email", $lpost)));
    $nome = strtoupper(trim(pegavar("nome", $lpost)));

    $cstr = "select * from CLIENTE_CLI WHERE IS_DELETED='N' AND ";
    if ($codigo) {
        $cstr .= " CODCLI='" . $codigo . "'";
    } elseif ($ruc) {
        $cstr .= " RUCRES='" . $ruc . "'";
        //$cstr = "select * from CLIENTE_CLI WHERE IS_DELETED='N' AND RUCRES='" . $adados["RUCCLI"] . "'";
        if ($afiltro[$ncli]["cliente_tipos"]) {
            $cstr .= " and find_in_set(GRUCLI,'" . $afiltro[$ncli]["cliente_tipos"] . "')";
        }
    } elseif ($email) {
        $cstr .= " e_mail='" . $email . "'";
    } elseif ($nome) {
        $cstr .= " nomcli='" . $nome . "'";
    }
    $otabcli = mysqli_query($dbc, $cstr);

    if (mysqli_num_rows($otabcli) == 0) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "82";
        $aret[0]["cestatus"] = "Cliente não existe";
        $aret[] = array("cod" => 0, "cmp" => "", "err" => "");
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }

    $olincli = mysqli_fetch_array($otabcli);
    $olinpfc = mysqli_fetch_array(mysqli_query($dbc, "select * from CLIENTE_PFC WHERE IS_DELETED='N' AND CODIGO=" . $olincli["CODCLI"]));

    $aret[] = array("codigo" => $olincli["CODCLI"]);
    $aret[] = array("docum" => LimpaRg($olincli["RUCCLI"]));
    $aret[] = array("email" => Converter($olincli["E_MAIL"]));
    $aret[] = array("nome" => Converter($olincli["NOMCLI"]));
    $aret[] = array("anive" => $olincli["NASCLI"]);
    $aret[] = array("sexo" => $olinpfc["SEXO"]);
    $aret[] = array("nasci" => $olinpfc["PAIS"]);
    $aret[] = array("cep" => $olincli["CEPCLI"]);
    $aret[] = array("ender" => Converter($olincli["ENDCLI"]));
    $aret[] = array("bairr" => Converter($olincli["BAICLI"]));
    $aret[] = array("cidad" => Converter($olincli["CIDCLI"]));
    $aret[] = array("uf" => Converter($olincli["UFCLI"]));
    $aret[] = array("pais" => Converter($olinpfc["PAIS"]));
    $aret[] = array("telef" => $olincli["FONCLI"]);
    $aret[] = array("celul" => $olincli["CELCLI"]);
    $aret[] = array("datac" => $olincli["CADCLI"]);

    $aret[0]["element"] = $aret[0]["element"] + 16;
} elseif ($nope == 82) { //alterar cliente
    $adados = array();

    $adados["ANIVER"] = strtoupper(trim(pegavar("anive", $lpost)));
    $adados["SEXO"] = strtoupper(trim(pegavar("sexo", $lpost)));
    $adados["NASCIO"] = strtoupper(trim(pegavar("nasci", $lpost)));
    $adados["CEPCLI"] = strtoupper(trim(pegavar("cep", $lpost)));
    $adados["ENDCLI"] = LimpaAcento(strtoupper(trim(pegavar("ender", $lpost))));
    $adados["BAICLI"] = LimpaAcento(strtoupper(trim(pegavar("bairr", $lpost))));
    $adados["CIDCLI"] = LimpaAcento(strtoupper(trim(pegavar("cidad", $lpost))));
    $adados["UFCLI"] = LimpaAcento(strtoupper(trim(pegavar("uf", $lpost))));
    $adados["PAICLI"] = LimpaAcento(strtoupper(trim(pegavar("pais", $lpost))));
    $adados["FONCLI"] = strtoupper(trim(pegavar("telef", $lpost)));
    $adados["CELCLI"] = strtoupper(trim(pegavar("celul", $lpost)));

    $adados["VENCLI"] = $olcfg["VENDEDOR"];

    $codigo = PegaVar("codigo", $lpost);

    if (!$codigo) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "83";
        $aret[0]["cestatus"] = "Falta campo obrigatório";
        $aret[] = array("cod" => 0, "cmp" => "codigo", "err" => "Codigo não passado");
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }

    $otabcli = mysqli_query($dbc, "select * from CLIENTE_CLI WHERE IS_DELETED='N' AND CODCLI='$codigo'");

    if (mysqli_num_rows($otabcli) == 0) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "82";
        $aret[0]["cestatus"] = "Cliente não existe";
        $aret[] = array("cod" => 0, "cmp" => "", "err" => "");
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    if ($adados["NASCIO"] && !VeNasc($adados["NASCIO"])) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "84";
        $aret[0]["cestatus"] = "Erro em campo obrigatório";
        $aret[] = array("cod" => 0, "cmp" => "nasci", "err" => "Nascionalidade inválida");
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }

    if ($adados["PAICLI"] && !VeNasc($adados["PAICLI"])) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "84";
        $aret[0]["cestatus"] = "Erro em campo obrigatório";
        $aret[] = array("cod" => 0, "cmp" => "pais", "err" => "Pais inválido");
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    if ($ccommit_web) {
        $omed = new Mediator3("CLIENTE.CLI", W_ALTERAR, '', "IS_DELETED='N' AND CODCLI='" . $codigo . "'", $dbc);
    } else {
        $omed = new Mediator();
        $omed->init($dbc, "CLIENTE.CLI", "CODCLI='" . $codigo . "'");
    }

    if ($adados["ANIVER"]) {
        $omed->aadd("nascli", $adados["ANIVER"]);
    }
    if ($adados["PAICLI"]) {
        $omed->aadd("paiscli", ($adados["PAICLI"] == "PRY" ? 1 : 2));
    }
    if ($adados["CEPCLI"]) {
        $omed->aadd("cepcli", $adados["CEPCLI"]);
    }
    if ($adados["ENDCLI"]) {
        $omed->aadd("endcli", $adados["ENDCLI"]);
    }
    if ($adados["BAICLI"]) {
        $omed->aadd("baicli", $adados["BAICLI"]);
    }
    if ($adados["CIDCLI"]) {
        $omed->aadd("cidcli", $adados["CIDCLI"]);
    }
    if ($adados["UFCLI"]) {
        $omed->aadd("ufcli", $adados["UFCLI"]);
    }
    if ($adados["FONCLI"]) {
        $omed->aadd("foncli", $adados["FONCLI"]);
    }
    if ($adados["CELCLI"]) {
        $omed->aadd("celcli", $adados["CELCLI"]);
        $omed->aadd("web1", $adados["CELCLI"]);
    }
    $omed->aadd("OBSCLI", "ALTERADO VIA ECOMMERCE - C:$ncli");
    if (!$omed->grava()) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "90";
        $aret[0]["cestatus"] = "Erro gravando dados CLI - " . $omed->cerro;
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }


    if (mysqli_num_rows(mysqli_query($dbc, "select codigo from CLIENTE_PFC where is_deleted='N' and codigo=$codigo")) == 0) {
        if ($ccommit_web) {
            $omedp = new Mediator3("CLIENTE.PFC", W_CRIAR, '', '', $dbc);
        } else {
            $omedp = new Mediator();
            $omedp->init($dbc, "CLIENTE.PFC");
        }
        $omedp->aadd("codigo", $codigo);
        if (!$omedp->grava()) {
            $aret[0]["data_ret"] = date('Y-m-d H:i:s');
            $aret[0]["estatus"] = "90";
            $aret[0]["cestatus"] = "Erro gravando dados PFC";
            $aret[0]["element"] = $aret[0]["element"] + 1;
            echo json_encode($aret);
            logs($aret);
            return;
        }
    }

    $olincli = mysqli_fetch_array(mysqli_query($dbc, "select * from CLIENTE_CLI WHERE IS_DELETED='N' AND CODCLI='$codigo'"));

    $cstr = "UPDATE CLIENTE_PFC SET nascimento='" . $olincli["NASCLI"] . "'";
    if ($adados["SEXO"]) {
        $cstr .= ",sexo='" . (!$adados["SEXO"] ? 3 : $adados["SEXO"]) . "'";
    }
    $cstr .= ",endereco='" . $olincli["ENDCLI"] . "',cidade='" . $olincli["CIDCLI"] . "',estado='" . $olincli["UFCLI"] . "'";
    if ($adados["PAICLI"]) {
        $cstr .= ",pais='" . $adados["PAICLI"] . "'";
    }
    $cstr .= ",bairro='" . $olincli["BAICLI"] . "',cep='" . $olincli["CEPCLI"] . "',complemen=''
        ,telefone='" . $olincli["FONCLI"] . "',celular='" . $olincli["CELCLI"] . "',whatsapp='" . $olincli["CELCLI"] . "',rg='" . $olincli["RUCCLI"] . "',tipocli='1',aceitamail='Y' where is_deleted='N' and codigo=$codigo
        ";
    mysqli_query($dbc, $cstr);
    if (mysqli_error($dbc)) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "90";
        $aret[0]["cestatus"] = "Erro gravando dados PFC";
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }


    $aret[] = array("cod" => $codigo, "cmp" => "", "err" => "");

    $aret[0]["element"] = $aret[0]["element"] + 1;
} elseif ($nope == 71) {
    $olinn = mysqli_fetch_array(mysqli_query($dbc, "select * from CAMBIO_NOM"));
    $olinc = mysqli_fetch_array(mysqli_query($dbc, "select * from CAMBIO_CAM WHERE IS_DELETED='N' ORDER BY RECNO DESC LIMIT 1"));

    $aret[] = array("moe1" => $olinn["NOME1"], "sim1" => $olinn["SIMB1"], "dec1" => $olinn["DECI1"], "cam1" => $olinc["MOEDA1"]
        , "moe2" => $olinn["NOME2"], "sim2" => $olinn["SIMB2"], "dec2" => $olinn["DECI2"], "cam2" => number_format($olinc["MOEDA2"] / $olinc["MOEDA1"], 4)
        , "moe3" => $olinn["NOME3"], "sim3" => $olinn["SIMB3"], "dec3" => $olinn["DECI3"], "cam3" => number_format($olinc["MOEDA3"] / $olinc["MOEDA1"], 4)
        , "moe4" => $olinn["NOME4"], "sim4" => $olinn["SIMB4"], "dec4" => $olinn["DECI4"], "cam4" => number_format($olinc["MOEDA4"] / $olinc["MOEDA1"], 4)
    );
    $aret[0]["element"] = $aret[0]["element"] + 1;
} elseif ($nope == 81) {
    $adados = array();
    $adados["RUCCLI"] = strtoupper(trim(pegavar("docum", $lpost)));
    $adados["E_MAIL"] = LimpaAcento(strtoupper(trim(pegavar("email", $lpost))));
    $adados["NOMCLI"] = LimpaAcento(strtoupper(trim(pegavar("nome", $lpost))));
    $adados["ANIVER"] = strtoupper(trim(pegavar("anive", $lpost)));
    $adados["SEXO"] = strtoupper(trim(pegavar("sexo", $lpost)));
    $adados["NASCIO"] = strtoupper(trim(pegavar("nasci", $lpost)));
    $adados["CEPCLI"] = strtoupper(trim(pegavar("cep", $lpost)));
    $adados["ENDCLI"] = LimpaAcento(strtoupper(trim(pegavar("ender", $lpost))));
    $adados["BAICLI"] = LimpaAcento(strtoupper(trim(pegavar("bairr", $lpost))));
    $adados["CIDCLI"] = LimpaAcento(strtoupper(trim(pegavar("cidad", $lpost))));
    $adados["UFCLI"] = LimpaAcento(strtoupper(trim(pegavar("uf", $lpost))));
    $adados["PAICLI"] = LimpaAcento(strtoupper(trim(pegavar("pais", $lpost))));
    $adados["FONCLI"] = strtoupper(trim(pegavar("telef", $lpost)));
    $adados["CELCLI"] = strtoupper(trim(pegavar("celul", $lpost)));

    foreach ($adados as $key => $value) {
        if (strpos(strtolower($value), "https:/") !== false || substr(strtolower($value), -2) == ".ru") {
            $aret[0]["data_ret"] = date('Y-m-d H:i:s');
            $aret[0]["estatus"] = "83";
            $aret[0]["cestatus"] = "Dados inválidos no campo => $key ";
            $aret[] = array("cod" => 0, "cmp" => "docum", "err" => "Dados inválidos no campo => $key : " . $value);
            $aret[0]["element"] = $aret[0]["element"] + 1;
            echo json_encode($aret);
            logs($aret);
            return;
        }
    }

    if (!$adados["RUCCLI"]) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "83";
        $aret[0]["cestatus"] = "Falta campo obrigatório";
        $aret[] = array("cod" => 0, "cmp" => "docum", "err" => "Documento não passado");
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }

    if (strlen(limparg($adados["RUCCLI"])) < 4) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "84";
        $aret[0]["cestatus"] = "Erro em campo obrigatório";
        $aret[] = array("cod" => 0, "cmp" => "docum", "err" => "Documento inválido");
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }

    $cstr = "select codcli from CLIENTE_CLI WHERE IS_DELETED='N' AND RUCRES='" . $adados["RUCCLI"] . "'";
    if ($afiltro[$ncli]["cliente_tipos"]) {
        $cstr .= " and find_in_set(GRUCLI,'" . $afiltro[$ncli]["cliente_tipos"] . "')";
    }
//    $ocli = mysqli_query($dbc, "select codcli from CLIENTE_CLI WHERE IS_DELETED='N' AND RUCRES='" . $adados["RUCCLI"] . "'");
    $ocli = mysqli_query($dbc, $cstr);
    if (mysqli_num_rows($ocli) > 0) {
        $lcli = mysqli_fetch_row($ocli);
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "82";
        $aret[0]["cestatus"] = "Documento já cadastrado";
        $aret[] = array("cod" => 0, "cod" => $lcli[0], "err" => "Documento já cadastrado");
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    if (!$adados["NOMCLI"]) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "83";
        $aret[0]["cestatus"] = "Falta campo obrigatório";
        $aret[] = array("cod" => 0, "cmp" => "nome", "err" => "Nome não passado");
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }

    if (!$adados["E_MAIL"]) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "83";
        $aret[0]["cestatus"] = "Falta campo obrigatório";
        $aret[] = array("cod" => 0, "cmp" => "email", "err" => "Email não passado");
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    if (!filter_var($adados["E_MAIL"], FILTER_VALIDATE_EMAIL)) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "83";
        $aret[0]["cestatus"] = "Email inválido";
        $aret[] = array("cod" => 0, "cmp" => "email", "err" => "Email inválido => " . $adados["E_MAIL"]);
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }

    $ocli = mysqli_query($dbc, "select codcli from CLIENTE_CLI WHERE IS_DELETED='N' AND e_mail='" . $adados["E_MAIL"] . "'");
    if (mysqli_num_rows($ocli) > 0) {
        $lcli = mysqli_fetch_row($ocli);
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "80";
        $aret[0]["cestatus"] = "Email já cadastrado";
        $aret[] = array("cod" => 0, "cod" => $lcli[0], "err" => "Email já cadastrado");
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }

    if (!$adados["SEXO"]) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "83";
        $aret[0]["cestatus"] = "Falta campo obrigatório";
        $aret[] = array("cod" => 0, "cmp" => "email", "err" => "Sexo não passado");
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    if (!($adados["SEXO"] >= 1 && $adados["SEXO"] <= 3)) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "84";
        $aret[0]["cestatus"] = "Erro em campo obrigatório";
        $aret[] = array("cod" => 0, "cmp" => "sexo", "err" => "Sexo inválido");
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }

    if (!$adados["NASCIO"]) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "83";
        $aret[0]["cestatus"] = "Falta campo obrigatório";
        $aret[] = array("cod" => 0, "cmp" => "nasci", "err" => "Nascionalidade não passada");
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    if (!VeNasc($adados["NASCIO"])) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "84";
        $aret[0]["cestatus"] = "Erro em campo obrigatório";
        $aret[] = array("cod" => 0, "cmp" => "nasci", "err" => "Nascionalidade inválida");
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }

    if (!$adados["ENDCLI"]) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "83";
        $aret[0]["cestatus"] = "Falta campo obrigatório";
        $aret[] = array("cod" => 0, "cmp" => "ender", "err" => "Endereço não passada");
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    if (strlen($adados["ENDCLI"]) < 5) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "84";
        $aret[0]["cestatus"] = "Erro em campo obrigatório";
        $aret[] = array("cod" => 0, "cmp" => "nasci", "err" => "Endereço com meno de 5 caracteres");
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }

    if (!$adados["CIDCLI"]) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "83";
        $aret[0]["cestatus"] = "Falta campo obrigatório";
        $aret[] = array("cod" => 0, "cmp" => "cidade", "err" => "Cidade não passada");
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    if (strlen($adados["CIDCLI"]) < 3) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "84";
        $aret[0]["cestatus"] = "Erro em campo obrigatório";
        $aret[] = array("cod" => 0, "cmp" => "cidad", "err" => "Cidade com meno de 3 caracteres");
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }

    if (!$adados["PAICLI"]) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "83";
        $aret[0]["cestatus"] = "Falta campo obrigatório";
        $aret[] = array("cod" => 0, "cmp" => "pais", "err" => "País não passado");
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    if (!VeNasc($adados["PAICLI"])) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "84";
        $aret[0]["cestatus"] = "Erro em campo obrigatório";
        $aret[] = array("cod" => 0, "cmp" => "pais", "err" => "Pais inválido");
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }



    //C;odigo é recno
    if ($ccommit_web) {
        $naux = mysqli_fetch_row(mysqli_query($dbc, "select PROX_CLI from PROXICAD_CAD WHERE RECNO=1"))[0];
        if ($naux && $naux > 0) {
            $lok = false;
            while (!$lok) {
                $omed = new Mediator3("PROXICAD.CAD", W_INCREMENT, '', 0, $dbc, "PROX_CLI");
                if (!$omed->grava() || !$omed->recno) {
                    sleep(2);
                    continue;
                }
                $lok = true;
            }
            $codigo = $omed->recno;
            $omed = new Mediator3("CLIENTE.CLI", W_CRIAR, '', '', $dbc);
            $omed->aadd("codcli", $codigo);
            $omed->aadd("nomcli", "INICIANDO GRAVACAO API");
            if (!$omed->grava()) {
                $aret[0]["data_ret"] = date('Y-m-d H:i:s');
                $aret[0]["estatus"] = "92";
                $aret[0]["cestatus"] = "Erro gravando dados CLI.. - " . $omed->cerro;
                $aret[0]["element"] = $aret[0]["element"] + 1;
                echo json_encode($aret);
                logs($aret);
                return;
            }
        } else {
            $omed = new Mediator3("CLIENTE.CLI", W_CRIAR, '', '', $dbc);
            $omed->aadd("nomcli", "INICIANDO GRAVACAO API");
            if (!$omed->grava()) {
                $aret[0]["data_ret"] = date('Y-m-d H:i:s');
                $aret[0]["estatus"] = "92";
                $aret[0]["cestatus"] = "Erro gravando dados CLI.. - " . $omed->cerro;
                $aret[0]["element"] = $aret[0]["element"] + 1;
                echo json_encode($aret);
                logs($aret);
                return;
            }
            $codigo = $omed->nrecno;
            mysqli_query($dbc,"UPDATE CLIENTE_CLI SET CODCLI=$codigo where recno=$codigo");
        }
    } else {
        $omed = new Mediator();
        $omed->init($dbc, "CLIENTE.CLI", '');
        $omed->aadd("nomcli", "INICIANDO GRAVACAO API");
        if (!$omed->grava()) {
            $aret[0]["data_ret"] = date('Y-m-d H:i:s');
            $aret[0]["estatus"] = "93";
            $aret[0]["cestatus"] = "Erro gravando dados CLI.. - " . $omed->cerro;
            $aret[0]["element"] = $aret[0]["element"] + 1;
            echo json_encode($aret);
            logs($aret);
            return;
        }
        $codigo = $omed->nrecno;
    }
    if (!$codigo) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "94";
        $aret[0]["cestatus"] = "Código do cliente não gerado";
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }

//    mysqli_query($dbc, "update CLIENTE_CLI set codcli=$codigo WHERE RECNO=$nrecno");

    if ($ccommit_web) {
        $omed = new Mediator3("CLIENTE.CLI", W_ALTERAR, '', "codcli=$codigo", $dbc);
    } else {
        $omed = new Mediator();
        $omed->init($dbc, "CLIENTE.CLI", "codigo=$codigo");
    }
    $omed->aadd("ruccli", limparg($adados["RUCCLI"]));
    $omed->aadd("rucres", limparg($adados["RUCCLI"]));
    $omed->aadd("e_mail", $adados["E_MAIL"]);
    $omed->aadd("nomcli", $adados["NOMCLI"]);
    $omed->aadd("nascli", $adados["ANIVER"]);
    $omed->aadd("paiscli", ($adados["PAICLI"] == "PRY" ? 1 : 2));
    $omed->aadd("cepcli", $adados["CEPCLI"]);
    $omed->aadd("endcli", $adados["ENDCLI"]);
    $omed->aadd("baicli", $adados["BAICLI"]);
    $omed->aadd("cidcli", $adados["CIDCLI"]);
    $omed->aadd("ufcli", $adados["UFCLI"]);
    $omed->aadd("foncli", $adados["FONCLI"]);
    $omed->aadd("celcli", $adados["CELCLI"]);
    $omed->aadd("web1", $adados["CELCLI"]);
    $omed->aadd("CADCLI", "CURRENT_DATE()", TRUE);
    $omed->aadd("BLKCLI", "N");
    $omed->aadd("BLOCLI", "N");
    $omed->aadd("CDNCLI", 1);
    $omed->aadd("CLIVIS", "S");
    $omed->aadd("CODCID", 1);

    $tipcli = pegavar("tip", $lpost);
    if (!$tipcli) {
        $tipcli = $afiltro[$ncli]["cliente_tipo"];
    }
    if (!$tipcli) {
        $tipcli = 1;
    }
    $omed->aadd("GRUCLI", $tipcli);
    $omed->aadd("FATCLI", "N");
    $omed->aadd("OBSCLI", "CADASTRADO VIA ECOMMERCE c:$ncli  " . $afiltro[$ncli]["config"]);
    $omed->aadd("VENCLI", "N");
    $zoncli = pegavar("zon", $lpost);
    if (!$zoncli) {
        $zoncli = $afiltro[$ncli]["cliente_zona"];
    }
    if (!$zoncli) {
        $zoncli = 1;
    }
    $omed->aadd("ZONCLI", $zoncli);
    $omed->aadd("EMIFAT", "S");
    if (!$omed->grava()) {
        mysqli_query($dbc, "update  CLIENTE_CLI SET IS_DELETED='Y' WHERE CODCLI=$codigo");
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "90";
        $aret[0]["cestatus"] = "Erro gravando dados CLI.. - " . $omed->cerro;
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }






    $nrecno = $omed->nrecno;
    /*
      $codigo = $omed->nrecno;
      //C;odigo é recno
      if ($ccommit_web) {
      $naux = mysqli_fetch_row(mysqli_query($dbc, "select PROX_CLI from PROXICAD_CAD WHERE RECNO=1"))[0];
      if ($naux && $naux > 0) {
      $lok=false;
      while (!$lok) {
      $omed = new Mediator3("PROXICAD.CAD", W_INCREMENT, '', 0, $dbc, "PROX_CLI");
      if (!$omed->grava() || !$omed->recno ) {
      sleep(2);
      continue;
      }
      $lok=true;
      }
      $codigo = $omed->recno;
      }
      }

      mysqli_query($dbc, "update CLIENTE_CLI set codcli=$codigo WHERE RECNO=$nrecno");
     */
    if (mysqli_num_rows(mysqli_query($dbc, "select codigo from CLIENTE_PFC where is_deleted='N' and codigo=$codigo")) == 0) {
        if ($ccommit_web) {
            $omedp = new Mediator3("CLIENTE.PFC", W_CRIAR, '', '', $dbc);
        } else {
            $omedp = new Mediator();
            $omedp->init($dbc, "CLIENTE.PFC");
        }

        $omedp->aadd("codigo", $codigo);
        if (!$omedp->grava()) {
            $aret[0]["data_ret"] = date('Y-m-d H:i:s');
            $aret[0]["estatus"] = "90";
            $aret[0]["cestatus"] = "Erro gravando dados PFC";
            $aret[0]["element"] = $aret[0]["element"] + 1;
            echo json_encode($aret);
            logs($aret);
            return;
        }
    }

    $cstr = "UPDATE CLIENTE_PFC SET nascimento='" . $adados["ANIVER"] . "',sexo='" . (!$adados["SEXO"] ? 3 : $adados["SEXO"]) . "',endereco='" . $adados["ENDCLI"] . "',cidade='" . $adados["CIDCLI"] . "',estado='" . $adados["UFCLI"] . "',pais='" . $adados["PAICLI"] . "',bairro='" . $adados["BAICLI"] . "',cep='" . $adados["CEPCLI"] . "',complemen=''
        ,telefone='" . $adados["FONCLI"] . "',celular='" . $adados["CELCLI"] . "',whatsapp='" . $adados["CELCLI"] . "',rg='" . $adados["RUCCLI"] . "',tipocli='1',aceitamail='Y' where is_deleted='N' and codigo=$codigo
        ";
    mysqli_query($dbc, $cstr);
    if (mysqli_error($dbc)) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "90";
        $aret[0]["cestatus"] = "Erro gravando dados PFC";
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }


    $aret[] = array("cod" => $codigo, "cmp" => "", "err" => "");

    $aret[0]["element"] = $aret[0]["element"] + 1;
} elseif ($nope == 5) {
    $lis = pegavar("lis");
    $sku = pegavar("sku");
    $asku = explode(",", $sku);
    if (count($asku) == 1 && !$asku[0]) {
        $asku = array();
    }

    $otlis = mysqli_query($dbc, "select codigo,data_ini,data_fim,data_atu,moeda,nome,percentual,precofixo,trim(preco_bas) as preco_bas,grupo,subgrupo,grupoesp,marca,fornec,itens,bloqueado from LISPRO_LIS WHERE IS_DELETED='N' AND CODIGO=$lis");
    if (mysqli_num_rows($otlis) == 0) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "90";
        $aret[0]["cestatus"] = "Lista não encontrada";
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $ollis = mysqli_fetch_array($otlis);
    if ($ollis["bloqueado"] == "Y") {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "91";
        $aret[0]["cestatus"] = "Lista bloqueada";
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }

    if ($ollis["data_ini"] > Date2Unix() || $ollis["data_fim"] < Date2Unix()) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "91";
        $aret[0]["cestatus"] = "Lista não vigente => " . Unix2Date($ollis["data_ini"])[0] . " a " . Unix2Date($ollis["data_fim"])[0];
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $alis = array();
    $alis["codigo"] = $ollis["codigo"];
    $alis["nome"] = $ollis["nome"];
    $alis["inicio"] = Unix2Date($ollis["data_ini"])[0];
    $alis["final"] = Unix2Date($ollis["data_fim"])[0];
    $nmoeda = trim($ollis["moeda"]) ? trim($ollis["moeda"]) : 1;
    $alis["moeda"] = $nmoeda;
    $alis["cmoeda"] = mysqli_fetch_row(mysqli_query($dbc, "select simb" . $nmoeda . " from CAMBIO_NOM WHERE IS_DELETED='N'"))[0];
    $alis["percentual"] = $ollis["percentual"];
    $alis["preco_bas"] = $ollis["preco_bas"];
    $alis["itens"] = $ollis["itens"];

    $ndec = mysqli_fetch_row(mysqli_query($dbc, "select deci" . $nmoeda . " from CAMBIO_NOM WHERE IS_DELETED='N'"))[0] == "S" ? 2 : 0;
    $aitens = array();
    $nitens = 0;
    if ($ollis["precofixo"] == 'Y') {
        $otpre = mysqli_query($dbc, "select l.produto,l.preco  from LISPROPR_LIS l  where l.is_deleted='N' and l.codigo=$lis order by l.produto ");
        while ($olpre = mysqli_fetch_array($otpre)) {
            if ($asku && array_search(poedigito($olpre["produto"]), $asku) === false) {
                continue;
            }
            $alis["produtos"][poedigito($olpre["produto"])]["precofix"] = number_format($olpre["preco"], $ndec);
            ++$nitens;
        }
    } elseif ($ollis["itens"] > 0) {
        $csql = "select l.produto,l.preco,l.precot,l.precoa,l.precob,l.precoc
            ,p.preatac as precot1,p.preven as precoa1,p.preco3 as precob1,p.premin as precoc1
            from LISPROPR_LIS l inner join PRODUTO_PRO p on p.is_deleted='N' and p.codpro=l.produto  where l.is_deleted='N' and l.codigo=$lis order by l.produto ";
        $otpre = mysqli_query($dbc, $csql);
        echo mysqli_num_rows($otpre);
        while ($olpre = mysqli_fetch_array($otpre)) {
            if ($asku && array_search(poedigito($olpre["produto"]), $asku) === false) {
                continue;
            }
            ++$nitens;
            if ($ollis["percentual"] != 0) {
                if ($ollis["preco_bas"] == "" || $ollis["preco_bas"] == "T") {
                    $alis["produtos"][poedigito($olpre["produto"])]["precot"] = number_format($olpre["precot1"] * (1 + $ollis["percentual"] / 100), $ndec);
                }
                if ($ollis["preco_bas"] == "" || $ollis["preco_bas"] == "A") {
                    $alis["produtos"][poedigito($olpre["produto"])]["precoa"] = number_format($olpre["precoa1"] * (1 + $ollis["percentual"] / 100), $ndec);
                }
                if ($ollis["preco_bas"] == "" || $ollis["preco_bas"] == "B") {
                    $alis["produtos"][poedigito($olpre["produto"])]["precob"] = number_format($olpre["precob1"] * (1 + $ollis["percentual"] / 100), $ndec);
                }
                if ($ollis["preco_bas"] == "" || $ollis["preco_bas"] == "C") {
                    $alis["produtos"][poedigito($olpre["produto"])]["precoc"] = number_format($olpre["precoc1"] * (1 + $ollis["percentual"] / 100), $ndec);
                }
            } else {
                if ($ollis["preco_bas"] == "" || $ollis["preco_bas"] == "T") {
                    $alis["produtos"][poedigito($olpre["produto"])]["precot"] = number_format($olpre["precot"], $ndec);
                }
                if ($ollis["preco_bas"] == "" || $ollis["preco_bas"] == "A") {
                    $alis["produtos"][poedigito($olpre["produto"])]["precoa"] = number_format($olpre["precoa"], $ndec);
                }
                if ($ollis["preco_bas"] == "" || $ollis["preco_bas"] == "B") {
                    $alis["produtos"][poedigito($olpre["produto"])]["precob"] = number_format($olpre["precob"], $ndec);
                }
                if ($ollis["preco_bas"] == "" || $ollis["preco_bas"] == "C") {
                    $alis["produtos"][poedigito($olpre["produto"])]["precoc"] = number_format($olpre["precoc"], $ndec);
                }
            }
        }
    } else {
        $csql = "select p.codpro as produto
            ,p.preatac as precot1,p.preven as precoa1,p.preco3 as precob1,p.premin as precoc1
            from PRODUTO_PRO p where p.is_deleted='N' ";
        if (trim($ollis["grupo"])) {
            $csql .= " and find_in_set(p.grupro,'" . trim($ollis["grupo"]) . "')";
        }
        if (trim($ollis["subgrupo"])) {
            $csql .= " and find_in_set(p.sgrupro,'" . trim($ollis["subgrupo"]) . "')";
        }
        if (trim($ollis["grupoesp"])) {
            $csql .= " and find_in_set(p.gruesp,'" . trim($ollis["grupoesp"]) . "')";
        }
        if (trim($ollis["marca"])) {
            $csql .= " and find_in_set(p.marcpro,'" . trim($ollis["marca"]) . "')";
        }
        if (trim($ollis["fornec"])) {
            $csql .= " and find_in_set(p.forpro,'" . trim($ollis["fornec"]) . "')";
        }
        $csql .= " order by produto ";
        $otpre = mysqli_query($dbc, $csql);
        $ncambio = mysqli_fetch_row(mysqli_query($dbc, "select moeda" . (!trim($ollis["moeda"]) ? 1 : $ollis["moeda"]) . " as cambio from CAMBIO_CAM where is_deleted='N' order by datcam desc limit 1"))[0];
        while ($olpre = mysqli_fetch_array($otpre)) {
            if ($asku && array_search(poedigito($olpre["produto"]), $asku) === false) {
                continue;
            }
            ++$nitens;
            if ($ollis["percentual"] != 0) {
                if ($ollis["preco_bas"] == "" || $ollis["preco_bas"] == "T") {
                    $alis["produtos"][poedigito($olpre["produto"])]["precot"] = number_format($olpre["precot1"] * (1 + $ollis["percentual"] / 100) * $ncambio, $ndec);
                }
                if ($ollis["preco_bas"] == "" || $ollis["preco_bas"] == "A") {
                    $alis["produtos"][poedigito($olpre["produto"])]["precoa"] = number_format($olpre["precoa1"] * (1 + $ollis["percentual"] / 100) * $ncambio, $ndec);
                }
                if ($ollis["preco_bas"] == "" || $ollis["preco_bas"] == "B") {
                    $alis["produtos"][poedigito($olpre["produto"])]["precob"] = number_format($olpre["precob1"] * (1 + $ollis["percentual"] / 100) * $ncambio, $ndec);
                }
                if ($ollis["preco_bas"] == "" || $ollis["preco_bas"] == "C") {
                    $alis["produtos"][poedigito($olpre["produto"])]["precoc"] = number_format($olpre["precoc1"] * (1 + $ollis["percentual"] / 100) * $ncambio, $ndec);
                }
            } else {
                if ($ollis["preco_bas"] == "" || $ollis["preco_bas"] == "T") {
                    $alis["produtos"][poedigito($olpre["produto"])]["precot"] = number_format($olpre["precot"], $ndec);
                }
                if ($ollis["preco_bas"] == "" || $ollis["preco_bas"] == "A") {
                    $alis["produtos"][poedigito($olpre["produto"])]["precoa"] = number_format($olpre["precoa"], $ndec);
                }
                if ($ollis["preco_bas"] == "" || $ollis["preco_bas"] == "B") {
                    $alis["produtos"][poedigito($olpre["produto"])]["precob"] = number_format($olpre["precob"], $ndec);
                }
                if ($ollis["preco_bas"] == "" || $ollis["preco_bas"] == "C") {
                    $alis["produtos"][poedigito($olpre["produto"])]["precoc"] = number_format($olpre["precoc"], $ndec);
                }
            }
        }
    }
    $alis["itens"] = $nitens;
    $aret["lista"] = $alis;
} elseif ($nope == 90) {
    $cod = PegaVar("sku", $lpost);

    $cat = array();
    if (!$cod) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "90";
        $aret[0]["cestatus"] = "SKU da categoria não passado";
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    } else {

        $otab1 = mysqli_query($dbc, "select codigo,nome,nome2 from GRADE_WEB where is_deleted='N' and find_in_set(codigo,'$cod')");
        while ($olin1 = mysqli_fetch_array($otab1)) {
            $cat[$olin1["codigo"]]["nome"] = converter1($olin1["nome"]);
            $cat[$olin1["codigo"]]["nome2"] = converter1($olin1["nome2"]);
            $cat[$olin1["codigo"]]["nivel"] = 1;
            $otab2 = mysqli_query($dbc, "select codigo,nome,nome2 from GRADE_WEB where is_deleted='N' and id_parent=" . $olin1["codigo"]);
            while ($olin2 = mysqli_fetch_array($otab2)) {
                $cat[$olin1["codigo"]]["child"][$olin2["codigo"]]["nome"] = converter1($olin2["nome"]);
                $cat[$olin1["codigo"]]["child"][$olin2["codigo"]]["nome2"] = converter1($olin2["nome2"]);
                $cat[$olin1["codigo"]]["child"][$olin2["codigo"]]["nivel"] = 2;
                $otab3 = mysqli_query($dbc, "select codigo,nome,nome2 from GRADE_WEB where is_deleted='N' and id_parent=" . $olin2["codigo"]);
                while ($olin3 = mysqli_fetch_array($otab3)) {
                    $cat[$olin1["codigo"]]["child"][$olin2["codigo"]]["child"][$olin3["codigo"]]["nome"] = converter1($olin3["nome"]);
                    $cat[$olin1["codigo"]]["child"][$olin2["codigo"]]["child"][$olin3["codigo"]]["nome2"] = converter1($olin3["nome2"]);
                    $cat[$olin1["codigo"]]["child"][$olin2["codigo"]]["child"][$olin3["codigo"]]["nivel"] = 3;
                    $otab4 = mysqli_query($dbc, "select codigo,nome,nome2 from GRADE_WEB where is_deleted='N' and id_parent=" . $olin3["codigo"]);
                    while ($olin4 = mysqli_fetch_array($otab4)) {
                        $cat[$olin1["codigo"]]["child"][$olin2["codigo"]]["child"][$olin3["codigo"]]["child"][$olin4["codigo"]]["nome"] = converter1($olin4["nome"]);
                        $cat[$olin1["codigo"]]["child"][$olin2["codigo"]]["child"][$olin3["codigo"]]["child"][$olin4["codigo"]]["nome2"] = converter1($olin4["nome2"]);
                        $cat[$olin1["codigo"]]["child"][$olin2["codigo"]]["child"][$olin3["codigo"]]["child"][$olin4["codigo"]]["nivel"] = 4;
                        $otab5 = mysqli_query($dbc, "select codigo,nome,nome2 from GRADE_WEB where is_deleted='N' and id_parent=" . $olin4["codigo"]);
                        while ($olin5 = mysqli_fetch_array($otab5)) {
                            $cat[$olin1["codigo"]]["child"][$olin2["codigo"]]["child"][$olin3["codigo"]]["child"][$olin4["codigo"]]["child"][$olin5["codigo"]]["nome"] = converter1($olin5["nome"]);
                            $cat[$olin1["codigo"]]["child"][$olin2["codigo"]]["child"][$olin3["codigo"]]["child"][$olin4["codigo"]]["child"][$olin5["codigo"]]["nome2"] = converter1($olin5["nome2"]);
                            $cat[$olin1["codigo"]]["child"][$olin2["codigo"]]["child"][$olin3["codigo"]]["child"][$olin4["codigo"]]["child"][$olin5["codigo"]]["nivel"] = 5;
                            $otab6 = mysqli_query($dbc, "select codigo,nome,nome2 from GRADE_WEB where is_deleted='N' and id_parent=" . $olin5["codigo"]);
                            while ($olin6 = mysqli_fetch_array($otab6)) {
                                $cat[$olin1["codigo"]]["child"][$olin2["codigo"]]["child"][$olin3["codigo"]]["child"][$olin4["codigo"]]["child"][$olin5["codigo"]]["child"][$olin6["codigo"]]["nome"] = converter1($olin6["nome"]);
                                $cat[$olin1["codigo"]]["child"][$olin2["codigo"]]["child"][$olin3["codigo"]]["child"][$olin4["codigo"]]["child"][$olin5["codigo"]]["child"][$olin6["codigo"]]["nome2"] = converter1($olin6["nome2"]);
                                $cat[$olin1["codigo"]]["child"][$olin2["codigo"]]["child"][$olin3["codigo"]]["child"][$olin4["codigo"]]["child"][$olin5["codigo"]]["child"][$olin6["codigo"]]["nivel"] = 6;
                                $otab7 = mysqli_query($dbc, "select codigo,nome,nome2 from GRADE_WEB where is_deleted='N' and id_parent=" . $olin6["codigo"]);
                                while ($olin7 = mysqli_fetch_array($otab7)) {
                                    $cat[$olin1["codigo"]]["child"][$olin2["codigo"]]["child"][$olin3["codigo"]]["child"][$olin4["codigo"]]["child"][$olin5["codigo"]]["child"][$olin6["codigo"]]["child"][$olin7["codigo"]]["nome"] = converter1($olin7["nome"]);
                                    $cat[$olin1["codigo"]]["child"][$olin2["codigo"]]["child"][$olin3["codigo"]]["child"][$olin4["codigo"]]["child"][$olin5["codigo"]]["child"][$olin6["codigo"]]["child"][$olin7["codigo"]]["nome2"] = converter1($olin7["nome2"]);
                                    $cat[$olin1["codigo"]]["child"][$olin2["codigo"]]["child"][$olin3["codigo"]]["child"][$olin4["codigo"]]["child"][$olin5["codigo"]]["child"][$olin6["codigo"]]["child"][$olin7["codigo"]]["nivel"] = 7;
                                    $otab8 = mysqli_query($dbc, "select codigo,nome,nome2 from GRADE_WEB where is_deleted='N' and id_parent=" . $olin7["codigo"]);
                                    while ($olin8 = mysqli_fetch_array($otab8)) {
                                        $cat[$olin1["codigo"]]["child"][$olin2["codigo"]]["child"][$olin3["codigo"]]["child"][$olin4["codigo"]]["child"][$olin5["codigo"]]["child"][$olin6["codigo"]]["child"][$olin7["codigo"]]["child"][$olin8["codigo"]]["nome"] = converter1($olin8["nome"]);
                                        $cat[$olin1["codigo"]]["child"][$olin2["codigo"]]["child"][$olin3["codigo"]]["child"][$olin4["codigo"]]["child"][$olin5["codigo"]]["child"][$olin6["codigo"]]["child"][$olin7["codigo"]]["child"][$olin8["codigo"]]["nome2"] = converter1($olin8["nome2"]);
                                        $cat[$olin1["codigo"]]["child"][$olin2["codigo"]]["child"][$olin3["codigo"]]["child"][$olin4["codigo"]]["child"][$olin5["codigo"]]["child"][$olin6["codigo"]]["child"][$olin7["codigo"]]["child"][$olin8["codigo"]]["nivel"] = 8;
                                        $otab9 = mysqli_query($dbc, "select codigo,nome,nome2 from GRADE_WEB where is_deleted='N' and id_parent=" . $olin8["codigo"]);
                                        while ($olin9 = mysqli_fetch_array($otab9)) {
                                            $cat[$olin1["codigo"]]["child"][$olin2["codigo"]]["child"][$olin3["codigo"]]["child"][$olin4["codigo"]]["child"][$olin5["codigo"]]["child"][$olin6["codigo"]]["child"][$olin7["codigo"]]["child"][$olin8["codigo"]]["child"][$olin9["codigo"]]["nome"] = converter1($olin9["nome"]);
                                            $cat[$olin1["codigo"]]["child"][$olin2["codigo"]]["child"][$olin3["codigo"]]["child"][$olin4["codigo"]]["child"][$olin5["codigo"]]["child"][$olin6["codigo"]]["child"][$olin7["codigo"]]["child"][$olin8["codigo"]]["child"][$olin9["codigo"]]["nome2"] = converter1($olin9["nome2"]);
                                            $cat[$olin1["codigo"]]["child"][$olin2["codigo"]]["child"][$olin3["codigo"]]["child"][$olin4["codigo"]]["child"][$olin5["codigo"]]["child"][$olin6["codigo"]]["child"][$olin7["codigo"]]["child"][$olin8["codigo"]]["child"][$olin9["codigo"]]["nivel"] = 9;
                                            $otab10 = mysqli_query($dbc, "select codigo,nome,nome2 from GRADE_WEB where is_deleted='N' and id_parent=" . $olin9["codigo"]);
                                            while ($olin10 = mysqli_fetch_array($otab10)) {
                                                $cat[$olin1["codigo"]]["child"][$olin2["codigo"]]["child"][$olin3["codigo"]]["child"][$olin4["codigo"]]["child"][$olin5["codigo"]]["child"][$olin6["codigo"]]["child"][$olin7["codigo"]]["child"][$olin8["codigo"]]["child"][$olin9["codigo"]]["child"][$olin10["codigo"]]["nome"] = converter1($olin10["nome"]);
                                                $cat[$olin1["codigo"]]["child"][$olin2["codigo"]]["child"][$olin3["codigo"]]["child"][$olin4["codigo"]]["child"][$olin5["codigo"]]["child"][$olin6["codigo"]]["child"][$olin7["codigo"]]["child"][$olin8["codigo"]]["child"][$olin9["codigo"]]["child"][$olin10["codigo"]]["nome2"] = converter1($olin10["nome2"]);
                                                $cat[$olin1["codigo"]]["child"][$olin2["codigo"]]["child"][$olin3["codigo"]]["child"][$olin4["codigo"]]["child"][$olin5["codigo"]]["child"][$olin6["codigo"]]["child"][$olin7["codigo"]]["child"][$olin8["codigo"]]["child"][$olin9["codigo"]]["child"][$olin10["codigo"]]["nivel"] = 10;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    $aret["cat"] = $cat;
} elseif ($nope == 91) {
    $cod = PegaVar("sku", $lpost);
    $cstr = "select codigo,nome,nome2,trim(ordem)+0 as ordem,id_parent,meta from GRADE_WEB where is_deleted='N' ";
    $otab = mysqli_query($dbc, $cstr);
    $acat = array();
    while ($olin = mysqli_fetch_array($otab)) {
        $acat[$olin["codigo"]] = array("nome" => $olin["nome"], "nome2" => $olin["nome2"], "ordem" => $olin["ordem"], "id_parent" => $olin["id_parent"], "meta" => $olin["meta"], "child" => array());
    }
    $cstr = "select codigo,nome,nome2,trim(ordem)+0 as ordem,id_parent,meta from GRADE_WEB where is_deleted='N' ";
    if ($cod) {
        $cstr .= " and codigo='$cod'";
    }
    $cstr .= " order by codigo";
    $otable = mysqli_query($dbc, $cstr);

    while ($olinha = mysqli_fetch_array($otable)) {
        $aret[] = array("sku" => $olinha["codigo"], "nom" => Converter1($olinha["nome"]), "no2" => Converter1($olinha["nome2"])
            , "ord" => $olinha["ordem"] + 0, "par" => $olinha["id_parent"], "met" => converter1($olinha["meta"]));
    }
    $aret[0]["element"] = $aret[0]["element"] + mysqli_num_rows($otable);
} elseif ($nope == 92) {
    $cod = PegaVar("sku", $lpost);
    $cstr = "select codmarc,nommarc from MARCAS_MAR where is_deleted='N' ";
    if ($cod) {
        $cstr .= " and codmarc='$cod'";
    }
    $cstr .= " order by codmarc";
    $otable = mysqli_query($dbc, $cstr);

    while ($olinha = mysqli_fetch_array($otable)) {
        $aret[] = array("sku" => $olinha[0], "nom" => Converter($olinha[1]));
    }
    $aret[0]["element"] = $aret[0]["element"] + mysqli_num_rows($otable);
} elseif ($nope == 93) {
    $cod = PegaVar("sku", $lpost);
    $cstr = "select codgru,nomgru from GRUPO_GRU where is_deleted='N' ";
    if ($cod) {
        $cstr .= " and codgru='$cod'";
    }
    $cstr .= " order by codgru";
    $otable = mysqli_query($dbc, $cstr);

    while ($olinha = mysqli_fetch_array($otable)) {
        $aret[] = array("sku" => $olinha[0], "nom" => Converter($olinha[1]));
    }
    $aret[0]["element"] = $aret[0]["element"] + mysqli_num_rows($otable);
} elseif ($nope == 95) {
    $cod = PegaVar("sku", $lpost);
    $cstr = "select codsgru,nomsgru from SGRUPO_SGR where is_deleted='N' ";
    if ($cod) {
        $cstr .= " and codsgru='$cod'";
    }
    $cstr .= " order by codsgru";
    $otable = mysqli_query($dbc, $cstr);

    while ($olinha = mysqli_fetch_array($otable)) {
        $aret[] = array("sku" => $olinha[0], "nom" => Converter($olinha[1]));
    }
    $aret[0]["element"] = $aret[0]["element"] + mysqli_num_rows($otable);
} elseif ($nope == 96) {
    $cod = PegaVar("sku", $lpost);
    $cstr = "select codesp,nomesp from GRUESP_GRE where is_deleted='N' ";
    if ($cod) {
        $cstr .= " and codesp='$cod'";
    }
    $cstr .= " order by codesp";
    $otable = mysqli_query($dbc, $cstr);

    while ($olinha = mysqli_fetch_array($otable)) {
        $aret[] = array("sku" => $olinha[0], "nom" => Converter($olinha[1]));
    }
    $aret[0]["element"] = $aret[0]["element"] + mysqli_num_rows($otable);
} elseif ($nope == 99) {
    $dat = PegaVar("dat", $lpost);
    $caux = substr($dat, 0, 4)
            . substr($dat, 5, 2)
            . substr($dat, 8, 2)
            . substr($dat, 11, 2)
            . substr($dat, 14, 2)
            . substr($dat, 17, 2);

    if ($caux + 1 < 20180101000001 || strlen($dat) != 19 || substr($dat, 4, 1) !== "-" || substr($dat, 7, 1) !== "-" || substr($dat, 10, 1) !== " " || substr($dat, 13, 1) !== ":" || substr($dat, 16, 1) !== ":") {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "40";
        $aret[0]["cestatus"] = "Data e/ou hora inválida";
        echo json_encode($aret);
        logs($aret);
        return;
    }




    $cstr = "select p.codpro as codigo,if(w.alterapro<date2unix('$dat') ,'',unix2date(w.alterapro)) as pro, if(w.alteraimg<date2unix('$dat') ,'', unix2date(w.alteraimg)) as img from " . PROD_WEB . " w 
            inner join " . PROD_PRO . " p on p.is_deleted='N' and p.codpro=w.codigo  
            where 1 and ";
    if (pegavar("mod") == "P") {
        $cstr .= " w.alterapro>=date2unix('$dat')";
    } elseif (pegavar("mod") == "I") {
        $cstr .= " w.alteraimg>=date2unix('$dat')";
    } else {
        $cstr .= " (w.alterapro>=date2unix('$dat') or w.alteraimg>=date2unix('$dat'))";
    }
    $cstr .= " order by p.codpro";
    $otable = mysqli_query($dbc, $cstr);
    while ($olinha = mysqli_fetch_array($otable)) {
        if ($olinha["pro"] >= $dat && $olinha["img"] >= $dat) {
            $nsta = 3;
        } elseif ($olinha["img"] >= $dat) {
            $nsta = 2;
        } else {
            $nsta = 1;
        }
        $aret[] = array("sku" => poedigito($olinha["codigo"]), "sta" => $nsta, "dpd" => $olinha["pro"], "dig" => $olinha["img"]);
    }
    $aret[0]["element"] = $aret[0]["element"] + mysqli_num_rows($otable);
} elseif ($nope == 111) {
    $csku = '';
    $cProd_Pro = "PRODUTO_PRO";
    $cProd_Web = "PRODUTOS_WEB";

    $cstr = "drop temporary table if exists tmpimg";
    mysqli_query($dbc, $cstr);
    $cstr = "drop temporary table if exists tmpimg1";
    mysqli_query($dbc, $cstr);
    $cstr = "create temporary table tmpimg
        select codigo,sum(ordem*imagem) as tam 
        from PRODUTOS_IMG p
        WHERE IS_DELETED='N' ";

    $cstr .= " GROUP BY codigo";
    mysqli_query($dbc, $cstr);
    $cstr = "alter table tmpimg add index codigo (codigo)";
    mysqli_query($dbc, $cstr);

    $cstr = "create temporary table tmpimg1 
        select 
            p.codpro ";
    $cstr .= ",convert(sum(if(isnull(l.proloc),0,l.qualoc)),decimal(10,0)) as estoque ";
    $cstr .= ",p.ativo
            ,w.bloqueado 
            ,if(w.nomeweb='',p.nomelong,w.nomeweb) as nome
            ,w.nimagem
            ,convert(" . $preco_env . ",decimal(15,2)) as preco
            ,convert(if(p.codpromo=0,0,p.preco5)" . ($ntax !== 0 ? "*(1+($ntax/100))" : "") . ",decimal(15,2)) as preco_promo
            ,p.precoweb" . ($ntax !== 0 ? "*(1+($ntax/100))" : "") . " as precoweb
            ,w.emax as estmax,w.permax
            ,p.grupro
            ,convert(" . $preco1_env . ",decimal(15,2)) as preco1
            ,w.ecommerc" . ($afiltro[$ncli]["config"] ? $afiltro[$ncli]["config"] : "e") . ($afiltro[$ncli]["lApenasEcommerceSim"] ? "='S' " : "!='N' ") . "
            as ecom    
                ";
    foreach ($asaldoporloja as $key => $value) {
        $cstr .= ",convert(sum(if(isnull(l.proloc) or !find_in_set(l.locloc,'$value'),0,l.qualoc)),decimal(10,0)) as $key ";
    }
    $cstr .= ",p.kitpro ";
    $cstr .= ",trim(p.gradepro) as gradepro ";
    $cstr .= ",(1 " . GeraFiltro("p", "w") . ") as filtro ";
    $cstr .= " from " . $cProd_Pro . " p 
    inner join " . $cProd_Web . " w on w.is_deleted='N'  and left(w.nomeweb,3)!='***' and w.codigo=p.codpro  
    left join LOCAL_LOC l on l.is_deleted='N' and l.proloc=p.codpro and find_in_set(l.locloc,'$clojasal')
    left join tmpimg i on i.codigo=p.codpro ";
    $cstr .= " where 1 ";
    if (pegavar("blo", $lpost) == "B") {
        $cstr .= " and ( p.ativo!='S' or w.bloqueado='Y' ) ";
    }
    if (pegavar("blo", $lpost) == "N") {
        $cstr .= " and (p.ativo='S' and w.bloqueado!='Y') ";
    }
    //filtro ecommerce ??
    //$cstr.=" and w.ecommerc" . ($afiltro[$ncli]["config"] ? $afiltro[$ncli]["config"] : "e") . ($afiltro[$ncli]["lApenasEcommerceSim"] ? "='S' " : "!='N' ") ;
    $cstr .= " group by p.codpro ";

    mysqli_query($dbc, $cstr);

    $cstr = "update tmpimg1 set estoque=if(estoque<0 or ativo!='S' or bloqueado='Y',0,estoque) ";
    mysqli_query($dbc, $cstr);

    $cstr = "update tmpimg1 set estoque=if(permax!=0,estoque*(permax/100),estoque)";
    mysqli_query($dbc, $cstr);

    $nemax = $olcfg["EMAX"] + 0;
    if ($nmax > 0) {
        $cstr = "update tmpimg1 set estoque=if($nemax>0 and $nemax<estoque,$nemax,estoque)";
        mysqli_query($dbc, $cstr);
    }

    $ngrupo = ($afiltro[$ncli]["grupoenvsaldo"][0] + 0);
    if ($ngrupo > 0) {
        $cstr = "update tmpimg1 set estoque=if(grupro=$ngrupo , $ngrupo ,floor(estoque))";
        mysqli_query($dbc, $cstr);
    }

    $ctabela = date("YmdHis") . "PrecosWeb";
    mysqli_query($dbc, "drop temporary table if exists $ctabela");
    $cstr = "create temporary table $ctabela 
        select (codpro*10)+digito(codpro) as sku
        ,preco
        ,preco_promo
        ,precoweb
        ,estoque as saldo
        ,nimagem as fotos
        ,if(filtro and ecom,if(estoque<=0,1,if(ativo='N' or bloqueado='Y',2,0)),11) as sta
        ," . ($afiltro[$ncli]["web2promo"] ? "precoweb" : "preco_promo") . " as prm 
        ,precoweb as pwb
        ,if(gradepro,1,0) as grd
        from tmpimg1
        where  1 "; //ativo='S' and bloqueado!='Y'

    if (pegavar("semsaldozero") == 1) {
        $cstr .= " and estoque>0";
    }
    mysqli_query($dbc, $cstr);
    //GRADES
    $cstr = "alter table $ctabela add index sku (sku)";
    mysqli_query($dbc, $cstr);

    $cstr = "select floor((sku/10)) as codigo from $ctabela where grd>0 ";
    $agradeb = array();
    $otabgra = mysqli_query($dbc, $cstr);
    $cstr = "drop table if exists " . $ctabela . "_grd ";
    mysqli_query($dbc, $cstr);
    $cstr = "create table " . $ctabela . "_grd (codigo varchar(30) not null default '',saldo int(10) not null default 0 )";
    mysqli_query($dbc, $cstr);

    $i = 0;
    $cgra = '';
    /*
      $cpro="9999999";
      while ($olingra = mysqli_fetch_array($otabgra)) {
      $cpro.=",".$olingra[0];
      }
     */
    while ($olingra = mysqli_fetch_array($otabgra)) {


//                from (select codpro,gradepro from " . PROD_PRO . " where is_deleted='N' " . ($afiltro[$ncli]["lEnviarInativos"] ? "" : " and ativo='S'") . " and codpro=" . $olingra[0] . ") p 
        $cstr = "select p.codpro,gp1.nomgrade as nom1,g1.tipogrd as val1,if(g.seqgrade1=g.seqgrade2,'',gp2.nomgrade) as nom2,if(g.seqgrade1=g.seqgrade2,'',g2.tipogrd) as val2,sum(g.estoque) as estoque 
                from (select codpro,gradepro from " . PROD_PRO . " where is_deleted='N' " . ($afiltro[$ncli]["lEnviarInativos"] ? "" : " and ativo='S'") . " and codpro=" . $olingra[0] . " ) p 
                inner join ESTLJGRA_PRO g on g.is_deleted='N' and  p.codpro=g.prograde  and find_in_set(g.lojgrade,'$clojasal')
                inner join GRADESEQ_PRO g1 on g1.is_deleted='N' and g1.recno=g.seqgrade1 
                inner join GRADESEQ_PRO g2 on g2.is_deleted='N' and g2.recno=g.seqgrade2 
                LEFT  JOIN GRADE_PRO gp1 on gp1.is_deleted='N' and gp1.codgrade=substring_index(p.gradepro,'-',1) 
                LEFT  JOIN GRADE_PRO gp2 on gp2.is_deleted='N' and gp2.codgrade=substring_index(p.gradepro,'-',-1) 
                where g.estoque>0 
                group by p.codpro,g1.tipogrd,g2.tipogrd 
                order by p.codpro,val1,val2 
             ";
        $otab = mysqli_query($dbc, $cstr);
        while ($olin = mysqli_fetch_row($otab)) {
//            $cgrade = poedigito($olingra[0]) . ($olin[1] ? "-" . $olin[1] : "") . ($olin[3] ? "-" . $olin[3] : "");
            $cgrade = poedigito($olin[0]) . ($olin[2] ? "-" . $olin[2] : "") . ($olin[4] ? "-" . $olin[4] : "");
            $cgrade = str_replace("  ", "-", trim($cgrade));
            $cgrade = str_replace(" ", "-", $cgrade);
            $cgrade = str_replace("--", "-", $cgrade);
            if ($i % 1000 == 0) {
                if ($cgra) {
                    mysqli_query($dbc, $cgra);
                }
                //$cgra = "insert " . $ctabela . "_grd (codigo,saldo) values ('$cgrade','" . $olin[4] . "')";
                $cgra = "insert " . $ctabela . "_grd (codigo,saldo) values ('$cgrade','" . $olin[5] . "')";
            } else {
                //$cgra .= ",('$cgrade','" . $olin[4] . "')";
                $cgra .= ",('$cgrade','" . $olin[5] . "')";
            }
            $i++;
        }
    }
    if ($cgra) {
        mysqli_query($dbc, $cgra);
    }

    ////
    //renomear 
    $ctabelaprv = "PW" . rand();
    $astr = ["drop table if exists " . $ctabelaprv, "create table " . $ctabelaprv . " select * from $ctabela"
        , "drop table if exists " . $ctabelaprv . "_grd", "create table " . $ctabelaprv . "_grd select * from $ctabela" . "_grd"];
    foreach ($astr as $value) {
        mysqli_query($dbc, $value);
    }

    $ctabela = "PrecosWeb";
    $csaida = "Dump$ncli";
    mkdir($csaida, 0750);
    $czip = $ctabela . date("YmdHis") . ".zip";
    $csaida = "./" . $csaida . "/"; //. $czip;

    $cexec = "/usr/bin/mysqldump -f -P$ccport -h$ccserver -u$ccuser -p'" . $ccpass . "' $ccdata $ctabelaprv " . $ctabelaprv . "_grd > " . $csaida . "X" . $ctabela . ".sql";
    shell_exec($cexec);
    // mudar nome interno .sql
    $cexec = "sed -e 's/" . $ctabelaprv . "/PrecosWeb/g' " . $csaida . "X" . $ctabela . ".sql > " . $csaida . $ctabela . ".sql";
    shell_exec($cexec);

    //compactar
    $cexec = "/usr/bin/zip -9 $csaida$czip " . $csaida . $ctabela . ".sql ";
    shell_exec($cexec);
    $aret["tabela"] = $ctabela;
    $aret["compress"] = $csaida . $czip;
    $cserver = "https://" . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strripos($_SERVER['REQUEST_URI'], "/", -1) + 1);
    $aret['request'] = $cserver . substr($csaida, 2);

    unlink($csaida . $ctabela . ".sql");
    unlink($csaida . "X" . $ctabela . ".sql");

    mysqli_query($dbc, "drop table if exists " . $ctabelaprv);
    mysqli_query($dbc, "drop table if exists " . $ctabelaprv . "_grd");
    mysqli_query($dbc, "drop table if exists " . $ctabela);
    mysqli_query($dbc, "drop table if exists " . $ctabela . "_grd");

    if (pegavar("download") == 1) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="PrecosWeb.zip"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($csaida));
        ob_clean();
        flush();
        readfile($csaida);
        exit();
    }
} elseif ($nope == 1 || $nope == 11) {
    $csku = pegavar("sku", $lpost);
    $cProd_Pro = ($nope == 1 ? PROD_PRO : "PRODUTO_PRO");
    $cProd_Web = ($nope == 1 ? PROD_WEB : "PRODUTOS_WEB");
    if (!$csku && $afiltro[$ncli]["lMuitosItens"]) {
        $cstr = "select count(*) as total
                    from " . $cProd_Pro . " p 
                    inner join " . $cProd_Web . " w on w.is_deleted='N' and left(w.nomeweb,3)!='***'  and w.codigo=p.codpro  
                    where 1  ";
        if ($csku) {
            if (strpos($csku, ",") == false) {
                $cstr .= " and p.codpro='" . tiradigito($csku) . "'";
            } else {
                $aaux = explode(",", $csku);
                $ccod = "9999999999";
                foreach ($aaux as $value) {
                    if ($value) {
                        $ccod .= "," . tiradigito($value);
                    }
                }
                $cstr .= " and find_in_set(p.codpro,'$ccod')";
            }
        } else {
            if (pegavar("blo", $lpost) == "B") {
                $cstr .= " and ( p.ativo!='S' or w.bloqueado='Y' ) ";
            }
            if (pegavar("blo", $lpost) == "N") {
                $cstr .= " and (p.ativo='S' and w.bloqueado!='Y') ";
            }
        }
        $nitens = mysqli_fetch_row(mysqli_query($dbc, $cstr))[0];

        if ($nitens > $afiltro[$ncli]["lMuitosItens"]) {


            $aret[0]["data_ret"] = date('Y-m-d H:i:s');
            $aret[0]["estatus"] = "99";
            $aret[0]["cestatus"] = "Muitos produtos no cliente para serem consultados em uma única vez. Max " . $afiltro[$ncli]["lMuitosItens"];
            echo json_encode($aret);
            logs($aret);
            return;
        }
    }

    $cstr = "drop temporary table if exists tmpimg";
    mysqli_query($dbc, $cstr);
    $cstr = "drop temporary table if exists tmpimg1";
    mysqli_query($dbc, $cstr);
    $cstr = "create temporary table tmpimg
        select codigo,sum(ordem*imagem) as tam 
        from PRODUTOS_IMG p
        WHERE IS_DELETED='N' ";
    if ($csku) {
        if (strpos($csku, ",") == false) {
            $cstr .= " and codigo='" . tiradigito($csku) . "'";
        } else {
            $aaux = explode(",", $csku);
            $ccod = "9999999999";
            foreach ($aaux as $value) {
                if ($value) {
                    $ccod .= "," . tiradigito($value);
                }
            }
            $cstr .= " and find_in_set(codigo,'$ccod')";
        }
    }
    $cstr .= " GROUP BY codigo";
    mysqli_query($dbc, $cstr);
    $cstr = "alter table tmpimg add index codigo (codigo)";
    mysqli_query($dbc, $cstr);

    if ($afiltro[$ncli]["enviakit"]) {
        $asql = array();
        $cstr1 = "drop temporary table if exists kitp";
        mysqli_query($dbc, $cstr1);
        $cstr1 = "create temporary table kitp 
                        select p.codpro ,sum(if(isnull(l.qualoc),0,l.qualoc)) as qualoc,k.prokit,k.quakit 
                        from PRODUTO_PRO p 
                        inner join PROKIT_PRO k on k.is_deleted='N' and k.codkit=p.codpro
                        left  join LOCAL_LOC l on p.is_deleted='N' and l.qualoc>0 and k.prokit=l.proloc and p.kitpro='S' ";
        if ($clojasal && $clojasal !== "9999999") {
            $cstr1 .= " and find_in_set(l.locloc,'$clojasal')";
        }
        $cstr1 .= " WHERE p.IS_DELETED='N' ";
        if ($csku) {
            if (strpos($csku, ",") == false) {
                $cstr1 .= " and p.codpro='" . tiradigito($csku) . "'";
            } else {
                $aaux = explode(",", $csku);
                $ccod = "9999999999";
                foreach ($aaux as $value) {
                    if ($value) {
                        $ccod .= "," . tiradigito($value);
                    }
                }
                $cstr1 .= " and find_in_set(p.codpro,'$ccod')";
            }
        }
        $cstr1 .= " group by p.codpro,k.prokit";
        mysqli_query($dbc, $cstr1);
        $cstr1 = "update kitp set qualoc=qualoc/quakit";
        mysqli_query($dbc, $cstr1);
    }



    $cstr = "create temporary table tmpimg1 
        select 
            p.codpro ";
    if ($afiltro[$ncli]["enviakit"]) {
        $cstr .= ",if(p.kitpro='S',k.qualoc,convert(sum(if(isnull(l.proloc),0,l.qualoc)),decimal(10,0))) as estoque ";
    } else {
        $cstr .= ",convert(sum(if(isnull(l.proloc),0,l.qualoc)),decimal(10,0)) as estoque ";
    }
    $cstr .= ",p.ativo
            ,w.bloqueado 
            ,if(w.nomeweb='',p.nomelong,w.nomeweb) as nome
            ,w.nimagem
            ,convert(" . $preco_env . ",decimal(15,2)) as preco
            ,convert(if(p.codpromo=0,0,p.preco5)" . ($ntax !== 0 ? "*(1+($ntax/100))" : "") . ",decimal(15,2)) as preco_promo
            ,p.precoweb" . ($ntax !== 0 ? "*(1+($ntax/100))" : "") . " as precoweb
            ,if(isnull(wm.memo),'',wm.memo) as texto 
            ,if(isnull(wm1.memo),'',wm1.memo) as mini
            ,w.link2 as fabrica
            ,w.link1 as video
            ,p.largura
            ,p.altura
            ,p.profund
            ,p.pespro as peso
            ,w.emax as estmax,w.permax
            ,convert(if(isnull(i.codigo),0,i.TAM),decimal(12,0)) as tam
            ,p.grupro,p.marcpro,w.grade
            ,trim(p.gradepro) as gradepro 
            ,convert(" . $preco1_env . ",decimal(15,2)) as preco1
            ";
    foreach ($asaldoporloja as $key => $value) {
        $cstr .= ",convert(sum(if(isnull(l.proloc) or !find_in_set(l.locloc,'$value'),0,l.qualoc)),decimal(10,0)) as $key ";
    }
    $cstr .= ",p.kitpro ";
    $cstr .= " from " . $cProd_Pro . " p 
    inner join " . $cProd_Web . " w on w.is_deleted='N'  and left(w.nomeweb,3)!='***' and w.codigo=p.codpro  
    left  join " . $cProd_Web . "_MEMO wm on w.recno=wm.recno  and wm.col_name='TEXTO'
    left  join " . $cProd_Web . "_MEMO wm1 on w.recno=wm1.recno  and wm1.col_name='MINIDESC'
    left join LOCAL_LOC l on l.is_deleted='N' and l.proloc=p.codpro and find_in_set(l.locloc,'$clojasal')
    left join tmpimg i on i.codigo=p.codpro ";
    if ($afiltro[$ncli]["enviakit"]) {
        $cstr .= "left join ( select * from (select codpro,min(qualoc) as qualoc from kitp group by codpro) x ) k on k.codpro=p.codpro ";
    }
    $cstr .= " where 1 ";
    if ($csku) {
        if (strpos($csku, ",") == false) {
            $cstr .= " and p.codpro='" . tiradigito($csku) . "'";
        } else {
            $aaux = explode(",", $csku);
            $ccod = "9999999999";
            foreach ($aaux as $value) {
                if ($value) {
                    $ccod .= "," . tiradigito($value);
                }
            }
            $cstr .= " and find_in_set(p.codpro,'$ccod')";
        }
    } else {
        if (pegavar("blo", $lpost) == "B") {
            $cstr .= " and ( p.ativo!='S' or w.bloqueado='Y' ) ";
        }
        if (pegavar("blo", $lpost) == "N") {
            $cstr .= " and (p.ativo='S' and w.bloqueado!='Y') ";
        }
    }
    $cstr .= " group by p.codpro";

    mysqli_query($dbc, $cstr);
    $cstr = "update tmpimg1 set tam=crc32(tam+(char(ativo)*10)+(char(bloqueado)*100)+nome+nimagem+preco*100+texto+mini+fabrica
+video+largura*1000+altura*1000+profund*1000+peso*1000+estmax+permax*1000+grupro+marcpro+grade)";
    mysqli_query($dbc, $cstr);
    $cstr = "select * from tmpimg1";
    $otable = mysqli_query($dbc, $cstr);
    while ($olinha = mysqli_fetch_array($otable)) {
        $nsta = 0;
        $nsal = $olinha["estoque"] < 0 ? 0 : $olinha["estoque"];
        if ($olinha["ativo"] != "S" || $olinha["bloqueado"] == "Y") {
            $nsta = 2;
            $nsal = 0;
        } elseif ($olinha["estoque"] <= 0) {
            $nsta = 1;
            $nsal = 0;
        }
        if ($olinha["permax"] != 0) {
            $nsal *= $olinha["permax"] / 100;
        }
        if ($olcfg["EMAX"] > 0 && $olcfg["EMAX"] < $nsal) {
            $nsal = $olcfg["EMAX"];
        }
        if ($olinha["estmax"] > 0 && $olinha["estmax"] < $nsal) {
            $nsal = $olinha["estmax"];
        }

        $nsal = $afiltro[$ncli]["grupoenvsaldo"][0] + 0 == $olinha["grupro"] + 0 ? $afiltro[$ncli]["grupoenvsaldo"][1] : floor($nsal);

        $agrade = array();
        if (trim($olinha["gradepro"]) !== '') {
            $cstr = "select p.codpro,p.nom1,p.val1,p.nom2,p.val2, sum(if(isnull(g.prograde),0,g.estoque)) as saldo from (
                select p.codpro,gp1.nomgrade as nom1,g1.tipogrd as val1,g1.seqgrade as seqgrade1,if(substring_index(p.gradepro,'-',1)=substring_index(p.gradepro,'-',-1) or substring_index(p.gradepro,'-',-1)='','',gp2.nomgrade) as nom2
                ,if(substring_index(p.gradepro,'-',1)=substring_index(p.gradepro,'-',-1) or substring_index(p.gradepro,'-',-1)='','',g2.tipogrd) as val2
                ,if(substring_index(p.gradepro,'-',1)=substring_index(p.gradepro,'-',-1) or substring_index(p.gradepro,'-',-1)='',g1.seqgrade,g2.seqgrade) as seqgrade2
                from (
                select codpro,gradepro from " . $cProd_Pro . " where is_deleted='N' and codpro=" . $olinha["codpro"] . "
                ) p 
                LEFT  JOIN GRADE_PRO gp1 on gp1.is_deleted='N' and gp1.codgrade=substring_index(p.gradepro,'-',1) 
                LEFT  JOIN GRADE_PRO gp2 on gp2.is_deleted='N' and gp2.codgrade=substring_index(p.gradepro,'-',-1) 

                left join GRADESEQ_PRO g1 on g1.grade=gp1.codgrade 
                left join GRADESEQ_PRO g2 on g2.grade=gp2.codgrade 
                where 1
                group by p.codpro,val1,val2 
                order by val1,val2 ) p

                left join ESTLJGRA_PRO g on g.is_deleted='N' 
                and p.codpro=g.prograde 
                and g.seqgrade1=p.seqgrade1
                and g.seqgrade2=p.seqgrade2
                and find_in_set(g.lojgrade,'$clojasal')
                group by p.codpro,p.val1,p.val2";
            $otab = mysqli_query($dbc, $cstr);
            while ($olin = mysqli_fetch_array($otab)) {
                if ($olin["saldo"] <= 0) {
                    continue;
                }
                $agrade[] = array($olin["nom1"], $olin["val1"], $olin["nom2"], $olin["val2"], floor($olin["saldo"]));
            }
        }
        if ($nope == 1) {
            $aaux = array("sku" => poedigito($olinha["codpro"]), "sta" => $nsta, "sal" => number_format($nsal, 0, "", ""), "crc" => $olinha["tam"], "grd" => $agrade, "lgd" => ($olinha["gradepro"] ? 1 : 0), "kit" => ($olinha["kitpro"] ? 1 : 0));
            foreach ($asaldoporloja as $key => $value) {
                $aaux[$key] = $olinha[$key];
            }
            $aret[] = $aaux;
        } else {
            $aaux = array("sku" => poedigito($olinha["codpro"]), "sta" => $nsta, "sal" => number_format($nsal, 0, "", ""), "pre" => $olinha["preco"], "prm" => $olinha[$afiltro[$ncli]["web2promo"] ? "precoweb" : "preco_promo"], "grd" => $agrade, "lgd" => ($olinha["gradepro"] ? 1 : 0), "kit" => ($olinha["kitpro"] ? 1 : 0));
            if ($lpreco1_env) {
                $aaux["pre1"] = $olinha["preco1"];
            }
            foreach ($asaldoporloja as $key => $value) {
                $aaux[$key] = $olinha[$key];
            }
            $aret[] = $aaux;
        }
        $aret[0]["element"] = $aret[0]["element"] + 1;
    }
    if ($aret[0]["element"] == 1) {
        $aret[0]["estatus"] = 11;
        $aret[0]["cestatus"] = "Inexistente ou bloqueado";
    }
} elseif ($nope == 4) {
    $ntam = PegaVar("tam", $lpost);
    $npag = PegaVar("pag", $lpost);
    $csku = "";
    if (!$ntam) {
        $ntam = 100;
    }
    if ($ntam > $afiltro[$ncli]["lMuitosItens"]) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "40";
        $aret[0]["cestatus"] = "Parametros invalidos. Tamanho da página  Max " . $afiltro[$ncli]["lMuitosItens"] . " .";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $cstr = "select count(*) as total
    from " . PROD_PRO . " p 
    inner join " . PROD_WEB . " w on w.is_deleted='N'  and w.codigo=p.codpro   
    where 1  ";
    if (pegavar("blo", $lpost) == "B") {
        $cstr .= " and ( p.ativo!='S' or w.bloqueado='Y' ) ";
    }
    if (pegavar("blo", $lpost) == "N") {
        $cstr .= " and (p.ativo='S' and w.bloqueado!='Y') ";
    }

    $otable = mysqli_query($dbc, $cstr);
    $olinha = mysqli_fetch_row($otable);

    $ntotal = $olinha[0];
    if (!$npag) {
        $aret[] = array("pgs" => floor($ntotal / $ntam) + ($ntotal % $ntam > 0 ? 1 : 0), "pag" => 0);
        $aret[0]["element"] = $aret[0]["element"] + 1;
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        echo json_encode($aret);
        logs($aret);
        return;
    }

    $aret[] = array("pgs" => floor($ntotal / $ntam) + ($ntotal % $ntam > 0 ? 1 : 0), "pag" => $npag);
    $aret[0]["element"] = $aret[0]["element"] + 1;
    $aret[0]["data_ret"] = date('Y-m-d H:i:s');

    $cstr = "drop temporary table if exists tmpimg";
    mysqli_query($dbc, $cstr);
    $cstr = "drop temporary table if exists tmpimg1";
    mysqli_query($dbc, $cstr);
    $cstr = "create temporary table tmpimg
        select codigo,sum(ordem*imagem) as tam 
        from PRODUTOS_IMG 
        WHERE IS_DELETED='N'
        GROUP BY codigo";
    mysqli_query($dbc, $cstr);

    $cstr = "alter table tmpimg add index codigo (codigo)";
    mysqli_query($dbc, $cstr);

    if ($afiltro[$ncli]["enviakit"]) {
        $asql = array();
        $cstr1 = "drop temporary table if exists kitp";
        mysqli_query($dbc, $cstr1);
        $cstr1 = "create temporary table kitp 
                        select p.codpro ,sum(if(isnull(l.qualoc),0,l.qualoc)) as qualoc,k.prokit,k.quakit 
                        from PRODUTO_PRO p 
                        inner join PROKIT_PRO k on k.is_deleted='N' and k.codkit=p.codpro
                        left  join LOCAL_LOC l on p.is_deleted='N' and l.qualoc>0 and k.prokit=l.proloc and p.kitpro='S' ";
        if ($clojasal && $clojasal !== "9999999") {
            $cstr1 .= " and find_in_set(l.locloc,'$clojasal')";
        }
        $cstr1 .= " WHERE p.IS_DELETED='N' group by p.codpro,k.prokit";
        mysqli_query($dbc, $cstr1);
        $cstr1 = "update kitp set qualoc=qualoc/quakit";
        mysqli_query($dbc, $cstr1);
    }


    $cstr = "create temporary table tmpimg1 
        select 
            p.codpro ";
    if ($afiltro[$ncli]["enviakit"]) {
        $cstr .= ",if(p.kitpro='S',k.qualoc,convert(sum(if(isnull(l.proloc),0,l.qualoc)),decimal(10,0))) as estoque ";
    } else {
        $cstr .= ",convert(sum(if(isnull(l.proloc),0,l.qualoc)),decimal(10,0)) as estoque ";
    }

    $cstr .= ", p.ativo
            ,w.bloqueado 
            ,if(w.nomeweb='',p.nomelong,w.nomeweb) as nome
            ,w.nimagem
            ," . $preco_env . " as preco
            ,convert(if(p.codpromo=0,0,p.preco5)" . ($ntax !== 0 ? "*(1+($ntax/100))" : "") . ",decimal(15,2)) as preco_promo
            ,p.precoweb" . ($ntax !== 0 ? "*(1+($ntax/100))" : "") . " as precoweb
            ,if(isnull(wm.memo),'',wm.memo) as texto 
            ,if(isnull(wm1.memo),'',wm1.memo) as mini
            ,w.link2 as fabrica
            ,w.link1 as video
            ,p.largura
            ,p.altura
            ,p.profund
            ,p.pespro as peso,p.ncm
            ,w.emax as estmax,w.permax
            ,convert(if(isnull(i.codigo),0,i.TAM),decimal(12,0)) as tam
            ,p.kitpro,p.grupro,p.marcpro,w.grade
            ," . $preco1_env . " as preco1
            ";

    foreach ($asaldoporloja as $key => $value) {
        $cstr .= ",convert(sum(if(isnull(l.proloc) or !find_in_set(l.locloc,'$value'),0,l.qualoc)),decimal(10,0)) as $key ";
    }

    $cstr .= " 
    from (
    select * from " . PROD_PRO . " p 

    where 1 ";
    if ($csku) {
        $cstr .= " and p.codpro='" . tiradigito($csku) . "'";
    }
    $cstr .= " 
        limit " . ( ($npag - 1) * $ntam ) . ",$ntam 
    ) p 
    inner join " . PROD_WEB . " w on w.is_deleted='N'  and w.codigo=p.codpro   
    left  join " . PROD_WEB . "_MEMO wm on w.recno=wm.recno  and wm.col_name='TEXTO'
    left  join " . PROD_WEB . "_MEMO wm1 on w.recno=wm1.recno  and wm1.col_name='MINIDESC'
    left join LOCAL_LOC l on l.is_deleted='N' and l.proloc=p.codpro and find_in_set(l.locloc,'$clojasal')
    left join tmpimg i on i.codigo=p.codpro ";
    if ($afiltro[$ncli]["enviakit"]) {
        $cstr .= "left join ( select * from (select codpro,min(qualoc) as qualoc from kitp group by codpro) x ) k on k.codpro=p.codpro ";
    }
    $cstr .= "";

    if (!$csku) {
        if (pegavar("blo", $lpost) == "B") {
            $cstr .= " and ( p.ativo!='S' or w.bloqueado='Y' ) ";
        }
        if (pegavar("blo", $lpost) == "N") {
            $cstr .= " and (p.ativo='S' and w.bloqueado!='Y') ";
        }
    }
    $cstr .= " group by p.codpro";
    $cstr .= " order by p.codpro ";

    mysqli_query($dbc, $cstr);

    $cstr = "update tmpimg1 set tam=crc32(tam+(char(ativo)*10)+(char(bloqueado)*100)+nome+nimagem+preco*100+texto+mini+fabrica
+video+largura*1000+altura*1000+profund*1000+peso*1000+estmax+permax*1000+grupro+marcpro+grade)";
    mysqli_query($dbc, $cstr);
    $cstr = "select * from tmpimg1";
    $otable = mysqli_query($dbc, $cstr);

    mysqli_query($dbc, "drop temporary table if exists tmpimg2 ");
    mysqli_query($dbc, "create temporary table tmpimg2 select proloc,convert(sum(qualoc),decimal(10,0)) as qualoc from LOCAL_LOC l where $cor and l.is_deleted='N' group by proloc");
    mysqli_query($dbc, "alter table tmpimg2 add index proloc (proloc)");

    mysqli_query($dbc, "update tmpimg1 p,tmpimg2 l set p.estoque=p.estoque-l.qualoc 
    where p.codpro=l.proloc");

    $cstr = "select * from tmpimg1";
    $otable = mysqli_query($dbc, $cstr);

    while ($olinha = mysqli_fetch_array($otable)) {
        $nsta = 0;
        $nsal = $olinha["estoque"] < 0 ? 0 : $olinha["estoque"];

        if ($olinha["ativo"] != "S" || $olinha["bloqueado"] == "Y") {
            $nsta = 2;
            $nsal = 0;
        } elseif ($olinha["estoque"] <= 0) {
            $nsta = 1;
            $nsal = 0;
        }
        if ($olinha["permax"] != 0) {
            $nsal *= $olinha["permax"] / 100;
        }
        if ($olcfg["EMAX"] > 0 && $olcfg["EMAX"] < $nsal) {
            $nsal = $olcfg["EMAX"];
        }
        if ($olinha["estmax"] > 0 && $olinha["estmax"] < $nsal) {
            $nsal = $olinha["estmax"];
        }
        $agrade = array();
        if ($nsal > 0) {
            if ($olinha["gradepro"]) {

                $cstr = "select gp1.nomgrade as nom1,g1.tipogrd as val1,if(g.seqgrade1=g.seqgrade2,'',gp2.nomgrade) as nom2,if(g.seqgrade1=g.seqgrade2,'',g2.tipogrd) as val2
                ,sum(g.estoque) as estoque 
                from (select codpro,gradepro from " . PROD_PRO . " where is_deleted='N' and codpro=" . $olinha["codpro"] . ") p 
                inner join ESTLJGRA_PRO g on g.is_deleted='N' and  p.codpro=g.prograde  and find_in_set(g.lojgrade,'$clojasal')
                inner join GRADESEQ_PRO g1 on g1.recno=g.seqgrade1 
                inner join GRADESEQ_PRO g2 on g2.recno=g.seqgrade2 
                LEFT  JOIN GRADE_PRO gp1 on gp1.is_deleted='N' and gp1.codgrade=substring_index(p.gradepro,'-',1) 
                LEFT  JOIN GRADE_PRO gp2 on gp2.is_deleted='N' and gp2.codgrade=substring_index(p.gradepro,'-',-1) 
                where 1
                group by p.codpro,g1.tipogrd,g2.tipogrd 
                order by val1,val2 ";
                $otab = mysqli_query($dbc, $cstr);
                while ($olin = mysqli_fetch_row($otab)) {
                    $agrade[] = array($olin[0], $olin[1], $olin[2], $olin[3], floor($olin[4]));
                }
            }
        }
        $aaux = array("sku" => poedigito($olinha["codpro"]), "sta" => $nsta, "sal" => number_format($nsal, 0, "", ""), "grd" => $agrade, "crc" => $olinha["tam"], "lgd" => (trim($olinha["gredepro"]) ? 1 : 0), "kit" => $olinha["kitpro"] == "S" ? 1 : 0);
        foreach ($asaldoporloja as $key => $value) {
            $aaux[$key] = $olinha[$key];
        }
        $aret[] = $aaux;
        $aret[0]["element"] = $aret[0]["element"] + 1;
    }
} elseif ($nope == 2) {
    if (pegavar("sku", $lpost) == '') {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "40";
        $aret[0]["cestatus"] = "Parametros invalidos";
        echo json_encode($aret);
        logs($aret);
        return;
    }

    if ($afiltro[$ncli]["enviakit"]) {
        $asql = array();
        $cstr1 = "drop temporary table if exists kitp";
        mysqli_query($dbc, $cstr1);
        $cstr1 = "create temporary table kitp 
                        select p.codpro ,sum(if(isnull(l.qualoc),0,l.qualoc)) as qualoc,k.prokit,k.quakit 
                        from PRODUTO_PRO p 
                        inner join PROKIT_PRO k on k.is_deleted='N' and k.codkit=p.codpro
                        left  join LOCAL_LOC l on p.is_deleted='N' and l.qualoc>0 and k.prokit=l.proloc and p.kitpro='S' ";
        if ($clojasal && $clojasal !== "9999999") {
            $cstr1 .= " and find_in_set(l.locloc,'$clojasal')";
        }
        $cstr1 .= " WHERE p.IS_DELETED='N' group by p.codpro,k.prokit";
        mysqli_query($dbc, $cstr1);
        $cstr1 = "update kitp set qualoc=qualoc/quakit";
        mysqli_query($dbc, $cstr1);
    }



    if (pegavar("sku", $lpost) !== false) {
        $asku = explode(",", pegavar("sku", $lpost));
    } else {
        $asku = array(pegavar("sku", true));
    }
    foreach ($asku as $value) {
        $aaux = array("", tiradigito($value));
        $cstr = "select p.codpro ";
        if ($afiltro[$ncli]["enviakit"]) {
            $cstr .= ",if(p.kitpro='S',k.qualoc,convert(sum(if(isnull(l.proloc),0,l.qualoc)),decimal(10,0))) as estoque ";
        } else {
            $cstr .= ",convert(sum(if(isnull(l.proloc),0,l.qualoc)),decimal(10,0)) as estoque ";
        }

        $cstr .= ",p.ativo,w.bloqueado 
            ,if(w.nomeweb$idi='',p.nomelong,w.nomeweb$idi) as nome
            ,if(w.nomeweb2='',p.nomelong,w.nomeweb2) as nome2
            ,w.nimagem
        ," . $preco_env . " as preco
            ,convert(if(p.codpromo=0,0,p.preco5)" . ($ntax !== 0 ? "*(1+($ntax/100))" : "") . ",decimal(15,2)) as preco_promo
            ,p.precoweb" . ($ntax !== 0 ? "*(1+($ntax/100))" : "") . " as precoweb
    ,if(isnull(wm.memo),'',wm.memo) as texto ,if(isnull(wm1.memo),'',wm1.memo) as mini,w.link2 as fabrica,w.link1 as video
    ,if(isnull(wm_2.memo),'',wm_2.memo) as texto2 ,if(isnull(wm1_2.memo),'',wm1_2.memo) as mini2
    ,p.largura,p.altura,p.profund,p.pespro as peso,w.emax as estmax,w.permax
        ,p.grupro as cat,p.gruesp as catsp,p.sgrupro catsb,p.marcpro as mar,concat(w.grade,',',w.grade2,',',w.grade3,',',w.grade4,',',w.grade5,',',w.grade6,',',w.grade7,',',w.grade8,',',w.grade9,',',w.grade10) as caw
    ,trim(p.gradepro) as gradepro
    ,p.decreto,p.ncm
    ,w.etiqueta
        ," . $preco1_env . " as preco1
    ";
        foreach ($asaldoporloja as $key => $value) {
            $cstr .= ",convert(sum(if(isnull(l.proloc) or !find_in_set(l.locloc,'$value'),0,l.qualoc)),decimal(10,0)) as $key ";
        }
        $cstr .= ",p.kitpro ";

        $cstr .= " from " . PROD_PRO . " p 
    inner join " . PROD_WEB . " w on w.is_deleted='N'  and w.codigo=p.codpro   
    left  join " . PROD_WEB . "_MEMO wm on w.recno=wm.recno  and wm.col_name='TEXTO$idi'
    left  join " . PROD_WEB . "_MEMO wm1 on w.recno=wm1.recno  and wm1.col_name='MINIDESC$idi'
    left  join " . PROD_WEB . "_MEMO wm_2 on w.recno=wm_2.recno  and wm_2.col_name='TEXTO2'
    left  join " . PROD_WEB . "_MEMO wm1_2 on w.recno=wm1_2.recno  and wm1_2.col_name='MINIDESC2'
    left join LOCAL_LOC l on  l.is_deleted='N' and l.proloc=p.codpro and find_in_set(l.locloc,'$clojasal')
    ";
        if ($afiltro[$ncli]["enviakit"]) {
            $cstr .= "left join ( select * from (select codpro,min(qualoc) as qualoc from kitp group by codpro) x ) k on k.codpro=p.codpro ";
        }
        $cstr .= " where 1  and p.codpro=" . $aaux[1];
        $otable = mysqli_query($dbc, $cstr);
        $olinha = mysqli_fetch_array($otable);

        $nsta = 0;

        if (mysqli_num_rows($otable) == 0 || !$olinha["codpro"]) {
            $nsta = 9;
            $aret[] = array("sku" => poedigito($aaux[1]), "sta" => $nsta, "sal" => 0);
        } else {
            if ($olinha["estoque"] == 0) {
                $nsta = 1;
            } elseif ($olinha["ativo"] != "S" || $olinha["bloqueado"] == "Y") {
                $nsta = 2;
            }
            $adet = array();
            $adet["sku"] = poedigito($olinha["codpro"]);
            $adet["sta"] = $nsta;
            $nsal = $olinha["estoque"];
            if ($olinha["permax"] > 0) {
                $nper = floor($olinha["estoque"] * ($olinha["permax"] / 100));
                if ($nsal > $nper) {
                    $nsal = $nper;
                }
            }
            if ($olcfg["EMAX"] > 0 && $olcfg["EMAX"] < $nsal) {
                $nsal = $olcfg["EMAX"];
            }
            if ($olinha["estmax"] > 0 && $nsal > $olinha["estmax"]) {
                $nsal = $olinha["estmax"];
            }
            $adet["sal"] = $afiltro[$ncli]["grupoenvsaldo"][0] + 0 == $olinha["cat"] + 0 ? $afiltro[$ncli]["grupoenvsaldo"][1] : floor($nsal);
            $adet["blo"] = $olinha["ativo"] != "S" || $olinha["bloqueado"] == "Y" ? 1 : 0;
            $adet["nom"] = urlencode(converter1($olinha["nome"]));
            if ($afiltro[$ncli]["idioma"] == 9) {
                $adet["nom_es"] = urlencode(converter1($olinha["nome2"]));
                $adet["des_es"] = urlencode(converter1($olinha["texto2"]));
                $adet["bre_es"] = urlencode(converter1($olinha["mini2"]));
            }
            $adet["img"] = $olinha["nimagem"];
            $adet["pre"] = number_format($olinha["preco"], 2);
            if ($lpreco1_env) {
                $adet["pre1"] = number_format($olinha["preco1"], 2);
            }
            $adet["prm"] = number_format($olinha[$afiltro[$ncli]["web2promo"] ? "precoweb" : "preco_promo"], 2);
            $adet["pwb"] = number_format($olinha["precoweb"], 2);
            $adet["des"] = urlencode(converter1($olinha["texto"]));
            $adet["bre"] = urlencode(converter1($olinha["mini"]));
            $adet["ufa"] = urlencode(converter1($olinha["fabrica"]));
            $adet["uvi"] = urlencode(converter1($olinha["video"]));
            $adet["alt"] = $olinha["altura"];
            $adet["lgr"] = $olinha["largura"];
            $adet["prd"] = $olinha["profund"];
            $adet["pes"] = $olinha["peso"];
            $adet["dcr"] = $olinha["decreto"];
            $adet["kit"] = $olinha["kitpro"] == "S" ? 1 : 0;
            $adet["lgd"] = (trim($olinha["gradepro"]) ? 1 : 0);
            $adet["ncm"] = $olinha["ncm"];
            foreach ($asaldoporloja as $key => $value) {
                $adet[$key] = $olinha[$key];
            }


            $otable1 = mysqli_query($dbc, "select c.nome as nome,v.valor$idi as valor 
        	,c.nome2 as nome2,v.valor2 as valor2 
                from CATVALOR_WEB v 
                inner join CATEGORI_WEB c on c.codigo=v.categoria and c.is_deleted='N' 
                where v.is_deleted='N' and v.codigo=" . $aaux[1]);
            $caux = '';
            $caux_2 = '';
            while ($olinha1 = mysqli_fetch_array($otable1)) {
                $caux1 = str_replace(";", " ", str_replace(":", " ", converter1($olinha1["nome"]))) . ":" . str_replace(";", " ", str_replace(":", " ", $olinha1["valor"]));
                $caux .= ($caux1 == ':' ? '' : ";") . $caux1;
                $caux1 = str_replace(";", " ", str_replace(":", " ", converter1($olinha1["nome2"]))) . ":" . str_replace(";", " ", str_replace(":", " ", $olinha1["valor2"]));
                $caux_2 .= ($caux1 == ':' ? '' : ";") . $caux1;
            }
            $adet["car"] = urlencode(converter1($caux));
            if ($afiltro[$ncli]["idioma"] == 9) {
                $adet["car_es"] = urlencode(converter1($caux_2));
            }
            $agrade = array();
            $cstr = "select gp1.nomgrade as nom1,g1.tipogrd as val1,if(g.seqgrade1=g.seqgrade2,'',gp2.nomgrade) as nom2,if(g.seqgrade1=g.seqgrade2,'',g2.tipogrd) as val2,sum(g.estoque) as estoque 
                from (select codpro,gradepro from " . PROD_PRO . " p where is_deleted='N' " . ($afiltro[$ncli]["lEnviarInativos"] ? "" : " and p.ativo='S'") . " and codpro=" . $olinha["codpro"] . ") p 
                inner join ESTLJGRA_PRO g on g.is_deleted='N' and  p.codpro=g.prograde  and find_in_set(g.lojgrade,'$clojasal')
                inner join GRADESEQ_PRO g1 on g1.recno=g.seqgrade1 
                inner join GRADESEQ_PRO g2 on g2.recno=g.seqgrade2 
                LEFT  JOIN GRADE_PRO gp1 on gp1.is_deleted='N' and gp1.codgrade=substring_index(p.gradepro,'-',1) 
                LEFT  JOIN GRADE_PRO gp2 on gp2.is_deleted='N' and gp2.codgrade=substring_index(p.gradepro,'-',-1) 
                where g.estoque>0 
                group by p.codpro,g1.tipogrd,g2.tipogrd 
                order by val1,val2 ";
            $otab = mysqli_query($dbc, $cstr);
            while ($olin = mysqli_fetch_row($otab)) {
                $agrade[] = array($olin[0], $olin[1], $olin[2], $olin[3], floor($olin[4]));
            }
            $adet["grd"] = $agrade;
            $adet["cat"] = $olinha["cat"];
            $adet["catsp"] = $olinha["catsp"];
            $adet["catsb"] = $olinha["catsb"];

            $aaux = explode(",", $olinha["caw"]);
            $cret = '';
            $acaw = array();

            foreach ($aaux as $value) {
                if ($value + 0 > 0) {
                    $cret .= "," . $value;
                    $acaw1 = array($value);
                    $ngrade = $value;
                    while (true) {
                        $ngrade = mysqli_fetch_row(mysqli_query($dbc, "select id_parent from GRADE_WEB WHERE CODIGO=$ngrade AND IS_DELETED='N' "))[0];
                        if (!$ngrade || $ngrade == 0) {
                            break;
                            ;
                        }
                        $acaw1[] = $ngrade;
                    }
                    $acaw[] = $acaw1;
                }
            }
            $adet["caw"] = substr($cret, 1);
            $adet["caw_tree"] = $acaw;

            $adet["mar"] = $olinha["mar"];

            $aaux = explode(",", $olinha["etiqueta"]);
            $adet["tag"] = array();

            foreach ($aaux as $value) {
                $caux = trim($value);
                if ($caux) {
                    $adet["tag"][] = urlencode(converter1($caux));
                }
            }



            $aret[] = $adet;
            $aret[0]["element"] = $aret[0]["element"] + 1;
        }
    }
    if ($aret[0]["element"] == 1) {
        $aret[0]["estatus"] = 11;
        $aret[0]["cestatus"] = "Inexistente ou bloqueado";
    }
} elseif ($nope == 22) {

    if ($OP_22) {
        $agora = date("H:i:s", time());
        if (!($agora > $OP_22[0] && $agora < $OP_22[1])) {
            $aret[0]["data_ret"] = date('Y-m-d H:i:s');
            $aret[0]["estatus"] = "10";
            $aret[0]["cestatus"] = "Horário permito para ver atualizações de preço => " . $OP_22[0] . " a " . $OP_22[1];
            echo json_encode($aret);
            logs($aret);
            return;
        }
    }

    $carq = "prod_api_a_$ncli";
    $cstr = "CREATE TABLE if not exists `" . $carq . "` (
  `ordem` int(10) NOT NULL AUTO_INCREMENT,
  `data_ini` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `alt` int(10) NOT NULL DEFAULT 0,
  `log` mediumtext,
  PRIMARY KEY (`ordem`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ";
    mysqli_query($dbc, $cstr);
    $carqd = $carq . "_d";
    $cstr = "CREATE TABLE if not exists `" . $carqd . "` (
  `ordem` int(10) NOT NULL default 0,
  `codigo` int(10) not null default 0,
  `crc` decimal(13,0) not null default 0,
  `crc_sal` decimal(13,0) not null default 0,
  index ordem (ordem,codigo,crc,crc_sal)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ";
    mysqli_query($dbc, $cstr);

    if (mysqli_fetch_row(mysqli_query($dbc, "desc $carqd 'estoque'"))[0] !== 'estoque') {
        mysqli_query($dbc, "alter table $carqd add estoque decimal(10,0) not null default '0' ");
    }


    $copret = strtoupper(pegavar("ret"));
    if ($copret != "T" && $copret != "D" && $copret != "S") {
        $copret = "T";
    }
    $nord = pegavar("ord");
    $nord_ant = pegavar("odi");
    if (!$nord_ant) {
        $nord_ant = $nord - 1;
    }
    if (!$nord) {
        GeraTempPro(true);
        $cstr = "insert $carq set data_ini=now()";
        mysqli_query($dbc, $cstr);
        $nordem = mysqli_fetch_row(mysqli_query($dbc, "select last_insert_id()"))[0];

        $cstr = "delete from $carqd where ordem< $nordem - " . QUANT_ORDEM;
        mysqli_query($dbc, $cstr);

        $adados = array();

        $cstr = "create TEMPORARY table teste_pro select p.codpro as codigo
            ,convert(0,decimal(10,0)) as estoque,p.ativo,w.bloqueado 
            ,if(w.nomeweb$idi='',p.nomelong,w.nomeweb$idi) as nome
            ,if(w.nomeweb2='',p.nomelong,w.nomeweb2) as nome2
            ,w.nimagem
        ," . $preco_env . " as preco
            ,convert(if(p.codpromo=0,0,p.preco5)" . ($ntax !== 0 ? "*(1+($ntax/100))" : "") . ",decimal(15,2)) as preco_promo
            ,p.precoweb" . ($ntax !== 0 ? "*(1+($ntax/100))" : "") . " as precoweb
    ,if(isnull(wm.memo),'',wm.memo) as texto ,if(isnull(wm1.memo),'',wm1.memo) as mini,w.link2 as fabrica,w.link1 as video
    ,if(isnull(wm_2.memo),'',wm_2.memo) as texto2 ,if(isnull(wm1_2.memo),'',wm1_2.memo) as mini2
    ,p.largura,p.altura,p.profund,p.pespro as peso,w.emax as estmax,w.permax
    ,p.grupro as cat,p.marcpro as mar,concat(w.grade,',',w.grade2,',',w.grade3,',',w.grade4,',',w.grade5,',',w.grade6,',',w.grade7,',',w.grade8,',',w.grade9,',',w.grade10) as caw
    ,trim(p.gradepro) as gradepro
    ,p.decreto
    ,w.etiqueta
    ,CONVERT(0,decimal(13,0)) AS valor
    ,CONVERT(0,decimal(13,0)) AS crc
    ,CONVERT(0,decimal(13,0)) AS crc_sal
    ,CONVERT(0,decimal(13,0)) AS crc_pre
        ," . $preco1_env . " as preco1
    from " . PROD_PRO . " p 
    inner join " . PROD_WEB . " w on w.is_deleted='N'  and w.codigo=p.codpro   
    left  join " . PROD_WEB . "_MEMO wm on w.recno=wm.recno  and wm.col_name='TEXTO$idi'
    left  join " . PROD_WEB . "_MEMO wm1 on w.recno=wm1.recno  and wm1.col_name='MINIDESC$idi'
    left  join " . PROD_WEB . "_MEMO wm_2 on w.recno=wm_2.recno  and wm_2.col_name='TEXTO2'
    left  join " . PROD_WEB . "_MEMO wm1_2 on w.recno=wm1_2.recno  and wm1_2.col_name='MINIDESC2'
    group by p.codpro";

        $adados[] = array("Produto", $cstr);

        $cstr = "alter table teste_pro add index codigo (codigo)";
        $adados[] = array("Produto Index", $cstr);

        $cstr = "create temporary table teste_sal select l.proloc as codigo,sum(if(isnull(l.proloc),0,l.qualoc)) as estoque,0 as existe from LOCAL_LOC l where l.is_deleted='N' and find_in_set(l.locloc,'$clojasal') group by l.proloc";
        $adados[] = array("Produto Saldo", $cstr);

        $cstr = "alter table teste_sal add index codigo (codigo)";
        $adados[] = array("Produto saldo Index", $cstr);

        $cstr = "update teste_pro p,
            teste_sal l
             set p.estoque=l.estoque where p.codigo=l.codigo";
        $adados[] = array("Saldo ", $cstr);

        $cstr = "update teste_pro set estoque=if(permax>0 , estoque*(permax/100), estoque)";
        $adados[] = array("Update permax", $cstr);

        if ($olcfg["EMAX"] > 0) {
            $cstr = "update teste_pro set estoque='" . $olcfg["EMAX"] . "' where estoque>'" . $olcfg["EMAX"] . "'";
            $adados[] = array("Update emax", $cstr);
        }
        $cstr = "update teste_pro set estoque=if(estmax>0 and estoque>estmax, estmax, estoque)";
        $adados[] = array("Update estmax", $cstr);

        $naux = $olcfg["EST_MAX"] + 0;
        if ($naux > 0) {
            $cstr = "Update teste_pro set estoque=$naux where estoque>$naux";
            $adados[] = array("Saldo Max ", $cstr);
        }

        $cstr = "create temporary table teste1_pro select codigo,SUM(crc32(valor)+CRC32(VALOR2)) AS valor  FROM CATVALOR_WEB where is_deleted='N' GROUP BY CODIGO";
        $adados[] = array("Calc Carac", $cstr);

        $cstr = "alter table teste1_pro add index codigo (codigo)";
        $adados[] = array("Index Carac", $cstr);

        $cstr = "update teste_pro p,teste1_pro v set p.valor=v.valor where p.codigo=v.codigo";
        $adados[] = array("Update produto,carac", $cstr);

        $cstr = "update teste_pro set crc=crc32(concat(ativo,bloqueado,nome,nome2,nimagem,texto,mini,fabrica,video,texto2,mini2,largura*1000
        ,altura*1000,profund*1000,peso*1000,estmax,permax,cat,mar,caw,gradepro,decreto,etiqueta,valor)),crc_pre=crc32(concat(preco*1000,preco_promo*1000))";
        $adados[] = array("Update CRC", $cstr);

        $cstr = "update teste_pro p,
            (SELECT PRODUTO as codigo FROM LISPROPR_LIS WHERE CRC32!=CRC32(CONCAT(PRECO,PRECOA,PRECOB,PRECOC,PRECOFIXO,PRECOT)) AND IS_DELETED='N') l
            set crc_pre=crc32(concat(p.crc_pre,l.crc32)) where p.codigo=l.codigo ";
        $adados[] = array("Update CRC Lista", $cstr);

        $cstr = "update LISPROPR_LIS SET CRC32=CRC32(CONCAT(PRECO,PRECOA,PRECOB,PRECOC,PRECOFIXO,PRECOT)) ";
        $adados[] = array("Update CRC Lista Tabela", $cstr);

        $cstr = "insert $carqd (codigo,crc,ordem,crc_sal,estoque) select codigo,crc,$nordem,crc32(concat(estoque,crc_pre)),estoque from teste_pro ";
        $adados[] = array("Inserir dados", $cstr);

        $clog = "Ordem: $nordem \n";
        foreach ($adados as $value) {
            $clog .= date("H:i:s") . "  " . $value[0] . "\n";
            mysqli_query($dbc, $value[1]);
            $clog .= date("H:i:s") . "\n\n";
        }


        $cstr = "select * from ( 
            select p.codigo
            ,if(p.crc!=v.crc,1,0) as crc
            ,if(p.crc_sal!=v.crc_sal or isnull(v.codigo),1,0) as crc_sal
            ,p.crc_sal as sal 
            ,p.estoque
            from $carqd p left join $carqd v on v.ordem=" . ($nordem - 1) . " and v.codigo=p.codigo 
                where p.ordem=" . $nordem . " and (isnull(v.codigo) or p.crc!=v.crc or p.crc_sal!=v.crc_sal) ) p ";
        if ($copret == "D") {
            $cstr .= " where crc!=0 ";
        } else if ($copret == "S") {
            $cstr .= " where crc_sal!=0 ";
        }
        $otab = mysqli_query($dbc, $cstr);
        $apro = array();
        while ($olin = mysqli_fetch_row($otab)) {
            /*
              $aprecos = mysqli_fetch_row(mysqli_query($dbc, "select preco," . ($afiltro[$ncli]["web2promo"] ? "precoweb" : "preco_promo") . " as web from teste_pro WHERE codigo=" . $olin[0]));
              if ($copret == "D") {
              $apro[] = array("sku" => poedigito($olin[0]), "dad" => $olin[1] == 1);
              } else if ($copret == "S") {
              $apro[] = array("sku" => poedigito($olin[0]), "sal" => $olin[2] == 1, "atu" => $olin[4], "pre" => $aprecos[0], "prm" => $aprecos[1]);
              } else {
              $apro[] = array("sku" => poedigito($olin[0]), "dad" => $olin[1] == 1, "sal" => $olin[2] == 1, "atu" => $olin[4], "pre" => $aprecos[0], "prm" => $aprecos[1]);
              }
              //$adet["pre"] = number_format($olinha["preco"], 2);
              //$adet["prm"] = number_format($olinha[$afiltro[$ncli]["web2promo"] ? "precoweb" : "preco_promo"], 2);
             * 
             */
            $aprecos = mysqli_fetch_row(mysqli_query($dbc, "select t.preco," . ($afiltro[$ncli]["web2promo"] ? "t.precoweb" : "t.preco_promo") . " as web 
                ,p.ativo,w.bloqueado
                from teste_pro t
                inner join " . PROD_PRO . " p on p.codpro=t.codigo
                inner join " . PROD_WEB . " w on w.codigo=t.codigo
                WHERE t.codigo=" . $olin[0]));
            $aenv = array();
            if ($copret == "D") {
                $aenv = array("sku" => poedigito($olin[0]), "dad" => $olin[1] == 1);
            } else if ($copret == "S") {
                $aenv = array("sku" => poedigito($olin[0]), "sal" => $olin[2] == 1, "atu" => $olin[4], "pre" => $aprecos[0], "prm" => $aprecos[1]);
            } else {
                $aenv = array("sku" => poedigito($olin[0]), "dad" => $olin[1] == 1, "sal" => $olin[2] == 1, "atu" => $olin[4], "pre" => $aprecos[0], "prm" => $aprecos[1]);
            }
            $nsta = 0;

            if (count($aprecos) == 0) {
                $nsta = 9;
            } else {
                if ($olin[2] == 0) {
                    $nsta = 1;
                } elseif ($aprecos[2] != "S" || $aprecos[3] == "Y") {
                    $nsta = 2;
                }
            }
            $aenv["sta"] = $nsta;
            $apro[] = $aenv;
        }



        $cstr = "select * from ( select p.codigo,0 as crc,1 as crc_sal,if(isnull(v.codigo),0, v.crc_sal) as sal 
            from $carqd p left join $carqd v on v.ordem=" . ($nordem) . " and v.codigo=p.codigo 
                where p.ordem=" . ($nordem - 1) . " and isnull(v.codigo) ) p ";
        if ($copret == "D") {
            $cstr .= " where crc!=0 ";
        } else if ($copret == "S") {
            $cstr .= " where crc_sal!=0 ";
        }
        $otab = mysqli_query($dbc, $cstr);
        while ($olin = mysqli_fetch_row($otab)) {
            $aprecos = mysqli_fetch_row(mysqli_query($dbc, "select t.preco," . ($afiltro[$ncli]["web2promo"] ? "t.precoweb" : "t.preco_promo") . " as web 
                ,p.ativo,w.bloqueado
                from teste_pro t
                inner join " . PROD_PRO . " p on p.codpro=t.codigo
                inner join " . PROD_WEB . " w on w.codigo=t.codigo
                WHERE t.codigo=" . $olin[0]));
            $aenv = array();
            if ($copret == "D") {
                $aenv = array("sku" => poedigito($olin[0]), "dad" => $olin[1] == 1);
            } else if ($copret == "S") {
                $aenv = array("sku" => poedigito($olin[0]), "sal" => $olin[2] == 1, "atu" => $olin[3], "pre" => $aprecos[0], "prm" => $aprecos[1]);
            } else {
                $aenv = array("sku" => poedigito($olin[0]), "dad" => $olin[1] == 1, "sal" => $olin[2] == 1, "atu" => $olin[3], "pre" => $aprecos[0], "prm" => $aprecos[1]);
            }
            $nsta = 0;

            if (count($aprecos) == 0) {
                $nsta = 9;
            } else {
                if ($olin[2] == 0) {
                    $nsta = 1;
                } elseif ($aprecos[2] != "S" || $aprecos[3] == "Y") {
                    $nsta = 2;
                }
            }
            $aenv["sta"] = $nsta;
            $apro[] = $aenv;
        }

        mysqli_query($dbc, "update $carq set data_fim=now(),log='" . $clog . "',alt=" . count($apro) . " where ordem=$nordem");
        $aret["ord_max"] = mysqli_fetch_row(mysqli_query($dbc, "select max(ordem) from $carq "))[0];
        $aret["ord"] = $nordem;
        $aret["odi"] = $nordem - 1;
        $aret["qua"] = count($apro);
        $aret["dat_ord"] = mysqli_fetch_row(mysqli_query($dbc, "select data_ini from $carq where ordem=$nordem"))[0];
        $aret["alt"] = $apro;
    } else {
        if ($nord_ant >= $nord) {
            $aret[0]["data_ret"] = date('Y-m-d H:i:s');
            $aret[0]["estatus"] = "83";
            $aret[0]["cestatus"] = "Erro na ordem. Inicial e final invalida: $nord_ant $nord";
            $aret[0]["element"] = $aret[0]["element"] + 1;
            echo json_encode($aret);
            logs($aret);
            return;
        }
        if (mysqli_fetch_row(mysqli_query($dbc, "select count(*) from $carqd where ordem=$nord"))[0] == 0 ||
                mysqli_fetch_row(mysqli_query($dbc, "select count(*) from $carqd where ordem=$nord_ant"))[0] == 0) {
            $aret[0]["data_ret"] = date('Y-m-d H:i:s');
            $aret[0]["estatus"] = "84";
            $aret[0]["cestatus"] = "Erro na ordem. Inexistente: $nord_ant $nord";
            $aret[0]["element"] = $aret[0]["element"] + 1;
            echo json_encode($aret);
            logs($aret);
            return;
        }
        $cstr = "select * from (select p.codigo,if(p.crc!=v.crc,1,0) as crc,if(p.crc_sal!=v.crc_sal  or isnull(v.codigo),1,0) as crc_sal,p.crc_sal as sal,p.estoque  
            from $carqd p left join $carqd v on v.ordem=" . ($nord_ant) . " and v.codigo=p.codigo 
                where p.ordem=" . $nord . " and (isnull(v.codigo) or p.crc!=v.crc or p.crc_sal!=v.crc_sal) ) p ";
        if ($copret == "D") {
            $cstr .= " where crc!=0 ";
        } else if ($copret == "S") {
            $cstr .= " where crc_sal!=0 ";
        }
        $otab = mysqli_query($dbc, $cstr);
        $apro = array();
        while ($olin = mysqli_fetch_row($otab)) {
            /*
              $cstr = "select " . $preco_env . " as preco,";
              if ($afiltro[$ncli]["web2promo"]) {
              $cstr .= "p.precoweb" . ($ntax !== 0 ? "*(1+($ntax/100))" : "") . " as precoweb ";
              } else {
              $cstr .= "convert(if(p.codpromo=0,0,p.preco5)" . ($ntax !== 0 ? "*(1+($ntax/100))" : "") . ",decimal(15,2)) as preco_promo ";
              }
              $cstr .= " from " . PROD_PRO . " p
              inner join " . PROD_WEB . " w on w.is_deleted='N'  and w.codigo=p.codpro
              where p.codpro=" . $olin[0];
              $aprecos = mysqli_fetch_row(mysqli_query($dbc, $cstr));

              if ($copret == "D") {
              $apro[] = array("sku" => poedigito($olin[0]), "dad" => $olin[1] == 1);
              } else if ($copret == "S") {
              $apro[] = array("sku" => poedigito($olin[0]), "sal" => $olin[2] == 1, "atu" => $olin[4], "pre" => $aprecos[0], "prm" => $aprecos[1]);
              } else {
              $apro[] = array("sku" => poedigito($olin[0]), "dad" => $olin[1] == 1, "sal" => $olin[2] == 1, "atu" => $olin[4], "pre" => $aprecos[0], "prm" => $aprecos[1]);
              }
             * 
             */
            $cstr = "select " . $preco_env . " as preco,";
            if ($afiltro[$ncli]["web2promo"]) {
                $cstr .= "p.precoweb" . ($ntax !== 0 ? "*(1+($ntax/100))" : "") . " as precoweb ";
            } else {
                $cstr .= "convert(if(p.codpromo=0,0,p.preco5)" . ($ntax !== 0 ? "*(1+($ntax/100))" : "") . ",decimal(15,2)) as preco_promo ";
            }
            $cstr .= ",p.ativo,w.bloqueado from " . PROD_PRO . " p 
            inner join " . PROD_WEB . " w on w.is_deleted='N'  and w.codigo=p.codpro   
            where p.codpro=" . $olin[0];
            $aprecos = mysqli_fetch_row(mysqli_query($dbc, $cstr));
            $aenv = array();
            if ($copret == "D") {
                $aenv = array("sku" => poedigito($olin[0]), "dad" => $olin[1] == 1);
            } else if ($copret == "S") {
                $aenv = array("sku" => poedigito($olin[0]), "sal" => $olin[2] == 1, "atu" => $olin[4], "pre" => $aprecos[0], "prm" => $aprecos[1]);
            } else {
                $aenv = array("sku" => poedigito($olin[0]), "dad" => $olin[1] == 1, "sal" => $olin[2] == 1, "atu" => $olin[4], "pre" => $aprecos[0], "prm" => $aprecos[1]);
            }
            $nsta = 0;

            if (count($aprecos) == 0) {
                $nsta = 9;
            } else {
                if ($olin[2] == 0) {
                    $nsta = 1;
                } elseif ($aprecos[2] != "S" || $aprecos[3] == "Y") {
                    $nsta = 2;
                }
            }
            $aenv["sta"] = $nsta;
            $apro[] = $aenv;
        }





        $cstr = "select * from ( select p.codigo,0 as crc,1 as crc_sal,if(isnull(v.codigo),0, v.crc_sal) as sal 
            from $carqd p left join $carqd v on v.ordem=" . ($nord) . " and v.codigo=p.codigo 
                where p.ordem=" . ($nord_ant) . " and isnull(v.codigo) ) p ";
        if ($copret == "D") {
            $cstr .= " where crc!=0 ";
        } else if ($copret == "S") {
            $cstr .= " where crc_sal!=0 ";
        }
        $otab = mysqli_query($dbc, $cstr);
        while ($olin = mysqli_fetch_row($otab)) {
            $cstr = "select " . $preco_env . " as preco,";
            if ($afiltro[$ncli]["web2promo"]) {
                $cstr .= "p.precoweb" . ($ntax !== 0 ? "*(1+($ntax/100))" : "") . " as precoweb ";
            } else {
                $cstr .= "convert(if(p.codpromo=0,0,p.preco5)" . ($ntax !== 0 ? "*(1+($ntax/100))" : "") . ",decimal(15,2)) as preco_promo ";
            }
            $cstr .= ",p.ativo,w.bloqueado from " . PROD_PRO . " p 
            inner join " . PROD_WEB . " w on w.is_deleted='N'  and w.codigo=p.codpro   
            where p.codpro=" . $olin[0];
            $aprecos = mysqli_fetch_row(mysqli_query($dbc, $cstr));
            $aenv = array();
            if ($copret == "D") {
                $aenv = array("sku" => poedigito($olin[0]), "dad" => $olin[1] == 1);
            } else if ($copret == "S") {
                $aenv = array("sku" => poedigito($olin[0]), "sal" => $olin[2] == 1, "atu" => $olin[3], "pre" => $aprecos[0], "prm" => $aprecos[1]);
            } else {
                $aenv = array("sku" => poedigito($olin[0]), "dad" => $olin[1] == 1, "sal" => $olin[2] == 1, "atu" => $olin[3], "pre" => $aprecos[0], "prm" => $aprecos[1]);
            }
            $nsta = 0;

            if (count($aprecos) == 0) {
                $nsta = 9;
            } else {
                if ($olin[2] == 0) {
                    $nsta = 1;
                } elseif ($aprecos[2] != "S" || $aprecos[3] == "Y") {
                    $nsta = 2;
                }
            }
            $aenv["sta"] = $nsta;
            $apro[] = $aenv;
        }

        $aret["ord_max"] = mysqli_fetch_row(mysqli_query($dbc, "select max(ordem) from $carq "))[0];
        $aret["ord"] = $nord;
        $aret["odi"] = $nord_ant;
        $aret["qua"] = count($apro);
        $aret["dat_ord"] = mysqli_fetch_row(mysqli_query($dbc, "select data_ini from $carq where ordem=$nord"))[0];
        $aret["alt"] = $apro;
    }
} elseif ($nope == 3) {
    if (pegavar("sku", $lpost) == "") {
        logs($aret);
        return;
    }
    $nimg = pegavar("img", $lpost);
    if (pegavar("img", $lpost) == "") {
        $nimg = 1;
    }
    $nsku = pegavar("sku", $lpost);
    if (!$nsku || !vedigito($nsku)) {
        logs($aret);
        return;
    }
    $nsku = tiradigito($nsku);
    $otable = mysqli_query($dbc, "select nimagem from " . PROD_WEB . " w WHERE 1 and w.codigo=" . $nsku);
    if (mysqli_num_rows($otable) == 0) {
        logs($aret);
        return;
    }
    $olinha = mysqli_fetch_array($otable);
    if ($olinha["nimagem"] < $nimg) {
        logs($aret);
        return;
    }
    $otable = mysqli_query($dbc, "select i.ordem,m.memo from PRODUTOS_IMG i 
    inner join PRODUTOS_IMG_MEMO m on m.recno=i.recno and m.col_name='IMAGEM' 
    where i.codigo=" . $nsku . " and i.is_deleted='N' and i.ordem=$nimg order by i.ordem,i.parte");
    $aimagem = array();

    $nordem = 0;
    $npos = -1;
    while ($olinha = mysqli_fetch_array($otable)) {
        if ($nordem != $olinha["ordem"]) {
            $nordem = $olinha["ordem"];
            $aimagem[] = array($nordem, "");
            $npos++;
        }
        $aimagem[$npos][1] .= $olinha["memo"];
    }
    $aaux = array();
    $aaux[] = "\\";
    $aaux[] = chr(0);
    $aaux[] = chr(13);
    $aaux[] = chr(10);
    $aaux[] = '"';
    $aaux[] = "'";
    $aaux[] = "`";
    $aaux[] = chr(9);
    $aaux[] = chr(141);
    $aaux[] = chr(8);
    $aaux[] = chr(26);
    $aaux[] = "%";
    $aaux[] = "_";

    $cretorna = "";
    for ($j = 0;
            $j < count($aimagem);
            $j++) {
        $caux = $aimagem[$j][1];
        for ($i = count($aaux);
                $i > 0;
                $i--) {
            $caux = str_replace("/chr" . ( $i) . "/", $aaux[$i - 1], $caux);
        }
    }

    Header("Content-type: image/jpeg");
    print $caux;
    logs($aret);
    RETURN;
} elseif ($nope == 11) {
    foreach ($asku as $value) {
        $aaux = explode("-", $value);
        $otable = mysqli_query($dbc, "select p.codpro,$nSaldoEstoque as estoque,p.ativo,w.bloqueado ,if(w.nomeweb='',p.nomelong,w.nomeweb) as nome,w.nimagem
        ,p." . $preco_env . " as preco
              ,convert(p.codpromo=0,0,p.preco5)" . ($ntax !== 0 ? "*(1+($ntax/100))" : "") . ",decimal(15,2)) as preco_promo
              ,p.precoweb" . ($ntax !== 0 ? "*(1+($ntax/100))" : "") . " as precoweb,p.grupro as cat
  ,if(isnull(wm.memo),'',wm.memo) as texto ,if(isnull(wm1.memo),'',wm1.memo) as mini,w.link2 as fabrica,w.link1 as video
    ,p.largura,p.altura,p.profund,p.pespro as peso,w.emax as estmax,w.permax
        ,p." . $preco1_env . " as preco1
    from " . PROD_PRO . " p 
    inner join " . PROD_WEB . " w on w.is_deleted='N'  and w.codigo=p.codpro   
    left  join " . PROD_WEB . "_MEMO wm on w.recno=wm.recno  and wm.col_name='TEXTO'
    left  join " . PROD_WEB . "_MEMO wm1 on w.recno=wm1.recno  and wm1.col_name='MINIDESC'
    left join LOCAL_LOC l on l.is_deleted='N' and l.locloc=$nloja and l.is_deleted='N' and l.proloc=p.codpro
        $cLojaEstoque
    where 1   and p.codpro=" . $aaux[1]);
        $olinha = mysqli_fetch_array($otable);
        $nsta = 0;
        $nsal = $olinha["estoque"];
        if ($aaux[0] != $ncli) {
            $nsta = 8;
            $aret[] = array("sku" => $value, "sta" => $nsta);
        } elseif (mysqli_num_rows($otable) == 0) {
            $nsta = 9;
            $aret[] = array("sku" => $value, "sta" => $nsta);
        } else {
            $nsal = $olinha["estoque"];
            if ($olcfg["EMAX"] > 0 && $olcfg["EMAX"] < $nsal) {
                $nsal = $olcfg["EMAX"];
            }
            if ($olinha["estmax"] > 0 && $nsal > $olinha["estmax"]) {
                $nsal = $olinha["estmax"];
            }
            if ($olinha["permax"] > 0) {
                $nper = floor($olinha["estoque"] * ($olinha["permax"] / 100));
                if ($nsal > $nper) {
                    $nsal = $nper;
                }
            }

            $nsal = $afiltro[$ncli]["grupoenvsaldo"][0] + 0 == $olinha["cat"] + 0 ? $afiltro[$ncli]["grupoenvsaldo"][1] : floor($nsal);

            if ($nsal == 0) {
                $nsta = 1;
            } elseif ($olinha["ativo"] != "S" || $olinha["bloqueado"] == "Y") {
                $nsta = 2;
            }
            $adet = array();
            $adet["sku"] = padl($ncli, 5, "0") . "-" . padl($olinha["codpro"], 7, "0");
            $adet["sta"] = $nsta;
            $adet["sal"] = floor($nsal);
            $adet["pre"] = number_format($olinha["preco"], 2);
            $adet["prm"] = number_format($olinha[$afiltro[$ncli]["web2promo"] ? "precoweb" : "preco_promo"], 2);
            $adet["pwb"] = number_format($olinha["precoweb"], 2);
            $aret[] = $adet;
            $aret[0]["element"] = $aret[0]["element"] + 1;
        }
    }
} elseif ($nope == 12) {
    $nped = pegavar("ped", $lpost);
    $ncliente = pegavar("cli", $lpost);
    $asku = explode(",", pegavar("sku", $lpost));
    $aqua = explode(",", pegavar("qtd", $lpost));
    $apre = explode(",", pegavar("pre", $lpost));
    $nfre = trim(pegavar("fre")) + 0;
    $nvend_ven = pegavar("ven");
    if ($nvend_ven) {
        $cstr = trim($olcfg["VEND_VEN"]);
        $aaux = explode(",", $cstr);
        if (!$cstr || !in_array($nvend_ven, $aaux)) {
            $aret[0]["estatus"] = 140;
            $aret[0]["cestatus"] = "Vendedor informado não está autorizado";
            echo json_encode($aret);
            logs($aret);
            return;
        }
    }else{
        $nvend_ven=$olcfg["VENDEDOR"];
    }
    $nloja_ven = pegavar("loj");
    if ($nloja_ven) {
        $cstr = trim($olcfg["LOJA_VEN"]);
        $aaux = explode(",", $cstr);
        if (!$cstr || !in_array($nloja_ven, $aaux)) {
            $aret[0]["estatus"] = 141;
            $aret[0]["cestatus"] = "Loja informada não está autorizada";
            echo json_encode($aret);
            logs($aret);
            return;
        }
    }else{
        $nloja_ven=$olcfg["LOJA"];
    }

    $ncodfre = pegavar("codfre");
    if (!$ncodfre) {
        $nCodigoFrete = $afiltro[$ncli]["codigo_frete"];
    } else {
        $nCodigoFrete = $afiltro[$ncli]["codigo_frete_$ncodfre"];
    }


    //if ($nfre > 0 && $afiltro[$ncli]["codigo_frete"]) {
//        $asku[] = poedigito($afiltro[$ncli]["codigo_frete"]);
    if ($nfre > 0 && $nCodigoFrete) {
        $asku[] = poedigito($nCodigoFrete);
        $aqua[] = 1;
        $apre[] = $nfre;
    }
    $agra = explode(",", pegavar("gra", $lpost));
    $nseq1 = 0;
    $nseq2 = 0;

    $npgt = pegavar("pgt", $lpost) + 0;
    $cobs = LimpaAcento(pegavar("obs", $lpost));

    $agrade = array();

    if ($nped == '') {
        $aret[0]["estatus"] = 40;
        $aret[0]["cestatus"] = "Pedido vazio";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    if (!$ncliente || $ncliente + 0 == 0) {
        $aret[0]["estatus"] = 40;
        $aret[0]["cestatus"] = "Numero do cliente no pedido invalido";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $otabcli = mysqli_query($dbc, "select * from CLIENTE_CLI WHERE IS_DELETED='N' AND CODCLI='$ncliente'");
    $otabpfc = mysqli_query($dbc, "select * from CLIENTE_PFC WHERE IS_DELETED='N' AND CODIDO='$ncliente'");
    if (mysqli_num_rows($otabcli) == 0) {
        $aret[0]["estatus"] = 73;
        $aret[0]["cestatus"] = "Cliente não existe";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $olincli = mysqli_fetch_array($otabcli);
    $olinpfc = mysqli_fetch_array($otabpfc);

    if ($olinpfc["BLOQUEADO"] == "Y") {
        $aret[0]["estatus"] = 74;
        $aret[0]["cestatus"] = "Cliente BLOQUEADO";
        echo json_encode($aret);
        logs($aret);
        return;
    }


    if (count($asku) == 0 || pegavar("sku", $lpost) == '') {
        $aret[0]["estatus"] = 40;
        $aret[0]["cestatus"] = "Produtos Vazio";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    if (count($asku) != count($aqua) || pegavar("qtd", $lpost) == '') {
        $aret[0]["estatus"] = 40;
        $aret[0]["cestatus"] = "Quantidade e produtos nao sao iguais ";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    if (count($asku) != count($apre)) {
        $aret[0]["estatus"] = 40;
        $aret[0]["cestatus"] = "Quantidade de preços e produtos nao sao iguais";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    if ($npgt < 1 || $npgt > 3) {
        $aret[0]["estatus"] = 40;
        $aret[0]["cestatus"] = "Forma de pagamento inválido";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    if ($nday > $olcfg["DAY"]) {
        $aret[0]["estatus"] = 81;
        $aret[0]["cestatus"] = "Prazo de dias para bloqueio do pedido nao aceito pelo cliente";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $olcam = mysqli_fetch_array(mysqli_query($dbc, "select * from CAMBIO_CAM WHERE IS_DELETED='N' ORDER BY DATCAM DESC LIMIT 1"));
    $olnom = mysqli_fetch_array(mysqli_query($dbc, "select * from CAMBIO_NOM WHERE IS_DELETED='N' limit 1"));
    $cerro = "";
    $i = 0;
    $atotais = array(0, 0);
    foreach ($asku as $value) {

        $i++;
        if (!vedigito($value)) {
            $cerro .= $value . "-Produto inválido|";
            continue;
        }
        if ($aqua[$i - 1] <= 0) {
            $cerro .= $value . "-Quantidade do Produto invalida|";
            continue;
        }
        if ($apre[$i - 1] <= 0) {
            $cerro .= $value . "-Preço inválido|";
            continue;
        }
        $atotais[0] += $aqua[$i - 1];
        /*
          $csql = "select p.codpro,sum(if(isnull(l.proloc),0,l.qualoc)) as estoque,p.ativo,w.bloqueado ,if(w.nomeweb='',p.nomelong,w.nomeweb) as nome,w.nimagem
          ," . $preco_env . " as preco
          ,convert(if(p.codpromo=0,0,p.preco5)" . ($ntax !== 0 ? "*(1+($ntax/100))" : "") . ",decimal(15,2)) as preco_promo
          ,p.precoweb" . ($ntax !== 0 ? "*(1+($ntax/100))" : "") . " as precoweb
          ,if(isnull(wm.memo),'',wm.memo) as texto ,if(isnull(wm1.memo),'',wm1.memo) as mini,w.link2 as fabrica,w.link1 as video
          ,p.largura,p.altura,p.profund,p.pespro as peso,w.emax as estmax,w.permax
          ,trim(p.gradepro) as gradepro
          ,p.grupro,p.kitpro
          ," . $preco1_env . " as preco1
          from " . PROD_PRO . " p
          left join " . PROD_WEB . " w on w.is_deleted='N'  and w.codigo=p.codpro
          left  join " . PROD_WEB . "_MEMO wm on w.recno=wm.recno  and wm.col_name='TEXTO'
          left  join " . PROD_WEB . "_MEMO wm1 on w.recno=wm1.recno  and wm1.col_name='MINIDESC'
          left join LOCAL_LOC l on l.is_deleted='N' and l.proloc=p.codpro  and find_in_set(l.locloc,'$clojasal')
          where 1 and p.codpro=" . tiradigito($value);
         * 
         */
        //mudanca slave
        $csql = "select p.codpro,sum(if(isnull(l.proloc),0,l.qualoc)) as estoque,p.ativo,w.bloqueado ,if(w.nomeweb='',p.nomelong,w.nomeweb) as nome,w.nimagem
        ," . $preco_env . " as preco
            ,convert(if(p.codpromo=0,0,p.preco5)" . ($ntax !== 0 ? "*(1+($ntax/100))" : "") . ",decimal(15,2)) as preco_promo
            ,p.precoweb" . ($ntax !== 0 ? "*(1+($ntax/100))" : "") . " as precoweb
        ,if(isnull(wm.memo),'',wm.memo) as texto ,if(isnull(wm1.memo),'',wm1.memo) as mini,w.link2 as fabrica,w.link1 as video
        ,p.largura,p.altura,p.profund,p.pespro as peso,w.emax as estmax,w.permax
        ,trim(p.gradepro) as gradepro
        ,p.grupro,p.kitpro
        ," . $preco1_env . " as preco1
        from PRODUTO_PRO p 
        left join PRODUTOS_WEB w on w.is_deleted='N'  and left(w.nomeweb,3)!='***' and w.codigo=p.codpro   
        left  join PRODUTOS_WEB_MEMO wm on w.recno=wm.recno  and wm.col_name='TEXTO'
        left  join PRODUTOS_WEB_MEMO wm1 on w.recno=wm1.recno  and wm1.col_name='MINIDESC'
        left join LOCAL_LOC l on l.is_deleted='N' and l.proloc=p.codpro  and find_in_set(l.locloc,'$clojasal')
        where p.is_deleted='N' and p.codpro=" . tiradigito($value);
        $otable = mysqli_query($dbc, $csql);
        $olinha = mysqli_fetch_array($otable);
        $atotais[1] += ($afiltro[$ncli]["preco_site"] ? $apre[$i - 1] : ($olinha["preco"] / $olcam["MOEDA1"]) * $olcam["MOEDA" . $olcfg["FRETE_M"]]) * $aqua[$i - 1];
        if (mysqli_num_rows($otable) == 0 || !$olinha["codpro"]) {
            $cerro .= $value . "-Produto nao localizado|";
            continue;
        }
        $nseq1 = 0;
        $nseq2 = 0;

        if (trim($olinha["gradepro"]) !== '') {
            if (!$agra[$i - 1]) {
                $cerro .= $value . "-Produto com grade e dados não informados corretamente";
                continue;
            }
            $aaux = explode("|", $agra[$i - 1]);
            $cgra1 = trim($aaux[0]);
            $cgra2 = trim($aaux[1]);
            if (!$cgra2) {
                $cgra2 = $cgra1;
            }
            $nseq1 = mysqli_fetch_row(mysqli_query($dbc, "select s.recno
from PRODUTO_PRO p
inner join GRADE_PRO g on     g.is_deleted='N'  and  g.codgrade=substring_index(p.gradepro,'-',1) 
inner join GRADESEQ_PRO s  on s.is_deleted='N'  and  s.grade=g.codgrade
where p.is_deleted='N' and p.codpro=" . tiradigito($value) . "
AND s.tipogrd='$cgra1'"
                            ))[0] + 0;
            if ($nseq1 <= 0) {
                $cerro .= $value . "-Produto com grade e dados não informados corretamente-Grade '$cgra1' não existe";
                continue;
            }
            $nseq2 = mysqli_fetch_row(mysqli_query($dbc, "select s.recno 
from PRODUTO_PRO p
inner join GRADE_PRO g on     g.is_deleted='N'  and  g.codgrade=substring_index(p.gradepro,'-',-1) 
inner join GRADESEQ_PRO s  on s.is_deleted='N'  and  s.grade=g.codgrade
where p.is_deleted='N' and p.codpro=" . tiradigito($value) . "
AND s.tipogrd='$cgra2'"
                            ))[0] + 0;
            if ($nseq2 <= 0) {
                $cerro .= $value . "-Produto com grade e dados não informados corretamente-Grade '$cgra2' não existe";
                continue;
            }
            $nestgra = mysqli_fetch_row(mysqli_query($dbc, "select estoque from ESTGRADE_PRO 
                where is_deleted='N' and prograde=" . tiradigito($value) . " 
                and seqgrade1=$nseq1 and seqgrade2=$nseq2"))[0] + 0;
            if ($nestgra < $aqua[$i - 1]) {
                $cerro .= $value . "-Produto sem estoque nesta grade";
                continue;
            }
            $agrade[$i - 1] = array($nseq1, $nseq2);
        }
//        if ($asku[$i - 1] !== poedigito($afiltro[$ncli]["codigo_frete"])) {
        if ($asku[$i - 1] !== poedigito($nCodigoFrete)) {
            $nsal = $olinha["estoque"];
            $nsal = $afiltro[$ncli]["grupoenvsaldo"][0] + 0 == $olinha["grupro"] + 0 ? $afiltro[$ncli]["grupoenvsaldo"][1] : floor($nsal);

            if ($olinha["kitpro"] == 'S' && $afiltro[$ncli]["enviakit"]) {
                $asql = array();
                $cstr1 = "drop temporary table if exists kitp";
                mysqli_query($dbc, $cstr1);
                $cstr1 = "create temporary table kitp 
                        select p.codpro ,sum(if(isnull(l.qualoc),0,l.qualoc)) as qualoc,k.prokit,k.quakit 
                        from PRODUTO_PRO p 
                        inner join PROKIT_PRO k on k.is_deleted='N' and k.codkit=p.codpro
                        left  join LOCAL_LOC l on p.is_deleted='N' and l.qualoc>0 and k.prokit=l.proloc and p.kitpro='S' ";
                if ($clojasal && $clojasal !== "9999999") {
                    $cstr1 .= " and find_in_set(l.locloc,'$clojasal')";
                }
                $cstr1 .= " WHERE p.IS_DELETED='N' and p.codpro=" . assql($olinha["codpro"]) . " group by p.codpro,k.prokit";
                mysqli_query($dbc, $cstr1);
                $cstr1 = "update kitp set qualoc=qualoc/quakit";
                mysqli_query($dbc, $cstr1);
                $nsal = mysqli_fetch_row(mysqli_query($dbc, "select convert(qualoc,decimal(10,0)) as quant from kitp"))[0];
            }

            if ($nsal == 0) {
                $cerro .= $value . "-Produto sem saldo|";
                continue;
            }
            if ($olinha["ativo"] != "S" || $olinha["bloqueado"] == "Y") {
                $cerro .= $value . "-Produto Bloqueado|";
                continue;
            }
            if ($olinha["permax"] > 0) {
                $nper = floor($nsal * ($olinha["permax"] / 100));
                if ($nsal > $nper) {
                    $nsal = $nper;
                }
            }
            if ($olcfg["EMAX"] > 0 && $olcfg["EMAX"] < $nsal) {
                $nsal = $olcfg["EMAX"];
            }
            if ($olinha["estmax"] > 0 && $nsal > $olinha["estmax"]) {
                $nsal = $olinha["estmax"];
            }
            $nsal = floor($nsal);
            if ($aqua[$i - 1] > $nsal) {
                $cerro .= $value . "-Produto sem saldo|";
                continue;
            }
        }
    }
    if ($cerro) {
        $aret[0]["estatus"] = 72;
        $aret[0]["cestatus"] = $cerro;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $otable = mysqli_query($dbc, "select PEDIDO from NOTA" . $afiltro[$ncli]["config"] . "_ECM where is_deleted='N'  and PEDIDO='$nped'");
    if (mysqli_num_rows($otable)) {
        $aret[0]["estatus"] = 70;
        $aret[0]["cestatus"] = "Pedido ja existe no cliente";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    if ($ccommit_web) {
        $omed = new Mediator3("SAIDAS.SAI", W_CRIAR, '', '', $dbc);
        $omag = new Mediator3("NOTA" . $afiltro[$ncli]["config"] . ".ECM", W_CRIAR, '', '', $dbc);
    } else {
        $omed = new Mediator();
        $omed->init($dbc, "SAIDAS.SAI");
        $omag = new Mediator();
        $omag->init($dbc, "NOTA" . $afiltro[$ncli]["config"] . ".ECM");
    }
    $omed->aadd("DATASAI", "CURRENT_DATE()", TRUE);
    $omed->aadd("DATAREC", "CURRENT_DATE()", TRUE);
    $omed->aadd("HORASAI", "CURRENT_TIME()", TRUE);
    $omed->aadd("CLIESAI", $olincli["CODCLI"]);
        $omed->aadd("VENDSAI", $nvend_ven);
//        $omed->aadd("VENDSAI", $olcfg["VENDEDOR"]);
    $nvence = (mysqli_fetch_row(mysqli_query($db, "select przcli from CLIENTE_CLI where is_deleted='N' and codcli=" . $olincli["CODCLI"]))[0]) + 0;
    if (!$nvence || $nvence == 0) {
        $nvence = floor($olcfg[$npgt == 1 ? "PGCARTAO" : ($npgt == 2 ? "PGBOLETO" : "PGOUTROS")] / 60 / 60 / 24) + 0;
    }
    $omed->aadd("VENCSAI", "date_add(current_date(),INTERVAL $nvence DAY)", true);
    $omed->aadd("NRDIAS", $nvence);

        $omed->aadd("LOJASAI", $nloja_ven);
        $omed->aadd("LOJACOR", $nloja_ven);

//        $omed->aadd("LOJASAI", $olcfg["LOJA"]);
//        $omed->aadd("LOJACOR", $olcfg["LOJA"]);
    $omed->aadd("TIPOSAI", "V");
    $omed->aadd("TIPOTJ", "T");
    $omed->aadd("CXUNSAI", "C");
    $omed->aadd("MOEDA", $olcfg["FRETE_M"]);
    $omed->aadd("CAMBSAI", $olcam["MOEDA" . $olcfg["FRETE_M"]]);
    $omed->aadd("BASESAI", $olcam["MOEDA1"]);
    if ($olnom["DECI" . $olcfg["FRETE_M"]] == "N") {
        $omed->aadd("VALOSAI", round($atotais[1], 0));
    } else {
        $omed->aadd("VALOSAI", $atotais[1]);
    }
    $omed->aadd("QUANSAI", $atotais[0]);
    $omed->aadd("QUANFIN", $atotais[0]);
    $omed->aadd("VVISTA", 0);
    $omed->aadd("OBSERV", "ECOMMERCE:" . $nped . "  c:$ncli ");
    $omed->aadd("RETISAI", "N");
    $omed->aadd("OPESAI", 1);
    $omed->aadd("FRETSAI", "N");
    $omed->aadd("SITSAI", "N");
    //$omed->aadd("VENDFAT", $olcfg["VENDEDOR"]);
    $omed->aadd("VENDFAT", $nvend_ven);

    $omed->aadd("DATAREC", "current_date()", TRUE);
    $omed->aadd("BYDUBAI", "1");
    $omed->aadd("PLACASAI", $nped);
    $omed->aadd("VENCSAI", "CURRENT_DATE()", TRUE);
    $omed->aadd("ORIGEM_VDA", "ECOMM");

    $omed->aadd("OPEREC", ($ocfgcec["BXNOTA"] == "S" || $ocfgcec["CAXCENT"] == "S") ? 0 : 1);
    $omed->aadd("TERMSAI", "E99");
    $cobs = "Pedido via web Ecommerce no. $nped " . chr(13) . chr(10) . $cobs;
    $omed->aadd("OBSAI", $cobs, false, true);
    if (!$omed->grava()) {
        $aret[0]["estatus"] = 71;
        $aret[0]["cestatus"] = "Erro criando o pedido no cliente - " . $omed->cerro;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    mysqli_query($dbc, "update SAIDAS_SAI SET NOTASAI=" . $omed->nrecno . " WHERE RECNO=" . $omed->nrecno);
    $omag->aadd("pedido", $nped);
    $omag->aadd("nota", $omed->nrecno);
    $omag->aadd("dtemi", "date2unix(now())", true);
    $omag->aadd("dtped", "date2unix(now())", true);
    $omag->aadd("FORMAPAG", $npgt);
    $omag->aadd("DIAS", $nvence);
    if (!$omag->grava()) {
        mysqli_query($dbc, "update SAIDAS_SAI SET is_deleted='Y' WHERE RECNO=" . $omed->nrecno);
        $aret[0]["estatus"] = 71;
        $aret[0]["cestatus"] = "Erro criando o pedido no cliente..." . $omag->cerro;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $i = 0;
    $k = 0;
    foreach ($asku as $value) {
        $k++;
        $value = tiradigito($value);
        $nqua = $aqua[$k - 1];
        $agrava = array();
        $agravapro = array();

        if ($ccommit_web) {
            $odet = new Mediator3("PROSAI.SAI", W_CRIAR, '', '', $dbc);
        } else {
            $odet = new Mediator();
            $odet->init($dbc, "PROSAI.SAI");
        }
        $otabp = mysqli_query($dbc, "select p.codpro,p.kitpro,$preco_env as preco,$preco1_env as preco1 from PRODUTO_PRO p where p.is_deleted='N' and p.codpro=$value");
        $olinp = mysqli_fetch_array($otabp);
        if ($afiltro[$ncli]["preco_site"]) {
            $nprecosite = $apre[$k - 1];
        } else {
            $nprecosite = $olinp["preco"];
        }
        $aprodutos = array();
        $aprodutos[] = array("cod" => $olinp["codpro"], "kit" => $olinp["kitpro"], "pre" => $nprecosite, "qua" => $nqua, "ind" => 1);

        if ($olinp["kitpro"] == 'S') {
            $cstr = "select k.prokit,k.quakit*$nqua as quant,$preco_envk as preco,$preco1_envk as preco1 from PROKIT_PRO k where k.is_deleted='N' and k.codkit=$value";
            $otabk = mysqli_query($dbc, $cstr);
            while ($olink = mysqli_fetch_array($otabk)) {
                $aprodutos[] = array("cod" => $olink["prokit"], "kit" => 'K', "pre" => $olink["preco"], "qua" => $olink["quant"], "ind" => ($olink["preco"] * $olink["quant"]) / $olinp["preco"]);
            }
        }

        foreach ($aprodutos as $value1) {
            $lpro = mysqli_fetch_array(mysqli_query($dbc, "select PREMIN AS MINIMO,PREVEN,NOMPRO,ESTOQUE,CUSMED,ULTCUS," . $preco_env . " as PRECO 
            ,convert(if(p.codpromo=0,0,p.preco5)" . ($ntax !== 0 ? "*(1+($ntax/100))" : "") . ",decimal(15,2)) as PRECO_PROMO
            ,p.precoweb as precoweb,p.kitpro," . $preco1_env . " as PRECO1 ,p.IVA
            from PRODUTO_PRO p where is_deleted='N' " . ($afiltro[$ncli]["lEnviarInativos"] ? "" : " and p.ativo='S'") . "  and CODPRO=" . $value1["cod"]));

            $odet->aadd("NFSAI", $omed->nrecno);
            $odet->aadd("DATASPRO", "current_date()", true);
            $odet->aadd("PRSAI", $value1["cod"]);
            $odet->aadd("QUSAI", $value1["qua"]);
            $ncumoe = $afiltro[$ncli]["preco_site"] ? $nprecosite * $value1["ind"] : ($nprecosite * $value1["ind"] / $olcam["MOEDA1"]) * $olcam["MOEDA" . $olcfg["FRETE_M"]];
            $odet->aadd("CUMOE", $ncumoe);
            $odet->aadd("QTVSAI", $value1["qua"]);
            ++$i;
            $odet->aadd("REGSAI", $i);
            $odet->aadd("KITSAI", $value1["kit"]);
            $odet->aadd("CXUNSA", "C");
            $odet->aadd("NOMEPSA", $lpro["NOMPRO"]);

            $aiva = CalcIva($aaux[1], $nprecosite * $value1["ind"], $ncliente);
            $odet->aadd("EXENTPRO", $aiva[0]);
            $odet->aadd("GRAVAPRO", $aiva[1]);

            $odet->aadd("CUSATU", $ocfgcec["LUCESPE"] == "N" ? ($lpro["CUSMED"] < 0 ? $lpro["ULTCUS"] : $lpro["CUSMED"]) : $lpro["ULTCUS"]);
            $odet->aadd("PREMIN", $lpro["MINIMO"]);
            $odet->aadd("CUSAI", $afiltro[$ncli]["preco_site"] ? ($nprecosite * $value1["ind"] * $olcam["MOEDA1"]) / $olcam["MOEDA" . $olcfg["FRETE_M"]] : $nprecosite * $value1["ind"]);

            if ($olincli["PAISCLI"] == 1) {
                $odet->aadd("GRAVADO", $ncumoe); //PARAGUAIO
                $odet->aadd("EXENTO", 0); //PARAGUAIO
                $odet->aadd("IVA", $lpro["IVA"]);
            } else {
                $odet->aadd("GRAVADO", 0); //PARAGUAIO
                $odet->aadd("EXENTO", $ncumoe); //PARAGUAIO
            }
            $odet->grava();

            //$otabloc = mysqli_query($dbc, "select recno from LOCAL_LOC WHERE IS_DELETED='N' AND LOCLOC=" . $olcfg["LOJA"] . " AND PROLOC=" . $value1["cod"]);
            $otabloc = mysqli_query($dbc, "select recno from LOCAL_LOC WHERE IS_DELETED='N' AND LOCLOC=" . $nloja_ven . " AND PROLOC=" . $value1["cod"]);
            if (mysqli_num_rows($otabloc) == 0) {
                if ($ccommit_web) {
                    $oloc = new Mediator3("LOCAL.LOC", W_CRIAR, '', '', $dbc);
                } else {
                    $oloc = new Mediator();
                    $oloc->init($dbc, "LOCAL.LOC");
                }
                //$oloc->aadd("LOCLOC", $olcfg["LOJA"]);
                $oloc->aadd("LOCLOC", $nloja_ven);
                $oloc->aadd("PROLOC", $value1["cod"]);
                $oloc->grava();
            }
            if ($ccommit_web) {
                //$oloc = new Mediator3("LOCAL.LOC", W_CRIAR, '', "IS_DELETED='N' AND LOCLOC=" . $olcfg["LOJA"] . " AND PROLOC=" . $value1["cod"], $dbc);
                $oloc = new Mediator3("LOCAL.LOC", W_CRIAR, '', "IS_DELETED='N' AND LOCLOC=" . $nloja_ven . " AND PROLOC=" . $value1["cod"], $dbc);
            } else {
                $oloc = new Mediator();
                //$oloc->init($dbc, "LOCAL.LOC", "LOCLOC=" . $olcfg["LOJA"] . " AND PROLOC=" . $value1["cod"]);
                $oloc->init($dbc, "LOCAL.LOC", "LOCLOC=" . $nloja_ven . " AND PROLOC=" . $value1["cod"]);
            }
            $oloc->aadd("QUALOC", "qualoc-" . $value1["qua"], true);
            $oloc->aadd("FPGLOC", "fpgloc+" . $value1["qua"], true);
            $oloc->grava();

            mysqli_query($dbc, "update PRODUTO_PRO SET ULTVEN=CURRENT_DATE(),ESTOQUE=ESTOQUE-" . $value1["qua"] . ",FPGPRO=FPGPRO+" . $value1["qua"] . " where is_deleted='N' and codpro=" . $value1["cod"]);
            $nseq1 = 0;
            $nseq2 = 0;
            if ($agrade[$i - 1]) {
                $nseq1 = $agrade[$i - 1][0];
                $nseq2 = $agrade[$i - 1][1];
            }
            if ($nseq1 > 0 && $nseq2 > 0) {
                if ($ccommit_web) {
                    $omedg = new Mediator3("GRADESAI.SAI", W_CRIAR, '', '', $dbc);
                } else {
                    $omedg = new Mediator();
                    $omedg->init($dbc, "GRADESAI.SAI");
                }
                $omedg->aadd("nfsai", $omed->nrecno);
                $omedg->aadd("prsai", $value1["cod"]);
                $omedg->aadd("qusai", $value1["qua"]);
                $omedg->aadd("seqgrade1", $nseq1);
                $omedg->aadd("seqgrade2", $nseq2);
                $omedg->aadd("obsgra", $agra[$i - 1][0] . " / " . $agra[$i - 1][1]);
                $omedg->grava();

                //if (mysqli_num_rows(mysqli_query($dbc, "select * from ESTLJGRA_PRO WHERE IS_DELETED='N' AND LOJGRADE=" . $olcfg["LOJA"] . " and PROGRADE=" . $value1["cod"] . " AND SEQGRADE1=$nseq1 and SEQGRADE2=$nseq2")) == 0) {
                if (mysqli_num_rows(mysqli_query($dbc, "select * from ESTLJGRA_PRO WHERE IS_DELETED='N' AND LOJGRADE=" . $nloja_ven . " and PROGRADE=" . $value1["cod"] . " AND SEQGRADE1=$nseq1 and SEQGRADE2=$nseq2")) == 0) {
                    if ($ccommit_web) {
                        $omedg = new Mediator3("ESTLJGRA.PRO", W_CRIAR, '', '', $dbc);
                    } else {
                        $omedg = new Mediator();
                        $omedg->init($dbc, "ESTLJGRA.PRO");
                    }
                    //$omedg->aadd("LOJGRADE", $olcfg["LOJA"]);
                    $omedg->aadd("LOJGRADE", $nloja_ven);
                    $omedg->aadd("PROGRADE", $value1["cod"]);
                    $omedg->aadd("SEQGRADE1", $nseq1);
                    $omedg->aadd("SEQGRADE2", $nseq2);
                    $omedg->grava();
                }


                //mysqli_query($dbc, "update ESTLJGRA_PRO SET ESTOQUE=ESTOQUE-" . $value1["qua"] . " WHERE IS_DELETED='N' AND LOJGRADE=" . $olcfg["LOJA"] . " AND PROGRADE=" . $value1["cod"] . " and seqgrade1=$nseq1 and seqgrade2=$nseq2");
                mysqli_query($dbc, "update ESTLJGRA_PRO SET ESTOQUE=ESTOQUE-" . $value1["qua"] . " WHERE IS_DELETED='N' AND LOJGRADE=" . $nloja_ven . " AND PROGRADE=" . $value1["cod"] . " and seqgrade1=$nseq1 and seqgrade2=$nseq2");
                mysqli_query($dbc, "update ESTGRADE_PRO set estoque=estoque-" . $value1["qua"] . ",fpgpro=fpgpro+" . $value1["qua"] . " WHERE IS_DELETED='N' AND PROGRADE=" . $value1["cod"] . " and seqgrade1=$nseq1 and seqgrade2=$nseq2");
            }
        }
    }

    $csex = array("M" => "Masculino", "F" => "Feminino", "O" => "Outros", "N" => "Não Declarado")[pegavar("sex")];
    $cstr = "insert cliente" . ($afiltro[$ncli]["config"] == '' ? '0' : $afiltro[$ncli]["config"]) . "_ecm 
(pedido,name,email,date_of_birth,gender
,vat_number,phone1,phone2,phone3
,b_street,b_number,b_detail,b_neighborhood,b_city,b_region,b_postcode,b_phone1,b_phone2
,s_street,s_number,s_detail,s_neighborhood,s_city,s_region,s_postcode,s_phone,s_phone1
,s_phone2)
values ('$nped','" . addslashes(pegavar("nom")) . "','" . addslashes(pegavar("eml")) . "','" . addslashes(pegavar("nas")) . "','$csex'
    ,'" . addslashes(pegavar("doc")) . "','" . addslashes(pegavar("fn1")) . "','" . addslashes(pegavar("fn2")) . "','" . addslashes(pegavar("fn3")) . "'
    ,'" . addslashes(pegavar("end")) . "','" . addslashes(pegavar("num")) . "','" . addslashes(pegavar("com")) . "','" . addslashes(pegavar("bai")) . "','" . addslashes(pegavar("cid")) . "','" . addslashes(pegavar("uf")) . "','" . addslashes(pegavar("cep")) . "','" . addslashes(pegavar("fn1")) . "','" . addslashes(pegavar("fn2")) . "'
    ,'" . addslashes(pegavar("end")) . "','" . addslashes(pegavar("num")) . "','" . addslashes(pegavar("com")) . "','" . addslashes(pegavar("bai")) . "','" . addslashes(pegavar("cid")) . "','" . addslashes(pegavar("uf")) . "','" . addslashes(pegavar("cep")) . "','" . addslashes(pegavar("fn1")) . "','" . addslashes(pegavar("fn2")) . "'
    ,'')";
    mysqli_query($dbc, $cstr);

    $onot = mysqli_fetch_array(mysqli_query($dbc, "select DATASAI,HORASAI,QUANSAI,VALOSAI from SAIDAS_SAI where RECNO=$omed->nrecno"));
    $aret[] = array("ped" => poedigito($omed->nrecno), "emi" => $onot["DATASAI"] . " " . $onot["HORASAI"], "ite" => number_format($onot["QUANSAI"], 0)
        , "val" => number_format($onot["VALOSAI"], 2), "day" => $nvence);
    $aret[0]["element"] = $aret[0]["element"] + 1;
} elseif ($nope == 13) { //CONSULTAR PEDIDOS
    $nped = pegavar("ped", $lpost);
    $npdc = pegavar("pdc", $lpost);

    if ($nped == '' && $npdc == '') {
        $aret[0]["estatus"] = 40;
        $aret[0]["cestatus"] = "Pedido vazio";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $npedant = '';
    if ($nped != '') {
        $otableped = mysqli_query($dbc, "select * from NOTA" . $afiltro[$ncli]["config"] . "_ECM WHERE PEDIDO='$nped'");
        if (mysqli_num_rows($otableped) == 0) {
            $aret[0]["estatus"] = 70;
            $aret[0]["cestatus"] = "Pedido nao existe no cliente";
            echo json_encode($aret);
            logs($aret);
            return;
        }
        $olinhaped = mysqli_fetch_array($otableped);
        $otablenot = mysqli_query($dbc, "select * from SAIDAS_SAI WHERE RECNO=" . $olinhaped["NOTA"]);
        $olinhanot = mysqli_fetch_array($otablenot);
        if ($olinhanot["IS_DELETED"] = 'Y') {
            $npedant = $olinhanot["NOTASAI"];
            $otablenot = mysqli_query($dbc, "select * from SAIDAS_SAI WHERE fidelidsai=$npedant");
            $olinhanot = mysqli_fetch_array($otablenot);
            $olinhaped["NOTA"] = $olinhanot["NOTASAI"];
        }
    }
    if ($npdc != '') {
        $otableped = mysqli_query($dbc, "select * from NOTA" . $afiltro[$ncli]["config"] . "_ECM WHERE NOTA='" . tiradigito($npdc) . "'");

        if (mysqli_num_rows($otableped) == 0 || !vedigito($npdc)) {
            $aret[0]["estatus"] = 70;
            $aret[0]["cestatus"] = "Pedido nao existe no cliente";
            echo json_encode($aret);
            logs($aret);
            return;
        }
        $olinhaped = mysqli_fetch_array($otableped);
        $otablenot = mysqli_query($dbc, "select * from SAIDAS_SAI WHERE RECNO='" . tiradigito($npdc) . "'");
        $olinhanot = mysqli_fetch_array($otablenot);
    }

    if (mysqli_num_rows($otablenot) == 0 && mysqli_num_rows($otableped) > 0) {
        $aret[0]["estatus"] = 71;
        $aret[0]["cestatus"] = "Pedido existe mas nao foi gerado pela plataforma";
        echo json_encode($aret);
        logs($aret);
        return;
    }

    if (mysqli_num_rows($otablenot) == 0 || mysqli_num_rows($otableped) == 0) {
        $aret[0]["estatus"] = 74;
        $aret[0]["cestatus"] = "Inconsistencia nos dados do pedido no cliente";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    if ($olinhanot["IS_DELETED"] == "Y" || $olinhaped["IS_DELETED"] == "Y") {
        $aret[0]["estatus"] = 72;
        $aret[0]["cestatus"] = "Pedido deletado ";
        echo json_encode($aret);
        logs($aret);
        return;
    }

    $adet = array();
    $adet["pedant"] = poedigito($npedant);
    $adet["ped"] = $olinhaped["PEDIDO"];
    $adet["pdc"] = POEDIGITO($olinhaped["NOTA"]);
    $adet["emi"] = $olinhanot["DATASAI"] . " " . $olinhanot["HORASAI"];
    $adet["ite"] = number_format($olinhanot["QUANSAI"], 0);
    $adet["val"] = number_format($olinhanot["VALOSAI"], 2);
    $adet["pgt"] = $olinhaped["FORMAPAG"];
    $adet["day"] = $olinhaped["DIAS"];
    $adet["ccl"] = $olinhanot["CLIESAI"];
    $adet["ncl"] = Converter(mysqli_fetch_row(mysqli_query($dbc, "select nomcli from CLIENTE_CLI WHERE IS_DELETED='N' AND CODCLI=" . $olinhanot["CLIESAI"]))[0]);
    $adet["rtr"] = $olinhaped["RASTREIO"];

    if ($olinhaped["DTPEDCAN"] > 0) {
        $adet["sit"] = 2;
    } elseif ($olinhaped["DTDEV"] > 0) {
        $adet["sit"] = 9;
    } elseif ($olinhaped["DTRMAINI"] > 0 && $olinhaped["DTRMAFIM"] == 0) {
        $adet["sit"] = 8;
    } elseif ($olinhaped["DTETG"] > 0) {
        $adet["sit"] = 7;
    } elseif ($olinhaped["DTEXP"] > 0) {
        $adet["sit"] = 6;
    } elseif ($olinhaped["DTSEPFIN"] > 0) {
        $adet["sit"] = 5;
    } elseif ($olinhaped["DTSEPINI"] > 0) {
        $adet["sit"] = 4;
    } elseif ($olinhaped["DTPAG"] > 0 || $olinhanot["SITSAI"] == "S") {
        $adet["sit"] = 3;
    } elseif ($olinhaped[""] > 0) {
        
    } else {
        $adet["sit"] = 1;
    }
    $aaux = array("can" => "DTPEDCAN", "pag" => "DTPAG", "spi" => "DTSEPINI", "spf" => "DTSEPFIN", "exp" => "DTEXP", "ent" => "DTETG", "dev" => "DTDEV", "rmi" => "DTRMAINI", "rmf" => "DTRMAFIN");
    foreach ($aaux as $key => $value) {
        $adet[$key] = $olinhaped[$value] > 0 ? (Unix2Date($olinhaped[$value], true)[0]) : '';
    }

    $aret[] = $adet;
    $aret[0]["element"] = $aret[0]["element"] + 1;

    $otable = mysqli_query($dbc, "select prsai,qusai,cusai from PROSAI_SAI WHERE NFSAI=" . $olinhanot["RECNO"] . " ORDER BY REGSAI ");
    while ($olinha = mysqli_fetch_array($otable)) {
        $adet = array();
        $adet["sku"] = poedigito($olinha["prsai"]);
        $adet["qtd"] = number_format($olinha["qusai"], 0);
        $adet["val"] = number_format($olinha["cusai"], 2);
        $aret[] = $adet;
        $aret[0]["element"] = $aret[0]["element"] + 1;
    }
} elseif ($nope == 15) {
    $nped = pegavar("ped", $lpost);
    $npdc = pegavar("pdc", $lpost);

    if ($nped == '' || !$npdc) {
        $aret[0]["estatus"] = 40;
        $aret[0]["cestatus"] = "Pedido vazio";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    if (!vedigito($npdc)) {
        $aret[0]["estatus"] = 40;
        $aret[0]["cestatus"] = "Pedido do CEC invalido";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $npdc = tiradigito($npdc);
    $otableped = mysqli_query($dbc, "select * from NOTA" . $afiltro[$ncli]["config"] . "_ECM WHERE PEDIDO='$nped'");
    if (mysqli_num_rows($otableped) == 0) {
        $aret[0]["estatus"] = 70;
        $aret[0]["cestatus"] = "Pedido nao existe no cliente";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $olinhaped = mysqli_fetch_array($otableped);
    if ($npdc + 0 != $olinhaped["NOTA"] + 0) {
        $aret[0]["estatus"] = 74;
        $aret[0]["cestatus"] = "Inconsistência de dados com o pedido do CEC ...";
        echo json_encode($aret);
        logs($aret);
        return;
    }

    $otablenot = mysqli_query($dbc, "select * from SAIDAS_SAI WHERE RECNO=" . $olinhaped["NOTA"]);
    $olinhanot = mysqli_fetch_array($otablenot);

    if (mysqli_num_rows($otablenot) == 0 && mysqli_num_rows($otableped) > 0) {
        $aret[0]["estatus"] = 71;
        $aret[0]["cestatus"] = "Pedido existe mas nao foi gerado pela plataforma";
        echo json_encode($aret);
        logs($aret);
        return;
    }

    if (mysqli_num_rows($otablenot) == 0 || mysqli_num_rows($otableped) == 0) {
        $aret[0]["estatus"] = 74;
        $aret[0]["cestatus"] = "Inconsistencia nos dados do pedido no cliente";
        echo json_encode($aret);
        logs($aret);
        return;
    }

    if ($olinhanot["SITSAI"] == "S" || $olinhaped["DTPAG"] > 0) {
        $aret[0]["estatus"] = 80;
        $aret[0]["cestatus"] = "Pedido ja faturado no CEC";
        echo json_encode($aret);
        logs($aret);
        return;
    }


    if ($olinhanot["IS_DELETED"] == "Y" || $olinhaped["IS_DELETED"] == "Y") {
        $aret[0]["estatus"] = 72;
        $aret[0]["cestatus"] = "Pedido deletado ";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    mysqli_query($dbc, "update SAIDAS_SAI n,CONFIG c 
        SET n.SITSAI='S'
        ,n.HORAREC=CURRENT_TIME()
        ,n.DATAREC=if(c.CUSRECE='N',CURRENT_DATE(),'1000-01-01')
        ,n.OPEREC=1
        ,n.VDUPSAI=n.VALOSAI
        ,n.NRDUP=1
        ,n.TIPOSAI='" . ($afiltro[$ncli]["pagvis"] ? "V" : "P") . "' 
        WHERE c.RECNO=1 and n.RECNO=" . $olinhaped["NOTA"]);
    $otablenot = mysqli_query($dbc, "select * from SAIDAS_SAI WHERE RECNO=" . $olinhaped["NOTA"]);
    $olinhanot = mysqli_fetch_array($otablenot);

    $ocfgcec = mysqli_fetch_array(mysqli_query($dbc, "select * from CONFIG where recno=1 "));
    if (!$afiltro[$ncli]["pagvis"]) {
        if ($ccommit_web) {
            $odup = new Mediator3("DUPLICAT.CLI", W_CRIAR, '', '', $dbc);
        } else {
            $odup = new Mediator();
            $odup->init($dbc, "DUPLICAT.CLI");
        }
        $l9 = mysqli_fetch_row(mysqli_query($dbc, "select COL_CLP_LEN from CLP_TAB_COLS where TAB_NAME='DUPLICAT_CLI' AND COL_NAME='NUMEDUP'"))[1] > 9;
        $odup->aadd("NUMEDUP", padl($olinhanot["NOTASAI"], ($l9 ? 9 : 7)) . padl("1", 2));
        $odup->aadd("VENCDUP", $olinhanot["VENCSAI"]);
        $odup->aadd("MOEDUP", $olinhanot["MOEDA"]);
        $odup->aadd("VALODUP", $olinhanot["VALOSAI"]);
        $odup->aadd("CLIEDUP", $olinhanot["CLIESAI"]);
        $odup->aadd("BANCDUP", $olcfg["BANCO"]);
        $odup->aadd("BASEDUP", $olinhanot["BASESAI"]);
        $odup->aadd("CAMBDUP", $olinhanot["CAMBSAI"]);
        $odup->aadd("EMISDUP", $olinhanot["DATASAI"]);
        $odup->aadd("FATDUP", "current_date()", true);
        $odup->aadd("DATODUP", "current_date()", true);
        $odup->grava();
    }
    if ($ccommit_web) {
        $oope = new Mediator3("OPERACAX.CAX", W_ALTERAR, '', 1, $dbc);
    } else {
        $oope = new Mediator();
        $oope->init($dbc, "OPERACAX.CAX", "RECNO=1");
    }
    $oope->aadd("NUMOPE", "NUMOPE+1", TRUE);
    $oope->grava();
    $olinopx = mysqli_linha("OPERACAX_CAX", "NUMOPE", "RECNO=1", '', $dbc);

    if ($ccommit_web) {
        $ocax = new Mediator3("CAIXA.CAX", W_CRIAR, '', '', $dbc);
    } else {
        $ocax = new Mediator();
        $ocax->init($dbc, "CAIXA.CAX");
    }
    $ocax->aadd("CODCAX", $olcfg["CONTA"]);
    $ocax->aadd("DOCCAX", $olinhanot["NOTASAI"]);
    $olinhadis = mysqli_linha("DISTRIB_CAX", "HISDIS", "RECNO=" . ($afiltro[$ncli]["pagvis"] ? "10" : "11"), '', $dbc); // 10 para vista
    $ocax->aadd("CODHIS", $olinhadis["HISDIS"]);
    $ocax->aadd("HORACAX", "CURRENT_TIME()", TRUE);
    $ocax->aadd("TERCAX", "W99");
    $ocax->aadd("NUMOPE", $olinopx["NUMOPE"]);
    $ocax->aadd("OPECAX", 1);
    $ocax->aadd("DATCAX", "current_date()", true);
    $ocax->aadd("EMICAX", "current_date()", true);
    $ocax->aadd("VALCAX", $olinhanot["VALOSAI"]);
    $ocax->aadd("VALMCAX", $olinhanot["VALOSAI"]);
    $ocax->aadd("MOECAX", $olinhanot["MOEDA"]);
    $ocax->aadd("BASECAX", $olinhanot["BASESAI"]);
    $ocax->aadd("CAMBCAX", $olinhanot["CAMBSAI"]);
    $ocax->aadd("LOJCAX", $olinhanot["LOJASAI"]);

    $ocax->aadd("TIPCAX", ($afiltro[$ncli]["pagvis"] ? "C" : "N")); // c para VISTA
    $olincli = mysqli_linha("CLIENTE_CLI", "NOMCLI", "CODCLI=" . $olinhanot["CLIESAI"], '', $dbc);
    $ocax->aadd("NOMCAX", $olincli["NOMCLI"]);
    $ocax->aadd("CPLCAX", "VENDA REALIZADA VIA ECOMMERCE PEDIDO: " . $olinhaped["PEDIDO"]);
    $ocax->grava();

    if ($ccommit_web) {
        $omoe = new Mediator3("MOEDAS.CAX", W_CRIAR, '', '', $dbc);
    } else {
        $omoe = new Mediator();
        $omoe->init($dbc, "MOEDAS.CAX");
    }
    $omoe->aadd("OPERMOE", $olinopx["NUMOPE"]);
    $omoe->aadd("OPCXMOE", $olinhanot["MOEDA"]);
    $omoe->aadd("HORAMOE", "current_time()", true);
    $omoe->aadd("CONTMOE", $olcfg["CONTA"]);
    $omoe->aadd("DATAMOE", "current_date()", true);
    $omoe->aadd("SINAL", ($afiltro[$ncli]["pagvis"] ? "+" : "N")); // + pava vista

    $olincam = mysqli_fetch_array(mysqli_query($dbc, "select * from CAMBIO_CAM where is_deleted='N' order by datcam desc limit 1"));

    $omoe->aadd("CAMB01", $olincam["MOEDA1"]);
    $omoe->aadd("CAMB02", $olincam["MOEDA2"]);
    $omoe->aadd("CAMB03", $olincam["MOEDA3"]);
    $omoe->aadd("CAMB04", $olincam["MOEDA4"]);
    $omoe->grava();

    mysqli_query($dbc, "update NOTA" . $afiltro[$ncli]["config"] . "_ECM SET DTPAG=CURRENT_DATE() WHERE IS_DELETED='N' AND PEDIDO='$nped'");

    $aret[0]["estatus"] = 0;
    $aret[0]["cestatus"] = "OK";
} elseif ($nope == 16) {
    $nped = pegavar("ped", $lpost);
    $npdc = pegavar("pdc", $lpost);

    if ($nped == '' || !$npdc) {
        $aret[0]["estatus"] = 40;
        $aret[0]["cestatus"] = "Pedido vazio";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    if (!vedigito($npdc)) {
        $aret[0]["estatus"] = 40;
        $aret[0]["cestatus"] = "Pedido do CEC invalido";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $npdc = tiradigito($npdc);
    $otableped = mysqli_query($dbc, "select * from NOTA" . $afiltro[$ncli]["config"] . "_ECM WHERE IS_DELETED='N' AND PEDIDO='$nped'");
    if (mysqli_num_rows($otableped) == 0) {
        $aret[0]["estatus"] = 70;
        $aret[0]["cestatus"] = "Pedido nao existe no cliente";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $olinhaped = mysqli_fetch_array($otableped);
    if ($npdc + 0 != $olinhaped["NOTA"] + 0) {
        $aret[0]["estatus"] = 74;
        $aret[0]["cestatus"] = "Inconsistência de dados com o pedido do CEC ...";
        echo json_encode($aret);
        logs($aret);
        return;
    }

    $otablenot = mysqli_query($dbc, "select * from SAIDAS_SAI WHERE RECNO=" . $olinhaped["NOTA"]);
    $olinhanot = mysqli_fetch_array($otablenot);

    if (mysqli_num_rows($otablenot) == 0 && mysqli_num_rows($otableped) > 0) {
        $aret[0]["estatus"] = 71;
        $aret[0]["cestatus"] = "Pedido existe mas nao foi gerado pela plataforma";
        echo json_encode($aret);
        logs($aret);
        return;
    }

    if (mysqli_num_rows($otablenot) == 0 || mysqli_num_rows($otableped) == 0) {
        $aret[0]["estatus"] = 74;
        $aret[0]["cestatus"] = "Inconsistencia nos dados do pedido no cliente";
        echo json_encode($aret);
        logs($aret);
        return;
    }

    if ($olinhanot["SITSAI"] == "S" || $olinhaped["DTPAG"] > 0) {
        $aret[0]["estatus"] = 80;
        $aret[0]["cestatus"] = "Pedido ja faturado no CEC";
        echo json_encode($aret);
        logs($aret);
        return;
    }


    if ($olinhanot["IS_DELETED"] == "Y" || $olinhaped["IS_DELETED"] == "Y") {
        $aret[0]["estatus"] = 72;
        $aret[0]["cestatus"] = "Pedido deletado ";
        echo json_encode($aret);
        logs($aret);
        return;
    }




    mysqli_query($dbc, "update SAIDAS_SAI set is_deleted='Y'
        WHERE RECNO=" . $olinhaped["NOTA"]);

    $otable = mysqli_query($dbc, "select * from PROSAI_SAI where is_deleted='N' and nfsai=" . $olinhaped["NOTA"]);

    while ($olinha = mysqli_fetch_array($otable)) {
        $otabloc = mysqli_query($dbc, "select prsai from LOCAL_LOC WHERE  IS_DELETED='N' AND LOCLOC=" . $olinhanot["LOJASAI"] . " AND PROLOC=" . $olinha["PRSAI"]);
        mysqli_query($dbc, "update LOCAL_LOC set qualoc=qualoc+" . $olinha["QUSAI"] . ",fpgloc=fpgloc-" . $olinha["QUSAI"] . " where IS_DELETED='N' AND LOCLOC=" . $olinhanot["LOJASAI"] . " AND PROLOC=" . $olinha["PRSAI"]);
        mysqli_query($dbc, "update PRODUTO_PRO SET ESTOQUE=ESTOQUE+" . $olinha["QUSAI"] . ",fpgpro=fpgpro-" . $olinha["QUSAI"] . " where IS_DELETED='N' AND CODPRO=" . $olinha["PRSAI"]);
        mysqli_query($dbc, "update PROSAI_SAI SET IS_DELETED='Y' WHERE RECNO=" . $olinha["RECNO"]);
    }



    $aret[0]["estatus"] = 0;
    $aret[0]["cestatus"] = "OK";
} elseif ($nope == 31) {

    $cstr = "select * from NOTA" . $afiltro[$ncli]["config"] . "_ECM WHERE IS_DELETED='N' AND DTPAG=0";
    $otableped = mysqli_query($dbc, $cstr);
    while ($olinha = mysqli_fetch_array($otableped)) {
        $adet = array();
        $adet["pdc"] = poedigito($olinha["NOTA"]);
        $adet["emi"] = unix2date($olinha["DTEMI"]);
        $adet["day"] = $olinha["DIAS"];
        $adet["ped"] = $olinha["PEDIDO"];
        $aret[] = $adet;
        $aret[0]["element"] = $aret[0]["element"] + 1;
    }
} else {
    $aret[0]["estatus"] = 3;
    $aret[0]["cestatus"] = "Operacao nao reconhecida";
}
$aret[0]["data_ret"] = date('Y-m-d H:i:s');
$aret[0]["query_time"] = strtotime($aret[0]["data_ret"]) - strtotime($aret[0]["data_sol"]);
echo json_encode($aret);
logs($aret);
return;

function logs($aretpar, $cComplemento = '') {
    global $db;
    if (!$db) {
        $db = conecta($server, $port, $user, $password, $database);
    }
    $aret = $aretpar[0];
    mysqli_query($db, "CREATE TABLE if not exists `logs` (
  `ordem` int(10) not null  primary key auto_increment,
  `ambiente` char(1) DEFAULT 'H',
  `data_sol` datetime DEFAULT NULL,
  `data_ret` datetime DEFAULT NULL,
  `estatus` decimal(3,0) DEFAULT '0',
  `cestatus` varchar(254) NOT NULL DEFAULT '',
  `element` decimal(10,0) DEFAULT NULL,
  `cliente` decimal(10,0) DEFAULT NULL,
  `operacao` decimal(2,0) DEFAULT NULL,
  `post` text NOT NULL,
  `get` text NOT NULL,
  `ip` varchar(14) DEFAULT '',
  `query` text not null,
  `complemento` text not null,
  KEY `ambiente` (`ambiente`,`data_sol`,`operacao`),
  KEY `operacao` (`operacao`,`data_sol`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ");

    $cpost = "";
    foreach ($_POST as $key => $value) {
        $cpost .= "$key , $value " . chr(10);
    }
    $cget = "";
    foreach ($_GET as $key => $value) {
        $cget .= "$key , $value " . chr(10);
    }
    $cstr = "insert logs set ambiente='" . $aret["ambiente"] . "'" .
            ",data_sol='" . $aret["data_sol"] . "'" .
            ",data_ret='" . $aret["data_ret"] . "'" .
            ",estatus='" . $aret["estatus"] . "'" .
            ",cestatus='" . $aret["cestatus"] . "'" .
            ",element='" . $aret["element"] . "'" .
            ",cliente='" . pegavar("cod") . "'" .
            ",operacao='" . pegavar("ope") . "'" .
            ",post='" . $cpost . "'" .
            ",get='" . $cget . "'" .
            ",complemento='" . $cComplemento . "'" .
            ",query='" . ($_SERVER['HTTPS'] ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . chr(10) . $_SERVER["QUERY_STRING"] . "'" .
            ",ip='" . $_SERVER["REMOTE_ADDR"] . "'"
    ;
    mysqli_query($db, $cstr);
}

function CalcIva($nProduto, $nValor, $ncliente) {
    return array(0, 0, 0);

    $nGravada = 0;
    $nExenta = 0;
    $nIva = 0;
    $nAux = 0;
    $nDif = 0;
    global $dbc;
    $otab = mysqli_query($dbc, "select IVA,DECRETO FROM PRODUTO_PRO p WHERE IS_DELETED='N'  " . ($afiltro[$ncli]["lEnviarInativos"] ? "" : " and p.ativo='S'") . " AND CODPRO=$nProduto");
    $olin = mysqli_fetch_array($otab);

    if (!$olin["IVA"] || $olin["DECRETO"] != 'S') {
        return array(0, 0, 0);
    }
    $olincli = mysqli_fetch_array(mysqli_query($dbc, "select PAISCLI,REGICLI FROM CLIENTE_CLI where is_deleted='N' and codcli=$ncliente"));
    if (!($olincli["PAISCLI"] == 1 && $olincli["REGICLI"] != '')) {
        return array(0, 0, 0);
    }
    $nIvaPer = $olin["IVA"];
    $nExenta = round(($nValor * 85 / 100) / 1.015, 4);

    $nAux = ($nValor * 15 / 100) / 1.015;
    $nAux = round($nAux + ($nAux * $nIvaPer / 100), 4);

    if ($nExenta + $nAux <> $nValor) {
        $nDif = $nValor - ($nExenta + $nAux);
        if ($nDif > 0) {
            $nAux = $nAux + $nDif;
        }
        if ($nDif < 0) {
            $nAux = $nAux - $nDif;
        }
    }

    $nGravada = $nAux;
    return array($nExenta, $nGravada, $ngravada - ($nGravada / (1 + ($nIvaPer / 100))));
}

function LimpaRg($crg, $lSemLetras = false) {
    $crg = strtoupper($crg);
    $cret = '';

    for ($ni = 0;
            $ni < strlen($crg);
            $ni++) {
        $naux = ord($crg[$ni]);
        if ($lSemLetras) {
            if (($naux >= 48 && $naux <= 57)) {
                $cret .= substr($crg, $ni, 1);
            }
        } else {
            if (($naux >= 65 && $naux <= 90) || ($naux >= 48 && $naux <= 57)) {
                $cret .= substr($crg, $ni, 1);
            }
        }
    }
    return $cret;
}

function VeNasc($cnas, &$cnome = '') {
    $apais = array("ABW" => "Aruba", "AFG" => "Afghanistan", "AGO" => "Angola", "AIA" => "Anguilla", "ALA" => "Åland Islands", "ALB" => "Albania", "AND" => "Andorra", "ANT" => "Netherlands Antilles", "ARE" => "United Arab Emirates", "ARG" => "Argentina", "ARM" => "Armenia", "ASM" => "American Samoa", "ATA" => "Antárctica", "ATF" => "French Southern Territories", "ATG" => "Antigua and Barbuda", "AUS" => "Australia", "AUT" => "Austria", "AZE" => "Azerbaijan", "BDI" => "Burundi", "BEL" => "Belgium", "BEN" => "Benin", "BFA" => "Burkina Faso", "BGD" => "Bangladesh", "BGR" => "Bulgaria", "BHR" => "Bahrain", "BHS" => "Bahamas", "BIH" => "Bosnia and Herzegovina", "BLM" => "Saint Barthélemy", "BLR" => "Belarus", "BLZ" => "Belize", "BMU" => "Bermuda", "BOL" => "Bolivia", "BRA" => "Brazil", "BRB" => "Barbados", "BRN" => "Brunei Darussalam", "BTN" => "Bhutan", "BVT" => "Bouvet Island", "BWA" => "Botswana", "CAF" => "Central African Republic", "CAN" => "Canada", "CCK" => "Cocos (Keeling) Islands", "CHE" => "Switzerland", "CHL" => "Chile", "CHN" => "China", "CIV" => "Côte d'Ivoire", "CMR" => "Cameroon", "COD" => "Congo, the Democratic Republic of the", "COG" => "Republic of the Congo|Congo", "COK" => "Cook Islands", "COL" => "Colombia", "COM" => "Comoros", "CPV" => "Cape Verde", "CRI" => "Costa Rica", "CUB" => "Cuba", "CXR" => "Christmas Island", "CYM" => "Cayman Islands", "CYP" => "Cyprus", "CZE" => "Czechia", "DEU" => "Germany", "DJI" => "Djibouti", "DMA" => "Dominica", "DNK" => "Denmark", "DOM" => "Dominican Republic", "DZA" => "Algeria", "ECU" => "Ecuador", "EGY" => "Egypt", "ERI" => "Eritrea", "ESH" => "Western Sahara", "ESP" => "Spain", "EST" => "Estonia", "ETH" => "Ethiopia", "FIN" => "Finland", "FJI" => "Fiji", "FLK" => "Falkland Islands (Malvinas)", "FRA" => "France", "FRO" => "Faroe Islands", "FSM" => "Micronesia, Federated States of", "GAB" => "Gabon", "GBR" => "United Kingdom", "GEO" => "Georgia", "GGY" => "Guernsey", "GHA" => "Ghana", "GIB" => "Gibraltar", "GIN" => "Guinea", "GLP" => "Guadeloupe", "GMB" => "Gambia", "GNB" => "Guinea-Bissau", "GNQ" => "Equatorial Guinea", "GRC" => "Greece", "GRD" => "Grenada", "GRL" => "Greenland", "GTM" => "Guatemala", "GUF" => "French Guiana", "GUM" => "Guam", "GUY" => "Guyana", "HKG" => "Hong Kong", "HMD" => "Heard Island and McDonald Islands", "HND" => "Honduras", "HRV" => "Croatia", "HTI" => "Haiti", "HUN" => "Hungary", "IDN" => "Indonesia", "IMN" => "Isle of Man", "IND" => "India", "IOT" => "British Indian Ocean Territory", "IRL" => "Ireland", "IRN" => "Iran, Islamic Republic of", "IRQ" => "Iraq", "ISL" => "Iceland", "ISR" => "Israel", "ITA" => "Italy", "JAM" => "Jamaica", "JEY" => "Jersey", "JOR" => "Jordan", "JPN" => "Japan", "KAZ" => "Kazakhstan", "KEN" => "Kenya", "KGZ" => "Kyrgyzstan", "KHM" => "Cambodia", "KIR" => "Kiribati", "KNA" => "Saint Kitts and Nevis", "KOR" => "Korea, Republic of", "KWT" => "Kuwait", "LAO" => "Lao People's Democratic Republic", "LBN" => "Lebanon", "LBR" => "Liberia", "LBY" => "Libyan Arab Jamahiriya", "LCA" => "Saint Lucia", "LIE" => "Liechtenstein", "LKA" => "Sri Lanka", "LSO" => "Lesotho", "LTU" => "Lithuania", "LUX" => "Luxembourg", "LVA" => "Latvia", "MAC" => "Macao", "MAF" => "Saint Martin (French part)", "MAR" => "Morocco", "MCO" => "Monaco", "MDA" => "Moldova, Republic of", "MDG" => "Madagascar", "MDV" => "Maldives", "MEX" => "Mexico", "MHL" => "Marshall Islands", "MKD" => "Macedonia, the former Yugoslav Republic of", "MLI" => "Mali", "MLT" => "Malta", "MMR" => "Myanmar", "MNE" => "Montenegro", "MNG" => "Mongolia", "MNP" => "Northern Mariana Islands", "MOZ" => "Mozambique", "MRT" => "Mauritania", "MSR" => "Montserrat", "MTQ" => "Martinique", "MUS" => "Mauritius", "MWI" => "Malawi", "MYS" => "Malaysia", "MYT" => "Mayotte", "NAM" => "Namibia", "NCL" => "New Caledonia", "NER" => "Niger", "NFK" => "Norfolk Island", "NGA" => "Nigeria", "NIC" => "Nicaragua", "NIU" => "Niue", "NLD" => "The Netherlands", "NOR" => "Norway", "NPL" => "Nepal", "NRU" => "Nauru", "NZL" => "New Zealand", "OMN" => "Oman", "PAK" => "Pakistan", "PAN" => "Panama", "PCN" => "Pitcairn", "PER" => "Peru", "PHL" => "Philippines", "PLW" => "Palau", "PNG" => "Papua New Guinea", "POL" => "Poland", "PRI" => "Puerto Rico", "PRK" => "Korea, Democratic People's Republic of", "PRT" => "Portugal", "PRY" => "Paraguay", "PSE" => "Palestinian Territory, Occupied", "PSL" => "Sealand", "PYF" => "French Polynesia", "QAT" => "Qatar", "REU" => "Réunion", "ROU" => "Romania", "RUS" => "Russia", "RWA" => "Rwanda", "SAU" => "Saudi Arabia", "SDN" => "Sudan", "SEN" => "Senegal", "SGP" => "Singapore", "SGS" => "South Georgia and the South Sandwich Islands", "SHN" => "Saint Helena", "SJM" => "Svalbard and Jan Mayen", "SLB" => "Solomon Islands", "SLE" => "Sierra Leone", "SLV" => "El Salvador", "SMR" => "San Marino", "SOM" => "Somalia", "SPM" => "Saint Pierre and Miquelon", "SRB" => "Serbia", "STP" => "Sao Tome and Principe", "SUR" => "Suriname", "SVK" => "Slovakia", "SVN" => "Slovenia", "SWE" => "Sweden", "SWZ" => "Swaziland", "SYC" => "Seychelles", "SYR" => "Syrian Arab Republic", "TCA" => "Turks and Caicos Islands", "TCD" => "Chad", "TGO" => "Togo", "THA" => "Thailand", "TJK" => "Tajikistan", "TKL" => "Tokelau", "TKM" => "Turkmenistan", "TLS" => "Timor-Leste", "TON" => "Tonga", "TTO" => "Trinidad and Tobago", "TUN" => "Tunisia", "TUR" => "Turkey", "TUV" => "Tuvalu", "TWN" => "Taiwan, Province of China", "TZA" => "Tanzania, United Republic of", "UGA" => "Uganda", "UKR" => "Ukraine", "UMI" => "United States Minor Outlying Islands", "URY" => "Uruguay", "USA" => "United States", "UZB" => "Uzbekistan", "VAT" => "Holy See (Vatican City State)", "VCT" => "Saint Vincent and the Grenadines", "VEN" => "Venezuela", "VGB" => "Virgin Islands, British", "VIR" => "Virgin Islands, U.S.", "VNM" => "Viet Nam", "VUT" => "Vanuatu", "WLF" => "Wallis and Futuna", "WSM" => "Samoa", "YEM" => "Yemen", "ZAF" => "South Africa", "ZMB" => "Zambia", "ZWE" => "Zimbabwe");
    $cnome = $apais[$cnas];
    return $cnome;
}

function GeraFiltro($cEst = 'p', $cWeb = '') {
    global $ncli;
    global $olcfg;
    require 'controles.php';
    $cret = "";

    if (!$afiltro[$ncli]["filtrocecwin"]) {
        $avet = array("grupo" => "grupro", "subgrupo" => "sgrupro", "marca" => "marcpro", "fornecedor" => "forpro", "grupoesp" => "gruesp");

        if (!$afiltro[$ncli]["filtroativo"]) {
            return "";
        }
        foreach ($avet as $key => $value0) {
            if (count($afiltro[$ncli][$key]) != 0) {
                $cret .= " and find_in_set($cEst.$value0,'99999999";
                foreach ($afiltro[$ncli][$key] as $value) {
                    $cret .= ",$value";
                }
                $cret .= "')";
            }
        }
        foreach ($avet as $key => $value0) {
            if (count($afiltro[$ncli]["ex_" . $key]) != 0) {
                $cret .= " and !find_in_set($cEst.$value0,'99999999";
                foreach ($afiltro[$ncli]["ex_" . $key] as $value) {
                    $cret .= ",$value";
                }
                $cret .= "')";
            }
        }
    } else {
        $avet = array("GRUPO" => "grupro", "SGRUPO" => "sgrupro", "MARCA" => "marcpro", "FORNEC" => "forpro", "GRUPOESP" => "gruesp");

        foreach ($avet as $key => $value0) {
            $caux = trim($olcfg[$key]);
            if ($key == "GRUPO" && $afiltro[$ncli]["grupoenvsaldo"][0]) {
                $aaux = explode(",", $caux);
                $aaux1 = array();
                foreach ($aaux as $cgrupo) {
                    if ($cgrupo + 0 !== ($afiltro[$ncli]["grupoenvsaldo"][0]) + 0) {
                        $aaux1[] = $cgrupo;
                    }
                }
                $caux = implode(",", $aaux1);
            }

            if ($caux) {
                $cret .= " and !find_in_set($cEst.$value0,'$caux')";
            }
        }
    }
    return $cret;
}

function Converter1($cstr) {
    return iconv("ISO-8859-1", "UTF-8", $cstr);
}

function GeraTempPro($lForce = false) {
    global $dbc;
    global $ncli;
    global $nsub_cli;
    global $afiltro;
    global $preco_env;
    global $preco1_env;
    global $REFRESH_SEC;
    global $clojasal;
    global $olcfg;

    if ($nsub_cli != $ncli) {
        return;
    }
    $csql = "create table if not exists control_api_$ncli (
        ordem int(10) not null auto_increment primary key
        ,data int(10) not null default 0
        ,tempo int(10) not null default 0
        )";
    mysqli_query($dbc, $csql);

    mysqli_query($dbc, "lock tables control_api_$ncli");
    $otab = mysqli_query($dbc, "select (UNIX_TIMESTAMP()-data) as tempo from control_api_$ncli order by ordem desc limit 1");
    $olin = mysqli_fetch_row($otab);
    if (mysqli_num_rows($otab) > 0 && $olin[0] < $REFRESH_SEC && !$lForce) {
        mysqli_query($dbc, "unlock tables");
        return false;
    }

    mysqli_query($dbc, "insert control_api_$ncli set data=unix_timestamp(),tempo=" . $REFRESH_SEC);
    $asql = array();
    $afrete = array();
    if ($afiltro[$ncli]["codigo_frete"]) {
        $afrete[] = $afiltro[$ncli]["codigo_frete"];
    }
    for ($k = 1; $k <= 10; $k++) {
        if ($afiltro[$ncli]["codigo_frete_$k"]) {
            $afrete[] = $afiltro[$ncli]["codigo_frete_$k"];
        }
    }
    if ($afrete) {
        $asql[] = "update PRODUTOS_WEB SET NIMAGEM=1 WHERE find_in_set(CODIGO,'" . implode(",", $afrete) . "') AND NIMAGEM=0";
    }

    $cpreco = str_replace("p.", "t.", $preco_env);
    $cpreco1 = str_replace("p.", "t.", $preco1_env);
    $asql[] = "drop table if exists XXprodalt_api_$ncli";
    $asql[] = "create table XXprodalt_api_$ncli (codigo int(10) not null default 0,ativo char(1) not null default 'N',preco decimal(12,2) not null default '0',preco1 decimal(12,2) not null default '0',precow decimal(12,2) not null default '0' )";
    $asql[] = "insert XXprodalt_api_$ncli (codigo,ativo,preco,preco1,precow)
            select p.codpro,p.ativo,$preco_env as preco,$preco1_env as preco1,p.precoweb from PRODUTO_PRO p
            inner join PRODUTOS_WEB w on w.is_deleted='N' and left(w.nomeweb,3)!='***' and w.codigo=p.codpro " . ($afiltro[$ncli]["lNaoEnviarSemImagem"] ? " and w.nimagem>0" : "") . "
            inner join XXprod_api_$ncli t on t.codpro=p.codpro and (t.ativo!=if(w.bloqueado='Y' or p.ativo='N','N','S')  or $cpreco!=$preco_env or $cpreco1!=$preco1_env or p.precoweb!=t.precoweb) and p.is_deleted='N'";
    $asql[] = "drop table if exists XXprod_api_$ncli";
    $asql[] = "drop table if exists XXprods_api_$ncli";
    $asql[] = "drop table if exists XXprods_api_" . $ncli . "_MEMO";

    if ($afiltro[$ncli]["semsaldozero"]) {
        $asql[] = "drop table if exists XXprodsal_api_" . $ncli;
        $cstr = "create table XXprodsal_api_" . $ncli . " select proloc,sum(qualoc) as qualoc from LOCAL_LOC WHERE IS_DELETED='N' ";
        if ($clojasal && $clojasal !== "9999999") {
            $cstr .= " and find_in_set(locloc,'$clojasal')";
        }
        $cstr .= " group by proloc";
        $asql[] = $cstr;

        if ($afiltro[$ncli]["enviakit"]) {
            $cstr = "drop temporary table if exists kitp";
            $asql[] = $cstr;
            $cstr = "create temporary table kitp 
                        select p.codpro ,sum(if(isnull(l.qualoc),0,l.qualoc)) as qualoc,k.prokit,k.quakit 
                        from PRODUTO_PRO p 
                        inner join PROKIT_PRO k on k.is_deleted='N' and k.codkit=p.codpro
                        left  join LOCAL_LOC l on p.is_deleted='N' and l.qualoc>0 and k.prokit=l.proloc and p.kitpro='S' ";
            if ($clojasal && $clojasal !== "9999999") {
                $cstr .= " and find_in_set(l.locloc,'$clojasal')";
            }
            $cstr .= " WHERE p.IS_DELETED='N' group by p.codpro,k.prokit";
            $asql[] = $cstr;
            $cstr = "update kitp set qualoc=qualoc/quakit";
            $asql[] = $cstr;
            $cstr = "update XXprodsal_api_" . $ncli . " s, 
                     ( select * from (select codpro as proloc,min(qualoc) as qualoc from kitp group by codpro) x where qualoc>0 ) x
                     set s.qualoc=x.qualoc where s.proloc=x.proloc ";
            $asql[] = $cstr;
        }


        $asql[] = " alter table XXprodsal_api_" . $ncli . " add index proloc(proloc) ";
        if ($afiltro[$ncli]["grupoenvsaldo"][0]) {
            $aaux = array();
            $otab = mysqli_query($dbc, "select codpro from PRODUTO_PRO WHERE IS_DELETED='N' AND GRUPRO=" . $afiltro[$ncli]["grupoenvsaldo"][0]);
            while ($olin = mysqli_fetch_array($otab)) {
                $asql[] = "insert XXprodsal_api_" . $ncli . " set proloc=" . $olin["codpro"];
                $asql[] = "update XXprodsal_api_" . $ncli . " set qualoc=" . $afiltro[$ncli]["grupoenvsaldo"][1] . " where proloc=" . $olin["codpro"];
            }
        }

//        $asql[] = "create table XXprod_api_$ncli select p.* from PRODUTO_PRO p 
        $asql[] = "create table XXprod_api_$ncli select " . CAMPOS_PRO . " from PRODUTO_PRO p 
        inner join PRODUTOS_WEB w on w.is_deleted='N' and left(w.nomeweb,3)!='***' and w.codigo=p.codpro AND  w.ecommerc" . ($afiltro[$ncli]["config"] ? $afiltro[$ncli]["config"] : "e") . ($afiltro[$ncli]["lApenasEcommerceSim"] ? "='S' " : "!='N' ") . ($afiltro[$ncli]["lNaoEnviarSemImagem"] ? " and w.nimagem>0" : "") . "
            inner join XXprodsal_api_" . $ncli . " s on s.proloc=p.codpro and s.qualoc>0 
        where p.is_deleted='N' " . GeraFiltro("p", "w") . ($afiltro[$ncli]["lEnviarInativos"] ? "" : " and p.ativo='S'")
        ;
    } else {
        $asql[] = "create table XXprod_api_$ncli select p.* from PRODUTO_PRO p 
        inner join PRODUTOS_WEB w on w.is_deleted='N' and left(w.nomeweb,3)!='***' and w.codigo=p.codpro AND  w.ecommerc" . ($afiltro[$ncli]["config"] ? $afiltro[$ncli]["config"] : "e") . ($afiltro[$ncli]["lApenasEcommerceSim"] ? "='S' " : "!='N' ") . ($afiltro[$ncli]["lNaoEnviarSemImagem"] ? " and w.nimagem>0" : "") . "
        where p.is_deleted='N' " . GeraFiltro("p", "w") . ($afiltro[$ncli]["lEnviarInativos"] ? "" : " and p.ativo='S'")
        ;
        if ($afiltro[$ncli]["grupoenvsaldo"][0]) {
            $asql[] = "update  XXprod_api_$ncli set estoque=" . $afiltro[$ncli]["grupoenvsaldo"][1] . " where grupro=" . $afiltro[$ncli]["grupoenvsaldo"][0];
        }
    }
    $asql[] = "update XXprod_api_$ncli t,PRODUTOS_WEB w,PRODUTO_PRO p 
    set t.ativo=if(w.bloqueado='Y' or p.ativo='N','N','S' )
    where p.is_deleted='N' and w.is_deleted='N' and t.codpro=p.codpro and w.codigo=p.codpro ";
    //$asql[] = "create table XXprods_api_$ncli select w.* from PRODUTO_PRO p 
    $asql[] = "create table XXprods_api_$ncli select " . CAMPOS_WEB . " from PRODUTO_PRO p 
        inner join PRODUTOS_WEB w on w.is_deleted='N' and left(w.nomeweb,3)!='***' and w.codigo=p.codpro AND  w.ecommerc" . ($afiltro[$ncli]["config"] ? $afiltro[$ncli]["config"] : "e") . ($afiltro[$ncli]["lApenasEcommerceSim"] ? "='S' " : "!='N' ") . ($afiltro[$ncli]["lNaoEnviarSemImagem"] ? " and w.nimagem>0" : "") . "
        where p.is_deleted='N' " . GeraFiltro("p", "w") . ($afiltro[$ncli]["lEnviarInativos"] ? "" : " and p.ativo='S'")
    ;
    $asql[] = "alter table XXprod_api_$ncli add index codigo (codpro) ";
    $asql[] = "alter table XXprod_api_$ncli add index recno (recno) ";
    $asql[] = "alter table XXprods_api_$ncli add index codigo (codigo)";
    $asql[] = "alter table XXprods_api_$ncli add index recno (recno)";

    $asql[] = "create table XXprods_api_" . $ncli . "_MEMO 
        select m.* from PRODUTOS_WEB_MEMO m
        INNER JOIN XXprods_api_$ncli w on w.recno=m.recno
        ";
    $asql[] = "alter table XXprods_api_" . $ncli . "_MEMO add index recno (recno,col_name)";
    $asql[] = "update XXprods_api_$ncli w,XXprodalt_api_$ncli i set w.alterapro=date2unix(now())
        where w.codigo=i.codigo       ";
    $asql[] = "update PRODUTOS_WEB w,XXprodalt_api_$ncli i set w.alterapro=date2unix(now())
        where w.codigo=i.codigo       ";

    $asql[] = "drop table if exists prodalt_api_$ncli";
    $asql[] = "drop table if exists prod_api_$ncli";
    $asql[] = "drop table if exists prods_api_$ncli";
    $asql[] = "drop table if exists prods_api_" . $ncli . "_MEMO";
    $asql[] = "rename table XXprodalt_api_$ncli to prodalt_api_$ncli";
    $asql[] = "rename table XXprod_api_$ncli to prod_api_$ncli";
    $asql[] = "create table XXprod_api_$ncli like prod_api_$ncli";
    $asql[] = "insert into  XXprod_api_$ncli select * from prod_api_$ncli";
    $asql[] = "alter table XXprod_api_$ncli add index codpro (codpro)";
    $asql[] = "rename table XXprods_api_$ncli to prods_api_$ncli";
    $asql[] = "rename table XXprods_api_" . $ncli . "_MEMO to prods_api_" . $ncli . "_MEMO";
    $asql[] = "insert control_api_$ncli set data=unix_timestamp(),tempo=" . $REFRESH_SEC;

    $asql[] = "unlock tables";

    foreach ($asql as $value) {
        echo " ";
        mysqli_query($dbc, $value);
        if (mysqli_errno($dbc)) {
            echo "$value <br>";
            echo mysqli_error($dbc) . "<bR><br>";
        }
    }
}

function randString($size) {
    $basic = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    $return = "";

    for ($count = 0;
            $size > $count;
            $count++) {
        $return .= $basic[rand(0, strlen($basic) - 1)];
    }

    return $return;
}

function LimpaAcento($cstr, $lMantemCaixa = false) {
    $aChar = array();
    $aChar[] = array("À", "A");
    $aChar[] = array("Ã", "A");
    $aChar[] = array("Â", "A");
    $aChar[] = array("Á", "A");
    $aChar[] = array("Ä", "A");
    $aChar[] = array("à", "a");
    $aChar[] = array("ã", "a");
    $aChar[] = array("â", "a");
    $aChar[] = array("á", "a");
    $aChar[] = array("ä", "a");
    $aChar[] = array("È", "E");
    $aChar[] = array("Ê", "E");
    $aChar[] = array("É", "E");
    $aChar[] = array("Ë", "E");
    $aChar[] = array("è", "e");
    $aChar[] = array("ê", "e");
    $aChar[] = array("é", "e");
    $aChar[] = array("ë", "e");
    $aChar[] = array("Ì", "I");
    $aChar[] = array("Î", "I");
    $aChar[] = array("Í", "I");
    $aChar[] = array("Ï", "I");
    $aChar[] = array("ì", "i");
    $aChar[] = array("î", "i");
    $aChar[] = array("í", "i");
    $aChar[] = array("ï", "i");
    $aChar[] = array("Õ", "O");
    $aChar[] = array("Ò", "O");
    $aChar[] = array("Ô", "O");
    $aChar[] = array("Ó", "O");
    $aChar[] = array("Ö", "O");
    $aChar[] = array("õ", "o");
    $aChar[] = array("ò", "o");
    $aChar[] = array("ô", "o");
    $aChar[] = array("ó", "o");
    $aChar[] = array("ö", "o");
    $aChar[] = array("Ù", "U");
    $aChar[] = array("Û", "U");
    $aChar[] = array("Ú", "U");
    $aChar[] = array("Ü", "U");
    $aChar[] = array("ù", "u");
    $aChar[] = array("û", "u");
    $aChar[] = array("ú", "u");
    $aChar[] = array("ú", "u");
    $aChar[] = array("Ñ", "N");
    $aChar[] = array("ñ", "n");
    $aChar[] = array("Ç", "C");
    $aChar[] = array("ç", "c");
    foreach ($aChar as $value) {
        $cstr = str_replace($value[0], $lMantemCaixa ? $value[1] : strtoupper($value[1]), $cstr);
    }
    return $cstr;
}

function VeDigitoRuc($cRuc) {
    $cRuc = preg_replace('/[^a-zA-Z0-9]/', "", $cRuc);
    return CalcDigitoRuc(substr($cRuc, 0, -1)) == substr($cRuc, -1);
}

function PoeDigitoRuc($cRuc, $lComHifen = false) {
    $cRuc = preg_replace('/[^a-zA-Z0-9]/', "", $cRuc);
    return $cRuc . ($lComHifen ? "-" : "") . CalcDigitoRuc($cRuc);
}

function CalcDigitoRuc($uRuc, $nBase = 11) {
    $cRuc = preg_replace('/[^a-zA-Z0-9]/', "", $uRuc);

    $cCaracter = "";
    $nNumero_a1 = "";

    $k = 2;
    $nTotal = 0;
    $nNumero_aux = 0;
    $nResto = 0;
    $nDigito = 0;
    for ($i = 0; $i <= strlen($cRuc) - 1; $i++) {
        $cCaracter = SubStr($cRuc, $i, 1);
        $cCaracterAscii = ord($cCaracter);
        if ($cCaracterAscii >= 48 && $cCaracterAscii <= 57) {
            $nNumero_a1 .= $cCaracter;
        } else {
            $nNumero_a1 .= $cCaracterAscii;
        }
    }
    for ($i = strLen($nNumero_a1); $i >= 1; $i--) {
        if ($k > $nBase) {
            $k = 2;
        }
        $nNumero_aux = SubStr($nNumero_a1, $i - 1, 1);
        $nTotal += $nNumero_aux * $k;
        $k++;
    }

    $nResto = $nTotal % 11;
    if ($nResto > 1) {
        $nDigito = 11 - $nResto;
    } else {
        $nDigito = 0;
    }

    return $nDigito;
}

function LimpaNome($cnome, $cadiciona = '') {
    $cvalidos = ".-ñÑçÇáéíóúÁÉÍÓÚãÃõÕ abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789" . $cadiciona;
    $cret = '';
    for ($i = 0; $i < strlen($cnome); $i++) {
        $caux = substr($cnome, $i, 1);
        if (strpos($cvalidos, $caux) !== false) {
            $cret .= $caux;
        }
    }
    $asub = array();
    $asub[] = array("ã", "A");
    $asub[] = array("Ã", "A");
    $asub[] = array("õ", "A");
    $asub[] = array("Õ", "A");
    $asub[] = array("á", "A");
    $asub[] = array("é", "E");
    $asub[] = array("í", "I");
    $asub[] = array("ó", "O");
    $asub[] = array("ú", "U");
    $asub[] = array("Á", "A");
    $asub[] = array("É", "E");
    $asub[] = array("Í", "I");
    $asub[] = array("Ó", "O");
    $asub[] = array("Ú", "U");
    $asub[] = array("ç", "C");
    $asub[] = array("Ç", "C");
    $asub[] = array("ñ", "N");
    $asub[] = array("ñ", "N");

    for ($i = 0; $i < count($asub); $i++) {
        $cret = str_replace($asub[$i][0], $asub[$i][1], $cret);
    }
    $cret = str_replace("    ", " ", $cret);
    $cret = str_replace("   ", " ", $cret);
    $cret = str_replace("  ", " ", $cret);
    return $cret;
}

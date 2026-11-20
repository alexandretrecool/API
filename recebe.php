<?php

//A-preven
//B-preco3
//C-premin


//ini_set('display_errors', 0);
//error_reporting(E_ALL | E_STRICT);

define("VERSAO", 112);
DEFINE("VERSAO_DATA", "2018-09-25 10:01");

require_once './inc/config.php';




date_default_timezone_set('America/Sao_Paulo');

$aret = array(array("ambiente" => ($lhomologa ? "H" : "P"), "data_sol" => date('Y-m-d H:i:s'), "data_ret" => '', "estatus" => '', "cestatus" => '', "element" => 1));
$aret[0]["ver"] = round(VERSAO / 100,2)."";
$aret[0]["dat"] = VERSAO_DATA;

global $lSemSsl;
global $db;

$lSemSsl = 1;
$lpost = '';

//if (!$lhomologa) {
$cip = getenv("REMOTE_ADDR");
$nret = array_search($cip, $aip);
if (!$nret) {
    sleep(10);
    $aret[0]["data_ret"] = date('Y-m-d H:i:s');
    $aret[0]["estatus"] = "5";
    $aret[0]["cestatus"] = "IP do solicitante nao aceito: " . $cip;
    echo json_encode($aret);
    logs($aret);
    return;
}
$lpost = "P";
//}
$lpost = '';
$nope = pegavar("ope", $lpost);

if (!$aoperacoes["o".$nope]){
    $aret[0]["data_ret"] = date('Y-m-d H:i:s');
    $aret[0]["estatus"] = "3";
    $aret[0]["cestatus"] = $aoperacoes["o0"][0];
    echo json_encode($aret);
    logs($aret);
    return;
    
}
if (!$aoperacoes["o".$nope][1]){
    $aret[0]["data_ret"] = date('Y-m-d H:i:s');
    $aret[0]["estatus"] = "3";
    $aret[0]["cestatus"] = "Operação Bloqueada =>".$aoperacoes["o".$nope][0];
    echo json_encode($aret);
    logs($aret);
    return;
    
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
//$lpost = '';
 //   $adet["ver"] = VERSAO / 100;
//    $adet["dat"] = VERSAO_DATA;
if ($nope == 10) {
    $aret[0]["data_ret"] = date('Y-m-d H:i:s');
    $aret[0]["estatus"] = "0";
    $aret[0]["cestatus"] = "Ok";
    $aret[0]["element"] = 2;
    $adet = array();
    $adet["ver"] = round(VERSAO / 100,2);
    $adet["dat"] = VERSAO_DATA;
    $aret[] = $adet;
    echo json_encode($aret);
    logs($aret);
    return;
}

$ncli = pegavar('cod', $lpost);
$asku = explode(",", pegavar("sku", $lpost));



$db = conecta($server, $port, $user, $password, $database);
if (!$db) {
    $aret[0]["data_ret"] = date('Y-m-d H:i:s');
    $aret[0]["estatus"] = "51";
    $aret[0]["cestatus"] = "Erro no Sql do Servidor";
    echo json_encode($aret);
    logs($aret);
    return;
}

$otable = mysqli_query($db, "select codigo from cliente where codigo=" . pegavar("cod"));
if (mysqli_num_rows($otable) == 0) {
    $aret[0]["data_ret"] = date('Y-m-d H:i:s');
    $aret[0]["estatus"] = "1";
    $aret[0]["cestatus"] = "Cliente Invalido";
    echo json_encode($aret);
    logs($aret);
    return;
}

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
//$dbc = mysqli_connect($olinha["server"] . ":" . $olinha["port"], $olinha["user"], $olinha["password"]);
$dbc = mysqli_connect($olinha["server"], $olinha["user"], $olinha["password"], $olinha["data"], $olinha["port"]);

//if (!$dbc || !mysqli_select_db($olinha["data"])) {
if (!$dbc) {
    $aret[0]["data_ret"] = date('Y-m-d H:i:s');
    $aret[0]["estatus"] = "7";
    $aret[0]["cestatus"] = "Cliente indisponivel";
    echo json_encode($aret);
    logs($aret);
    return;
}

//mysqli_select_db($olinha["data"]);


$otcfg = mysqli_query($dbc, "select * FROM CONFIG_ECM where recno=1");
$olcfg = mysqli_fetch_array($otcfg);

//A-preven
//B-preco3
//C-premin

$preco_env = $olcfg["PRECO"] == "A" ? "PREVEN" : ($olcfg["PRECO"] == "B" ? "PRECO3" : ($olcfg["PRECO"] == "C" ? "PREMIN" : ($olcfg["PRECO"] == "T" ? "PREATAC" : "PREVEN")));
$ntax = 0;
$preco_env = "round(p.$preco_env ,2)";

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
}
//FRETE PESO
if ($olcfg["FRETEP"] > 0) {
    $preco_env .= "+(p.pespro*" . $olcfg["FRETEP"] . ")";
}
//FRETE VOLUME
if ($olcfg["FRETEV"] > 0) {
    $preco_env .= "+((p.largura*p.altura*p.profund)*" . $olcfg["FRETEV"] . ")";
}
//COMISSAO VALOR
if ($olcfg["COMISSAOV"] > 0) {
    $preco_env .= "+" . $olcfg["COMISSAOV"];
}

//$ocfg = mysqli_fetch_array(mysqli_query($db,"select * from MAGENTO_CFG WHERE RECNO=1"));
$ocfgcec = mysqli_fetch_array(mysqli_query($db, "select * from CONFIG WHERE RECNO=1"));
$nloja = $olcfg["LOJA"];
$nrestar = 0; //(!$ocfg["RESTAR"] ? '0' : $ocfg["RESTAR"]) + 0;

$clojasal = trim($olcfg["LOJASALDO"]);

$aret[0]["estatus"] = 0;
$aret[0]["cestatus"] = "ok";
if ($nope == 83) {
    $adados = array();

    $codigo = PegaVar("codigo", $lpost);
    $ruc = limparg(strtoupper(trim(pegavar("docum", $lpost))));
    $email = strtoupper(trim(pegavar("email", $lpost)));
    $nome = strtoupper(trim(pegavar("nome", $lpost)));
    
    $cstr="select * from CLIENTE_CLI WHERE IS_DELETED='N' AND ";
    if ($codigo){
        $cstr.=" CODCLI='".$codigo."'";
    }elseif ($ruc){
        $cstr.=" RUCRES='".$ruc."'";
    }elseif ($email) {
        $cstr.=" e_mail='".$email."'";
    }elseif ($nome) {
        $cstr.=" nomcli='".$nome."'";
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

    $olincli= mysqli_fetch_array($otabcli);
    $olinpfc= mysqli_fetch_array(mysqli_query($dbc,"select * from CLIENTE_PFC WHERE IS_DELETED='N' AND CODIGO=".$olincli["CODCLI"] ));
    
    $aret[] = array("codigo" => $olincli["CODCLI"]);
    $aret[] = array("docum" => LimpaRg($olincli["RUCCLI"]));
    $aret[] = array("email" => $olincli["E_MAIL"]);
    $aret[] = array("nome" => Converter($olincli["NOMCLI"]));
    $aret[] = array("anive" => $olincli["NASCLI"]);
    $aret[] = array("sexo" => $olinpfc["SEXO"]);
    $aret[] = array("nasci" => $olinpfc["PAIS"]);
    $aret[] = array("cep" => $olincli["CEPCLI"]);
    $aret[] = array("ender" => $olincli["ENDCLI"]);
    $aret[] = array("bairr" => $olincli["BAICLI"]);
    $aret[] = array("cidad" => $olincli["CIDCLI"]);
    $aret[] = array("uf" => $olincli["UFCLI"]);
    $aret[] = array("pais" => $olinpfc["PAIS"]);
    $aret[] = array("telef" => $olincli["FONCLI"]);
    $aret[] = array("celul" => $olincli["CELCLI"]);
    $aret[] = array("datac" => $olincli["CADCLI"]);

    $aret[0]["element"] = $aret[0]["element"] + 16;

}elseif ($nope == 82) {
    $adados = array();

    $adados["ANIVER"] = strtoupper(trim(pegavar("anive", $lpost)));
    $adados["SEXO"] = strtoupper(trim(pegavar("sexo", $lpost)));
    $adados["NASCIO"] = strtoupper(trim(pegavar("nasci", $lpost)));
    $adados["CEPCLI"] = strtoupper(trim(pegavar("cep", $lpost)));
    $adados["ENDCLI"] = strtoupper(trim(pegavar("ender", $lpost)));
    $adados["BAICLI"] = strtoupper(trim(pegavar("bairr", $lpost)));
    $adados["CIDCLI"] = strtoupper(trim(pegavar("cidad", $lpost)));
    $adados["UFCLI"] = strtoupper(trim(pegavar("uf", $lpost)));
    $adados["PAICLI"] = strtoupper(trim(pegavar("pais", $lpost)));
    $adados["FONCLI"] = strtoupper(trim(pegavar("telef", $lpost)));
    $adados["CELCLI"] = strtoupper(trim(pegavar("celul", $lpost)));

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

    $omed = new Mediator();
    $omed->init($dbc, "CLIENTE.CLI", "CODCLI='" . $codigo . "'");

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
    $omed->aadd("OBSCLI", "ALTERADO VIA ECOMMERCE");
    if (!$omed->grava()) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "90";
        $aret[0]["cestatus"] = "Erro gravando dados CLI";
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }


    if (mysqli_num_rows(mysqli_query($dbc, "select codigo from CLIENTE_PFC where is_deleted='N' and codigo=$codigo")) == 0) {
        $omedp = new Mediator();
        $omedp->init($dbc, "CLIENTE.PFC");
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
    if ($adados["SEXO"]){
        $cstr.=",sexo='" . (!$adados["SEXO"] ? 3 : $adados["SEXO"]) . "'";
    }
    $cstr.=",endereco='" . $olincli["ENDCLI"] . "',cidade='" . $olincli["CIDCLI"] . "',estado='" . $olincli["UFCLI"] . "'";
    if ($adados["PAICLI"]){
        $cstr.=",pais='" . $adados["PAICLI"] . "'";
    }
    $cstr.=",bairro='" . $olincli["BAICLI"] . "',cep='" . $olincli["CEPCLI"] . "',complemen=''
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
} elseif ($nope == 81) {
    $adados = array();
    $adados["RUCCLI"] = strtoupper(trim(pegavar("docum", $lpost)));
    $adados["E_MAIL"] = strtoupper(trim(pegavar("email", $lpost)));
    $adados["NOMCLI"] = strtoupper(trim(pegavar("nome", $lpost)));
    $adados["ANIVER"] = strtoupper(trim(pegavar("anive", $lpost)));
    $adados["SEXO"] = strtoupper(trim(pegavar("sexo", $lpost)));
    $adados["NASCIO"] = strtoupper(trim(pegavar("nasci", $lpost)));
    $adados["CEPCLI"] = strtoupper(trim(pegavar("cep", $lpost)));
    $adados["ENDCLI"] = strtoupper(trim(pegavar("ender", $lpost)));
    $adados["BAICLI"] = strtoupper(trim(pegavar("bairr", $lpost)));
    $adados["CIDCLI"] = strtoupper(trim(pegavar("cidad", $lpost)));
    $adados["UFCLI"] = strtoupper(trim(pegavar("uf", $lpost)));
    $adados["PAICLI"] = strtoupper(trim(pegavar("pais", $lpost)));
    $adados["FONCLI"] = strtoupper(trim(pegavar("telef", $lpost)));
    $adados["CELCLI"] = strtoupper(trim(pegavar("celul", $lpost)));

    if (!$adados["RUCCLI"]) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "83";
        $aret[0]["cestatus"] = "Falta campo obrigatório";
        $aret[] = array("cod" => 0, "cmp" => "documen", "err" => "Documento não passado");
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }

    if (strlen(limparg($adados["RUCCLI"])) < 4) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "84";
        $aret[0]["cestatus"] = "Erro em campo obrigatório";
        $aret[] = array("cod" => 0, "cmp" => "documen", "err" => "Documento inválido");
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }

    $ocli = mysqli_query($dbc, "select codcli from CLIENTE_CLI WHERE IS_DELETED='N' AND RUCRES='" . $adados["RUCCLI"] . "'");
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
    $ocli = mysqli_query($dbc, "select codcli from CLIENTE_CLI WHERE IS_DELETED='N' AND nomcli='" . $adados["NOMCLI"] . "'");
    if (mysqli_num_rows($ocli) > 0) {
        $lcli = mysqli_fetch_row($ocli);
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "81";
        $aret[0]["cestatus"] = "Nome já cadastrado";
        $aret[] = array("cod" => 0, "cod" => $lcli[0], "err" => "Nome já cadastrado");
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






    $omed = new Mediator();
    $omed->init($dbc, "CLIENTE.CLI", '', "CODCLI");
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
    $omed->aadd("GRUCLI", 1);
    $omed->aadd("FATCLI", "N");
    $omed->aadd("OBSCLI", "CADASTRADO VIA ECOMMERCE");
    $omed->aadd("VENCLI", "N");
    $omed->aadd("ZONCLI", 1);
    $omed->aadd("EMIFAT", "S");
    if (!$omed->grava()) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "90";
        $aret[0]["cestatus"] = "Erro gravando dados CLI";
        $aret[0]["element"] = $aret[0]["element"] + 1;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $codigo = $omed->ncodigo;


    if (mysqli_num_rows(mysqli_query($dbc, "select codigo from CLIENTE_PFC where is_deleted='N' and codigo=$codigo")) == 0) {
        $omedp = new Mediator();
        $omedp->init($dbc, "CLIENTE.PFC");
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
} elseif ($nope == 91) {
    $cod = PegaVar("sku", $lpost);
    $cstr = "select codigo,nome,nome2,trim(ordem)+0 as ordem,id_parent,meta from GRADE_WEB where is_deleted='N' ";
    if ($cod) {
        $cstr .= " and codigo='$cod'";
    }
    $cstr .= " order by codigo";
    $otable = mysqli_query($dbc, $cstr);

    while ($olinha = mysqli_fetch_array($otable)) {
        $aret[] = array("sku" => $olinha["codigo"], "nom" => Converter($olinha["nome"]), "no2" => Converter($olinha["nome2"])
            , "ord" => $olinha["ordem"] + 0, "par" => $olinha["id_parent"], "met" => converter($olinha["meta"]));
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





    $cstr = "select p.codpro as codigo,if(w.alterapro<date2unix('$dat') ,'',unix2date(w.alterapro)) as pro, if(w.alteraimg<date2unix('$dat') ,'', unix2date(w.alteraimg)) as img from PRODUTOS_WEB w 
            inner join PRODUTO_PRO p on p.is_deleted='N' and p.codpro=w.codigo 
            where w.is_deleted='N' and ";
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
        if ($olinha["pro"] >= $dat || $olinha["img"] >= $dat) {
            $nsta = 3;
        } elseif ($olinha["img"] >= $dat) {
            $nsta = 2;
        } else {
            $nsta = 1;
        }
        $aret[] = array("sku" => poedigito($olinha["codigo"]), "sta" => $nsta, "dpd" => $olinha["pro"], "dig" => $olinha["img"]);
    }
    $aret[0]["element"] = $aret[0]["element"] + mysqli_num_rows($otable);
} elseif ($nope == 1 || $nope == 11) {
    $csku = pegavar("sku", $lpost);
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
    $cstr.=" GROUP BY codigo";
    mysqli_query($dbc, $cstr);
    $cstr = "alter table tmpimg add index codigo (codigo)";
    mysqli_query($dbc, $cstr);
    $cstr = "create temporary table tmpimg1 
        select 
            p.codpro
            ,convert(sum(if(isnull(l.proloc),0,l.qualoc)),decimal(10,0)) as estoque
            ,p.ativo
            ,w.bloqueado 
            ,if(w.nomeweb='',p.nompro,w.nomeweb) as nome
            ,w.nimagem
            ,convert(" . $preco_env . ",decimal(15,2)) as preco
            ,if(isnull(wm.memo),'',wm.memo) as texto 
            ,if(isnull(wm1.memo),'',wm1.memo) as mini
            ,w.link2 as fabrica
            ,w.link1 as video
            ,p.largura
            ,p.altura
            ,p.profund
            ,p.pespro as peso
            ,w.estmax,w.permax
            ,convert(if(isnull(i.codigo),0,i.TAM),decimal(12,0)) as tam
            ,p.grupro,p.marcpro,w.grade
    from PRODUTO_PRO p 
    inner join PRODUTOS_WEB w on w.is_deleted='N'  and w.codigo=p.codpro  AND w.ecommerce='S'
    left  join PRODUTOS_WEB_MEMO wm on w.recno=wm.recno  and wm.col_name='TEXTO'
    left  join PRODUTOS_WEB_MEMO wm1 on w.recno=wm1.recno  and wm1.col_name='MINIDESC'
    left join LOCAL_LOC l on l.is_deleted='N' and l.proloc=p.codpro and find_in_set(l.locloc,'$clojasal')
    left join tmpimg i on i.codigo=p.codpro
    where p.is_deleted='N'";
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
//echo $cstr."<br>";
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
        if ($olinha["estmax"] > 0 && $olinha["estmax"] < $nsal) {
            $nsal = $olinha["estmax"];
        }
//        $aret[] = array("sku" => padl($ncli, 5, "0") . "-" . padl($olinha["codpro"], 7, "0"), "sta" => $nsta, "sal" => number_format($nsal, 0), "crc" => $olinha["tam"]);
        if ($nope == 1) {
            $aret[] = array("sku" => poedigito($olinha["codpro"]), "sta" => $nsta, "sal" => number_format($nsal, 0), "crc" => $olinha["tam"]);
        } else {
            $aret[] = array("sku" => poedigito($olinha["codpro"]), "sta" => $nsta, "sal" => number_format($nsal, 0), "pre" => $olinha["preco"]);
        }
        $aret[0]["element"] = $aret[0]["element"] + 1;
    }
//operacao 1 - Consultar
} elseif ($nope == 4) {
    $ntam = PegaVar("tam", $lpost);
    $npag = PegaVar("pag", $lpost);
    $csku = "";
    if (!$ntam) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "40";
        $aret[0]["cestatus"] = "Parametros invalidos";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $cstr = "select count(*) as total
    from PRODUTO_PRO p 
    inner join PRODUTOS_WEB w on w.is_deleted='N'  and w.codigo=p.codpro  AND w.ecommerce='S'
    where p.is_deleted='N'";
    if (pegavar("blo", $lpost) == "B") {
        $cstr .= " and ( p.ativo!='S' or w.bloqueado='Y' ) ";
    }
    if (pegavar("blo", $lpost) == "N") {
        $cstr .= " and (p.ativo='S' and w.bloqueado!='Y') ";
    }

    $otable = mysqli_query($dbc, $cstr);

    $olinha = mysqli_fetch_row($otable);

    $ntotal = $olinha[0];
    //echo $ntotal.CR;
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
    //echo date("H:i:s").CR;
    $cstr = "create temporary table tmpimg
        select codigo,sum(ordem*imagem) as tam 
        from PRODUTOS_IMG 
        WHERE IS_DELETED='N'
        GROUP BY codigo";
    mysqli_query($dbc, $cstr);
    //echo date("H:i:s").CR;
    $cstr = "alter table tmpimg add index codigo (codigo)";
    mysqli_query($dbc, $cstr);

    $cstr = "create temporary table tmpimg1 
        select 
            p.codpro
            ,convert(sum(if(isnull(l.proloc),0,l.qualoc)),decimal(10,0)) as estoque
            ,p.ativo
            ,w.bloqueado 
            ,if(w.nomeweb='',p.nompro,w.nomeweb) as nome
            ,w.nimagem
            ," . $preco_env . " as preco
            ,if(isnull(wm.memo),'',wm.memo) as texto 
            ,if(isnull(wm1.memo),'',wm1.memo) as mini
            ,w.link2 as fabrica
            ,w.link1 as video
            ,p.largura
            ,p.altura
            ,p.profund
            ,p.pespro as peso
            ,w.estmax,w.permax
            ,convert(if(isnull(i.codigo),0,i.TAM),decimal(12,0)) as tam
            ,p.grupro,p.marcpro,w.grade
    from PRODUTO_PRO p 
    inner join PRODUTOS_WEB w on w.is_deleted='N'  and w.codigo=p.codpro  AND w.ecommerce='S'
    left  join PRODUTOS_WEB_MEMO wm on w.recno=wm.recno  and wm.col_name='TEXTO'
    left  join PRODUTOS_WEB_MEMO wm1 on w.recno=wm1.recno  and wm1.col_name='MINIDESC'
    left join LOCAL_LOC l on l.is_deleted='N' and l.proloc=p.codpro and find_in_set(l.locloc,'$clojasal')
    left join tmpimg i on i.codigo=p.codpro
    where p.is_deleted='N'";
    if ($csku) {
        $cstr .= " and p.codpro='" . tiradigito($csku) . "'";
    } else {
        if (pegavar("blo", $lpost) == "B") {
            $cstr .= " and ( p.ativo!='S' or w.bloqueado='Y' ) ";
        }
        if (pegavar("blo", $lpost) == "N") {
            $cstr .= " and (p.ativo='S' and w.bloqueado!='Y') ";
        }
    }
    $cstr .= " group by p.codpro";
    $cstr .= " order by p.codpro limit " . ( ($npag - 1) * $ntam ) . ",$ntam ";

    mysqli_query($dbc, $cstr);

    $cstr = "update tmpimg1 set tam=crc32(tam+(char(ativo)*10)+(char(bloqueado)*100)+nome+nimagem+preco*100+texto+mini+fabrica
+video+largura*1000+altura*1000+profund*1000+peso*1000+estmax+permax*1000+grupro+marcpro+grade)";
    mysqli_query($dbc, $cstr);
    $cstr = "select * from tmpimg1";
    $otable = mysqli_query($dbc, $cstr);

    mysqli_query($dbc, "drop temporary table if exists tmpimg2 ");
    mysqli_query($dbc, "create temporary table tmpimg2 select proloc,convert(sum(qualoc),decimal(10,0)) as qualoc from LOCAL_LOC l where $cor group by proloc");
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
        if ($olinha["estmax"] > 0 && $olinha["estmax"] < $nsal) {
            $nsal = $olinha["estmax"];
        }
        $aret[] = array("sku" => poedigito($olinha["codpro"]), "sta" => $nsta, "sal" => number_format($nsal, 0), "crc" => $olinha["tam"]);
        $aret[0]["element"] = $aret[0]["element"] + 1;
    }
//operacao 2 - Detalhes dos produtos
} elseif ($nope == 2) {
    if (pegavar("sku", $lpost) == '') {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "40";
        $aret[0]["cestatus"] = "Parametros invalidos";
        echo json_encode($aret);
        logs($aret);
        return;
    }

    if (pegavar("sku", $lpost) !== false) {
        $asku = explode(",", pegavar("sku", $lpost));
    } else {
        $asku = array(pegavar("sku", true));
    }
    foreach ($asku as $value) {
        $aaux = array("", tiradigito($value));
        $otable = mysqli_query($dbc, "select p.codpro
            ,sum(if(isnull(l.proloc),0,l.qualoc)) as estoque,p.ativo,w.bloqueado ,if(w.nomeweb='',p.nompro,w.nomeweb) as nome,w.nimagem
        ," . $preco_env . " as preco
    ,if(isnull(wm.memo),'',wm.memo) as texto ,if(isnull(wm1.memo),'',wm1.memo) as mini,w.link2 as fabrica,w.link1 as video
    ,p.largura,p.altura,p.profund,p.pespro as peso,w.estmax,w.permax
    ,p.grupro as cat,p.marcpro as mar,w.grade as caw
    ,trim(p.gradepro) as gradepro
    from PRODUTO_PRO p 
    inner join PRODUTOS_WEB w on w.is_deleted='N'  and w.codigo=p.codpro  AND w.ecommerce='S'
    left  join PRODUTOS_WEB_MEMO wm on w.recno=wm.recno  and wm.col_name='TEXTO'
    left  join PRODUTOS_WEB_MEMO wm1 on w.recno=wm1.recno  and wm1.col_name='MINIDESC'
    left join LOCAL_LOC l on  l.proloc=p.codpro and find_in_set(l.locloc,'$clojasal')
    where p.is_deleted='N' and p.codpro=" . $aaux[1]);
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
            if ($olinha["estmax"] > 0 && $nsal > $olinha["estmax"]) {
                $nsal = $olinha["estmax"];
            }
            $adet["sal"] = floor($nsal);
            $adet["blo"] = $olinha["ativo"] != "S" || $olinha["bloqueado"] == "Y" ? 1 : 0;
            $adet["nom"] = urlencode(Converter($olinha["nome"])); //str_replace('"',"'",$olinha["nome"]);
            $adet["img"] = $olinha["nimagem"];
            $adet["pre"] = number_format($olinha["preco"], 2);
            $adet["des"] = urlencode(Converter($olinha["texto"]));
            $adet["bre"] = urlencode(Converter($olinha["mini"]));
            $adet["ufa"] = urlencode(Converter($olinha["fabrica"]));
            $adet["uvi"] = urlencode(Converter($olinha["video"]));
            $adet["alt"] = $olinha["altura"];
            $adet["lgr"] = $olinha["largura"];
            $adet["prd"] = $olinha["profund"];
            $adet["pes"] = $olinha["peso"];

            $otable1 = mysqli_query($dbc, "select c.nome,v.valor 
                from CATVALOR_WEB v 
                inner join CATEGORI_WEB c on c.codigo=v.categoria and c.is_deleted='N' 
                where v.is_deleted='N' and v.codigo=" . $aaux[1]);
            $caux = '';
            while ($olinha1 = mysqli_fetch_array($otable1)) {
                $caux1 = str_replace(";", " ", str_replace(":", " ", Converter($olinha1["nome"]))) . ":" . str_replace(";", " ", str_replace(":", " ", $olinha1["valor"]));
                $caux .= ($caux1 == ':' ? '' : ";") . $caux1;
            }
            $adet["car"] = urlencode(Converter($caux));
            
            $agrade = array();
            $cstr="select gp1.nomgrade as nom1,g1.tipogrd as val1,if(g.seqgrade1=g.seqgrade2,'',gp2.nomgrade) as nom2,if(g.seqgrade1=g.seqgrade2,'',g2.tipogrd) as val2,sum(g.estoque) as estoque 
                from (select codpro,gradepro from PRODUTO_PRO where is_deleted='N' and codpro=".$olinha["codpro"].") p 
                inner join ESTLJGRA_PRO g on g.is_deleted='N' and  p.codpro=g.prograde 
                inner join GRADESEQ_PRO g1 on g1.recno=g.seqgrade1 
                inner join GRADESEQ_PRO g2 on g2.recno=g.seqgrade2 
                LEFT  JOIN GRADE_PRO gp1 on gp1.is_deleted='N' and gp1.codgrade=substring_index(p.gradepro,'-',1) 
                LEFT  JOIN GRADE_PRO gp2 on gp2.is_deleted='N' and gp2.codgrade=substring_index(p.gradepro,'-',-1) 
                where g.estoque>0 
                group by p.codpro,g1.tipogrd,g2.tipogrd 
                order by val1,val2 " ;
            $otab= mysqli_query($dbc, $cstr);
            while ($olin= mysqli_fetch_row($otab)){
                $agrade[]=array($olin[0],$olin[1],$olin[2],$olin[3],floor($olin[4]));
            }
            $adet["grd"] = $agrade;
            $adet["cat"] = $olinha["cat"];
            $adet["caw"] = $olinha["caw"];
            $adet["mar"] = $olinha["mar"];
            $aret[] = $adet;
            $aret[0]["element"] = $aret[0]["element"] + 1;
        }
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
    $otable = mysqli_query($dbc, "select nimagem from PRODUTOS_WEB w WHERE w.is_deleted='N'  AND w.ecommerce='S' and w.codigo=" . $nsku);
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
    for ($j = 0; $j < count($aimagem); $j++) {
        $caux = $aimagem[$j][1];
        for ($i = count($aaux); $i > 0; $i--) {
            $caux = str_replace("/chr" . ( $i) . "/", $aaux[$i - 1], $caux);
        }
    }
//echo substr($cretorna, 1);
    Header("Content-type: image/jpeg");
    print $caux;
    logs($aret);
    RETURN;
//consulta para venda
} elseif ($nope == 11) {
    foreach ($asku as $value) {
        $aaux = explode("-", $value);
        $otable = mysqli_query($dbc, "select p.codpro,$nSaldoEstoque as estoque,p.ativo,w.bloqueado ,if(w.nomeweb='',p.nompro,w.nomeweb) as nome,w.nimagem
        ,p." . $preco_env . " as preco
    ,if(isnull(wm.memo),'',wm.memo) as texto ,if(isnull(wm1.memo),'',wm1.memo) as mini,w.link2 as fabrica,w.link1 as video
    ,p.largura,p.altura,p.profund,p.pespro as peso,w.estmax,w.permax
    from PRODUTO_PRO p 
    inner join PRODUTOS_WEB w on w.is_deleted='N'  and w.codigo=p.codpro  AND w.ecommerce='S'
    left  join PRODUTOS_WEB_MEMO wm on w.recno=wm.recno  and wm.col_name='TEXTO'
    left  join PRODUTOS_WEB_MEMO wm1 on w.recno=wm1.recno  and wm1.col_name='MINIDESC'
    left join LOCAL_LOC l on l.locloc=$nloja and l.is_deleted='N' and l.proloc=p.codpro
        $cLojaEstoque
    where p.is_deleted='N' and p.codpro=" . $aaux[1]);
        $olinha = mysqli_fetch_array($otable);
        $nsta = 0;
        $nsal = $olinha["estoque"];
        if ($aaux[0] != $ncli) {
            $nsta = 8;
//            $aret[] = array("sku" => pegavar("sku", $lpost), "sta" => $nsta);
            $aret[] = array("sku" => $value, "sta" => $nsta);
        } elseif (mysqli_num_rows($otable) == 0) {
            $nsta = 9;
//            $aret[] = array("sku" => pegavar("sku", $lpost), "sta" => $nsta);
            $aret[] = array("sku" => $value, "sta" => $nsta);
        } else {
            $nsal = $olinha["estoque"];
            if ($olinha["estmax"] > 0 && $nsal > $olinha["estmax"]) {
                $nsal = $olinha["estmax"];
            }
            if ($olinha["permax"] > 0) {
                $nper = floor($olinha["estoque"] * ($olinha["permax"] / 100));
                if ($nsal > $nper) {
                    $nsal = $nper;
                }
            }

            if ($nsal == 0) {
                $nsta = 1;
            } elseif ($olinha["ativo"] != "S" || $olinha["bloqueado"] == "Y") {
                $nsta = 2;
            }
            $adet = array();
            $adet["sku"] = padl($ncli, 5, "0") . "-" . padl($olinha["codpro"], 7, "0");
            $adet["sta"] = $nsta;
            /*            $nsal = $olinha["estoque"];
              if ($olinha["estmax"] > 0 && $nsal > $olinha["estmax"]) {
              $nsal = $olinha["estmax"];
              }
              if ($olinha["permax"] > 0) {
              $nper = floor($olinha["estoque"] * ($olinha["permax"] / 100));
              if ($nsal > $nper) {
              $nsal = $nper;
              }
              }
             */
            $adet["sal"] = floor($nsal);
            $adet["pre"] = number_format($olinha["preco"], 2);
            $aret[] = $adet;
            $aret[0]["element"] = $aret[0]["element"] + 1;
        }
    }
} elseif ($nope == 12) {
    $nped = pegavar("ped", $lpost);
    $ncliente = pegavar("cli",$lpost);
    $asku = explode(",", pegavar("sku", $lpost));
    $aqua = explode(",", pegavar("qtd", $lpost));
    $npgt = pegavar("pgt", $lpost) + 0;
    $cobs = pegavar("obs", $lpost);

    if ($nped == '') {
        $aret[0]["estatus"] = 40;
        $aret[0]["cestatus"] = "Pedido vazio";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    if (!$ncliente ) {
        $aret[0]["estatus"] = 40;
        $aret[0]["cestatus"] = "Numero do cliente no pedido invalido";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $otabcli= mysqli_query($dbc, "select * from CLIENTE_CLI WHERE IS_DELETED='N' AND CODCLI='$ncliente'");
    $otabpfc= mysqli_query($dbc, "select * from CLIENTE_PFC WHERE IS_DELETED='N' AND CODIDO='$ncliente'");
    if (mysqli_num_rows($otabcli)==0 ) {
        $aret[0]["estatus"] = 73;
        $aret[0]["cestatus"] = "Cliente não existe";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $olincli= mysqli_fetch_array($otabcli);
    $olinpfc= mysqli_fetch_array($otabpfc);

    if ($olinpfc["BLOQUEADO"]=="Y" ) {
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
        $aret[0]["cestatus"] = "Quantidade e produtos nao sao iguais";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    /*
    if ($nday == 0) {
        $aret[0]["estatus"] = 40;
        $aret[0]["cestatus"] = "Dias de bloqueio invalido";
        echo json_encode($aret);
        logs($aret);
        return;
    }
     */
    if ($npgt<1 || $npag>3) {
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
        $atotais[0] += $aqua[$i - 1];
        $otable = mysqli_query($dbc, "select p.codpro,sum(if(isnull(l.proloc),0,l.qualoc)) as estoque,p.ativo,w.bloqueado ,if(w.nomeweb='',p.nompro,w.nomeweb) as nome,w.nimagem
        ," . $preco_env . " as preco
        ,if(isnull(wm.memo),'',wm.memo) as texto ,if(isnull(wm1.memo),'',wm1.memo) as mini,w.link2 as fabrica,w.link1 as video
        ,p.largura,p.altura,p.profund,p.pespro as peso,w.estmax,w.permax
        from PRODUTO_PRO p 
        inner join PRODUTOS_WEB w on w.is_deleted='N'  and w.codigo=p.codpro  AND w.ecommerce='S'
        left  join PRODUTOS_WEB_MEMO wm on w.recno=wm.recno  and wm.col_name='TEXTO'
        left  join PRODUTOS_WEB_MEMO wm1 on w.recno=wm1.recno  and wm1.col_name='MINIDESC'
        left join LOCAL_LOC l on l.is_deleted='N' and l.proloc=p.codpro  and find_in_set(l.locloc,'$clojasal')
        where p.is_deleted='N' and p.codpro=" . tiradigito($value) );
        $olinha = mysqli_fetch_array($otable);
        $atotais[1] += $olinha["preco"] * $aqua[$i - 1];
        if (mysqli_num_rows($otable) == 0) {
            $cerro .= $value . "-Produto nao localizado|";
            continue;
        }
        if ($olinha["estoque"] == 0) {
            $cerro .= $value . "-Produto sem saldo|";
            continue;
        }
        if ($olinha["ativo"] != "S" || $olinha["bloqueado"] == "Y") {
            $cerro .= $value . "-Produto Bloqueado|";
            continue;
        }
        $nsal = $olinha["estoque"];
        if ($olinha["permax"] > 0) {
            $nper = floor($olinha["estoque"] * ($olinha["permax"] / 100));
            if ($nsal > $nper) {
                $nsal = $nper;
            }
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
    if ($cerro) {
        $aret[0]["estatus"] = 72;
        $aret[0]["cestatus"] = $cerro;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $otable = mysqli_query($dbc, "select PEDIDO from NOTA_ECM where is_deleted='N'  and PEDIDO='$nped'");
    if (mysqli_num_rows($otable)) {
        $aret[0]["estatus"] = 70;
        $aret[0]["cestatus"] = "Pedido ja existe no cliente";
        echo json_encode($aret);
        logs($aret);
        return;
    }
//cria o pedido
    $omed = new Mediator();
    $omed->init($dbc, "SAIDAS.SAI");
    $omag = new Mediator();
    $omag->init($dbc, "NOTA.ECM");

    $omed->aadd("DATASAI", "CURRENT_DATE()",TRUE);
    $omed->aadd("DATAREC", "CURRENT_DATE()",TRUE);
    $omed->aadd("HORASAI", "CURRENT_TIME()",TRUE);
    //$omed->aadd("DATA"   , "current_date()",true);
    $omed->aadd("CLIESAI", $olincli["CODCLI"]);
    $omed->aadd("VENDSAI", $olcfg["VENDEDOR"]);
    $nvence= floor($olcfg[$npgt==1?"PGCARTAO":($npgt==2?"PGBOLETO":"PGOUTROS")]/60/60/24) ;
    $agrava[] = array("VENCSAI", date('Y-m-d', strtotime(hoje_sql() . ' + ' . $nvence . ' days')));
    $omed->aadd("LOJASAI", $olcfg["LOJA"]);
    $omed->aadd("LOJACOR", $olcfg["LOJA"]);
    $omed->aadd("TIPOSAI", "V");
    $omed->aadd("CXUNSAI", "C");
    $omed->aadd("MOEDA", 1);
    $omed->aadd("CAMBSAI", 1);
    $omed->aadd("BASESAI", 1);
    $omed->aadd("VALOSAI", $atotais[1]);
    $omed->aadd("QUANSAI", $atotais[0]);
    $omed->aadd("QUANFIN", $atotais[0]);
    $omed->aadd("VVISTA", 0);
    //$agrava[] = array("NRDIAS", $ocfg["PRAZO"]);
    $omed->aadd("OBSERV", "ECOMMERCE:" . $nped);
    $omed->aadd("RETISAI", "N");
    $omed->aadd("OPESAI", 1);
    $omed->aadd("FRETSAI", "N");
    $omed->aadd("SITSAI", "N");
    $omed->aadd("VENDFAT", $ocfg["VENDEDOR"]);
    $omed->aadd("DATAREC", "current_date()");
    $omed->aadd("OPEREC", ($ocfgcec["BXNOTA"] == "S" || $ocfgcec["CAXCENT"] == "S") ? 0 : 1);
    $omed->aadd("TERMSAI", "E99");
    $cobs = "Pedido via web Ecommerce no. $nped " . chr(13) . chr(10) .  $cobs;
    $omed->aadd("OBSAI", $cobs, false, true);
    if (!$omed->grava()){
        $aret[0]["estatus"] = 71;
        $aret[0]["cestatus"] = "Erro criando o pedido no cliente - ".$omed->cerro;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    mysqli_query($dbc, "update SAIDAS_SAI SET NOTASAI=".$omed->nrecno." WHERE RECNO=".$omed->nrecno);
    
    $omag = new Mediator();
    $omag->init($dbc, "NOTA.ECM");
 
    $omag->aadd("pedido",$nped );
    $omag->aadd("nota",$omed->nrecno );
    $omag->aadd("dtemi", "date2unix(now())",true );
    $omag->aadd("dtped", "date2unix(now())",true );
    $omag->aadd("FORMAPAG", $npgt );
    $omag->aadd("DIAS", $nvence );
    if (!$omag->grava()){
        mysqli_query($dbc, "update SAIDAS_SAI SET is_deleted='Y' WHERE RECNO=".$omed->nrecno);
        $aret[0]["estatus"] = 71;
        $aret[0]["cestatus"] = "Erro criando o pedido no cliente...".$omag->cerro;
        echo json_encode($aret);
        logs($aret);
        return;
    }
    //+81 3-4510-2539
    //141832

    $i = 0;
    
    foreach ($asku as $value) {
        $i++;
        $value= tiradigito($value);
        $nqua=$aqua[$i - 1];
        $agrava = array();
        $agravapro = array();

        $odet = new Mediator();
        $odet->init($dbc, "PROSAI.SAI");


        $lpro = mysqli_fetch_array(mysqli_query($dbc,"select PREMIN AS MINIMO,PREVEN,NOMPRO,ESTOQUE,CUSMED,ULTCUS," . $preco_env . " as PRECO 
            from PRODUTO_PRO p where is_deleted='N' and CODPRO=$value"));
//        $opro = mysqli_linha("PRODUTO_PRO", "PREMIN AS MINIMO,PREVEN,NOMPRO,ESTOQUE,CUSMED,ULTCUS," . $preco_env . "", "CODPRO=" . $aaux[1]);

        $odet->aadd("NFSAI", $omed->nrecno);
        $odet->aadd("DATASPRO", "current_date()",true);
        $odet->aadd("PRSAI", $value);
        $odet->aadd("QUSAI", $aqua[$i - 1]);
        $odet->aadd("CUMOE", $lpro["PRECO"]);
        $odet->aadd("QTVSAI", $nqua);
        $odet->aadd("REGSAI", $i);
        $odet->aadd("KITSAI", "N");
        $odet->aadd("CXUNSA", "C");
        $odet->aadd("NOMEPSA", $lpro["NOMPRO"]);

        $aiva = CalcIva($aaux[1], $lpro["PRECO"]);
        $odet->aadd("EXENTPRO", $aiva[0]);
        $odet->aadd("GRAVAPRO", $aiva[1]);

        //$oloc->aadd("ESTOQUE", $lpro["ESTOQUE"]);
        //$oloc->aadd("ULTVEN", "CURRENT_DATE()",TRUE);

        $odet->aadd("CUSATU", $ocfgcec["LUCESPE"] == "N" ? ($lpro["CUSMED"] < 0 ? $lpro["ULTCUS"] : $lpro["CUSMED"]) : $lpro["ULTCUS"]);
        $odet->aadd("PREMIN", $lpro["MINIMO"]);
        $odet->aadd("CUSAI", $lpro["PRECO"]);
        $odet->grava();
        
        $otabloc = mysqli_query($dbc, "select recno from LOCAL_LOC WHERE IS_DELETED='N' AND LOCLOC=".$olcfg["LOJA"]." AND PROLOC=$value");
        if (mysqli_num_rows($otabloc) == 0) {
            $oloc = new Mediator();
            $oloc->init($dbc, "LOCAL.LOC");
            $oloc->aadd("LOCLOC",$olcfg["LOJA"]);
            $oloc->aadd("PROLOC",$value);
            $oloc->grava();
        }
        $oloc = new Mediator();
        $oloc->init($dbc, "LOCAL.LOC","LOCLOC=".$olcfg["LOJA"]." AND PROLOC=".$value);
        $oloc->aadd("QUALOC", "qualoc-$nqua", true);
        $oloc->aadd("FPGLOC", "fpgloc+$nqua", true);
        $oloc->grava();

        mysqli_query($dbc, "update PRODUTO_PRO SET ULTVEN=CURRENT_DATE(),ESTOQUE=ESTOQUE-$nqua,FPGPRO=FPGPRO+$nqua where is_deleted='N' and codpro=$value");

    }

    $onot = mysqli_fetch_array(mysqli_query($dbc,"select DATASAI,HORASAI,QUANSAI,VALOSAI from SAIDAS_SAI where RECNO=$omed->nrecno"));
    $aret[] = array("ped" => poedigito($omed->nrecno), "emi" => $onot["DATASAI"] . " " . $onot["HORASAI"], "ite" => number_format($onot["QUANSAI"], 0)
        , "val" => number_format($onot["VALOSAI"], 2),"day" => $nvence);
    $aret[0]["element"] = $aret[0]["element"] + 1;
    
} elseif ($nope == 13) {
    $nped = pegavar("ped", $lpost);
    $npdc = pegavar("pdc", $lpost);

    if ($nped == '' && $npdc == '') {
        $aret[0]["estatus"] = 40;
        $aret[0]["cestatus"] = "Pedido vazio";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    if ($nped != '') {
        $otableped = mysqli_query($dbc, "select * from NOTA_ECM WHERE PEDIDO='$nped'");
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
    }
    if ($npdc != '') {
        $otableped = mysqli_query($dbc, "select * from NOTA_ECM WHERE NOTA='".tiradigito($npdc)."'");
        if (mysqli_num_rows($otableped) == 0 || !vedigito($npdc) ) {
            $aret[0]["estatus"] = 70;
            $aret[0]["cestatus"] = "Pedido nao existe no cliente";
            echo json_encode($aret);
            logs($aret);
            return;
        }
        $olinhaped = mysqli_fetch_array($otableped);
        $otablenot = mysqli_query($dbc, "select * from SAIDAS_SAI WHERE RECNO='".tiradigito($npdc)."'");
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
    $adet["ped"] = $olinhaped["PEDIDO"];
    $adet["pdc"] = POEDIGITO($olinhaped["NOTA"]);
    $adet["emi"] = $olinhanot["DATASAI"] . " " . $olinhanot["HORASAI"];
    $adet["ite"] = number_format($olinhanot["QUANSAI"], 0);
    $adet["val"] = number_format($olinhanot["VALOSAI"], 2);
    $adet["pgt"] = $olinhaped["FORMAPAG"];
    $adet["day"] = $olinhaped["DIAS"];
    $adet["ccl"] = $olinhanot["CLIESAI"];
    $adet["ncl"] = Converter(mysqli_fetch_row(mysqli_query($dbc, "select nomcli from CLIENTE_CLI WHERE IS_DELETED='N' AND CODCLI=".$olinhanot["CLIESAI"]))[0]);
    
    if ($olinhaped["DTPEDCAN"]>0){
        $adet["sit"]=2;
    }elseif ($olinhaped["DTDEV"]>0){
        $adet["sit"]=9 ;
    }elseif ($olinhaped["DTRMAINI"]>0 && $olinhaped["DTRMAFIM"]==0){
        $adet["sit"]=8 ;
    }elseif ($olinhaped["DTETG"]>0){
        $adet["sit"]=7 ;
    }elseif ($olinhaped["DTEXP"]>0){
        $adet["sit"]=6 ;
    }elseif ($olinhaped["DTSEPFIN"]>0){
        $adet["sit"]=5 ;
    }elseif ($olinhaped["DTSEPINI"]>0){
        $adet["sit"]=4 ;
    }elseif ($olinhaped["DTPAG"]>0){
        $adet["sit"]=3 ;
    }elseif ($olinhaped[""]>0){
    }else{
        $adet["sit"]=1 ;    
    }
    $aaux=array("can" => "DTPEDCAN","pag" => "DTPAG","spi" => "DTSEPINI","spf" => "DTSEPFIN","exp" => "DTEXP","ent" => "DTETG","dev" => "DTDEV","rmi" => "DTRMAINI","rmf" => "DTRMAFIN");
    foreach ($aaux as $key => $value) {
       $adet[$key] =$olinhaped[$value]>0?(Unix2Date($olinhaped[$value],true)[0]):'';
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
} elseif ($nope == 15 ) {
    $nped = pegavar("ped", $lpost);
    $npdc = pegavar("pdc", $lpost);

    if ($nped == '' || !$npdc ) {
        $aret[0]["estatus"] = 40;
        $aret[0]["cestatus"] = "Pedido vazio";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    if (!vedigito($npdc) ) {
        $aret[0]["estatus"] = 40;
        $aret[0]["cestatus"] = "Pedido do CEC invalido";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $npdc= tiradigito($npdc);
    $otableped = mysqli_query($dbc, "select * from NOTA_ECM WHERE PEDIDO='$nped'");
    if (mysqli_num_rows($otableped) == 0) {
        $aret[0]["estatus"] = 70;
        $aret[0]["cestatus"] = "Pedido nao existe no cliente";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $olinhaped = mysqli_fetch_array($otableped);
    if ($npdc+0 != $olinhaped["NOTA"]+0 ) {
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

    if ($olinhanot["SITSAI"] == "S" || $olinhaped["DTPAG"]>0) {
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
/*
    if ($olinha["naofatura"]) {
        mysqli_query($dbc, "update SAIDAS_SAI n,CONFIG c 
        SET n.BYDB_LOCAL='S'
        ,n.HORAREC=CURRENT_TIME()
        ,n.DATAREC=if(c.CUSRECE='N',CURRENT_DATE(),'1000-01-01')
        ,n.OPEREC=1
        ,n.VDUPSAI=n.VALOSAI
        ,n.NRDUP=1
        ,n.TIPOSAI='P' 
        WHERE c.RECNO=1 and n.RECNO=" . $olinhaped["NOTA"]);
    } else {
 * 
 */
        mysqli_query($dbc, "update SAIDAS_SAI n,CONFIG c 
        SET n.SITSAI='S'
        ,n.HORAREC=CURRENT_TIME()
        ,n.DATAREC=if(c.CUSRECE='N',CURRENT_DATE(),'1000-01-01')
        ,n.OPEREC=1
        ,n.VDUPSAI=n.VALOSAI
        ,n.NRDUP=1
        ,n.TIPOSAI='P' 
        WHERE c.RECNO=1 and n.RECNO=" . $olinhaped["NOTA"]);
   // }
    $otablenot = mysqli_query($dbc, "select * from SAIDAS_SAI WHERE RECNO=" . $olinhaped["NOTA"]);
    $olinhanot = mysqli_fetch_array($otablenot);

    $ocfgcec = mysqli_fetch_array(mysqli_query($dbc, "select * from CONFIG where recno=1 "));
    //$ocfg = mysqli_fetch_array(mysqli_query($dbc, "select * from MAGENTO_CFG where recno=1"));
    $odup=new Mediator();
    $odup->init($dbc, "DUPLICAT.CLI");
    
    $odup->aadd("NUMEDUP", padl($olinhanot["NOTASAI"], 7) . padl("1", 2));
    $odup->aadd("VENCDUP", $olinhanot["VENCSAI"]);
    $odup->aadd("MOEDUP", 1);
    $odup->aadd("VALODUP", $olinhanot["VALOSAI"]);
    $odup->aadd("CLIEDUP", $olinhanot["CLIESAI"]);
    $odup->aadd("BANCDUP", $olcfg["BANCO"]); //$ocfgcec["BCO_PAGARE"]);
    $odup->aadd("BASEDUP", $olinhanot["BASESAI"]);
    $odup->aadd("CAMBDUP", $olinhanot["CAMBSAI"]);
    $odup->aadd("EMISDUP", $olinhanot["DATASAI"]);
    $odup->aadd("FATDUP", "current_date()",true);
    $odup->aadd("DATODUP", "current_date()",true);
    $odup->grava();

    $oope=new Mediator();
    $oope->init($dbc, "OPERACAX.CAX","RECNO=1");
    $oope->aadd("NUMOPE", "NUMOPE+1", TRUE);
    $oope->grava();
    $olinopx = mysqli_linha("OPERACAX_CAX", "NUMOPE", "RECNO=1");

    $ocax=new Mediator();
    $ocax->init($dbc, "CAIXA.CAX");
    $ocax->aadd("CODCAX", $olcfg["CONTA"]);
    $ocax->aadd("DOCCAX", $olinhanot["NOTASAI"]);
    $olinhadis = mysqli_linha("DISTRIB_CAX", "HISDIS", "RECNO=11");
    $ocax->aadd("CODHIS", $olinhadis["HISDIS"]);
    $ocax->aadd("HORACAX", "CURRENT_TIME()", TRUE);
    $ocax->aadd("TERCAX", "W99");
    $ocax->aadd("NUMOPE", $olinopx["NUMOPE"]);
    $ocax->aadd("OPECAX", 1);
    $ocax->aadd("DATCAX", "current_date()", true);
    $ocax->aadd("EMICAX", "current_date()", true);
    $ocax->aadd("VALCAX", $olinhanot["VALOSAI"]);
    $ocax->aadd("VALMCAX", $olinhanot["VALOSAI"]);
    $ocax->aadd("MOECAX", 1);
    $ocax->aadd("BASECAX", $olinhanot["BASESAI"]);
    $ocax->aadd("CAMBCAX", 1);
    $ocax->aadd("LOJCAX", $olinhanot["LOJASAI"]);
    $ocax->aadd("TIPCAX", "N");
    $olincli = mysqli_linha("CLIENTE_CLI", "NOMCLI", "CODCLI=" . $olinhanot["CLIESAI"]);
    $ocax->aadd("NOMCAX", $olincli["NOMCLI"]);
    $ocax->aadd("CPLCAX", "VENDA REALIZADA VIA ECOMMERCE PEDIDO: " . $olinhaped["PEDIDO"]);
    $ocax->grava();
    //GravaSqlMed("CAIXA_CAX", $agravacx);
    //file($ccommit . "?tabela=CAIXA.CAX&refresh=1");

    $omoe=new Mediator();
    $omoe->init($dbc,"MOEDAS.CAX");
    $omoe->aadd("OPERMOE", $olinopx["NUMOPE"]);
    $omoe->aadd("OPCXMOE", 1);
    $omoe->aadd("HORAMOE", "current_time()", true);
    $omoe->aadd("CONTMOE", $ocfg["CONTA"]);
    $omoe->aadd("DATAMOE", "current_date()", true);
    $omoe->aadd("SINAL", "N");

    $olincam = mysqli_linha("CAMBIO_CAM", "MOEDA1,MOEDA2,MOEDA3,MOEDA4");

    $omoe->aadd("CAMB01", $olincam["MOEDA1"]);
    $omoe->aadd("CAMB02", $olincam["MOEDA2"]);
    $omoe->aadd("CAMB03", $olincam["MOEDA3"]);
    $omoe->aadd("CAMB04", $olincam["MOEDA4"]);
    $omoe->grava();

    mysqli_query($dbc, "update NOTA_ECM SET DTPAG=CURRENT_DATE() WHERE IS_DELETED='N' AND PEDIDO='$nped'");

    $aret[0]["estatus"] = 0;
    $aret[0]["cestatus"] = "OK";
} elseif ($nope == 16) {
    $nped = pegavar("ped", $lpost);
    $npdc = pegavar("pdc", $lpost);

    if ($nped == '' || !$npdc ) {
        $aret[0]["estatus"] = 40;
        $aret[0]["cestatus"] = "Pedido vazio";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    if (!vedigito($npdc) ) {
        $aret[0]["estatus"] = 40;
        $aret[0]["cestatus"] = "Pedido do CEC invalido";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $npdc= tiradigito($npdc);
    $otableped = mysqli_query($dbc, "select * from NOTA_ECM WHERE PEDIDO='$nped'");
    if (mysqli_num_rows($otableped) == 0) {
        $aret[0]["estatus"] = 70;
        $aret[0]["cestatus"] = "Pedido nao existe no cliente";
        echo json_encode($aret);
        logs($aret);
        return;
    }
    $olinhaped = mysqli_fetch_array($otableped);
    if ($npdc+0 != $olinhaped["NOTA"]+0 ) {
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

    if ($olinhanot["SITSAI"] == "S" || $olinhaped["DTPAG"]>0) {
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
        //$otablinha = mysqli_fetch_row($otabloc);
      /*
        if (mysqli_num_rows($otabloc) == 0) {
            $agrava = array();
            $agrava[] = array("LOCLOC", $olinhanot["LOJASAI"]);
            $agrava[] = array("PROLOC", $olinha["PRSAI"]);
            GravaSqlMed("LOCAL_LOC", $agrava);
            file($ccommit . "?tabela=LOCAL.LOC&refresh=1");
            logs($aret, "Nota(2):" . $olinhaped["NOTA"] . "|select prsai from LOCAL_LOC WHERE  IS_DELETED='N' AND LOCLOC=" . $olinhanot["LOJASAI"] . " AND PROLOC=" . $olinha["PRSAI"]);
        }
       * 
       */
        mysqli_query($dbc, "update LOCAL_LOC set qualoc=qualoc+" . $olinha["QUSAI"].",fpgloc=fpgloc-" . $olinha["QUSAI"] . " where IS_DELETED='N' AND LOCLOC=" . $olinhanot["LOJASAI"] . " AND PROLOC=" . $olinha["PRSAI"]);
        mysqli_query($dbc, "update PRODUTO_PRO SET ESTOQUE=ESTOQUE+" . $olinha["QUSAI"].",fpgpro=fpgpro-" . $olinha["QUSAI"] . " where IS_DELETED='N' AND CODPRO=" . $olinha["PRSAI"]);
        mysqli_query($dbc, "update PROSAI_SAI SET IS_DELETED='Y' WHERE RECNO=" . $olinha["RECNO"]);
    }



    $aret[0]["estatus"] = 0;
    $aret[0]["cestatus"] = "OK";
/*} elseif ($nope == "21") {
    $ctip = pegavar("tip", $lpost);
    if (!$ctip || ($ctip != "G" && $ctip != "W" && $ctip != "M")) {
        $aret[0]["data_ret"] = date('Y-m-d H:i:s');
        $aret[0]["estatus"] = "40";
        $aret[0]["cestatus"] = "Parametros invalidos";
        echo json_encode($aret);
        logs($aret);
        return;
    }

    if ($ctip == "G") {
        $cstr = "select codgru as cod,nomgru as nom from GRUPO_GRU where is_deleted='N' order by codgru ";
    } elseif ($ctip == "W") {
        $cstr = "select codigo as cod,nome as nom from GRADE_WEB where is_deleted='N' order by codIGO ";
    } else {
        $cstr = "select codmarc as cod,nommarc as nom from MARCAS_MAR where is_deleted='N' order by codMARC ";
    }

    $otable = mysqli_query($dbc, $cstr);
    while ($olinha = mysqli_fetch_array($otable)) {
        $adet = array();
        $adet["cod"] = $olinha["cod"];
        $adet["nom"] = urlencode($olinha["nom"]);
        $aret[] = $adet;
//echo $adet["nom"]."<br>";
        $aret[0]["element"] = $aret[0]["element"] + 1;
//echo $aret[1]["nom"]."<br>";
    }
*/
} elseif ($nope == 31) {
 
    $cstr="select * from NOTA_ECM WHERE IS_DELETED='N' AND DTPAG=0";
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
//echo $aret[1]["nom"]."<br>";
//echo urldecode($aret[1]["nom"])."<br>";
//echo PHP_VERSION;
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ", $db);

    $cpost = "";
    foreach ($_POST as $key => $value) {
        $cpost .= "$key , $value " . chr(10);
    }
    $cget = "";
    foreach ($_GET as $key => $value) {
        $cget .= "$key , $value " . chr(10);
    }
    /*
      foreach ($_SERVER as $key => $value) {
      echo "$key , $value <br>";
      }
     */
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
    mysqli_query($db, $cstr, $db);
}

function CalcIva($nProduto, $nValor) {
    $nGravada = 0;
    $nExenta = 0;
    $nIva = 0;
    $nAux = 0;
    $nDif = 0;

    $otab = mysqli_query($dbc, "select IVA,DECRETO FROM PRODUTO_PRO WHERE IS_DELETED='N' AND CODPRO=$nProduto");
    $olin = mysqli_fetch_array($otab);

    if (!$olin["IVA"] || $olin["DECRETO"] != 'S') {
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

    for ($ni = 0; $ni < strlen($crg); $ni++) {
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

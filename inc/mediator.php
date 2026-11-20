<?php
//error_reporting(E_ALL);
//ini_set('display_errors',0);
//ini_set('display_startup_erros',0);
require_once 'mediator1.php';

function VeSituacao($ctipo) {
    $aret = array();
    $aret["RC"] = "Recepção";
    $aret["AN"] = "Em Análise";
    $aret["CT"] = "Em Análise de Custos";
    $aret["RJ"] = "Rejeitado";
    $aret["AP"] = "Aguardando Aprovação";
    $aret["AV"] = "Aprovado";
    $aret["EX"] = "Em Execução";
    $aret["ET"] = "Serviço Executado";
    $aret["EN"] = "Entregue";
    $aret["OT"] = "Outros";
    $aret["PC"] = "Aguardando Peças";
    $aret["CK"] = "em Checagem";
    return $aret[strtoupper($ctipo)];
}


function AtualizaIndice($carquivo, $nrecno) {
    global $db;
    $afuncoes = array();
//    $afuncoes[] = "drop function if exists ctod";
    $afuncoes[] = "create function if not exists ctod(cstr varchar(256))
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
    $afuncoes[] = "create function str(numero DECIMAL(25,10),tam INT)
returns varchar(100)
begin
        declare deci int(1) default 0;
	declare cret varchar(100) default '';
	declare cdec varchar(20) default '';
	if (deci>0) THEN
		set cret=lpad(floor(numero),tam-1-deci,' ');
		set cdec=concat((numero%1),'0000000000');
		set cdec=substr(cdec,3,deci);
		return concat(cret,'.',cdec);	
	end if;
 	return lpad(round(numero,0),tam,' ');
end

";
    $afuncoes[] = "drop function if exists strzero";
    $afuncoes[] = "create function strzero(numero DECIMAL(25,10),tam INT,deci INT)
returns varchar(100)
begin
	declare cret varchar(100) default '';
	declare cdec varchar(20) default '';
	if (deci>0) THEN
		set cret=lpad(floor(numero),tam-1-deci,'0');
		set cdec=concat((numero%1),'0000000000');
		set cdec=substr(cdec,3,deci);
		return concat(cret,'.',cdec);	
	end if;
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
        $fp = fopen("/tmp/teste1.txt", "a");
        $escreve = fwrite($fp, $cstr);
        fclose($fp);
//        echo mysql_error().CR;
    }
}

function GravaSqlMedMemo($ctabela, $acampos, $arecno) {
    global $db;
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

function GravaSqlMed($ctabela, $acampos, $lupdate = false, $cwhere = '', $ltodosrecno = false,$cCampoCod='',&$ncodref=0) {
    global $db;
    $amemo = array();
    $ctabela = strtoupper($ctabela);
    $ctabela = str_replace(".", "_", $ctabela);

    if (substr($ctabela, -4) == "_DBF")
        $ctabela = substr($ctabela, 0, -4);

    $otable = mysqli_query($db,"SELECT COL_NAME NAME
,IF(COL_CLP_TYPE=1,'C',
IF(COL_CLP_TYPE=2048,'N',
IF(COL_CLP_TYPE=65,'M',
IF(COL_CLP_TYPE=8,'D',
IF(COL_CLP_TYPE=4,'L',
'' ))))) TIPO
,IF(COL_CLP_TYPE=1,COL_CLP_LEN,
IF(COL_CLP_TYPE=2048,COL_CLP_LEN,
IF(COL_CLP_TYPE=65,10,
IF(COL_CLP_TYPE=8,8,
IF(COL_CLP_TYPE=4,1,
'0' ))))) TAMANHO
,IF(COL_CLP_TYPE=2048,COL_CLP_DEC,'0') DECIMAIS
FROM CLP_TAB_COLS
WHERE TAB_NAME='" . $ctabela . "'
");
    $ncodigo=0;
    if ($cCampoCod){
        $olincod= mysqli_fetch_row(mysqli_query($db, "select max($cCampoCod) as codigo from $ctabela "));
        $ncodref=($olincod[0]?$olincod[0]:0)+1;
        $acampos[]=  array($cCampoCod,$ncodref);
    }
    $afields = array();
    while ($olinha = mysqli_fetch_row($otable)) {
        $afields[strtoupper($olinha[0])] = array($olinha[0], $olinha[1], $olinha[2], $olinha[3]);
    }

    if ($lupdate) {
        $cstr = "update ";
    } else {
        $cstr = "insert ";
    }
    $cstr.=$ctabela . " set ";
    for ($i = 0; $i < count($acampos); $i++) {
        //memo
        if ($afields[strtoupper($acampos[$i][0])][1] == "M" || (count($acampos[$i]) > 3 && $acampos[$i][3])) {
            $amemo[] = array($acampos[$i][0], $acampos[$i][1]);
            continue;
        }
        if ($i > 0)
            $cstr.=",";
        if ((count($acampos[$i]) > 2 && $acampos[$i][2]) || substr($acampos[$i][1], 0, 1) == "'") {
            $cstr.=$acampos[$i][0] . "=" . $acampos[$i][1];
        } else {
            if (substr($acampos[$i][1], 0, 1) == "|") {
                $cstr.=$acampos[$i][0] . "=";
                $cstr.=substr(substr(addslashes($acampos[$i][1]), 1), 0, -1);
            } else {
                $cstr.=$acampos[$i][0] . "='";
                //LOGICO
                if ($afields[strtoupper($acampos[$i][0])][1] == "L") {
                    $cstr.= ($acampos[$i][1] == 'N' || !$acampos[$i][1] ? 'N' : 'Y') . "'";
                } else {
                    $cstr.=addslashes($acampos[$i][1]) . "'";
                }
            }
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
//    echo $cstr.CR.CR;
    mysql_myquery($cstr);

//    $fp = fopen("/tmp/teste.txt", "a");
//    $escreve = fwrite($fp, $cstr);
//    fclose($fp);

    $caux = mysqli_error($db);
    if ($caux) {
        return "-1|$caux";
    }
    for ($i = 0; $i < count($arecno); $i++) {
        AtualizaIndice($ctabela, $arecno[$i]);
    }

//    men($cstr);
    //$arecno=mysql_fetch_row(mysql_query("select last_insert_id()"));
    if (count($amemo) > 0)
        GravaSqlMedMemo($ctabela, $amemo, $arecno);
    if (!$ltodosrecno) {
        return $arecno[0];
    } else {
        return $arecno;
    }
}


function VeMedEstru($ctable) {
    global $db;
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
    $cstr = trim($cstr);
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

function VeCfg($db,$cCampo, $nTela = '') {
    global $db;
    $olinha = mysql_linha("CONFIG" . $nTela, $cCampo, "RECNO=1");
    return $olinha[0];
}

function VePar($db,$cCampo) {
    global $db;
    $olinha = mysql_linha("PARAMETR.CFG", $cCampo, "RECNO=1");
    return $olinha[0];
}

function VeImp($nTerminal, $cCampo) {
    global $db;
    $olinha = mysql_linha("CONFIMP_IMP", $cCampo, "terminal=$nTerminal");
    return $olinha[0];
}

function VeCambio($nmoeda) {
    global $db;
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

    global $ctimezone;

    date_default_timezone_set(($mail_zone ? $mail_zone : $ctimezone));

    require_once("mailer/phpmailer/class.phpmailer.php");
//include("class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded

    $mail = new PHPMailer();
    /*
      $body             = $mail->getFile('contents.html');
      $body             = eregi_replace("[\]",'',$body);
     */
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
    /*
      if (!$mail->Send()) {

      echo "Mailer Error: " . $mail->ErrorInfo;
      } else {
      echo "Message sent!";
      }
     */
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

function transform($val, $mask, $ntam = 0) {
    if (!$ntam)
        $ntam = strlen($val);
    $val = padl($val, $ntam);
    $maskared = '';
    $k = 0;
    for ($i = 0; $i <= strlen($mask) - 1; $i++) {
        if ($mask[$i] == '#' || $mask[$i] == '9') {
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

function LeMemo($cTabela, $cCampo, $nRegistro) {
    global $db;
    $cCampo = strtoupper($cCampo);
    $olinha = mysqli_fetch_row(mysqli_query($db,"select $cCampo from $cTabela where recno=$nRegistro"));
    if (!$olinha[0])
        return '';
    $olinha = mysqli_fetch_row(mysqli_query($db,"select memo from " . $cTabela . "_MEMO where recno=$nRegistro and col_name='$cCampo' "));
    return $olinha[0];
}

function VeMoeSim($nMoeda = 1) {
    global $db;
    $olinha = mysqli_fetch_row(mysqli_query($db,"select SIMB$nMoeda from CAMBIO_NOM"));
    return $olinha[0];
}

function VeMoeNom($nMoeda = 1) {
    global $db;
    $olinha = mysqli_fetch_row(mysqli_query($db,"select NOME$nMoeda from CAMBIO_NOM"));
    return $olinha[0];
}

function VeMoeMasc($nMoeda = 1, $nTamanho = 13) {
    global $db;
    $olinha = mysqli_fetch_row(mysqli_query($db,"select DECI$nMoeda from CAMBIO_NOM"));
    $caux = "999,999,999,999,999,999" . ($olinha[0] != 'N' ? ".99" : "");
    $caux = substr($caux, ($nTamanho * -1));
    if (substr($caux, 0, 1) != "9")
        $caux = "9" . substr($caux, 1);

    return $caux;
}

function VeMoeFormat($nValor, $nMoeda = 1) {
    global $db;
    $olinha = mysqli_fetch_row(mysqli_query($db,"select DECI$nMoeda from CAMBIO_NOM"));
    return my_number_format($nValor, ($olinha[0] != 'N' ? 2 : 0));
}

?>
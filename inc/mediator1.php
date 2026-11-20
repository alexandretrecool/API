<?php

error_reporting(0);
ini_set('display_errors', 0);
ini_set('display_startup_erros', 0);

/*

  //commit
  $omed = new MedCommit("transfe.tra,protra.tra", true);
  //var_dump($omed->erro);
  //var_dump($omed->cerro);
  //ADICIONAR REGISTRO
  $omed = new MedCommit("transfe.tra", false, true);
  //var_dump($omed->erro);
  //var_dump($omed->cerro);
  //DELETAR
  $omed = new MedCommit("transfe.tra", false, false,true,336985);
  //var_dump($omed->erro);
  //var_dump($omed->cerro);
  //ATUALIZAR REGISTRO
  $omed = new MedCommit("transfe.tra", false, true,false,336985,array("obstra" => "okokokokokok"));
  //var_dump($omed->erro);
  //var_dump($omed->cerro);
  //INCLUIR REGISTRO
  $omed = new MedCommit("transfe.tra", false, true,false,0,array("obstra" => "&Teste de ação à data 01/01/2018"));
  //var_dump($omed->erro);
  //var_dump($omed->cerro);

  return;
 */

class MedCommit {

    public $nrecno;
    private $ctabela;
    private $acampos = "";
    private $cgrava = "";
    private $commit = 'http://localhost/cgi-bin/mediator.new';
    private $aret = array();
    public $erro = 0;
    public $cerro = "";

    public function __construct($cTabela = '', $lRefresh = false, $lAdiciona = false, $lDeleta = false, $nRecno = 0, $aCampos = array(), $db=null) {
        global $ccommit_new;
        if ($ccommit_new) {
            $this->commit = $ccommit_new;
        }
        if (!$this->commit) {
            $this->cerro = "Sem COMMIT CONFIGURADO";
            $this->erro = 1;
            return false;
        }
        if (!$cTabela) {
            $this->cerro = "Sem tabela informada";
            $this->erro = 1;
            return false;
        }
        $this->ctabela = $cTabela;
        if ($lRefresh) {
            if (!$nRecno || $nRecno == 0) {
                $this->cgrava = "refresh=" . $this->ctabela;
            } else {
                $ctab = Tab2Mediator($this->ctabela);
                $cname =  mysqli_fetch_row(mysqli_query($db, "select COL_NAME from CLP_TAB_COLS WHERE TAB_NAME='$ctab' AND COL_NAME NOT LIKE 'IE$%' LIMIT 1"))[0];
                $cdados = mysqli_fetch_row(mysqli_query($db, "select $cname from $ctab where recno=$nRecno"))[0];
                $this->cgrava = "tabela=" . $this->ctabela . "&recno=" . $nRecno . "&CMP" . $cname . "=" . urlencode($cdados);
            }
            $cstr = $this->commit . "?" . $this->cgrava;
            $this->aret = file($cstr);
            if (trim($this->aret[0]) == "ERR:") {
                $this->erro = $this->aret[1];
                $this->cerro = $this->aret[2];
                return;
            }
            if (trim($this->aret[0]) == "OK:") {
                $this->erro = 0;
                for ($i = 1; $i < count($this->aret); $i++) {
                    if (trim($this->aret[$i])) {
                        $this->cerro .= "," . $this->aret[1];
                        $this->nrecno .= "," . explode("-", $this->aret[$i])[0];
                    }
                }
                $this->nrecno = substr($this->nrecno, 1);
                $this->cerro = substr($this->cerro, 1);
                return;
            }
            $this->cerro = $this->aret[0];
            return;
        }
        if ($lDeleta) {
            if (!$nRecno) {
                $this->erro = 99;
                $this->cerro .= "Registro não passao";
            }
            $this->cgrava = "tabela=" . $this->ctabela . "&recno=" . ($nRecno * -1);
            //var_dump($this->cgrava);
            $cstr = $this->commit . "?" . $this->cgrava;
            $this->aret = file($cstr);
            if (trim($this->aret[0]) == "ERR:") {
                $this->erro = $this->aret[1];
                $this->cerro = $this->aret[2];
                return;
            }
            if (trim($this->aret[0]) == "OK:") {
                $this->erro = 0;
                $this->cerro .= $this->aret[1];
                $this->nrecno .= $nRecno;
                return;
            }
            $this->cerro = $this->aret[0];
            return;
        }
        if ($lAdiciona && !$aCampos) {
            $this->cgrava = "tabela=" . $this->ctabela . "&recno=0";
            $cstr = $this->commit . "?" . $this->cgrava;
            $this->aret = file($cstr);

            if (trim($this->aret[0]) == "ERR:") {
                $this->erro = $this->aret[1];
                $this->cerro = $this->aret[2];
                return;
            }
           
            if (trim($this->aret[0]) == "OK:") {
                $this->erro = 0;
                $this->cerro .= $this->aret[1];

                $aaux=explode("-", $this->aret[1]);
                $this->nrecno .= $aaux[0];
                
                return;
            }
            $this->cerro = $this->aret[0];
            return;
        }
        if ($aCampos) {
            $this->cgrava = "tabela=" . $this->ctabela . "&recno=" . ($lAdiciona ? "0" : $nRecno);
            foreach ($aCampos as $key => $value) {
                $this->cgrava .= "&CMP$key=" . str_replace("+", "%20", urlencode(iconv("utf8", "cp850", $value)));
            }
            $cstr = $this->commit . "?" . $this->cgrava;
            $this->aret = file($cstr);
            if (trim($this->aret[0]) == "ERR:") {
                $this->erro = $this->aret[1];
                $this->cerro = $this->aret[2];
                return;
            }
            if (trim($this->aret[0]) == "OK:") {
                $this->erro = 0;
                $this->cerro .= $this->aret[1];
                $this->nrecno .= explode("-", $this->aret[1])[0];
                return;
            }
            $this->cerro = $this->aret[0];
            return;
        }
    }

    private function grava() {
        $cstr = $this->commit . "?" . $this->cgrava;
        $this->aret = file($cstr);
        if ($this->aret[0] == "ERR:") {
            $this->erro = 0;
            $this->nerro = $this->aret[1];
            $this->cerro = $this->aret[2];
            return;
        }
        //var_dump($this->aret);
    }

}

class Mediator {

    public $db;
    public $arquivo;
    public $tabela;
    public $where;
    public $cerro = "";
    public $lok = false;
    private $campos = array();
    private $codigo = "";
    public $ncodigo = 0;
    private $agrava = array();
    public $nrecno = 0;
    public $arecno = array();

    public function limpa() {
        $this->agrava = array();
        $this->nrecno = 0;
        $this->arecno = array();
        $this->lok = false;
        $this->ncodigo = 0;
        $this->cerro = "";
    }

    public function CriaFunction() {

        $afuncoes = array();
        $afuncoes["ctod"] = "create function ctod(cstr varchar(256))
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
        $afuncoes["dtoc"] = "create function dtoc(cstr varchar(256))
returns varchar(100)

begin
 	return concat(substr(cstr,9,2),'/',substr(cstr,6,2),'/',substr(cstr,3,2));
end
";
        $afuncoes["alltrim"] = "create function alltrim(cstr varchar(256))
returns varchar(100)

begin
 	return trim(cstr);
end
";
        $afuncoes["len"] = "create function len(cstr varchar(256))
returns varchar(100)

begin
 	return length(cstr);
end
";
        $afuncoes["str"] = "create function str(numero DECIMAL(25,10),tam INT)
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
        $afuncoes["strzero"] = "create function strzero(numero DECIMAL(25,10),tam INT,deci INT)
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
        $afuncoes["dtos"] = "create function dtos(ddata CHAR(10))
returns varchar(30)

begin
return if(ddata='1000-01-01','        ',replace(ddata,'-',''));
return 'lpad(\''+if(ddata='1000-01-01','        ',replace(ddata,'-',''))+'\',8,\'\')';
end
";
        $afuncoes["val"] = "create function val(cstr VARCHAR(20))
returns DECIMAL(12,2)

begin
    return cstr+0;
end
";
        $afuncoes["descend"] = "create function descend(cstr VARCHAR(20))
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
        foreach ($afuncoes as $key => $value) {
            if (mysqli_num_rows(mysqli_query($this->db, "show function status where db=database() and name='$key'")) == 0) {
                mysqli_query($this->db, $value);
//                ////var_dump($value);
            }
//            ////var_dump(mysqli_error($this->db));
        }
    }

    public function init($db, $cArquivo, $cWhere = '', $cCampoCodigo = '') {
        if (!$db) {
            $this->cerro = "Erro conectando à database";
            return false;
        }
        $this->db = $db;
        $this->where = $cWhere;
        if (!$cArquivo) {
            $this->cerro = "Falta parâmetros";
            return false;
        }
        $this->CriaFunction();
        $this->codigo = strtoupper($cCampoCodigo);
        $this->arquivo = $cArquivo;
        /*
        $this->tabela = strtoupper($cArquivo);
        $this->tabela = str_replace(".", "_", $this->tabela);
        if (substr($this->tabela, -4) == "_DBF") {
            $this->tabela = substr($this->tabela, 0, -4);
        }
        */
        $this->tabela= Tab2Mediator($this->arquivo);
        $otable = mysqli_query($this->db, "SELECT COL_NAME NAME
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
WHERE TAB_NAME='" . $this->tabela . "'
");
        if (mysqli_num_rows($otable) == 0) {
            $this->cerro = "Tabela " . $this->tabela . " não encontrada";
            return false;
        }
        while ($olinha = mysqli_fetch_array($otable)) {
            $this->campos[$olinha["NAME"]] = array("NAME" => $olinha["NAME"], "TIPO" => $olinha["TIPO"], "TAMANHO" => $olinha["TAMANHO"], "DECIMAIS" => $olinha["DECIMAIS"]);
        }
        return true;
    }

    public function aadd($cCampo, $uValor, $lDireto = false) {
        if (!$lDireto) {
            $this->agrava[strtoupper($cCampo)] = $uValor;
        } else {
            $this->agrava[strtoupper($cCampo)] = "'" . $uValor . "'";
        }
    }

    public function grava() {
        global $ccommit;
        global $ccommit_new;
        if (!$this->where) {
           if ($this->codigo) {
                $csql = "select max($this->codigo) as codigo from " . $this->tabela;
                $this->ncodigo = mysqli_fetch_array(mysqli_query($this->db, $csql))[0] + 1;
                if (mysqli_errno($this->db)) {
                    $this->cerro = mysqli_error($this->db);
                    StrFile("/var/log/mediator_error_log", "Date:" . date("Y-m-d H:i:s") . "\nQuery: " . $csql . "\nError: " . $this->cerro, true);
                    return false;
                }
                $this->agrava[$this->codigo] = $this->ncodigo;
            }

            $cstr = "insert ";
            $cstr .= $this->tabela . " (`RECNO`";

            foreach ($this->agrava as $key => $value) {
                $cstr .= "," . $key;
            }
            $cstr .= ") values (RECNO";
            foreach ($this->agrava as $key => $value) {
                $cstr .= ",";
                if ($this->campos[$key]["TIPO"] == "N") {
                    if (substr($value, -1, 1) == "'") {
                        $value = substr($value, 1);
                        $value = substr($value, 0, -1);
                    } else {
                        $value = "'" . addslashes($value) . "'";
                    }
                    $cstr .= $value;
                } elseif ($this->campos[$key]["TIPO"] == "L") {
                    $cstr .= strtoupper($value) == "N" || strtoupper($value) == "F" || !strtoupper($value) ? "'N'" : "'Y'";
                } elseif ($this->campos[$key]["TIPO"] == "M") {
                    $cstr .= "0";
                } else {
                    if (substr($value, -1, 1) == "'") {
                        $value = substr($value, 1);
                        $value = substr($value, 0, -1);
                    } else {
                        $value = "'" . addslashes($value) . "'";
                    }
                    $cstr .= $value;
                }
            }
            $cstr .= ")";
            $ltradicional = true;

            if ($ccommit_new) {
                $omed = new MedCommit($this->arquivo, false, true);
                if ($omed->nrecno) {
                    $ltradicional = false;
                    $this->arecno[] = $omed->nrecno;
                    $this->nrecno = $omed->nrecno;
                } else {
                    StrFile("/var/log/mediator_error_log", "Date:" . date("Y-m-d H:i:s") . "\nErro adicionando registro " .$this->arquivo, true);
                    return false;
                }
            }
            if ($ltradicional) {
                $csql = "select max(recno) from $this->tabela";
                $this->nrecno = (mysqli_fetch_row(mysqli_query($this->db, $csql))[0]) + 1;
                if (mysqli_errno($this->db)) {
                    $this->cerro = mysqli_error($this->db);
                    StrFile("/var/log/mediator_error_log", "Date:" . date("Y-m-d H:i:s") . "\nQuery: " . $csql . "\nError: " . $this->cerro, true);
                    return false;
                }

                $this->arecno[] = $this->nrecno;
            }
            $cstr = str_replace("(RECNO,", "(" . $this->nrecno . ",", $cstr);
            if (!$ltradicional) {
                $cstr = "replace " . substr($cstr, 7);
            }
            $csql = "LOCK TABLES " . $this->tabela . " WRITE";
            mysqli_query($this->db, $csql);

            if (mysqli_errno($this->db)) {
                $this->cerro = mysqli_error($this->db);
                StrFile("/var/log/mediator_error_log", "Date:" . date("Y-m-d H:i:s") . "\nQuery: " . $csql . "\nError: " . $this->cerro, true);
                return false;
            }

            //strfile("teste.err","\n".$cstr,true);
            mysqli_query($this->db, $cstr);
            if (mysqli_errno($this->db)) {
                $csql = $cstr;
                $this->cerro = mysqli_error($this->db);
                StrFile("/var/log/mediator_error_log", "Date:" . date("Y-m-d H:i:s") . "\nQuery: " . $csql . "\nError: " . $this->cerro, true);
                return false;
            }
            if (!$ltradicional) {
                mysqli_query($this->db, "UNLOCK TABLES ");
                $omed = new MedCommit($this->arquivo, true);
            } else {
                $csql = "UNLOCK TABLES ";
                mysqli_query($this->db, $csql);
                if (mysqli_errno($this->db)) {
                    $this->cerro = mysqli_error($this->db);
                    StrFile("/var/log/mediator_error_log", "Date:" . date("Y-m-d H:i:s") . "\nQuery: " . $csql . "\nError: " . $this->cerro, true);
                    return false;
                }
            }

            $csql = "UNLOCK TABLES ";
            mysqli_query($this->db, $csql);
            if (mysqli_errno($this->db)) {
                $this->cerro = mysqli_error($this->db);
                StrFile("/var/log/mediator_error_log", "Date:" . date("Y-m-d H:i:s") . "\nQuery: " . $csql . "\nError: " . $this->cerro, true);
                return false;
            }
        } else {
            $cstr = "update ";
            $cstr .= $this->tabela . " set RECNO=RECNO";
            foreach ($this->agrava as $key => $value) {
                $cstr .= ",$key=";

                if ($this->campos[$key]["TIPO"] == "N") {
                    if (substr($value, -1, 1) == "'") {
                        $value = substr($value, 1);
                        $value = substr($value, 0, -1);
                    } else {
                        $value = "'" . addslashes($value) . "'";
                    }
                    $cstr .= $value;
                } elseif ($this->campos[$key]["TIPO"] == "L") {
                    $cstr .= strtoupper($value) == "N" || strtoupper($value) == "F" || !strtoupper($value) ? "'N'" : "'Y'";
                } elseif ($this->campos[$key]["TIPO"] == "M") {
                    $cstr .= "0";
                } else {

                    if (substr($value, -1, 1) == "'") {
                        $value = substr($value, 1);
                        $value = substr($value, 0, -1);
                    } else {
                        $value = "'" . addslashes($value) . "'";
                    }
                    $cstr .= $value;
                }
            }
            $cstr .= " where $this->where";
            $csql = "select recno from $this->tabela where $this->where";
            $otab = mysqli_query($this->db, $csql);
            if (mysqli_errno($this->db)) {
                $this->cerro = mysqli_error($this->db);
                StrFile("/var/log/mediator_error_log", "Date:" . date("Y-m-d H:i:s") . "\nQuery: " . $csql . "\nError: " . $this->cerro, true);
                return false;
            }
            while ($olin = mysqli_fetch_row($otab)) {
                $this->arecno[] = $olin[0];
            }

            mysqli_query($this->db, $cstr);
            if (mysqli_errno($this->db)) {
                $csql = $cstr;
                StrFile("/var/log/mediator_error_log", "Date:" . date("Y-m-d H:i:s") . "\nQuery: " . $csql . "\nError: " . $this->cerro, true);
                $this->cerro = mysqli_error($this->db);
                return false;
            }
        }

        foreach ($this->arecno as $recno) {

            foreach ($this->agrava as $key => $value) {
                if ($this->campos[$key]["TIPO"] == "M") {
                    $cstr = "REPLACE " . $this->tabela . "_MEMO (recno,col_name,memo) values ($recno,'$key','" . addslashes($value) . "')";
                    mysqli_query($this->db, $cstr);
                    if (mysqli_errno($this->db)) {
                        $csql = $cstr;
                        $this->cerro = mysqli_error($this->db);
                        StrFile("/var/log/mediator_error_log", "Date:" . date("Y-m-d H:i:s") . "\nQuery: " . $csql . "\nError: " . $this->cerro, true);
                        return false;
                    }

                    $cstr = "update $this->tabela set $key=" . strlen($value) . " where recno=$recno";
                    mysqli_query($this->db, $cstr);
                    if (mysqli_errno($this->db)) {
                        $csql = $cstr;
                        $this->cerro = mysqli_error($this->db);
                        StrFile("/var/log/mediator_error_log", "Date:" . date("Y-m-d H:i:s") . "\nQuery: " . $csql . "\nError: " . $this->cerro, true);
                        return false;
                    }
                }
            }
            //commitar ?
            //if ($ccommit_new) {
            $omed = new MedCommit($this->arquivo, true, false, false, $recno, array(), $this->db);
            //}
        }
        /*
          foreach ($this->campos as $key => $value) {
          if (substr($key, 0, 3) == "IE\$") {
          $otable = mysqli_query($this->db, "select col_name as nome,ind_clp_exp as indice from CLP_TAB_COLS where ind_clp_exp!='' and col_name like '$key' and  tab_name='$this->tabela'");
          while ($olinha = mysqli_fetch_array($otable)) {
          $caux = str_replace('+', ',', $olinha["indice"]);
          $caux = str_replace('"', "'", $caux);
          $caux = str_replace('==', "=", $caux);
          $cstr = "update $this->tabela set " . $olinha["nome"] . "=concat(" . $caux . ") where (0 ";
          foreach ($this->arecno as $recno) {
          $cstr .= " or recno=$recno";
          }
          $cstr .= ")";
          mysqli_query($this->db, $cstr);
          if (mysqli_errno($this->db)) {
          $csql=$cstr;
          $this->cerro = mysqli_error($this->db);
          StrFile("/var/log/mediator_error_log", "Date:" . date("Y-m-d H:i:s") . "\nQuery: " . $csql . "\nError: " . $this->cerro, true);
          return false;
          }
          }
          }
          }
          if ($ccommit_new) {
          $XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXx;
          $omed = new MedCommit($this->arquivo, true);
          }
         */
        return true;
    }

    public function __get($nome) {
        $nome = strtoupper($nome);
        return $this->agrava[$nome];
    }

    public function __set($nome, $uvalue) {
        $nome = strtoupper($nome);
        $this->aadd($nome, $uvalue);
        return $this->agrava[$nome];
    }

}

function Tab2Mediator($ctab) {
    $ctab = str_replace(".", "_", strtoupper($ctab));
    if (substr($ctab, -4) == "_DBF") {
        $ctab = substr($ctab, 0, -4);

//        $ctab = str_replace("_DBF", "", $ctab);
    }
    return $ctab;
}

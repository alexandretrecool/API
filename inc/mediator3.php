<?php

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('display_startup_erros', 0);

/*
  W_CRIAR/W_CRIAR_DEL Cria um registro e adiciona campos / Cria deletado
  $omed=new Mediator3("TESTE.DBF",W_CRIAR);
  $omed->add("codigo",1);
  $omed->add("nome","Dunha");

  if ($omed->grava()){
  var_dump(omed->nrecno);
  }

  W_DELETAR/W_RECALL Deleta/Recupera um registro
  $nregistro=10;
  $omed=new Mediator3("TESTE.DBF",W_DELETAR,'',$nregistro);

  if ($omed->grava()){
  echo "ok";
  }

  W_ELIMINAR Apaga arquivo
  $omed=new Mediator3("TESTE.DBF",W_ELIMINAR);

  if ($omed->grava()){
  echo "Arquivo apagado";
  }

  W_CRIAR_ARQUIVO  Cria arquivo
  $aestru=array();
  $aestru[]=array("codigo","n",10,0);
  $aestru[]=array("nome","c",30,0);

  $omed=new Mediator3("TESTE.DBF",W_CRIAR_ARQUIVO,$aestru);

  if ($omed->grava()){
  echo "ok";
  }

  W_REFRESH  Refresh no arquivo e traz o ultimo recno
  $omed=new Mediator3("TESTE.DBF",W_REFRESH);

  if ($omed->grava()){
  echo $omed->nrecno;
  }

  W_COMMIT  Comita os dados (para os indices IE$x)
  $omed=new Mediator3("TESTE.DBF",W_COMMIT);

  if ($omed->grava()){
  echo "ok";
  }



  W_BLOQUEAR Tenta Bloquea um registro. ** Desbloqueia após a execução
  $nregistro=10;
  $omed=new Mediator3("TESTE.DBF",W_BLOQUEAR,'',$nregistro);

  if ($omed->grava()){
  echo "Conseguiu bloquear";
  }else{
  echo "Não Conseguiu bloquear";
  }



  W_STRUCT
  $omed=new Mediator3("TESTE.DBF",W_STRUCT);
  $omed->grava();
  var_dump(omed->estrutura);
 */
define("W_CRIAR", 1);
define("W_ALTERAR", 1);
define("W_CRIAR_DEL", 2);
define("W_ALTERAR_DEL", 2);
define("W_DELETAR", 3);
define("W_RECALL", 4);
define("W_BLOQUEAR", 5);
define("W_ELIMINAR", 6);
define("W_CRIAR_ARQUIVO", 7);
define("W_REFRESH", 8);
define("W_COMMIT", 9);
define("W_STRUCT", 10);
define("W_LIMPAR", 11);
define("W_INCREMENT", 12);

define("W_MAX", 12);

class Mediator3 {

    private $tabela;
    private $tabela_memo = '';
    private $arquivo;
    private $campos = array();
    private $commit = 'http://localhost/cgi-bin/medweb.cgi';
    private $aret = array();
    private $modo = 0;
    private $aestru = array();
    private $id;
    private $chave = 0;
    private $aEstruTab;
    private $db;
    public $recnopos;
    public $ntempo = 0;
    public $campocodigo = '';
    public $erro = 0;
    public $cerro = "";
    public $omedret;
    public $recno;
    public $nrecno;
    public $estrutura = array();
    public $lLimpaAlt = false;

    public function __construct($cArquivo = '', $nmodo = 0, $acampos = '', $nRecnoposcWhere = 0, $odb = '', $cCampoCodigo = '',$lLimpaAltera=false) {
        global $ccommit_web;
        global $db;

        if ($ccommit_web) {
            $this->commit = $ccommit_web;
        }
        if ($cCampoCodigo) {
            $this->campocodigo = strtoupper(trim($cCampoCodigo));
        }

        if (!$this->commit) {
            $this->cerro = "Sem COMMIT CONFIGURADO";
            $this->erro = 11;
            return false;
        }
        if (!$cArquivo) {
            $this->cerro = "Sem arquivo informado";
            $this->erro = 12;
            return false;
        }
        if ($odb) {
            $this->db = $odb;
        } else {
            $this->db = $db;
        }
        if (!$this->db) {
            $this->cerro = "Nao conectado ao MySql";
            $this->erro = 13;
            return false;
        }
        if (mysqli_num_rows(mysqli_query($this->db, "SHOW TABLES LIKE 'MEDWEB'")) == 0 || mysqli_num_rows(mysqli_query($this->db, "SHOW TABLES LIKE 'MEDWEBC'")) == 0) {
            $omedret = json_decode(file_get_contents($this->commit));
            if (!$omedret || $omedret->erro !== 80) {
                $this->erro = $omedret->erro;
                $this->cerro = $omedret->cerro;
                return false;
            }
        }
        if ($nmodo < 1 || $nmodo > W_MAX) {
            $this->cerro = "Modo invalido";
            $this->erro = 14;
            return false;
        }
        $this->modo = $nmodo;
        if ($lLimpaAltera && ($nmodo== W_ALTERAR || $nmodo== W_ALTERAR_DEL || $nmodo== W_LIMPAR)){
            $this->lLimpaAlt=true;
        }


        $this->arquivo = $cArquivo;
        $this->tabela = strtoupper($this->arquivo);
        if (substr($this->tabela, -4) == ".DBF") {
            $this->tabela = substr($this->tabela, 0, -4);
        } elseif (strpos($this->tabela, ".")) {
            $this->tabela = str_replace(".", "_", $this->tabela);
        }
        if (strlen($this->tabela) > 12) {
            $this->tabela = substr($this->tabela, 0, 12);
        }

        if ($nRecnoposcWhere) {
            if (is_numeric($nRecnoposcWhere)) {
                $this->recnopos = $nRecnoposcWhere;
            } else {
                $naux = mysqli_fetch_row(mysqli_query($odb, "select recno from " . $this->tabela . " where " . $nRecnoposcWhere))[0];
                if (mysqli_errno($odb)) {
                    $this->cerro = "Erro na clausula WHERE => " . mysqli_error($odb);
                    $this->erro = 15;
                    return false;
                }
                $this->recnopos = $naux;
            }
        }
        if (!$this->recnopos && ($this->modo == W_DELETAR || $this->modo == W_RECALL || $this->modo == W_BLOQUEAR || $this->modo == W_LIMPAR)) {
            $this->cerro = "Falta registro para este modo";
            $this->erro = 15;
            return false;
        }

        if ($acampos) {
            foreach ($acampos as $value) {
                $caux = trim(strtoupper($value[0]));
                $this->campos[$caux] = array($value[1], $value[2], 0);
                if (count($value) > 3) {
                    $this->campos[$caux][2] = $value[3];
                }
            }
        }
    }

    public function limpar() {
        /*
          | COL_NAME   | COL_TYPE | COL_CLP_TYPE |
          +------------+----------+--------------+
          | CHAR       |        0 |            1 |
          | DATE       |        0 |            8 |
          | IS_DELETED |        2 |            4 |
          | LOGIC      |        0 |            4 |
          | MEMO       |        0 |           65 |
          | NUMBER     |        0 |         2048 |
          | NUMBER_DEC |        0 |         2048 |
          | RECNO      |        1 |            2 |
         */

        $cstr = "select COL_NAME,COL_CLP_TYPE FROM CLP_TAB_COLS WHERE TAB_NAME='" . $this->tabela . "' AND COL_NAME!='RECNO' AND COL_NAME!='IS_DELETED' ";
        $otab = mysqli_query($this->db, $cstr);

        $cstr = "update " . $this->tabela . " set is_deleted='N'";
        while ($olin = mysqli_fetch_array($otab)) {
            $cstr .= "," . $olin["COL_NAME"] . "=";
            switch ($olin["COL_CLP_TYPE"]) {
                case "2048": //numeric
                    $cstr .= '0';
                    break;
                case "8": // date
                    $cstr .= "'1000-01-01'";
                    break;
                case "4": //logic
                    $cstr .= "'N'";
                    break;
                case "65": //memo
                    $cstr .= '0';
                    break;
                default :
                    $cstr .= "''";
                    break;
            }
        }
        
        $cstr .= " where recno=" . $this->recnopos;
        mysqli_query($this->db, $cstr);
        mysqli_query($this->db, "delete from " . $this->tabela_memo . " where recno=" . $this->recnopos);
    }

    public function grava() {
        if ($this->erro) {
            return false;
        }
        $aaux = explode(" ", microtime());
        $this->ntempo = $aaux[1] + $aaux[0];

        $cchave = date("U") . rand(1, 99999);
        $this->chave = $cchave;
        $csql = "insert MEDWEB SET ORDEM='$cchave',modo=" . $this->modo . ",arquivo='" . $this->arquivo . "'";
        if ($this->recnopos) {
            $csql .= ",REG=" . $this->recnopos;
        }
        mysqli_query($this->db, $csql);
        $this->id = mysqli_fetch_row(mysqli_query($this->db, "select last_insert_id()"))[0];
        if ($this->modo == W_LIMPAR) {
            $this->modo = W_RECALL;
        }
        if ($this->lLimpaAlt && ($this->modo== W_ALTERAR || $this->modo== W_ALTERAR_DEL)){
            $this->limpar();
        }
        if ($this->modo == W_REFRESH || $this->modo == W_COMMIT || $this->modo == W_DELETAR || $this->modo == W_RECALL || $this->modo == W_BLOQUEAR || 
                $this->modo == W_CRIAR_DEL || $this->modo == W_CRIAR || $this->modo == W_ELIMINAR || $this->modo == W_STRUCT || $this->modo == W_INCREMENT) {
            if ($this->campocodigo && ( $this->modo == W_CRIAR || $this->modo == W_CRIAR_DEL|| $this->modo == W_INCREMENT)) {
                mysqli_query($this->db, "insert MEDWEBC SET ORDEM='$cchave',NOME='" . $this->campocodigo . "'");
            }
            foreach ($this->campos as $key => $value) {
                $csqlcampo = "insert MEDWEBC SET ORDEM='$cchave',NOME='$key'";
                if (strtoupper($value[0]) == "C") {
                    $csqlcampo .= ",C=" . assql($value[1]);
                    mysqli_query($this->db, $csqlcampo);
                } elseif (strtoupper($value[0]) == "N") {
                    $csqlcampo .= ",N=" . assql($value[1]);
                    mysqli_query($this->db, $csqlcampo);
                } elseif (strtoupper($value[0]) == "D") {
                    $csqlcampo .= ",D=" . assql($value[1]);
                    mysqli_query($this->db, $csqlcampo);
                } elseif (strtoupper($value[0]) == "L") {
                    $caux = strtoupper($value[1]);
                    if ($caux == "Y" || $caux == "S" || $caux == "T" || $caux + 0 > 0) {
                        $caux = 'Y';
                    } else {
                        $caux = 'N';
                    }
                    $csqlcampo .= ",L=" . assql($caux);
                    mysqli_query($this->db, $csqlcampo);
                } elseif (strtoupper($value[0]) == "M") {
                    $cmemo = $value[1];
                    $csqlcampo .= ",M=" . strlen($cmemo);
                    mysqli_query($this->db, $csqlcampo);
                    $nid = mysqli_fetch_row(mysqli_query($this->db, "select last_insert_id()"))[0];
                    mysqli_query($this->db, "SET FOREIGN_KEY_CHECKS = 0");

                    $csql = "insert MEDWEBC_MEMO (recno,col_name,memo) values ($nid,'M'," . assql($cmemo) . ")";

                    mysqli_query($this->db, $csql);
                    mysqli_query($this->db, "SET FOREIGN_KEY_CHECKS = 1");
                } elseif (strtoupper($value[0]) == "Q") {
                    $csqlcampo .= ",Q=" . assql($value[1]);
                    mysqli_query($this->db, $csqlcampo);
                }
            }
            return $this->grava_web();
        } elseif ($this->modo == W_CRIAR_ARQUIVO) {
            foreach ($this->campos as $key => $value) {
                $csqlcampo = "insert MEDWEBC SET ORDEM='" . $this->chave . "'";
                $caux = "$key," . strtoupper($value[0]) . ",";
                if (!$value[1]) {
                    $caux .= "10,";
                } else {
                    $caux .= $value[1] . ",";
                }
                if (!$value[2]) {
                    $caux .= "0";
                } else {
                    $caux .= $value[2];
                }
                mysqli_query($this->db, $csqlcampo . ",C='$caux'");
            }
            return $this->grava_web();
        }
    }
/*
    public function add($cCampo, $cTipo = 'C', $uValor = '', $uDec = 0) {
        $cCampo = trim(strtoupper($cCampo));
        $this->campos[$cCampo] = array($cTipo, $uValor, $uDec);
    }
*/
    public function aadd($cCampo, $uValor = '', $lsql = false) {

        if (!$this->aEstruTab) {
            /*
              8-data
              1-char
              65-memo
              `       2048-2-integer
              4-logico
             */
            $otab = mysqli_query($this->db, "SELECT COL_NAME,COL_CLP_TYPE FROM CLP_TAB_COLS WHERE TAB_NAME='" . $this->tabela . "' AND COL_NAME!='RECNO' AND COL_NAME!='IS_DELETED' AND COL_NAME NOT LIKE 'IE$%'");

            $this->aEstruTab = array();
            while ($olin = mysqli_fetch_row($otab)) {
                $ctipo = "C";
                if ($olin[1] == 8) {
                    $ctipo = "D";
                } elseif ($olin[1] == 65) {
                    $ctipo = "M";
                } elseif ($olin[1] == 2048 || $olin[1] == 2) {
                    $ctipo = "N";
                } elseif ($olin[1] == 4) {
                    $ctipo = "L";
                } else {
                    $ctipo = "C";
                }
                $this->aEstruTab[$olin[0]] = $ctipo;
            }
        }
        $cCampo = trim(strtoupper($cCampo));
        if ($lsql) {
            $this->campos[$cCampo] = array("Q", $uValor, 0);
        } else {
            $this->campos[$cCampo] = array($this->aEstruTab[$cCampo], $uValor, 0);
        }
    }

    public function grava_web() {
        $crequest = $this->commit . "?chave=" . $this->chave;
        $cret = file_get_contents($crequest);
        $this->omedret = json_decode($cret);
        if (!$this->omedret) {
            $this->erro = 100;
            $this->cerro = $cret;//"erro acessando medweb |".$cret."|";
            $aaux = explode(" ", microtime());
            $this->ntempo = $aaux[1] + $aaux[0] - $this->ntempo;
            return false;
        }
        if ($this->omedret->erro > 0) {
            $this->erro = $this->omedret->erro;
            $this->cerro = $this->omedret->cerro;
            $aaux = explode(" ", microtime());
            $this->ntempo = $aaux[1] + $aaux[0] - $this->ntempo;
            return false;
        }
        if ($this->omedret->estrutura) {
            $this->estrutura = $this->omedret->estrutura;
        }
        $this->recno = $this->omedret->reg;
        $this->nrecno = $this->omedret->reg;
        $aaux = explode(" ", microtime());
        $this->ntempo = $aaux[1] + $aaux[0] - $this->ntempo;
        return true;
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

        $otab = mysqli_query($this->db, "show function status where db=database()");
        $afuncoes = array();
        while ($olin = mysqli_fetch_row($otab)) {
            $afuncoes[] = $olin[1];
        }

        foreach ($afuncoes as $key => $value) {
            if (array_search($key, $afuncoes) === false) {
                mysqli_query($this->db, $value);
            }
        }
    }

    public function __get($nome) {
        $nome = strtoupper($nome);
        return $this->campos[$nome][1];
    }

    public function __set($nome, $uvalue) {
        $nome = strtoupper($nome);
        if (count($uvalue) > 1) {
            $this->aadd($nome, $uvalue[0], $uvalue[1]);
        } else {
            $this->aadd($nome, $uvalue);
        }
        return $this->campos[$nome][1];
    }
}

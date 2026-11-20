<?php
/*
require("./inc/config.php");
conecta();
dia();
return;
*/
function dia() {
    $estru = array();
    $estru[0] = array("cliente","");
    $estru[0][] = array("server"    , "varchar(100)", false, '');
    $estru[0][] = array("port"      , "varchar(30)", false, '');
    $estru[0][] = array("user"      , "varchar(30)", false, '');
    $estru[0][] = array("password"  , "varchar(30)", false, "");
    $estru[0][] = array("data"  , "varchar(30)", false, "");
    $estru[0][] = array("usuario"   , "varchar(30)", false, "");
    $estru[0][] = array("senha"     , "varchar(30)", false, "");
    $estru[0][] = array("codigo"    , "varchar(30)", false, "");
    $estru[0][] = array("commit"    , "varchar(150)", false, "");
    $aprimary = array("codigo");

    vecampos($estru,$aprimary);
/*
    
    $estru = array();
    $estru[0] = array("php_wc_session", "codigo");
    $estru[0][] = array("codigo", "int(10)", false, null);
    $estru[0][] = array("nome", "varchar(30)", false, null);
    $estru[0][] = array("sid", "varchar(100)", false, "''");
    $estru[0][] = array("data_in", "datetime", false, "'0000-00-00 00:00:00'");
    $estru[0][] = array("data_out", "datetime", false, "'0000-00-00 00:00:00'");
    $estru[0][] = array("nivel", "int(10)", false, "0");
    $estru[0][] = array("ip", "varchar(16)", false, "''");
    $estru[0][] = array("language", "int(1)", false, "0");

    vecampos($estru);
    $estru = array();

    $estru[0] = array("php_wc_config_operador", "usuario");
    $estru[0][] = array("usuario", "int(10)", false, '0');
    $estru[0][] = array("conta", "int(10)", false, '0');
    $estru[0][] = array("i_0", "time", false, "'00:00:00'");
    $estru[0][] = array("f_0", "time", false, "'23:59:59'");
    $estru[0][] = array("i_1", "time", false, "'00:00:00'");
    $estru[0][] = array("f_1", "time", false, "'23:59:59'");
    $estru[0][] = array("i_2", "time", false, "'00:00:00'");
    $estru[0][] = array("f_2", "time", false, "'23:59:59'");
    $estru[0][] = array("i_3", "time", false, "'00:00:00'");
    $estru[0][] = array("f_3", "time", false, "'23:59:59'");
    $estru[0][] = array("i_4", "time", false, "'00:00:00'");
    $estru[0][] = array("f_4", "time", false, "'23:59:59'");
    $estru[0][] = array("i_5", "time", false, "'00:00:00'");
    $estru[0][] = array("f_5", "time", false, "'23:59:59'");
    $estru[0][] = array("i_6", "time", false, "'00:00:00'");
    $estru[0][] = array("f_6", "time", false, "'23:59:59'");
    $estru[0][] = array("lojas", "varchar(30)", false, "''");
    $estru[0][] = array("vendedores", "varchar(30)", false, "''");
    $estru[0][] = array("bloqueado", "int(10)", false, "'0'");
    $estru[0][] = array("imprimir", "varchar(100)", false, "''");
    $estru[0][] = array("vias", "int(10)", false, '0');
    $estru[0][] = array("cadcli", "int(10)", false, '0');
    $estru[0][] = array("limcli", "decimal(14,2)", false, '0');
    $estru[0][] = array("pracli", "int(10)", false, '0');
    $estru[0][] = array("grupos", "varchar(100)", false, "''");
    $estru[0][] = array("lojas_ven", "varchar(100)", false, "''");
    $estru[0][] = array("lojas_transf", "int(10)", false, "0");
    $aprimary = array("usuario");
    $aindices = array();
    $aindices["usuario"] = array("usuario", "conta");
//    $aindices["conta"] = array("conta", "usuario");
    vecampos($estru, $aprimary,$aindices);
*/
    return;
}

function vecampos($estru, $aprimary, $aindices='') {
    global $db;
    for ($i = 0; $i < count($estru); $i++) {
        //cria se nao existe
        $caux = "create table if not exists `" . $estru[$i][0] . "` (";
        for ($j = 2; $j < count($estru[$i]); $j++) {
            $caux = $caux . "`" . $estru[$i][$j][0] . "` " . $estru[$i][$j][1];
            if (!$estru[$i][$j][2]) {
                $caux = $caux . " NOT NULL ";
            }
            if ($estru[$i][$j][3] != "") {
                $caux = $caux . " default " . $estru[$i][$j][3];
            }
            if ($j + 1 != count($estru[$i])) {
                $caux = $caux . ",";
            }
        }
        if ($estru[$i][1]) {
            $caux = $caux . ", key `" . $estru[$i][1] . "` (`" . $estru[$i][1] . "`)";
        }
        $caux = $caux . ") ENGINE=MyISAM default charset=latin1";
        mysqli_query($db,$caux);
        //echo $caux."<br><br>";
        //altera se existe    
        for ($j = 2; $j < count($estru[$i]); $j++) {
            $otable = mysqli_query($db,"desc " . $estru[$i][0]);
            $ltem = 0;
            $cdesc = "";
            $caux = "";
            while ($olinha = mysqli_fetch_row($otable)) {
                if ($estru[$i][$j][0] == $olinha[0]) {
                    $ltem = $j;
                    $cdesc = $olinha[1];
                }
            }
            if (!$ltem) {
                $caux = " add (`" . $estru[$i][$j][0] . "` " . $estru[$i][$j][1];
                if (!$estru[$i][$j][2])
                    $caux = $caux . " NOT NULL ";
                if ($estru[$i][$j][3] != "")
                    $caux = $caux . " default " . $estru[$i][$j][3];
                $caux = $caux . ") ";
            }else {
                if ($estru[$i][$j][1] !== $cdesc) {
                    $caux = " modify `" . $estru[$i][$j][0] . "` " . $estru[$i][$j][1];
                    if (!$estru[$i][$j][2])
                        $caux = $caux . " NOT NULL ";
                    if ($estru[$i][$j][3] != "")
                        $caux = $caux . " default " . $estru[$i][$j][3];
//		$caux=$caux.") ";
                }
            }
            if ($caux) {
                $caux = "alter table `" . $estru[$i][0] . "` " . $caux;
                mysqli_query($db,$caux);
            }
//	echo $caux."<br>";
        }
    }

    if ($aprimary) {
        $otable = mysqli_query($db,"show index from " . $estru[0][0]);
        $aaux = array();
        while ($olinha = mysqli_fetch_row($otable)) {
            if ($olinha[2] == "PRIMARY") {
                $aaux[] = $olinha[4];
            }
        }
        if (count($aprimary) > 0 && count($aaux) != 0 && count($aaux) != count($aprimary)) {
            mysqli_query($db,"alter table " . $estru[0][0] . " drop primary key");
            $aaux = array();
        }
        for ($j = 0; $j < count($aaux); $j++) {
            if (!($aaux[$j] == $aprimary[$j])) {
                mysqli_query($db,"alter table " . $estru[0][0] . " drop primary key");
                $aaux = array();
                break;
            }
        }
        if (count($aaux) == 0) {
            $caux = "alter table " . $estru[0][0] . " add primary key(";
            for ($j = 0; $j < count($aprimary); $j++) {
                if ($j != 0)
                    $caux.=",";
                $caux.=$aprimary[$j];
            }
            $caux.=")";
            mysqli_query($db,$caux);
        }
    }

    if ($aindices) {
        foreach ($aindices as $cindice => $achaves) {
            $otable = mysqli_query($db,"show index from " . $estru[0][0]);
            $aaux = array();
            while ($olinha = mysqli_fetch_row($otable)) {
                if ($olinha[2] == $cindice) {
                    $aaux[] = $olinha[4];
                }
            }
            if (count($achaves) > 0 && count($aaux) != 0 && count($aaux) != count($achaves)) {
                mysqli_query($db,"alter table " . $estru[0][0] . " drop index $cindice");
                $aaux = array();
            }
            for ($j = 0; $j < count($aaux); $j++) {
                if (!($aaux[$j] == $achaves[$j])) {
                    mysqli_query($db,"alter table " . $estru[0][0] . " drop index $cindice");
                    $aaux = array();
                    break;
                }
            }
            if (count($aaux) == 0) {
                $caux = "alter table " . $estru[0][0] . " add index $cindice (";
                for ($j = 0; $j < count($achaves); $j++) {
                    if ($j != 0)
                        $caux.=",";
                    $caux.=$achaves[$j];
                }
                $caux.=")";
                mysqli_query($db,$caux);
            }
        }
    }
}

?>
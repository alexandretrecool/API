<?php

/*  Mudado para os vetores
 * 
 * $lEnviarInativos = false;
  $lApenasEcommerceSim = false;
  $lMuitosItens = 10000;
 */

/* vetolr para filtro de dados a enviar */


$afiltro = array();
// com sub cliente, os dados de prefiltro dos produtos sao respeitados pelo sub
$cliente = 6997;
$afiltro[$cliente]["noenvasteriscos"] = true; 
$afiltro[$cliente]["sub_cli"] = 1; 
$afiltro[$cliente]["timezone"] = 
"America/Asuncion"; $afiltro[$cliente]["horario_trabalho_venda"] = 
array("05:47:00", "23:59:59"); 
$afiltro[$cliente]["horario_trabalho_consulta"] = array("05:47:00", 
"23:59:59"); $afiltro[$cliente]["ips_permitidos"] = 
array("192.168.18.251","192.168.12.1","89.116.51.29","181.1.154.169","192.168.6.99"); 
$afiltro[$cliente]["cliente_zona"] = 1; 
$afiltro[$cliente]["cliente_tipo"] = 1; 
$afiltro[$cliente]["codigo_frete"] = 185076; 
$afiltro[$cliente]["preco_site"] = true; $afiltro[$cliente]["config"] = 1; 
$afiltro[$cliente]["lEnviarInativos"] = false; 
$afiltro[$cliente]["lApenasEcommerceSim"] = false; 
$afiltro[$cliente]["lMuitosItens"] = 10000; 
$afiltro[$cliente]["filtroativo"] = true; $afiltro[$cliente]["grupo"] = 
array(); $afiltro[$cliente]["grupoesp"] = array(); 
$afiltro[$cliente]["subgrupo"] = array(); 
$afiltro[$cliente]["fornecedor"] = array(); $afiltro[$cliente]["marca"] 
= array(); $afiltro[$cliente]["ex_grupo"] = array(); 
$afiltro[$cliente]["ex_grupoesp"] = array(); 
$afiltro[$cliente]["ex_subgrupo"] = array(); 
$afiltro[$cliente]["ex_fornecedor"] = array(); 
$afiltro[$cliente]["ex_marca"] = array(); $afiltro[$cliente]["idioma"] = 
1; $afiltro[$cliente]["naoenviasaldo"] = false; 
$afiltro[$cliente]["naoenviapreco"] = false; 
$afiltro[$cliente]["filtrocecwin"] = true; 
$afiltro[$cliente]["semsaldozero"] = true; 
$afiltro[$cliente]["CommitWeb"] = "http://localhost/cgi-bin/medweb.cgi"; 
$afiltro[$cliente]["grupoenvsaldo"]=array(17,9999);
//grupo,valor do estoque $afiltro[$cliente]["enviakit"] = true; 
$afiltro[$cliente]["pagvis"] = false ;
 $afiltro[$cliente]["preco_site"] = true;
$afiltro[$cliente]["preco_site_min"] = "T";
$afiltro[$ncli]["SemPedidoEmObs"]=true;
//BLOQUEAR OU LIBERAR OPERACOES
//$afiltro[$cliente]["operacao"] = array("12" => false,"81" => false,"82" => false,"83" => true) ;
//REFRESH MINUTOS
//$afiltro[$cliente]["refresh"] = 5 ;

// com sub cliente, os dados de prefiltro dos produtos sao respeitados pelo sub

//$afiltro = array();
// com sub cliente, os dados de prefiltro dos produtos sao respeitados pelo sub
$cliente = 6999;
$afiltro[$cliente]["noenvasteriscos"] = true; 
$afiltro[$cliente]["sub_cli"] = 1; 
$afiltro[$cliente]["timezone"] = 
"America/Asuncion"; $afiltro[$cliente]["horario_trabalho_venda"] = 
array("05:47:00", "23:59:59"); 
$afiltro[$cliente]["horario_trabalho_consulta"] = array("05:47:00", 
"23:59:59"); $afiltro[$cliente]["ips_permitidos"] = 
array("192.168.18.251","192.168.12.1","89.116.51.29","181.1.154.169","192.168.6.99"); 
$afiltro[$cliente]["cliente_zona"] = 1; 
$afiltro[$cliente]["cliente_tipo"] = 1; 
$afiltro[$cliente]["codigo_frete"] = 185076; 
$afiltro[$cliente]["preco_site"] = true;
 $afiltro[$cliente]["config"] = 2; 
$afiltro[$cliente]["lEnviarInativos"] = false; 
$afiltro[$cliente]["lApenasEcommerceSim"] = false; 
$afiltro[$cliente]["lMuitosItens"] = 10000; 
$afiltro[$cliente]["filtroativo"] = true; $afiltro[$cliente]["grupo"] = 
array(); $afiltro[$cliente]["grupoesp"] = array(); 
$afiltro[$cliente]["subgrupo"] = array(); 
$afiltro[$cliente]["fornecedor"] = array(); $afiltro[$cliente]["marca"] 
= array(); $afiltro[$cliente]["ex_grupo"] = array(); 
$afiltro[$cliente]["ex_grupoesp"] = array(); 
$afiltro[$cliente]["ex_subgrupo"] = array(); 
$afiltro[$cliente]["ex_fornecedor"] = array(); 
$afiltro[$cliente]["ex_marca"] = array(); $afiltro[$cliente]["idioma"] = 
1; $afiltro[$cliente]["naoenviasaldo"] = false; 
$afiltro[$cliente]["naoenviapreco"] = false; 
$afiltro[$cliente]["filtrocecwin"] = true; 
$afiltro[$cliente]["semsaldozero"] = true; 
$afiltro[$cliente]["CommitWeb"] = "http://localhost/cgi-bin/medweb.cgi"; 
$afiltro[$cliente]["grupoenvsaldo"]=array(17,9999);
//grupo,valor do estoque $afiltro[$cliente]["enviakit"] = true; 
$afiltro[$cliente]["pagvis"] = false ;
 $afiltro[$cliente]["preco_site"] = true;
$afiltro[$cliente]["preco_site_min"] = "T";
$afiltro[$ncli]["SemPedidoEmObs"]=true;
//BLOQUEAR OU LIBERAR OPERACOES
//$afiltro[$cliente]["operacao"] = array("12" => false,"81" => false,"82" => false,"83" => true) ;
//REFRESH MINUTOS
//$afiltro[$cliente]["refresh"] = 5 ;


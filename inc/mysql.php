<?php
//REFRESH DE DADOS
$REFRESH_SEC=10;
// servidor de dados
$server = "127.0.0.1";
$port = "3306";
$user = "root";
$password = "KoNeCt@3313" ;
$database = "ecommerce";

//servidor de trabalho
$server_trab = "127.0.0.1";
$port_trab = "3306";
$user_trab = "root";
$password_trab = "KoNeCt@3313" ;
$database_trab = "ecommerce";

$cImagem = "./images/konecta.jpg" ;
$cLogoPadrao = "./webcell.gif";
$cImagemLink = "./images/consoft.php";
$nlimitecon = 10 ;
$nlimitemeses = 60 ;
$lOrcamento = true ;

/* SALDOS POR GRUPO DE LOJAS 
$asaldoporloja["cde"]="1,3,5";
 ONDE: "cde" É A CHAVE QUE IRA VOLTAR 
        "1,2,4" o numero das lojas, separadas por vírgula
podem ser criados varios agrupamentos
 */
$asaldoporloja=array();
$asaldoporloja["cde"]="1,3,5";
$asaldoporloja["asu"]="7,9,12";
$asaldoporloja["outras"]="2,4,6,8,10,11";

// commit no servidor de dados
$ccommit="http://192.168.12.100/cgi-bin/mediator";
$ccommit_new="http://192.168.12.100/MedPhp/hscript.php";
$ccommit_web="http://localhost/cgi-bin/medweb.cgi";
$aoperacoes=array(
 "o0" => Array("Operação não reconhecida",TRUE)
,"o1" => Array("Consulta de produtos",true)
,"o2" => Array("Detalhes de Produto",TRUE)
,"o3" => Array("Imagem vinculada a produto",TRUE)
,"o4" => Array("Consulta de produtos por página",TRUE)
,"o5" => Array("Consulta de Lista de preço",TRUE)
,"o7" => Array("Consulta de Kits",TRUE)
,"o10" => Array("Versão da API",TRUE)
,"o11" => Array("Consulta de saldo e preço",TRUE)
,"o12" => Array("Enviar pedido",TRUE)
,"o13" => Array("Consultar pedido",TRUE)
,"o15" => Array("Faturar pedido",TRUE)
,"o16" => Array("Deletar pedido não faturado",TRUE)
,"o22" => Array("Alteracoes",TRUE)
,"o31" => Array("Consultar Pedidos não faturados",TRUE)
,"o71" => Array("Moedas e Câmbios",TRUE)
,"o81" => Array("Inserir Cliente para venda",TRUE)
,"o82" => Array("Alterar Cliente para venda",TRUE)
,"o83" => Array("Consultar Cliente para venda",TRUE)
,"o84" => Array("Inserir Imagem cédula Cliente",TRUE)
,"o85" => Array("Consultar Imagem Cédula Cliente",TRUE)
,"o86" => Array("Consultar Series",TRUE)
,"o87" => Array("Cadastrar cliente Factura",TRUE)
,"o88" => Array("Alterar cliente Factura",TRUE)
,"o89" => Array("Consultar cliente Factura",TRUE)
,"o91" => Array("Consultar Categorias",TRUE)
,"o90" => Array("Consultar Categorias por árvore",TRUE)
,"o92" => Array("Consultar Marcas",TRUE)
,"o93" => Array("Consultar Grupos do CEC",TRUE)
,"o95" => Array("Consultar Sub Grupos do CEC",TRUE)
,"o96" => Array("Consultar Grupos Especiais do CEC",TRUE)
,"o94" => Array("Consultar Imagens em base-64",TRUE)
,"o98" => Array("Download de listas",TRUE)
,"o99" => Array("Consultar itens alterados",TRUE)
,"o101" => Array("Consultar RESTOQ",TRUE)
,"o102" => Array("Gravar RESTOQ",TRUE)
,"o111" => Array("Lista SQL",TRUE)
);


$cChave="\x52\x74\x31\x7A\x30\x59\x33\x4D\x63\x66\x47\x34\x78\x36\x50\x76\x79\x44\x44\x75\x34\x4A\x53\x63\x6B\x71\x66\x48\x69\x39\x65\x79\x34\x30\x76\x56\x32\x50\x70\x37\x4F\x39\x61\x43\x33\x52\x51\x44\x69\x43\x52\x43\x47\x46\x31\x44\x30\x64\x4D\x2F\x39\x36\x38\x68\x42\x79\x44\x35\x35\x77\x57\x5A\x64\x78\x57\x41\x6C\x41\x4D\x42\x78\x66\x38\x73\x73\x32\x71\x78\x58\x59\x76\x79\x6C\x78\x58\x56\x62\x6D\x45\x46\x41\x51\x43\x41\x38\x4B\x77\x76\x4E\x6D\x4C\x4A\x50\x67\x58\x49\x72\x57\x65\x67\x52\x72\x44\x45\x48\x59";

if (file_exists("inc/connect.php")){
    require_once 'inc/connect.php';
}
if (file_exists("connect.php")){
    require_once 'connect.php';
}
/*NAO ESQUECER DE INSTLAR O PHP5-OPENSSL
 * e php5-gd
 * 
 */

/*
 $mail_server="smtp.gmail.com";
 
$mail_ssl="ssl";
$mail_user="webcellbr@gmail.com";
$mail_password="rsk24432595";
$mail_port="465";
$mail_zone="America/Sao_Paulo";
$mail_from="webcellbr@gmail.com";
$mail_name="Sistema WebCell";
$mail_cabecalho="<img src=\"http://www.zambi.com.br/webcell/consoft.jpg\"><BR><h2><b>WebCell - Sistema de vendas On-Line</b><BR>";
$mail_rodape="<p><font color=black size=1>
Essa mensagem é destinada exclusivamente ao seu destinatário e pode conter informações confidenciais,<br> 
protegidas por sigilo profissional ou cuja divulgação seja proibida por lei. <br>
O uso não autorizado de tais informações é proibido e está sujeito às penalidades cabíveis. <br>
<hr>
Este mensaje se dirige exclusivamente a su destinatario y puede contener información que es confidencial <br>
y protegido por un secreto profesional o cuya divulgación esté prohibida por la ley. <br>
El uso no autorizado de esa información está prohibida y sujeta a las sanciones aplicables. <BR>
<hr>
This message is intended exclusively for its addressee and may contain information that is confidential <br>
and protected by a professional privilege or whose disclosure is prohibited by law. <br>
Unauthorized use of such information is prohibited and subject to applicable penalties.</p>";

















































































































































*/
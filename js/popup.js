var foco="";
var pfoco="";
function consultacli(sid,cmodulo){
  
    cnome=document.getElementById("nome");
    xmlhttp=Ajax();
    xmlhttp.open('POST','concli11.php',false);
    xmlhttp.setRequestHeader('Content-type','application/x-www-form-urlencoded');
    
    xmlhttp.send('sid='+sid+'&cli='+cnome.value+'&nome='+cmodulo);
    document.getElementById('cliselect').innerHTML=xmlhttp.responseText ;
/*    document.getElementById('retornar').disabled=true; */
}

function retorna(lret){
    if (!lret){
        if (document.getElementById('codcli').selectedIndex!=-1){
            document.getElementById('retornar').disabled=false;
        }
        return;
    }
    if (document.getElementById('codcli').selectedIndex==-1){
        return;
    }
    document.getElementById(foco).value=document.getElementById('codcli').value;
    document.getElementById(foco).focus();
    document.getElementById(pfoco).focus();
    document.getElementById('popup').style.display='none'; 
    return;
}
    function mostrapop(lmostra){
        if (lmostra){
        document.getElementById("loja").disabled=true;
        document.getElementById("placa").disabled=true;
        document.getElementById("incvei").style.display='none';
        document.getElementById("cliente").disabled=true;
        document.getElementById("concli").style.display='none';
        document.getElementById("inccli").style.display='none';
        document.getElementById("vendedor").disabled=true;
        document.getElementById("pagamento_0").disabled=true;
        document.getElementById("pagamento_1").disabled=true;
        document.getElementById("dias").disabled=true;
        document.getElementById("enviar").disabled=true;
        document.getElementById("limpar").disabled=true;
        document.getElementById("voltar").disabled=true;
            
        }else{
        document.getElementById("loja").disabled=false;
        document.getElementById("placa").disabled=false;
        document.getElementById("incvei").style.display='';
        document.getElementById("cliente").disabled=false;
        document.getElementById("concli").style.display='';
        document.getElementById("inccli").style.display='';
        document.getElementById("vendedor").disabled=false;
        document.getElementById("pagamento_0").disabled=false;
        document.getElementById("pagamento_1").disabled=false;
        document.getElementById("dias").disabled=false;
        document.getElementById("enviar").disabled=false;
        document.getElementById("limpar").disabled=false;
        document.getElementById("voltar").disabled=false;
            
        }
    }
    function incluiplapop(sid,cnome,cfoco,cpfoco){
        mostrapop(true);
        foco=cfoco;
        pfoco=cpfoco;
        document.getElementById('popuppla').style.display = 'block';
        xmlhttp=Ajax();
        xmlhttp.open('POST','incpla.php',false);
        xmlhttp.setRequestHeader('Content-type','application/x-www-form-urlencoded');
                xmlhttp.send('sid='+sid+'&nome='+cnome);
        document.getElementById('popuppla').innerHTML=xmlhttp.responseText ;
    }
    function incluiclipop(sid,cnome,cfoco,cpfoco){
        mostrapop(true);
        foco=cfoco;
        pfoco=cpfoco;
        document.getElementById('popupcli').style.display = 'block';
        xmlhttp=Ajax();
        xmlhttp.open('POST','inccli.php',false);
        xmlhttp.setRequestHeader('Content-type','application/x-www-form-urlencoded');
                xmlhttp.send('sid='+sid+'&nome='+cnome);
        document.getElementById('popupcli').innerHTML=xmlhttp.responseText ;
    }
    function consultapop(sid,cnome,cfoco,cpfoco){
        foco=cfoco;
        pfoco=cpfoco;
        document.getElementById('popup').style.display = 'block';
        xmlhttp=Ajax();
        xmlhttp.open('POST','concli1.php',false);
        xmlhttp.setRequestHeader('Content-type','application/x-www-form-urlencoded');
                xmlhttp.send('sid='+sid+'&nome='+cnome);
        document.getElementById('popup').innerHTML=xmlhttp.responseText ;
    }
    
     function calcuse(){
            xmlhttp=Ajax();
            xmlhttp.open('POST','user.php',false);
            xmlhttp.setRequestHeader('Content-type','application/x-www-form-urlencoded');
            caux='user='+document.getElementById("user").value;
            xmlhttp.send(caux);
            caux=xmlhttp.responseText;
            caux=parseFloat(caux);
            if (caux==0){
                document.getElementById('carousel').style.display='none';
                document.getElementById('nnuser').value="";
                document.getElementById('nuser').innerHTML="";
            }else{
                document.getElementById('carousel').style.display='block';
                document.getElementById('nnuser').innerHTML=caux;
                document.getElementById('nuser').innerHTML=caux;
            }
         
     }
    
function popsen(){
    var caux='<table id="popsent"><tr><th colspan=2>Insira a senha :</th></tr>\
<tr><td class="d">Nome:</td><td class="e">\
    <input type="text" id="user" name="user" size="20" maxlength="20" value=""  \
           onkeypress="return(validaConteudo(event,this,\'uppercase\'))" onFocus="removeCaracs(this,\'uppercase\')" onblur="calcuse();formatCamp(this,\'uppercase\')" \
           onkeyup="saltaCampo(event,this,\'uppercase\',20 )" >  \
    <div id="carousel"><div id="nuser"></div></div>\
</td></tr>\
<tr><td class="d">Senha:</td><td class="e">\
<input type="password" value="" name="pass" size="20"><input id="nnuser" type="hidden" value="" name="nnuser" > \
</table>';
    document.getElementById('popsen').innerHTML=caux;
    document.getElementById('popsen').style.display='block';
}
function alerta(msg,retorno,permanecer,btn){
	if(msg != 'close'){
		if(retorno != ''){
			acao = ""+retorno+"";
		}
		else{
			acao = "alerta('close')";
		}
		if(permanecer != ''){
			if(permanecer == 'close'){
				acaopermanecer = "alerta('close');";
			}
			else{
				acaopermanecer = ""+permanecer+"";
			}
			botaopermanece = "<button id=\"botaopermanecer\" onclick=\""+acaopermanecer+"\" class=\"classbtns\">"+btn+"</button>";
			botaook = 'botaook2';
		}
		else{
			botaopermanece ='';
			botaook = 'botaook';
		}
		$('#block').show();
		
		$("#contentale                                                                                                                                                                                                                 rt").html("<div id=\"alert\" onclick=\"dragdrop('alert','alert');\"><div class=\"topo\"><b>Observa��o</b></div><div class=\"msg\">"+msg+"</div><button id=\""+botaook+"\" onclick=\""+acao+"\" class=\"classbtns\">Ok</button>"+botaopermanece+"</div>");
		$("#alert").fadeIn("normal");
		document.getElementById(''+botaook+'').focus();
	}
	else{
		$("#alert").fadeOut("normal");
		$('#block').hide();
	}
}

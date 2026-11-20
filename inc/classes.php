<?php
class GravaMed {
    protected $acam=array();
    var $ctab="";
    var $cwhe="";
    var $arecno=array();
    
    public function AddCampo($cCampo='',$uValor='',$lEstatico=false,$lMemo=false){
        $this->acam[]=array(strtoupper($cCampo),$uValor,$lEstatico,$lMemo);
        return $this->acam;
   }
   
   public function Grava($cTabela,$cWhere,$lMemoAut=false){
       global $db;
       if ($cTabela) $this->ctab=$cTabela;
       $this->ctab=str_replace(".", "_",strtoupper($this->ctab));
       if (substr($this->ctab, -4,4)=="_DBF"){
           $this->ctab=substr($this->ctab,0,-4);
       }
       if ($lMemoAut){
           $otable=mysqli_query($db,"SELECT COL_NAME FROM CLP_TAB_COLS WHERE TAB_NAME='".$this->ctab."'  and COL_CLP_TYPE=65");
           while ($olinha=  mysqli_fetch_array($otable)){
               for ($i=0;$i<count($this->acam);$i++){
                   if (trim($olinha["COL_NAME"])==$this->acam[$i][0]){
                       $this->acam[$i][3]=true;
                       break 1;
                   }
               }
           }
       }
       if ($cWhere) $this->cwhe=$cWhere;
       $this->arecno=GravaSqlMed($this->ctab, $this->acam, $this->cwhe!='' , $this->cwhe,true);
       return $this->arecno[0];
   }
   
}
class relatorio {

    var $cbuffer = "";
    var $anometab = array();

    /*
     * abrecelula(
     * $cstr,       //string para mostrar
     * $cpos,       //posicao  e,c,d,h,h1
     * $lneg,       //negrito
     * $nspncol,    //colunas a expandir
     * $nspnrow,    //linhas a expandir
     * $cfon,       // nome da fonte 
     * $cfontam,    //tamanho da fonte
     * $cfoncor,    //cor da fonte
     * $chalgn,     //alinhamento horizontal
     * $cvalgn,     //alinhamento vertical
     * $ccelcor,    //cor da célula
     * $ncellgr,    //largura da célula
     * $ncelalt,    //altura da celula
     * $ctit,       // titulo da celula
     * $cdiv,       // nome da div na celula
     * $clnk        // link da celula
     *  )
     */
    var $cstr = "";
    var $cpos = "";
    var $lneg = "";
    var $nspncol = "";
    var $nspnrow = "";
    var $cfon = "";
    var $cfontam = "";
    var $cfoncor = "";
    var $chalgn = "";
    var $cvalgn = "";
    var $ccelcor = "";
    var $ncellgr = "";
    var $ncelalt = "";
    var $ctit = "";
    var $cdiv = "";
    var $clnk = "";
    var $lLinAberta = false;
    var $lCelAberta = false;
    var $ntableaberta = 0;
    var $lalt = false;
    var $lisalt = false;

    function celula() {
        $this->limpa();
    }

    function limpa() {
        $this->cstr = "";
        $this->cpos = "";
        $this->lneg = "";
        $this->nspncol = "";
        $this->nspnrow = "";
        $this->cfon = "";
        $this->cfontam = "";
        $this->cfoncor = "";
        $this->chalgn = "";
        $this->cvalgn = "";
        $this->ccelcor = "";
        $this->ncellgr = "";
        $this->ncelalt = "";
        $this->ctit = "";
        $this->cdiv = "";
        $this->clnk = "";
    }

    function abretabela($lNaoLimpa, $lSemCss=false, $cLargura='', $cnome='', $lalt='', $ccss='', $lcssadiciona=false) {
        $this->lalt = $lalt;
        $this->ntableaberta+=1;
        if (!$cnome) {
            $cnome = "_TAB_" . $this->ntableaberta;
        }
        $this->anometab[] = "";
        $this->anometab[$this->ntableaberta - 1] = $cnome;
        if (!$lNaoLimpa)
            $this->cbuffer = "";
        $cLargura = $cLargura ? $cLargura : "100%";
        if (!$lSemCss) {
            $this->cbuffer .= "<style type=\"text/css\">";
            if (!$ccss || $lcssadiciona) {
                $this->cbuffer.="
                #" . $this->anometab[$this->ntableaberta - 1] . " {
                font-family:\"Trebuchet MS\", Arial, Helvetica, sans-serif; 
                width: $cLargura ;
                border-collapse:collapse;
                }
                #" . $this->anometab[$this->ntableaberta - 1] . " td, #" . $this->anometab[$this->ntableaberta - 1] . " th {
                font-size:".(mobile()?"1.4":"0.7")."em;
                border:1px solid #98bf21;
                padding:3px 3px 2px 3px;
                }
                #" . $this->anometab[$this->ntableaberta - 1] . " th, #" . $this->anometab[$this->ntableaberta - 1] . " td.h {
                font-size:".(mobile()?"1.6":"0.9")."em;
                text-align:center;
                padding-top:4px;
                padding-bottom:4px;
                background-color:#A7C942;
                color:#ffffff;
                }
                #" . $this->anometab[$this->ntableaberta - 1] . " tr.alt td{
                font-size:".(mobile()?"1.4":"0.7")."em;
                color:#000000;
                background-color:#EAF2D3;
                }
                #" . $this->anometab[$this->ntableaberta - 1] . " td.e {
                font-size:".(mobile()?"1.4":"0.7")."em;
                text-align:left;
                background-color:#EAF2D3;
                }
                #" . $this->anometab[$this->ntableaberta - 1] . " td.d {
                font-size:".(mobile()?"1.4":"0.7")."em;
                text-align:right;
                background-color:#EAF2D3;
                }
                #" . $this->anometab[$this->ntableaberta - 1] . " td.c {
                font-size:".(mobile()?"1.4":"0.7")."em;
                text-align:center;
                background-color:#EAF2D3;
                }";
            }
            if ($ccss)
                $this->cbuffer.=$ccss;

            $this->cbuffer.="</style>";
        }
        $this->cbuffer.="<table id=\"" . $this->anometab[$this->ntableaberta - 1] . "\">";
    }

    function fechatabela() {
        $this->fechacelula();
        $this->fechalinha();
        if ($this->ntableaberta<=0) return;
        $this->cbuffer.="</table>";
        $this->ntableaberta-=1;
    }

    function abrelinha($llimpa=false) {
        if ($llimpa)
            $this->limpa();
        $this->fechalinha();
        
        
        $this->lLinAberta = true;
        $this->cbuffer .= "\r<tr";
        if ($this->lalt && $this->lisalt) {
            $this->cbuffer.=" class=\"alt\"";
        }
        $this->cbuffer.=">";
        $this->lisalt = !$this->lisalt;
    }

    function fechalinha() {
        $this->fechacelula();
        if ($this->lLinAberta)
            $this->cbuffer.="\r</tr>";
        $this->lLinAberta = false;
    }

    function imprime($cstr, $lneg=false, $cfon='', $cfontam='', $cfoncor='') {
        if (!$this->lCelAberta)
            $this->abrecelula();
        if ($cfon || $cfoncor || $cfontam) {
            $this->cbuffer.="<spam style=\"";
            $this->cbuffer.=$cfon ? " font-family:$cfon;" : "";
            $this->cbuffer.=$cfontam ? " font-size:$cfontam;" : "";
            $this->cbuffer.=$cfoncor ? " color:$cfoncor;" : "";
            $this->cbuffer.="\">";
        }
        $this->cbuffer.=($lneg ? "<b>" : "") . $cstr . ($lneg ? "</b>" : "");
        if ($cfon || $cfoncor || $cfontam) {
            $this->cbuffer.="</spam>";
        }
    }

    function abrecelula($cstr='', $cpos='', $lneg=false, $nspncol='', $nspnrow='', $cfon='', $cfontam='', $cfoncor='', $chalgn='', $cvalgn='', $ccelcor='', $ncellgr='', $ncelalt='', $ctit='', $cdiv='', $clnk='') {
        $this->fechacelula();
        if (!$this->lLinAberta)
            $this->abrelinha();

        $this->lCelAberta = true;
        $this->cbuffer.="<td";

        //titulo
        $caux = $ctit ? $ctit : $this->ctit;
        if ($caux)
            $this->cbuffer.=" title=\"" . $caux . "\" ";

        //alinhamento vertical
        $caux = strtoupper($cvalgn ? $cvalgn : $this->cvalgn);
        if ($caux) {
            if (strtoupper($caux) == "T")
                $this->cbuffer.=" valign=\"top\" ";
            if (strtoupper($caux) == "C")
                $this->cbuffer.=" valign=\"middle\" ";
            if (strtoupper($caux) == "M")
                $this->cbuffer.=" valign=\"middle\" ";
            if (strtoupper($caux) == "B")
                $this->cbuffer.=" valign=\"bottom\" ";
            if (strtoupper($caux) == "L")
                $this->cbuffer.=" valign=\"baseline\" ";
        }
        // div
        $caux = $cdiv ? $cdiv : $this->cdiv;
        if ($caux)
            $this->cbuffer.=" div=\"$caux\"";

        //classe
        $this->cbuffer.=" class=\"";

        $caux = strtoupper($cpos ? $cpos : $this->cpos);
        if ($caux == "D") {
            $this->cbuffer.="d";
            $calign = "right";
        } elseif ($caux == "C") {
            $this->cbuffer.="c";
            $calign = "center";
        } elseif ($caux == "H") {
            $this->cbuffer.="h";
        } elseif ($caux == "H1" || $caux == "H2") {
            $this->cbuffer.="h";
        } else {
            $this->cbuffer.="e";
        }
        $this->cbuffer.="\" ";

        // alinhamento horizonta
        $caux = strtoupper($chalgn ? $chalgn : $this->chalgn);
        if ($caux) {
            $caux = strtoupper(substr($caux, 0, 1));
            if ($caux == "E" || $caux == "L") {
                $this->cbuffer.=" style=\"text-align: left\" ";
            } elseif ($caux == "R" || $caux == "D") {
                $this->cbuffer.=" style=\"text-align: right\" ";
            } else {
                $this->cbuffer.=" style=\"text-align: center\" ";
            }
        }

        // spam de coluna
        $caux = $nspncol ? $nspncol : $this->nspncol;
        if ($caux > 1)
            $this->cbuffer.=" colspan=\"" . $caux . "\" ";
        // spam de linha
        $caux = $nspnrow ? $nspnrow : $this->nspnrow;
        if ($caux > 1)
            $this->cbuffer.=" rowspan=\"" . $caux . "\" ";
        // largura da celula
        $caux = $ncellgr ? $ncellgr : $this->ncellgr;
        if ($caux)
            $this->cbuffer.=" width=\"" . $caux . "\" ";
        $caux = $ncelalt ? $ncelalt : $this->ncelalt;
        //altura da celula
        if ($caux)
            $this->cbuffer.=" height=\"" . $caux . "\" ";

        // cor de fundo
        $caux = $ccelcor ? $ccelcor : $this->ccelcor;
        if ($caux) {
            $this->cbuffer.=" style=\"background-color: " . $caux . ";\"";
        }

        //fecha Celula
        $this->cbuffer.=">";



        /*
          if (strtoupper($this->pos)=="H2" ){
          $this->tamfon=2;
          $this->cor="#cfcfcf";
          }
         */
        $caux = $cfontam ? $cfontam : $this->cfontam;
        $caux1 = $cfon ? $cfon : $this->cfon;
        $caux2 = $cfoncor ? $cfoncor : $this->cfoncor;
        if ($caux || $caux1 || $caux2) {
            $this->cbuffer.="<font ";
            if ($caux)
                $this->cbuffer.=" size=\"" . $caux . "\" ";
            if ($caux2)
                $this->cbuffer.=" color=\"" . $caux2 . "\" ";
            if ($caux1)
                $this->cbuffer.=" face=\"" . $caux1 . "\" ";
            $this->cbuffer.=">";
        }
//	$this->cbuffer.=$caux.">";
        $caux = $clnk ? $clnk : $this->clnk;
        if ($caux) {
            $this->cbuffer.="<a href='" . $caux . "'>";
        }

        $caux = $lneg ? $lneg : $this->lneg;
        if ($caux)
            $this->cbuffer.="<b>";
        if ($cstr)
            $this->cbuffer.=$cstr;
    }

    function fechacelula() {
        if ($this->lCelAberta)
            $this->cbuffer.="</td>";
        $this->lCelAberta = false;
    }

    function linhadiv($nspan) {
        $this->abrelinha(true);
        $this->pos = "h2";
        if ($nspan)
            $this->colspn = $nspan;
        $this->abrecel(".");
        $this->fechalinha();
    }

    function mostra($llimpa=false, $lretornacar=false) {
        $cret = "";
        $this->fechacelula();
        $this->fechalinha();
        while ($this->ntableaberta) {
            $this->cbuffer.=$this->fechatabela();
        }
        if ($lretornacar) {
            $cret = $this->cbuffer;
        } else {
            echo $this->cbuffer;
        }
        $this->cbuffer = "";
        if ($llimpa)
            $this->limpa();
        return $cret;
    }

}

class celula {

    var $cbuffer = "";
    var $str = "";
    var $pos = "";
    var $neg = "";
    var $tamfon = "";
    var $colspn = "";
    var $rowspn = "";
    var $cor = "";
    var $corfun = "";
    var $valign = "";
    var $align = "";
    var $lgr = "";
    var $alt = "";
    var $title = "";
    var $lLinAberta = false;
    var $lCelAberta = false;
    var $ntableaberta = 0;
    var $link = "";

    function celulaLimp() {
        $this->limpa();
    }

    function limpa() {
        $this->str = "";
        $this->pos = "";
        $this->neg = "";
        $this->tamfon = "";
        $this->colspn = "";
        $this->rowspn = "";
        $this->cor = "";
        $this->corfun = "";
        $this->align = "";
        $this->valign = "";
        $this->lgr = "";
        $this->alt = "";
        $this->title = "";
        $this->link = "";
    }

    function linhadiv($nspan) {
        $this->abrelinha(true);
        $this->pos = "h2";
        if ($nspan)
            $this->colspn = $nspan;
        $this->abrecel(".");
        $this->fechalinha();
    }

    function abretable($nborder, $npad, $nspace, $cwidth) {
        $this->cbuffer.=htmltableon($nborder, $npad, $nspace, $cwidth);
        $this->ntableaberta = $this->ntableaberta + 1;
    }

    function fechatable() {
        $this->cbuffer.=htmltableoff();
        $this->ntableaberta = $this->ntableaberta - 1;
    }

    function abrelin($llimpa,$cdiv) {
        $this->abrelinha($llimpa,$cdiv);
    }

    function abrelinha($llimpa=false) {
        if ($llimpa)
            $this->limpa();
        $this->fechalin();
        $this->lLinAberta = true;
        $this->cbuffer .= "\r<tr>";
    }

    function abrecel($cstr='') {
        $this->abrecelula($cstr);
    }

    function abrecelula($cstr='') {
        $this->fechacel();
        if (!$this->lLinAberta)
            $this->abrelinha();

        $this->lCelAberta = true;
        $this->cbuffer.="\r  <td  ";
        if ($this->title)
            $this->cbuffer.=" title=\"" . $this->title . "\" ";

        if ($this->valign) {
            if (strtoupper($this->valign) == "T")
                $this->cbuffer.=" valign=\"top\" ";
            if (strtoupper($this->valign) == "C")
                $this->cbuffer.=" valign=\"middle\" ";
            if (strtoupper($this->valign) == "M")
                $this->cbuffer.=" valign=\"middle\" ";
            if (strtoupper($this->valign) == "B")
                $this->cbuffer.=" valign=\"bottom\" ";
            if (strtoupper($this->valign) == "L")
                $this->cbuffer.=" valign=\"baseline\" ";
        }
        $this->cbuffer.=" class=\"";
        $calign = "";
        if (strtoupper($this->pos) == "D") {
            $this->cbuffer.="right";
            $calign = "right";
        } elseif (strtoupper($this->pos) == "C") {
            $this->cbuffer.="cent";
            $calign = "center";
        } elseif (strtoupper($this->pos) == "H") {
            $this->cbuffer.="header";
            $calign = "center";
        } elseif (strtoupper($this->pos) == "H1" || strtoupper($this->pos) == "H2") {
            $this->cbuffer.="headers";
            $calign = "center";
        } else {
            $this->cbuffer.="default";
            $calign = "left";
        }
        $this->cbuffer.="\" ";
        if ($this->align) {
            $this->align = strtoupper(substr($this->align, 0, 1));
            if ($this->align == "E" || $this->align == "L") {
                $calign = "left";
            } elseif ($this->align == "R" || $this->align == "D") {
                $calign = "right";
            } else {
                $calign = "center";
            }
        }
        $caux = "<p align=\"$calign\" ";

        if ($this->colspn > 1)
            $this->cbuffer.=" colspan=\"" . $this->colspn . "\" ";
        if ($this->rowspn > 1)
            $this->cbuffer.=" rowspan=\"" . $this->rowspn . "\" ";
        if ($this->lgr)
            $this->cbuffer.=" width=\"" . $this->lgr . "\" ";
        if ($this->alt)
            $this->cbuffer.=" height=\"" . $this->alt . "\" ";
        $this->cbuffer.=">";


        /*
          $this->cbuffer.=$caux."\">";

          if ($this->corfun){
          $this->cbuffer.="<p style=\"background-color: ".$this->corfun.";\">";
          }
          if ($this->tamfon || $this->cor){
          $this->cbuffer.="<font ";
          if ($this->tamfon) $this->cbuffer.=" size=\"".$this->tamfon."\" ";
          if ($this->cor)    $this->cbuffer.=" color=\"".$this->cor."\" ";
          $this->cbuffer.=">";
          }
          if ($this->link){
          $this->cbuffer.="<a href='".$this->link."'>";
          }

          if (strtoupper($this->pos)=="H2" )
          $this->cbuffer.="<p style=\"font-size: 2pt  \"><font color=\"#cfcfcf\">";

          if ($this->neg) $this->cbuffer.="<b>";
          if ($cstr) $this->cbuffer.=$cstr;
         */

        if ($this->corfun) {
            $caux.=" style=\"background-color: " . $this->corfun . ";\"";
        }
        if (strtoupper($this->pos) == "H2") {
            $this->tamfon = 2;
//            $this->cor = "#cfcfcf";
        }

        $this->cbuffer.=$caux . ">";
        if ($this->tamfon || $this->cor) {
            $this->cbuffer.="<font ";
            if ($this->tamfon)
                $this->cbuffer.=" size=\"" . $this->tamfon . "\" ";
            if ($this->cor)
                $this->cbuffer.=" color=\"" . $this->cor . "\" ";
            $this->cbuffer.=">";
        }
//	$this->cbuffer.=$caux.">";
        if ($this->link) {
            $this->cbuffer.="<a href='" . $this->link . "'>";
        }

        if ($this->neg)
            $this->cbuffer.="<b>";
        if ($cstr)
            $this->cbuffer.=$cstr;
    }

    function imp($cstr) {
        $this->imprime($cstr);
    }

    function impcel($cstr) {
        $this->imprime($cstr);
    }

    function imprime($cstr) {
        if (!$this->lCelAberta)
            $this->abrecel();
        $this->cbuffer.=$cstr;
    }

    function fechacel() {
        $this->fechacelula();
    }

    function fechacelula() {
        if ($this->lCelAberta)
            $this->cbuffer.="\r  </td>";
        $this->lCelAberta = false;
    }

    function fechalin() {
        $this->fechalinha();
    }

    function fechalinha() {
        $this->fechacel();
        if ($this->lLinAberta)
            $this->cbuffer.="\r</tr>";
        $this->lLinAberta = false;
    }

    function mostra($llimpa=false, $lretornacar=false) {
        $cret = "";
        $this->fechalin();
        while ($this->ntableaberta) {
            $this->cbuffer.=htmltableoff();
            $this->ntableaberta = $this->ntableaberta - 1;
        }
        if ($lretornacar) {
            $cret = $this->cbuffer;
        } else {
            echo $this->cbuffer;
        }
        $this->cbuffer = "";
        if ($llimpa)
            $this->limpa();
        return $cret;
    }

}
?>
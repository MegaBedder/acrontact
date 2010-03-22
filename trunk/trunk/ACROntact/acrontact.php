<?php
/*
 *
   _____      .___.__           ____  __.     .__
  /  _  \   __| _/|__|_______  |    |/ _|__ __|  |__   ____
 /  /_\  \ / __ | |  |\_  __ \ |      < |  |  \  |  \ /    \
/    |    | /_/ | |  | |  | \/ |    |  \|  |  /   Y  \   |  \
\____|__  |____ | |__| |__|    |____|__ \____/|___|  /___|  /
        \/     \/                      \/          \/     \/

Adir Kuhn - adirkuhn@gmail.com

v.0.01b
 *
 */

include_once("erros.php");


//Lista com os serviços disponiveis neste script.
$arr_servicos = array(
		//Serviço de email
		'Gmail',

		//Redes Sociais
		'Orkut'
	);

#####################
### Classe geral. ###
#####################
class acrontact {
	####################
	### Conf. Padrão ###
	####################
		private $mostra_erros = true; //'true' para mostrar msg de erro ou 'false' para não

		private $ch;
		private $cookiearr;
		private $cookie;
		private $charset;


		public $contatos = array();
		public $login;
		public $senha;

	######################################################################################################
	### Função para retornar os contatos, definir o 'id' do serviço, que a classe se vira com o resto. ###
	######################################################################################################
	public function pegaContatos($servico = null) {
		global $arr_servicos;
		//Verifica se o serviço esta setado.
		if (!isset($servico) || in_array($servico, $arr_servicos) ) {
			$this->mostra_erro(0);
		}

		//Chama função
		switch(strtolower($servico)) {
			case 'gmail': //Gmail
				$this->pegaContatosGmail();
				break;

			case 'orkut': //Orkut
				$this->pegaContatosOrkut();
				break;

			default:
				$this->mostra_erro(1);
		}
	}

	####################################################################
	### Função para mostrar mensagens de erro, e possiveis soluções. ###
	####################################################################
	private function mostra_erro($erro_id) {
		if ($this->mostra_erros) {
			global $msg_erros;
			echo $msg_erros[$erro_id]['ERR'] . "<br/>" . $msg_erros[$erro_id]['SOL'];
		}
	}


	####################################################
	### Grava cookies das paginas chamadas pelo curl ###
	####################################################
	function gravaCookies($ch, $html) {
		$cookiearr = array();

		//Pega o charset da pagina requisitada, variavel usada no gmail.
		if (preg_match("/Content-Type: text\\/csv; charset=([^\s;$]+)/", $html, $matches)) {
			$this->charset = $matches[1];
		}

		//trata os set-cookie do cabeçalho
		if(!strncmp($html, "Set-Cookie:", 11)) {
			$cookiestr = trim(substr($html, 11, -1));
			$cookie = explode(';', $cookiestr);
			foreach($cookie as $k => $v) {
				if(strpos($v, "=") !== false) {
					$cookie = explode('=', $v, 2);
					$this->cookiearr[trim($cookie[0])] = trim($cookie[1]);
				}
				else {
					$this->cookiearr[trim($v)] = "";
				}
			}
		}

		//seta cookie no curl
		$this->cookie = "";
		if(!empty($this->cookiearr)) {
			foreach ($this->cookiearr as $key=>$value) {
				$this->cookie .= "$key=$value; ";
			}
			//curl_setopt($this->ch, CURLOPT_COOKIE, $this->cookie);
			curl_setopt($this->ch ,CURLOPT_HTTPHEADER, array (
				"Cookie: {$this->cookie}",
			));
		}
		return strlen($html);
	}

	########################################
	### Importador de contatos do gmail. ###
	########################################
	private function pegaContatosGmail() {
		//Carrega pagina de login
		$this->ch = curl_init();
		curl_setopt($this->ch, CURLOPT_URL, "https://www.google.com/accounts/ServiceLoginAuth?service=mail");
		curl_setopt($this->ch, CURLOPT_REFERER, "");
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, array($this, 'gravaCookies'));
		$html = curl_exec($this->ch);

		//pega os campos hidden para fazer o login
		preg_match_all('/<input type="hidden"[^>]*name\="([^"]+)"[^>]*value\="([^"]*)"[^>]*>/si', $html, $matches);
		$values = $matches[2];
		$params = "";
		foreach ($matches[1] as $k => $name) {
			$params .= "$name=" . urlencode($values[$k]) . "&";
		}

		//encoda o login e senha
		$login = urlencode($this->login);
		$senha = urlencode($this->senha);

		//Login no google
		$action = "https://www.google.com/accounts/ServiceLoginAuth?service=mail";
		curl_setopt($this->ch, CURLOPT_URL, $action);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($this->ch, CURLOPT_POST, 1);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params ."Email=$login&Passwd=$senha&PersistentCookie=");
		$html = curl_exec($this->ch);

		//Verifica se está logado.
		if (!isset($this->cookiearr['GX']) && (!isset($this->cookiearr['LSID']) || $this->cookiearr['LSID'] == "EXPIRED")) {
			die($this->mostra_erro(2));
		}

		//Carrega url com lista de contatos (csv)
		curl_setopt($this->ch, CURLOPT_URL, "http://mail.google.com/mail/contacts/data/export?exportType=ALL&groupToExport=&out=GMAIL_CSV");
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
		$html = curl_exec($this->ch);

		//converte charset para utf-8
		$html = iconv($this->charset, 'utf-8', $html);

		//cria array com cada linha
		$csvrows = explode("\n", $html);
		array_shift($csvrows);

		foreach ($csvrows as $k=>$v) {
			if (preg_match('/^((?:"[^"]*")|(?:[^,]*)).*?([^,@]+@[^,]+)/', $v, $matches))
			{
				$this->contatos[$k]['nome'] = trim( ( trim($matches[1] )=="" ) ? current(explode("@",$matches[2])) : $matches[1] , '" ');
				$this->contatos[$k]['email'] = trim( $matches[2] );
			}
		}

		return 0;
	}



	########################################
	### Importador de contatos do orkut. ###
	########################################
	private function pegaContatosOrkut() {

		$pagina_login = "https://www.google.com/accounts/ServiceLogin?service=orkut";

		$this->ch = curl_init();
		curl_setopt($this->ch, CURLOPT_URL, $pagina_login);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, array($this, 'gravaCookies'));

		$html = curl_exec($this->ch);

		preg_match_all('/<input type="hidden"[^>]*name\="([^"]+)"[^>]*value\="([^"]*)"[^>]*>/si', $html, $matches);
		$extra_post = "&";
		foreach ($matches[1] as $k => $name) {
			$extra_post .= "{$name}=" . urlencode($matches[2][$k]) . "&";
		}

		preg_match_all('/<input type="hidden"[^>]*name\="([^"]+)"[^>]*value\="([^"]*)"[^>]*>/si', $html, $matches);

		preg_match('/action="([^"]+)"/', $html, $matches);
		$pagina_login = $matches[1];

		//pega login e senha
		$login = urlencode($this->login);
		$senha = urlencode($this->senha);

		//submete o form
		curl_setopt($this->ch, CURLOPT_URL, $pagina_login);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($this->ch, CURLOPT_POST, 1);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, "Email={$login}&Passwd={$senha}" . $extra_post);
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($this->ch, CURLOPT_HEADER, TRUE);
		$html = curl_exec($this->ch);

		//grava cookies orkut
		$url = "http://www.orkut.com/";
		$rurl = "https://www.google.com/accounts/ManageAccount";

		curl_setopt($this->ch, CURLOPT_URL, $url);
		curl_setopt($this->ch, CURLOPT_REFERER, $rurl);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($this->ch, CURLOPT_HEADER, TRUE);
		curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, array($this, 'gravaCookies'));

		$html = curl_exec($this->ch);
		$info = curl_getinfo($this->ch);

		preg_match('/auth=([a-zA-Z0-9_\/\+\-]+)/ims', $html, $matches);
		//echo urldecode($matches[1]);
		//echo $matches[1]."<hr>";
		$url = "http://www.orkut.com/RedirLogin?msg=0&auth=" . $matches[1];

		curl_setopt($this->ch, CURLOPT_URL, $url);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($this->ch, CURLOPT_HEADER, TRUE);
		curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, array($this, 'gravaCookies'));

		$html = curl_exec($this->ch);

		//Verifica se está logado.
		if (!isset($this->cookiearr['GX']) && (!isset($this->cookiearr['LSID']) || $this->cookiearr['LSID'] == "EXPIRED")) {
			die($this->mostra_erro(2));
		}

		//pega contatos

		//regex.
		$regex_contatos_div = '/<div id="f\d+">(.*?)(?:<div class="listdivi">|<\/form>)/ims';
		$regex_contatos_nome = '/<h3\s+class="smller".*?>\s*<a\s+href="\/Main#Profile\?uid=\d+".*?>(.*)<\/a>/ims';
		$regex_contatos_email = '/onclick="_editUser\(\'[^\']*\',\s*\'([^\']*)\'/ims';
		$regex_contatos_foto = '/<img\s+src="(.*)"\s+class="listimg"\s+>/';
		$regex_contatos_num_ult_pag = '/<a\s+href="\/ShowFriends\.aspx\?show=all&pno=(\d+)"/';

		//numero de pagina e url
		$ult_pag = -1; //flag -1 para achar a ultima pagina da lista da contatos
		$num_pag = 1; //primeira pagia da lista de contatos
		$arr_key = 0;
		do {

			$url = "http://www.orkut.com.br/ShowFriends.aspx?show=all&pno=" . $num_pag;
			//carrega pagina
			curl_setopt($this->ch, CURLOPT_URL, $url);
			curl_setopt($this->ch, CURLOPT_HEADER, TRUE);
			curl_setopt($this->ch ,CURLOPT_HTTPHEADER, array (
				"Accept-Encoding: gzip,deflate",
				"Cookie: __utma=254116750.598064938.1268082709.1268082709.1268082709.1; __utmz=254116750.1268082709.1.1.utmccn=(direct)|utmcsr=(direct)|utmcmd=(none); OGC={$this->cookiearr['OGC']}; orkut_state={$this->cookiearr['orkut_state']}; S={$this->cookiearr['S']}; frame=; TZ=180"
			));
			curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, array($this, 'gravaCookies'));
			$html = curl_exec($this->ch);
			//

			//pega ultima pagina
			if ($ult_pag == -1) {
				preg_match_all($regex_contatos_num_ult_pag, $html, $matches, PREG_PATTERN_ORDER);
				if (empty($matches[1])) {
					$ult_pag = 1;
				}
				else {
					$ult_pag = array_pop($matches[1]);
				}
			}

			//filtra contatos;
			preg_match_all($regex_contatos_div, $html, $matches, PREG_PATTERN_ORDER);
			foreach ($matches[0] as $k => $v) {

				//nome e email
				preg_match($regex_contatos_nome, $v, $m1);
				preg_match($regex_contatos_email, $v, $m2);
				if(empty($m1[1]) || empty($m2[1])) {
					continue;
				}
				$this->contatos[$arr_key]['nome'] = $m1[1];
				$this->contatos[$arr_key]['email'] = $m2[1];

				//foto
				preg_match($regex_contatos_foto, $v, $m3);
				$this->contatos[$arr_key]['foto'] = empty($m3[1])?'':$m3[1];

				unset($m1,$m2,$m3);
				$arr_key++;
			}

			$num_pag++;
		} while ($num_pag <= $ult_pag);

		return 0;
	}
}
?>

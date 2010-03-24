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

v.0.02b
 *
 */

include_once("erros.php");

//Lista com os serviços disponiveis neste script.
$arr_servicos = array(
		//Serviço de email
		'Gmail',
		'Hotmail',

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

	########################################################################################################
	### Função para retornar os contatos, definir o 'nome' do serviço, que a classe se vira com o resto. ###
	########################################################################################################
	public function pegaContatos($servico = null) {
		global $arr_servicos;
		//Verifica se o serviço esta setado.
		if (!isset($servico)) {
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

			case 'hotmail': //Hotmail / Live
				$this->pegaContatosHotmail();
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
	private function gravaCookies($ch, $html) {
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
		require_once("servicos/gmail.php");
	}

	########################################
	### Importador de contatos do orkut. ###
	########################################
	private function pegaContatosOrkut() {
		require_once("servicos/orkut.php");
	}

	###################################
	### Importa contatos do Hotmail ###
	###################################
	function pegaContatosHotmail() {
		require_once("servicos/hotmail.php");
	}

}
?>

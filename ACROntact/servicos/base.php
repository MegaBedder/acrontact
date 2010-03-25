<?
require_once("erros.php");

class acrontactBase {

	//Configuração
	public $mostra_erros = true;
	public $debug = false;

	protected $ch;
	protected $cookiearr;
	protected $cookie ;
	protected $charset;
	protected $logado = false;

	public $contatos = array();
	public $login;
	public $senha;

	####################################################################
	### Função para mostrar mensagens de erro, e possiveis soluções. ###
	####################################################################
	protected function mostra_erro($erro_id) {
		if ($this->mostra_erros) {
			global $msg_erros;
			echo $msg_erros[$erro_id]['ERR'] . "<br/>" . $msg_erros[$erro_id]['SOL'];
		}
	}

	####################################################
	### Grava cookies das paginas chamadas pelo curl ###
	####################################################
	protected function gravaCookies($ch, $html) {
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
				"Cookie: {$this->cookie}"
			));
		}
		return strlen($html);
	}

	######################################
	### Pega campos hidden das paginas ###
	######################################
	protected function pegaCamposHidden($html) {
		//passar o html da pagina para filtrar os campos
		preg_match_all('/<input type="hidden"[^>]*name\="([^"]+)"[^>]*value\="([^"]*)"[^>]*>/si', $html, $matches);
		$values = $matches[2];
		$params = "";
		foreach ($matches[1] as $k => $name) {
			$params .= "$name=" . urlencode($values[$k]) . "&";
		}
		return $params;
	}

	#############
	### Debug ###
	#############
	protected function debug() {
		echo "<pre>";
		print_r($this->contatos);
		print_r($this->cookiearr);
		echo "</pre>";
	}

	###############################
	### verifica se esta logado ###
	###############################
	public function logado() {
		return $this->logado;
	}

	##############
	### logoff ###
	##############
	protected function logoff(){
		if($this->debug)
			$this->debug();
		unset($this->cookie, $this->cookiearr, $this->ch);
		return true;
	}
}

?>

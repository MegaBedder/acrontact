<?
class acrontactYahoomail extends acrontactBase {

	#################
	### faz login ###
	#################
	private function login() {
		//carrega pagina de login
		$this->ch = curl_init();
		curl_setopt($this->ch, CURLOPT_URL, "https://login.yahoo.com/");
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, array($this, 'gravaCookies'));
		$html = curl_exec($this->ch);

		//pega campos hidden para fazer o login
		$extrapost = $this->pegaCamposHidden($html);
		$extrapost .= "&";

		//pega login e senha
		$login = urlencode($this->login);
		$senha = urlencode($this->senha);

		//pega action do form de login
		preg_match('/action="([^"]+)"/', $html, $matches);
		$pagina_login_action = $matches[1];

		//submete o form
		curl_setopt($this->ch, CURLOPT_URL, $pagina_login_action);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($this->ch, CURLOPT_POST, 1);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $extrapost . "&login={$login}&passwd={$senha}");
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
		$html = curl_exec($this->ch);

		//verifica se esta logado
		if (!isset($this->cookiearr['Y']) && !isset($this->cookiearr['SSL'])) {
			$this->mostra_erro(2);
			$this->logado = false;
			return false;
		}
		else {
			$this->logado = true;
			return true;
		}
	}

	#####################
	### pega contatos ###
	#####################
	public function pegaContatos() {
		$this->contatos = array();
		//faz login
		$this->login();
		if (!$this->logado)
			return 0;

		curl_setopt($this->ch, CURLOPT_URL, "http://address.mail.yahoo.com/?_src=&VPC=tools_export");
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
		$html = curl_exec($this->ch);

		$extrapost = $this->pegaCamposHidden($html);

		curl_setopt($this->ch, CURLOPT_URL, "http://address.mail.yahoo.com/?_src=&VPC=tools_export");
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($this->ch, CURLOPT_POST, 1);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $extrapost . "&submit[action_display]=Display for Printing&VPC=print&field[style]=detailed&field[allc]=1&field[catid]=0");
		$html = curl_exec($this->ch);

		//echo str_replace(array("<", ">"), array("&lt;", "&gt;"), $html);
		//filtra contatos
		$html = str_replace(array('  ','	',PHP_EOL, "\n", "\r\n"), array("", "", "", "", ""), $html);
		//preg_match_all('/\<tr\s+class="phead"\>(.+)/ims', $html, $matches, PREG_PATTERN_ORDER);
		$matches = explode('<tr class="phead">', $html);

		foreach($matches as $k => $v) {
			if (strpos($v, "<b>") !== false && strpos($v, "@")) {
				$nome = "";
				preg_match('/\<b\>(.+)\<\/b\>/ims', $v, $match);
				if(!empty($match)) {
					$nome = trim(strip_tags($match[1]));
				}

				//email
				preg_match('/\<div\>([a-z0-9\._-]+@+[a-z0-9\._-]+\.+[a-z]{2,3})\<\/div\>/ims', $v, $match);
				if(empty($match)) {
					continue;
				}
				$email = trim(strip_tags($match[1]));

				$nome = (empty($nome)? current(explode("@", $email)):$nome);

				$this->contatos[] = array('nome'=>$nome, 'email'=>$email);

				unset($nome, $email);
			}
		}
		//logoff
		$this->logoff();

		return count($this->contatos);
	}

}
?>

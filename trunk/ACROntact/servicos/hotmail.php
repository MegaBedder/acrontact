<?
class acrontactHotmail extends acrontactBase {

	#################
	### faz login ###
	#################
	private function login() {
		//carrega pagina de login
		$this->ch = curl_init();
		curl_setopt($this->ch, CURLOPT_URL, "http://login.live.com/");
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
		curl_setopt($this->ch, CURLOPT_HEADER, TRUE);
		$html = curl_exec($this->ch);

		//verifica se esta logado
		if (!isset($this->cookiearr['PPAuth']) && !isset($this->cookiearr['MSPAuth'])) {
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

		//abre pagina compor email
		$n = rand(1,200000);
		curl_setopt($this->ch, CURLOPT_URL, "http://bl132w.blu132.mail.live.com/mail/EditMessageLight.aspx?n=".$n);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
		$html = curl_exec($this->ch);

		//verifica se foi setado cookie 'mt' para pegar contatos
		if(!isset($this->cookiearr['mt'])) {
			die($this->mostra_erro(3));
		}

		//pega lista de contatos
		curl_setopt($this->ch, CURLOPT_URL, "http://bl132w.blu132.mail.live.com/mail/ContactList.aspx?n=".$n."&mt=".$this->cookiearr['mt']);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
		$html = curl_exec($this->ch);

		$jsonArray=explode("['",$html);
		unset($jsonArray[0], $jsonArray[count($jsonArray)]);
		$k = 0;
		foreach($jsonArray as $v) {
			$v = str_replace(array("']],","'") ,'' , $v);

			if(strpos($v,'0,0,0,') !== false) {
				$tmp_arr_nome = explode(',', $v);
				if (!empty($tmp_arr_nome[2])) {
					$nome = html_entity_decode(urldecode(str_replace('\x', '%', $tmp_arr_nome[2])), ENT_QUOTES, "utf-8");
				}
			}
			else {
				$tmp_arr_email = explode('\x26\x2364\x3b', $v);
				if(count($tmp_arr_email) > 0) {
					$tmp_arr_email = explode(',', $v);
					if(!empty($tmp_arr_email)) {
						foreach($tmp_arr_email as $vemail) {
							$email = html_entity_decode(urldecode(str_replace('\x', '%', $vemail)));
							if(!empty($email)) {
								if(strpos($email, "@") === false) {
									continue;
								}
								$this->contatos[$k]['nome']  = (empty($nome)? current(explode("@", $email)) : $nome);
								$this->contatos[$k]['email'] = $email;
								$k++;
							}
						}
					}
				}
			}
			unset($nome, $email);
		}

		//faz logoff
		$this->logoff();

		return count($this->contatos);
	}

}
?>

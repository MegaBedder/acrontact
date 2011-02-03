<?
class acrontactGmail extends acrontactBase {

	#################
	### faz login ###
	#################
	private function login() {
		//Carrega pagina de login
		$this->ch = curl_init();
		//curl_setopt($this->ch, CURLOPT_URL, "https://www.google.com/accounts/ServiceLoginAuth?service=mail");
		//curl_setopt($this->ch, CURLOPT_REFERER, "");
		//curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
		//curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
		//curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, array($this, 'gravaCookies'));
		//$html = curl_exec($this->ch);

		//pega campos hidden para fazer o login
		//$extrapost = $this->pegaCamposHidden($html);
		//$extrapost .= "&";

		//encoda o login e senha
		$login = urlencode($this->login);
		$senha = urlencode($this->senha);

		//extrapost
		$extrapost = array(
			'accountType' => 'GOOGLE',
			'Email'       => $login,
			'Passwd'      => $senha,
			'service'     => 'cp',
			'source'      => 'acrontact-acrontact-1',
		);

		//tenta fazer o login
		$action = "https://www.google.com/accounts/ClientLogin";
		curl_setopt($this->ch, CURLOPT_URL, $action);
		curl_setopt($this->ch, CURLOPT_REFERER, "");
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($this->ch, CURLOPT_POST, 1);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $extrapost);
		$html = curl_exec($this->ch);
		
		//Não está mais setando os cookie no cabeçalho, retorna um html com a autorização
		$Auth = substr($html,strpos($html,'Auth=')+strlen('Auth='));
		

		//verifica se efetuou o login com sucesso
		if (!$Auth) {
			$this->mostra_erro(2);
			$this->logado = false;
			return false;
		}
		else {
			$this->logado = $Auth;
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

		//Verifica se digitou o login com ou sem o @gmail.com e adiciona
		if( (strpos($this->login, "@") === false) ) {
			$this->login .= "@gmail.com";
		}

		//Carrega url com lista de contatos (csv)
		curl_setopt($this->ch, CURLOPT_URL, "https://www.google.com/m8/feeds/contacts/default/full?alt=rss&max-results=10000");
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, array('Authorization: GoogleLogin auth='.$this->logado));
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
		curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "GET");
		$html = curl_exec($this->ch);

		//substitui gd:email e openSearch:totalResults para facilitar o tratamento
		$html = str_replace( 
						array("gd:email", "openSearch:totalResults"),
						array("gd"      , "totalResults"), 
						$html
				);

		//simple xml para transformar o retorno em array
		$xml = new SimpleXMLElement($html);
	
		$totalResults = (int) $xml->channel->totalResults;
		for($i=0; $i<$totalResults; $i++) {
			if($xml->channel->item[$i]->gd->attributes()->address == "") {
				continue;
			}
			
			$this->contatos[$i]['nome'] = ((string)$xml->channel->item[$i]->title != "")?(string)$xml->channel->item[$i]->title:current(explode("@", $xml->channel->item[$i]->gd->attributes()->address));
			$this->contatos[$i]['email'] = (string) $xml->channel->item[$i]->gd->attributes()->address;
		}

		//faz logoff
		$this->logoff();

		//retorna total de contatos
		return count($this->contatos);
	}

}
?>

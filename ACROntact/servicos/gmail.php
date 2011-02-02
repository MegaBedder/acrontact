<?
class acrontactGmail extends acrontactBase {

	#################
	### faz login ###
	#################
	private function login() {
		//Carrega pagina de login
		$this->ch = curl_init();
		curl_setopt($this->ch, CURLOPT_URL, "https://www.google.com/accounts/ServiceLoginAuth?service=contacts");
		curl_setopt($this->ch, CURLOPT_REFERER, "");
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, array($this, 'gravaCookies'));
		$html = curl_exec($this->ch);
		
		//pega campos hidden para fazer o login
		$extrapost = $this->pegaCamposHidden($html);
		$extrapost .= "&";

		//encoda o login e senha
		$login = urlencode($this->login);
		$senha = urlencode($this->senha);

		//tenta fazer o login
		$action = "https://www.google.com/accounts/ServiceLoginAuth?service=contacts";
		curl_setopt($this->ch, CURLOPT_URL, $action);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($this->ch, CURLOPT_POST, 1);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $extrapost ."Email=$login&Passwd=$senha&service=cp");
		$html = curl_exec($this->ch);
	
		
		$this->debug();
		echo strip_tags($html);
		echo "<hr>";
		
		curl_setopt($this->ch, CURLOPT_URL, "https://www.google.com/accounts/CheckCookie?chtml=LoginDoneHtml");
		curl_setopt($this->ch, CURLOPT_REFERER, $action);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, array($this, 'gravaCookies'));
		$html = curl_exec($this->ch);
		
		$this->debug();
		echo strip_tags($html);
		die("<hr>");

		//verifica se efetuou o login com sucesso
		if (!isset($this->cookiearr['GX']) && (!isset($this->cookiearr['LSID']) || $this->cookiearr['LSID'] == "EXPIRED")) {
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

		//Carrega url com lista de contatos (csv)
		curl_setopt($this->ch, CURLOPT_URL, "https://mail.google.com/mail/c/data/export?exportType=ALL&groupToExport=%5EMine&out=GMAIL_CSV&tok=aCSw8y0BAAA.DoR76MWRj1F8tmV8YSwM0g.YWKjARMmk2ZLStrSo2Em5Q");
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
		$html = curl_exec($this->ch);
		
		echo $this->login;
		$this->debug();
		echo strip_tags($html);
		die("<hr>");

		//converte charset para utf-8
		$html = iconv($this->charset, 'utf-8', $html);

		//cria array com cada linha
		$csvrows = explode("\n", $html);
		array_shift($csvrows);

		foreach ($csvrows as $k=>$v) {
			if (preg_match('/^((?:"[^"]*")|(?:[^,]*)).*?([^,@]+@[^,]+)/', $v, $matches)) {
				$this->contatos[$k]['nome'] = trim( ( trim($matches[1] )=="" ) ? current(explode("@",$matches[2])) : $matches[1] , '" ');
				$this->contatos[$k]['email'] = trim( $matches[2] );
			}
		}
		//faz logoff
		$this->logoff();

		//retorna total de contatos
		return count($this->contatos);
	}

}
?>

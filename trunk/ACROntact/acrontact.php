<?php
/*
 *
   _____  _________ __________________          __                   __
  /  _  \ \_   ___ \\______   \_____  \   _____/  |______     ____ _/  |_
 /  /_\  \/    \  \/ |       _//   |   \ /    \   __\__  \  _/ ___\\   __\
/    |    \     \____|    |   |    |    \   |  \  |  / __ \_\  \___ |  |
\____|__  /\______  /|____|_  |_______  /___|  /__| (____  / \___  >|__|
        \/        \/        \/        \/     \/          \/      \/
 *
 */

require_once("servicos/base.php");

//Lista com os serviços disponiveis neste script.
$arr_servicos = array(
		//Serviço de email
		'Gmail',
		'Hotmail',
		'YahooMail',

		//Redes Sociais
		'Orkut'
	);

#####################
### Classe geral. ###
#####################
class acrontact {

	private $mostra_erros = true; //'true' para mostrar msg de erro ou 'false' para não

	public $contatos = array();

	########################################################################################################
	### Função para retornar os contatos, definir o 'nome' do serviço, que a classe se vira com o resto. ###
	########################################################################################################
	public function pegaContatos($servico = null) {

		$base = new acrontactBase;
		$base->mostra_erros = $this->mostra_erros;
		//Verifica se o serviço esta setado.
		if (!isset($servico)) {
			$base->mostra_erro(0);
		}

		//Chama função
		$return = 0;
		switch(strtolower($servico)) {
			case 'gmail': //Gmail
				$return = $this->pegaContatosGmail();
				break;

			case 'orkut': //Orkut
				$return = $this->pegaContatosOrkut();
				break;

			case 'hotmail': //Hotmail / Live
				$return = $this->pegaContatosHotmail();
				break;

			case 'yahoomail': //yahoo
				$return = $this->pegaContatosYahoomail();
				break;

			default:
				$base->mostra_erro(1);
		}
		unset($base);
		return $return;
	}

	########################################
	### Importador de contatos do gmail. ###
	########################################
	private function pegaContatosGmail() {
		require_once("servicos/gmail.php");

		$acro = new acrontactGmail;
		$acro->mostra_erros = $this->mostra_erros;
		$acro->login = $this->login;
		$acro->senha = $this->senha;
		$return = $acro->pegaContatos();
		$this->contatos = $acro->contatos;
		unset($acro);
		return $return;
	}

	########################################
	### Importador de contatos do orkut. ###
	########################################
	private function pegaContatosOrkut() {
		require_once("servicos/orkut.php");

		$acro = new acrontactOrkut;
		$acro->mostra_erros = $this->mostra_erros;
		$acro->login = $this->login;
		$acro->senha = $this->senha;
		$return = $acro->pegaContatos();
		$this->contatos = $acro->contatos;
		unset($acro);
		return $return;
	}

	###################################
	### Importa contatos do Hotmail ###
	###################################
	function pegaContatosHotmail() {
		require_once("servicos/hotmail.php");

		$acro = new acrontactHotmail;
		$acro->mostra_erros = $this->mostra_erros;
		$acro->login = $this->login;
		$acro->senha = $this->senha;
		$return = $acro->pegaContatos();
		$this->contatos = $acro->contatos;
		unset($acro);
		return $return;
	}

	###################################
	### Importa contatos do Hotmail ###
	###################################
	function pegaContatosYahoomail() {
		require_once("servicos/yahoo.php");

		$acro = new acrontactYahoomail;
		$acro->mostra_erros = $this->mostra_erros;
		$acro->login = $this->login;
		$acro->senha = $this->senha;
		$return = $acro->pegaContatos();
		$this->contatos = $acro->contatos;
		unset($acro);
		return $return;
	}

}
?>

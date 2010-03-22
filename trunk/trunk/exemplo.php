<?
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
include "ACROntact/acrontact.php";
?>
<html>
	<head>
		<title>Importador de contatos</title>
	</head>
	<body>
		<form method="post">
			Login: <input type="text" name="login" value="" id="" /><br/>
			Senha: <input type="password" name="senha" id="" /><br/>
			<select name="servicos" id="servicos">
				<?
				foreach($arr_servicos as $v) {
					?>
					<option value="<?=strtolower($v)?>"><?=$v?></option>
					<?
				}
				?>
			</select>
			<input type="submit" value="Logar" name="Logar" />
		</form>
	</body>
</html>
<?
if(isset($_POST['Logar'])) {
	$acro = new acrontact;

	$acro->login = $_POST['login'];
	$acro->senha = $_POST['senha'];

	$acro->pegaContatos($_POST['servicos']);

	echo "<pre>";
	print_r($acro->contatos);
	echo"</pre>";
}
?>

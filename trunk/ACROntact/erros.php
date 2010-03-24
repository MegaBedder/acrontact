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
global $msg_erros;
$msg_erros = array();

$msg_erros[0]["ERR"] = "Serviço não definido.";
$msg_erros[0]["SOL"] = "Defina o nome do serviço na função pegaContatos. Ex. pegaContatos('gmail')";

$msg_erros[1]["ERR"] = "Serviço inválido.";
$msg_erros[1]["SOL"] = "Consulte o manual para verificar os serviços disponiveis.";

$msg_erros[2]["ERR"] = "Usuário ou senha inválidos.";
$msg_erros[2]["SOL"] = "";

$msg_erros[3]["ERR"] = "Não foi possivel pegar seus contatos, tente novamente.";
$msg_erros[3]["SOL"] = "Caso o erro persista reporta bug em <a href='https://code.google.com/p/acrontact/' target='_blank'>https://code.google.com/p/acrontact/</a>";
?>

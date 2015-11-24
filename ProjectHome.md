# O que é ACROntact #

O **ACROntact** é uma ferramenta desenvolvida em [PHP](http://pt.wikipedia.org/wiki/PHP), para retornar lista de contatos, atualmente suporta os seguintes serviços:


  * GMail - ('gmail')
  * Yahoo Mail - ('yahoomail')
  * Hotmail - ('hotmail')
  * _~~Orkut - ('orkut')~~_ ( desativado, google bloqueou o email :( )

<font color='red'>o <b>ACROntact</b> não salva ou armazena a senha do usuário</font>

# Requisitos #

O **ACROntact** necessita de no mínimo a versão 5.2 do PHP, [cUrl](http://www.php.net/manual/pt_BR/intro.curl.php) e que esteja ativo o [SimpleXML](http://br2.php.net/manual/pt_BR/intro.simplexml.php)

# Exemplo #

Dentro da versão trunk e dos arquivos disponíveis para download tem um arquivo de exemplo (**exemplo.php**).

## Usando ACROntact ##

Faça o download da [última versão do ACROntact](https://code.google.com/p/acrontact/downloads/list) ou puxe a última versão trunk do repositório.

```
svn checkout https://acrontact.googlecode.com/svn/trunk/ .
```

na sua página faça o include do arquivo **acrontact.php** que se encontra na pasta _**ACROntact**_.
O arquivo **acrontact.php** tem a classe principal para importar os contatos e uma várivel global chamada **$arr\_servicos** do tipo _array_ com os serviços suportados.

```
<?php
include "ACROntact/acrontact.php"
...
?>
```

antes de chamar o serviço desejado é necessário instânciar a classe e fornecer os dados de autenticação (login e senha)

```
<?php
include "ACROntact/acrontact.php"
...

$inst = new acrontact;
$inst->login = 'algumlogin@dominio.com';
$inst->senha = 'senhaDoUsuario';

...
?>
```

a função **pegaContato(_servico_)** é responsável por fazer a autenticação, buscar e disponibilizar os contatos dentro da váriavel do tipo _array_ **contatos**.

Basta passar o nome do serviço desejado para **pegaContato(_servico_)** e pegar os contatos na variavel **contatos**

#### Exemplo GMail ####


```
<?php
include "ACROntact/acrontact.php"
...

$inst = new acrontact;
$inst->login = 'algumLogin@gmail.com';
$inst->senha = 'senhaDoUsuario';

//autentica e busca lista de contatos ($instancia->pegaContatos(_servico_))
$inst->pegaContatos('Gmail'); //tanto faz maiuscula ou minuscula o importante é estar escrito corretamente

//imprime a lista de contatos ($instancia->contatos)
print_r($inst->contatos)
...
?>
```


#### Exemplo Hotmail ####


```
<?php
include "ACROntact/acrontact.php"
...

$inst = new acrontact;
$inst->login = 'algumLogin@hotmail.com';
$inst->senha = 'senhaDoUsuario';

//autentica e busca lista de contatos ($instancia->pegaContatos(_servico_))
$inst->pegaContatos('hotmail'); //tanto faz maiuscula ou minuscula o importante é estar escrito corretamente

//imprime a lista de contatos ($instancia->contatos)
print_r($inst->contatos)
...
?>
```

# Dúvidas #

Caso tenha alguma dúvida pode me consultar pelo e-mail adirkuhn`[at]`gmail.com.

Erros, críticas ou sugestões de melhoria enviar um [issue](https://code.google.com/p/acrontact/issues/entry).
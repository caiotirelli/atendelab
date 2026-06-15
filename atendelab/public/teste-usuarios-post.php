<?php
// Arquivo temporário apenas para teste didático.

$dados = [
    'nome'   => 'Maria Teste',
    'email'  => 'joao3@atendelab.com',
    'senha'  => '123456',
    'perfil' => 'atendente',
    'status' => 'ativo'
];

$ch = curl_init('http://localhost/atendelab/public/?controller=usuarios&action=criar');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($dados));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$resposta = curl_exec($ch);

echo '<pre>';
echo $resposta;
echo '</pre>';
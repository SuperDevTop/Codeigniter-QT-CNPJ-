<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Caminho certificados
|--------------------------------------------------------------------------
|
| Caminho da pasta dos certificados dos clientes
|
*/
$config['caminho_certificados'] = 'C:\Users\job\Documents\certificados';

/*
|--------------------------------------------------------------------------
| Caminho pasta pdfs
|--------------------------------------------------------------------------
|
| Caminho da pasta para salvar os pdfs
|
*/
$path_file_config = FCPATH . '/settings/pasta_pdfs.json';
$file = file_get_contents($path_file_config);
$json = json_decode($file, true);
$config['caminho_pasta_pdf'] = $json['caminho_pasta_pdf'];


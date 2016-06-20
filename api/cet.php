<?php 
header("Content-type: text/json");
include_once("cet.class.php");
$cet = new CET();
$result = array();

$action = isset($_GET['action']) ? $_GET['action'] : '';

$actionOptions = array(
	'informacoes_gerais',
	'ocorrencias',
	);

if(in_array($action, $actionOptions))
{
	switch ($action) {
		case 'informacoes_gerais':
		$result = $cet->getInformacoesGerais();
		break;

		case 'ocorrencias':
		$result = $cet->getUltimasOcorrencias();
		break;
		
		default:
			# code...
		break;
	}
}
else
{
	$result = array('status' => false, 'message' => 'Invalid Action');
}


echo json_encode($result);
?>
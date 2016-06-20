<?php 
include_once 'vendor/autoload.php';
$page = isset($_GET['page']) ? $_GET['page'] : 'index';
$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader);

$page = $page . '.twig.html';

if(file_exists('templates/' . $page)){
	echo $twig->render($page, array('page' => $page));
}
else
{
	echo $twig->render('error_404.twig.html', array('page' => $page));
}


?>
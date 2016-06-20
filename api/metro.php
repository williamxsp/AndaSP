<?php 
/**
* 
*/
class Metro
{
	private $options = array('http' => array('header' => 'User-Agent:Google-Chrome/1.0\r\n'));
	private $statusUrl = 'http://www.metro.sp.gov.br/Sistemas/direto-do-metro-via4/diretodoMetroHome.aspx';
	private $context;

	function __construct()
	{
		libxml_use_internal_errors(TRUE);
		$this->context = stream_context_create($this->options);

	}

	public function getStatusLinhas()
	{
		$html = file_get_contents($this->statusUrl, false, $this->context);
		$metroDoc = new DOMDocument();
		$result = array();

		if(!empty($html))
		{
			$metroDoc->loadHtml($html);
			$companhias = $metroDoc->getElementsByTagName("ul");

			$metro = $companhias->item(0);
			$viaQuatro = $companhias->item(1);

			foreach ($metro->getElementsByTagName("li") as $key => $linha) {
				$result['metro']['linhas'][] = array(
					'nome' => trim($linha->getElementsByTagName("div")->item(0)->getElementsByTagName('span')->item(0)->nodeValue),
					'operacao' => trim($linha->getElementsByTagName("div")->item(1)->getElementsByTagName('span')->item(0)->nodeValue),
					);
			}

			foreach ($viaQuatro->getElementsByTagName("li") as $key => $linha) {
				$result['viaquatro']['linhas'][] = array(
					'nome' => trim($linha->getElementsByTagName("div")->item(0)->getElementsByTagName('span')->item(0)->nodeValue),
					'operacao' => trim($linha->getElementsByTagName("div")->item(1)->getElementsByTagName('span')->item(0)->nodeValue),
					);
			}

			$result['metro']['ultima_atualizacao'] = $metroDoc->getElementById("dataAtualizacaoStatus")->nodeValue;
			$result['viaquatro']['ultima_atualizacao'] = $metroDoc->getElementById("dataAtualizacaoStatusViaQuatro")->nodeValue;
		}

		return $result;
	}
}

$metro = new Metro();

echo "<pre>";
print_r($metro->getStatusLinhas());
?>
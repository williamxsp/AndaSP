<?php 
 function trimHTML($value)
{
	$value = preg_replace("/&#?[a-z0-9]{2,8};/i","",$value);
	$value = str_replace('&nbsp;', '', $value);
	if(strlen($value) == 2){
		return '';
	}
	return trim($value);
}
class CET
{
	private $geralUrl = 'http://cetsp1.cetsp.com.br/monitransmapa/agora/';
	private $ocorrenciasUrl = '';
	private $mapaGeralUrl = 'http://cetsp1.cetsp.com.br/monitransmapa/painel/default.asp';
	private $eixosUrl = 'eixos.asp';
	private $baseUrl = 'http://cetsp1.cetsp.com.br/monitransmapa/';
	private $lentidaoUrl = 'lentidao.asp';

	function __construct()
	{
		libxml_use_internal_errors(TRUE);
	}

	public function getInformacoesGerais()
	{
		$result = array();
		$html = file_get_contents($this->geralUrl);
		$cetDoc = new DOMDocument();

		if(!empty($html))
		{
			$cetDoc->loadHTML($html);

			$geralXPath = new DOMXPath($cetDoc);
			$result = array(
				'km_vias_monitoradas' => $cetDoc->getElementById('tamanhoTotal')->nodeValue,
				'lentidao_total' => $cetDoc->getElementById('lentidao')->nodeValue,
				'percentual_lentidao_total' => $cetDoc->getElementById('percentualLentidao')->nodeValue,
				'tendencia' => '',
				'centro_lentidao' => $cetDoc->getElementById('CentroLentidao')->nodeValue,
				'centro_tendencia' => '',
				'leste_lentidao' => $cetDoc->getElementById('LesteLentidao')->nodeValue,
				'leste_tendencia' => '',
				'norte_lentidao' => $cetDoc->getElementById('NorteLentidao')->nodeValue,
				'norte_tendencia' => '',
				'oeste_lentidao' => $cetDoc->getElementById('OesteLentidao')->nodeValue,
				'oeste_tendencia' => '',
				'sul_lentidao' => $cetDoc->getElementById('SulLentidao')->nodeValue,
				'sul_tendencia' => '',
				);

			$tendencia = $this->extrairTendenciaImagem((string)$cetDoc->getElementById('tendencia')->getElementsByTagName('img')->item(0)->getAttribute('src'));

			$oesteTendencia = $this->extrairTendenciaImagem((string)$cetDoc->getElementById('OesteTendencia')->getElementsByTagName('img')->item(0)->getAttribute('src'));

			$lesteTendencia = $this->extrairTendenciaImagem((string)$cetDoc->getElementById('LesteTendencia')->getElementsByTagName('img')->item(0)->getAttribute('src'));

			$sulTendencia = $this->extrairTendenciaImagem((string)$cetDoc->getElementById('SulTendencia')->getElementsByTagName('img')->item(0)->getAttribute('src'));

			$norteTendencia = $this->extrairTendenciaImagem((string)$cetDoc->getElementById('NorteTendencia')->getElementsByTagName('img')->item(0)->getAttribute('src'));

			$centroTendencia = $this->extrairTendenciaImagem((string)$cetDoc->getElementById('CentroTendencia')->getElementsByTagName('img')->item(0)->getAttribute('src'));

			$result['oeste_tendencia'] = $oesteTendencia;
			$result['leste_tendencia'] = $lesteTendencia;
			$result['sul_tendencia'] = $sulTendencia;
			$result['centro_tendencia'] = $centroTendencia;
			$result['norte_tendencia'] = $norteTendencia;
			$result['tendencia'] = $tendencia;

		}

		return $result;
	}

	public function getUltimaAtualizacaoId()//A CET UTILIZA UM PADRÃO IMG{00} PARA SEPARAR AS ATUALIZAÇÕES
	{
		$mapaGeral = file_get_contents($this->mapaGeralUrl);
		$cetDoc = new DOMDocument();

		if(!empty($mapaGeral))
		{
			$cetDoc->loadHTML($mapaGeral);
			$link = $cetDoc->getElementById("bOcorrencias")->getElementsByTagName("a")->item(0)->getAttribute('href'); //javascript pra abrir link Ow God =x
			preg_match('/http:\/\/cetsp1.cetsp.com.br\/monitransmapa\/(.*?)\//', $link, $linkOcorrencias);
			return isset($linkOcorrencias[1]) ? $linkOcorrencias[1] : 0;			
		}

		return false;
	}

	public function getUltimasOcorrencias()
	{
		$result = array();
		$mapaGeral = file_get_contents($this->mapaGeralUrl);
		$cetDoc = new DOMDocument();

		$id = $this->getUltimaAtualizacaoId();

		if($id)
		{
			$this->ocorrenciasUrl = $this->baseUrl . $id . '/ocorrencias.asp';
			$html = file_get_contents($this->ocorrenciasUrl);
			$cetDoc = new DOMDocument();
			if(!empty($html))
			{
				$cetDoc->loadHTML($html);
				$ocorrenciasTr = $cetDoc->getElementsByTagName('table')->item(1)->getElementsBytagName("tr");
				foreach ($ocorrenciasTr as $key => $ocorrencia) {
					$informacoes = $ocorrencia->getElementsBytagName("td");
					if($informacoes->length == 4){
						$result[] = array(
							'motivo' => $ocorrencia->getAttribute('title'),
							'local' => $informacoes->item(1)->nodeValue,
							'sentido' => $informacoes->item(2)->nodeValue,
							'data' => str_replace('-', ' ', $informacoes->item(3)->nodeValue),
							);
					}
				}
			}
		}

		usort($result, array('CET', 'orderArrayByDateField'));

		return $result;

	}

	public static function orderArrayByDateField($a, $b)
	{

		return self::compararData($a['data'], $b['data']);
	}

	public static function compararData($a, $b)
	{

		$a = explode(" ", $a);
		$b = explode(" ", $b);

		$adata= implode("-",array_reverse(explode("/",$a[0])));
		$ahora = '00:00:00';
		if(isset($a[1]))
		{
			$ahora = explode(':', $a[1]);
			$ahora = (int)$ahora[0] . ":" . (int)$ahora[1];
		}

		
		$adata = strtotime($adata . " " . $ahora);

		$bdata= implode("-",array_reverse(explode("/",$b[0])));
		$bhora = '00:00:00';
		if(isset($b[1]))
		{
			$bhora = explode(':', $b[1]);
			$bhora = (int)$bhora[0] . ":" . (int)$bhora[1];
		}

		$bdata = strtotime($bdata . " " . $bhora);

		if ($adata == $bdata) {
			return 0;
		}
		return ($adata > $bdata) ? -1 : 1;
	}
	public function extrairTendenciaImagem($string)
	{

		preg_match('/img\\\(.*)\./', $string, $tendenciaMatch);
		if(isset($tendenciaMatch[1]))
		{
			return strtolower($tendenciaMatch[1]);
		}

		preg_match('/img\/(.*)\./', $string, $tendenciaMatch);


		if(isset($tendenciaMatch[1]))
		{
			return strtolower($tendenciaMatch[1]);
		}

		return false;
	}

	public function getTendenciaPorEixo()
	{
		$result = array();
		$id = $this->getUltimaAtualizacaoId();

		if($id){

			$html = file_get_contents($this->baseUrl  . $id . "/" . $this->eixosUrl);
			$cetDoc = new DOMDocument();

			if(!empty($html))
			{
				$cetDoc->loadHTML($html);
				$tabelas = $cetDoc->getElementsByTagName('table');
				$eixos = $tabelas->item(0);
				$corredores = $tabelas->item(1);

				$eixoNome = '';

				foreach ($eixos->getElementsBytagName('tr') as $key => $eixo) {
					$informacoes = $eixo->getElementsBytagName("td");
					if($informacoes->length > 0){

						if($informacoes->length == 5){
							$eixoNome = $informacoes->item(0)->nodeValue;
							$result['eixos'][$eixoNome][] = array(
								'sentido' => $informacoes->item(1)->nodeValue,
								'tamanho_congestionamento' => $informacoes->item(2)->nodeValue,
								'porcentagem_congestionamento' => $informacoes->item(3)->nodeValue,
								'tendencia' => $this->extrairTendenciaImagem($informacoes->item(4)->getElementsByTagName('img')->item(0)->getAttribute('src')),
								);
						}
						else
						{
							$result['eixos'][$eixoNome][] = array(
								'sentido' => $informacoes->item(0)->nodeValue,
								'tamanho_congestionamento' => $informacoes->item(1)->nodeValue,
								'porcentagem_congestionamento' => $informacoes->item(2)->nodeValue,
								'tendencia' => $this->extrairTendenciaImagem($informacoes->item(3)->getElementsByTagName('img')->item(0)->getAttribute('src')),
								);
						}
					}
				}
			}
		}

		return $result;

	}


	public function getCorredores()
	{
		$result = array();
		$id = $this->getUltimaAtualizacaoId();
		if(!$id){
			return false;
		}

		$html = file_get_contents($this->baseUrl . $id . '/' . $this->lentidaoUrl);
		$cetDoc = new DOMDocument();
		if(!empty($html))
		{
			$cetDoc->loadHTML($html);
			$lentidoes = $cetDoc->getElementsBytagName('table')->item(1)->getElementsByTagName('tr');

			foreach ($lentidoes as $key => $lentidao) {
				$lentidao = $lentidao->getElementsBytagName("td");
				if($lentidao->length == 5)
				{

					$result[$key] = array(
						'corredor' => trimHTML((string)$lentidao->item(0)->nodeValue),
						'local' => trimHTML($lentidao->item(3)->nodeValue),
						'tamanho' => trimHTML($lentidao->item(4)->nodeValue),
						'sentido' => trimHTML($lentidao->item(1)->nodeValue),
						'via' => trimHTML($lentidao->item(2)->nodeValue),
						);
					
					if($result[$key]['corredor'] == '')
					{
						$result[$key]['corredor'] = $result[$key-1]['corredor'];
					}
						
				}
			}
		}

		return $result;
	}
}
?>

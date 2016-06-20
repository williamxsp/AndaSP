Instagram.search('andasp');

function updateHome()
{
	$.get(
		'api/cet.php', {action: 'informacoes_gerais'}, function(data)
		{
			if(data.status != false)
			{
				var d = new Date();
				$("#congestionamento-total > .informacao").text(data.lentidao_total);
				$("#ultima-atualizacao .informacao").text(d.getHours() + ":" + d.getMinutes());
			}
			else
			{
				console.log('bug');
			}
		}, 'json');
}

function ultimasOcorrencias (pai) {
	$.get('api/cet.php', {action: 'ocorrencias'}, function(data)
		{
			if(data.status != false)
			{
				for(k in data)
				{
					var ocorrencia = data[k];
					var li =  $("<li>");
					var hora = $("<span>");
					var local = $("<p>");
					var sentido = $("<p>");
					var motivo = $("<h2>");

					$(hora).text(ocorrencia.data);
					$(sentido).text(ocorrencia.sentido);
					$(local).text(ocorrencia.local);
					$(motivo).text(ocorrencia.motivo);

					$(li).append(hora).append(local).append(sentido).append(motivo);
					$(pai).append(li);

				}
			}
			else
			{
				console.log('bug');
			}
		});
}

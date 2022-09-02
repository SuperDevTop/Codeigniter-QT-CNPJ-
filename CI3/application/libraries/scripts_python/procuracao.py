import sys
import requests
import warnings
from htmldom import htmldom
from lxml import html
import json
import random

cookiecav = sys.argv[1]
aspsession = sys.argv[2]

with requests.Session() as session:
	session.cookies.set('COOKIECAV', cookiecav)
	session.cookies.set('ASP.NET_SessionId', aspsession)
	session.headers.update({'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'})
	# username = 'lum-customer-c_aeeb4574-zone-residential'
	# password = 'xcop8rz17amg'
	# port = 22225
	# session_id = random.random()
	# super_proxy_url = ('http://%s-country-br-session-%s:%s@zproxy.lum-superproxy.io:%d' %
	# 					(username, session_id, password, port))

	# proxies = {
	# 	'http': super_proxy_url,
	# 	'https': super_proxy_url,
	# }

	# session.proxies.update(proxies)

	headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.61 Safari/537.36'}

	request = session.get('https://cav.receita.fazenda.gov.br/Servicos/ATSDR/Procuracoes.app/ProcuracoesControlador.asp?acao=Iniciar',headers=headers)
	
	dom = htmldom.HtmlDom().createDom( request.text )
	data = {
		'MesAnoExpiracao': "",
		'NI_Delegante': "",
		'NI_DeleganteP': "",
		'SituacaoProcuracao': "",
		'numPagina': "",
		'numPaginaP': "",
		'pag1': dom.find( "[id=pag1]" ).attr( "value" ),
		'pag1P': dom.find( "[id=pag1P]" ).attr( "value" ),
		'qtdePaginas': dom.find( "[id=qtdePaginas]" ).attr( "value" ),
		'qtdePaginasP': dom.find( "[id=qtdePaginasP]" ).attr( "value" ),
		'rdpfpjdelegante': "Todos",
		'strData': dom.find( "[id=strData]" ).attr( "value" ),
		'strDataP': dom.find( "[id=strDataP]" ).attr( "value" ),
		'strHora': dom.find( "[id=strHora]" ).attr( "value" ),
		'strHoraP': dom.find( "[id=strHoraP]" ).attr( "value" ),
		'tipoConsulta': "Procurador",
		'txtpfdelegante': "",
		'txtpjdelegante': "",
	}
	
	request = session.post('https://cav.receita.fazenda.gov.br/Servicos/ATSDR/Procuracoes.app/ProcuracoesControlador.asp?acao=botao', data=data,headers=headers)

	byte_data = request.content 
	source_code = html.fromstring(byte_data)
	resultado = []
	for tbl in source_code.xpath('//*[@id="FormConsulta"]/table[2]'):
		for index, tr in enumerate(tbl.xpath(".//tr")):    
			if index > 0:
				try:                        
					resultado.append({
						"cnpj_outorgante": tr.xpath('.//td//text()')[1].strip(),
						"nome_outorgante": tr.xpath('.//td//text()')[4].strip(),
						"data_inicio": tr.xpath('.//td//text()')[7].strip(),
						"data_fim": tr.xpath('.//td//text()')[9].strip(),
						"situacao": tr.xpath('.//td//text()')[23].strip(),
					})
				except IndexError:
					print('')
	
 
	try:
		source_code.xpath('//*[@id="pag_2"]/@value')[0]
		vgenerator = True
	except IndexError:
		vgenerator = False
		

	if vgenerator == True:

		qt_paginas = int(source_code.xpath('//*[@id="qtdePaginas"]/@value')[0]) + 1
		for x in range(2, qt_paginas, 1):
			data = {
				'MesAnoExpiracao': source_code.xpath('//*[@id="MesAnoExpiracao"]/@value')[0],
				'NI_Delegante': "",
				'SituacaoProcuracao': "",
				'numPagina': x,
				'qtdePaginas': source_code.xpath('//*[@id="qtdePaginas"]/@value')[0],
				'strData': source_code.xpath('//*[@id="strData"]/@value')[0],
				'strHora': source_code.xpath('//*[@id="strHora"]/@value')[0],
				'tipoConsulta': "Procurador",
			}
			
			
			request = session.post('https://cav.receita.fazenda.gov.br/Servicos/ATSDR/Procuracoes.app/ProcuracoesControlador.asp?acao=botao', data=data,headers=headers)
			byte_data = request.content 
			source_code = html.fromstring(byte_data)
			for tbl in source_code.xpath('//*[@id="FormConsulta"]/table[2]'):
				for index, tr in enumerate(tbl.xpath(".//tr")):    
					if index > 0:
						try:
							situacao = tr.xpath('.//td//text()')[23].strip()
							if not bool(situacao):
								situacao = 'Cancelada'
							resultado.append({
								"cnpj_outorgante": tr.xpath('.//td//text()')[1].strip(),
								"nome_outorgante": tr.xpath('.//td//text()')[4].strip(),
								"data_inicio": tr.xpath('.//td//text()')[7].strip(),
								"data_fim": tr.xpath('.//td//text()')[9].strip(),
								"situacao": situacao ,
							})
						except IndexError:
							print('')
			
	print(json.dumps(resultado))
   
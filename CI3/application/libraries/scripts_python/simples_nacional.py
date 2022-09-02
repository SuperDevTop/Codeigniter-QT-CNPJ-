import sys
import requests
import warnings
from bs4 import BeautifulSoup
import json
import re
import random

warnings.filterwarnings('ignore')

def getUltimaDeclaracao(tr):
	compentencia = tr.get_text().strip()
	trProxima = tr
	encontrou_proxima_conpetencia = False
	declaracao = {
		'compentencia': compentencia,
		'operacao':'',
		'numero_declaracao':'',
		'data_hora_transmissao':'',
		'caminho_download_declaracao':'',
		'caminho_download_recibo':'',
	}
	while not encontrou_proxima_conpetencia:
		trProxima = trProxima.find_next('tr')
		if str(type(trProxima)) == "<class 'NoneType'>":
			break;
		try:
			if trProxima['style'] == 'background-color:#F0F0F0':
				continue
			if trProxima['style'] == 'background-color:#DEF0C1':
				encontrou_proxima_conpetencia = True
				break
		except:
			pass

		if 'Declaração' in trProxima.find_next('td').get_text().strip():
			declaracao = {
				'compentencia': compentencia,
				'operacao' : trProxima.find_next('td').get_text().strip(),
				'numero_declaracao' : trProxima.find_next('td').find_next('td').get_text().strip(),
				'data_hora_transmissao' : trProxima.find_next('td').find_next('td').find_next('td').get_text().strip(),
				'caminho_download_recibo' : '',
				'caminho_download_declaracao' : '',
			}
	return declaracao

def getUltimoDas(tr):
	compentencia = tr.get_text().strip()
	trProxima = tr
	encontrou_proxima_conpetencia = False
	das = {
		'numero_das':'',
		'data_hora_emissao':'',
		'caminho_download_extrato':'',
		'pago':'',
	}
	while not encontrou_proxima_conpetencia:
		trProxima = trProxima.find_next('tr')
		if str(type(trProxima)) == "<class 'NoneType'>":
			break;
		try:
			if trProxima['style'] == 'background-color:#F0F0F0':
				continue
			if trProxima['style'] == 'background-color:#DEF0C1':
				encontrou_proxima_conpetencia = True
				break
		except:
			pass

		if 'DAS' in trProxima.find_next('td').get_text().strip():
			das = {
				'numero_das': trProxima.find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').get_text().strip(),
				'data_hora_emissao': trProxima.find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').get_text().strip(),
				'caminho_download_extrato': '',
				'pago' : trProxima.find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').get_text().strip(),
			}
	return das

def verificarDasPagoRetificado(das, tr):
	try:
		dasPago = buscaDasPago(tr)
		if das['pago'] == 'Não' and  'Retificadora' in das['operacao'] and dasPago['pago'] == 'Sim':
			return dasPago
	except Exception as e:
		return ''

def buscaDasPago(tr):
	compentencia = tr.get_text().strip()
	trProxima = tr
	encontrou_proxima_conpetencia = False
	context = {}
	while not encontrou_proxima_conpetencia:
		trProxima = trProxima.find_next('tr')
		if str(type(trProxima)) == "<class 'NoneType'>":
			break;
		try:
			if trProxima['style'] == 'background-color:#F0F0F0':
				continue
			if trProxima['style'] == 'background-color:#DEF0C1':
				encontrou_proxima_conpetencia = True
				break
		except:
			pass

		if 'DAS' in trProxima.find_next('td').get_text().strip():
			pago = trProxima.find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').get_text().strip()
			if pago == 'Sim':
				das = {
					'numero_das': trProxima.find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').get_text().strip(),
					'data_hora_emissao': trProxima.find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').get_text().strip(),
					'caminho_download_extrato': '',
					'pago' : pago,
				}
				trAnterior = trProxima.find_previous('tr')
				if 'Declaração' in trAnterior.find_next('td').get_text().strip():
					declaracao = {
						'compentencia': compentencia,
						'operacao' : trAnterior.find_next('td').get_text().strip(),
						'numero_declaracao' : trAnterior.find_next('td').find_next('td').get_text().strip(),
						'data_hora_transmissao' : trAnterior.find_next('td').find_next('td').find_next('td').get_text().strip(),
					}

				context.update(declaracao)
				context.update(das)


	return context

def executar():
	cookiecav = sys.argv[1]
	aspsession = sys.argv[2]
	folder_pdf = sys.argv[3]
	ano = sys.argv[4]

	with requests.Session() as session:
		session.cookies.set('COOKIECAV', cookiecav)
		session.cookies.set('ASP.NET_SessionId', aspsession)
		session.headers.update({'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'})
		username = 'lum-customer-c_aeeb4574-zone-residential'
		password = 'xcop8rz17amg'
		port = 22225
		session_id = random.random()
		super_proxy_url = ('http://%s-country-br-session-%s:%s@zproxy.lum-superproxy.io:%d' %
			(username, session_id, password, port))

		proxies = {
		  'http': super_proxy_url,
		  'https': super_proxy_url,
		}

		session.proxies.update(proxies)
		headers = {'Content-Type': 'application/x-www-form-urlencoded',
							 'Host': 'sinac.cav.receita.fazenda.gov.br',
							 'Origin': 'https://sinac.cav.receita.fazenda.gov.br',
							 'Referer': 'https://cav.receita.fazenda.gov.br/ecac/'}

		request = session.get('https://sinac.cav.receita.fazenda.gov.br/simplesnacional/aplicacoes/atspo/pgdasd2018.app/')
		request = session.get('https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgdasd2018.app/Consulta')
		request = session.post('https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgdasd2018.app/Consulta', data={'anoDigitado': ano})

		soup = BeautifulSoup(request.text, 'html.parser')

		atributo_marcador_competencia = {"style": "background-color:#DEF0C1"}
		allTr = soup.find_all(attrs=atributo_marcador_competencia)
		listaResulado = []
		for tr in allTr:
			declaracao = getUltimaDeclaracao(tr)
			das = getUltimoDas(tr)
			context = {}
			context.update(declaracao)
			context.update(das)
			dasPagoRetificado = verificarDasPagoRetificado(context, tr)
			if dasPagoRetificado:
				context['das_pago_retificado'] = dasPagoRetificado
			listaResulado.append(context)
		session.close()

		return(json.dumps(listaResulado))

print(executar())
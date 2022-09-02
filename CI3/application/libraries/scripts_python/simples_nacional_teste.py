import sys
import requests
import warnings
from bs4 import BeautifulSoup
import json
import re

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
				'caminho_download_declaracao' : trProxima.find_next('td').find_next('td').find_next('td').find_next('td').find('a')['href'],
				'caminho_download_recibo' : trProxima.find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find('a')['href'],
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
				'caminho_download_extrato': trProxima.find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find('a')['href'],
				'pago' : trProxima.find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').get_text().strip(),
			}
	return das

def baixarAnexos(listaResulado, folder_pdf, session, hashmap):
	for resultado in listaResulado:
		ainda_nao_baixou = resultado['numero_declaracao'] in hashmap.split(';')
		try:
			if len(resultado['caminho_download_recibo']) > 0:
				path_recibo = folder_pdf+"PGDASD-RECIBO-"+resultado['numero_declaracao']+".pdf"
				if not ainda_nao_baixou:
					response = session.get("https://sinac.cav.receita.fazenda.gov.br" + resultado['caminho_download_recibo'])
					file = open(path_recibo, "wb")
					file.write(response.content)
					file.close()
				resultado['caminho_download_recibo'] = path_recibo
			else:
				resultado['caminho_download_recibo'] = ''

		except:
			pass

		try:
			if len(resultado['caminho_download_declaracao']) > 0:
				path_declaracao = folder_pdf+"PGDASD-DECLARACAO-"+resultado['numero_declaracao']+".pdf"
				if not ainda_nao_baixou:
					response = session.get("https://sinac.cav.receita.fazenda.gov.br" + resultado['caminho_download_declaracao'])
					file = open(path_declaracao, "wb")
					file.write(response.content)
					file.close()
				resultado['caminho_download_declaracao'] = path_declaracao
			else:
				resultado['caminho_download_declaracao'] = ''

		except:
			pass

		try:
			if len(resultado['caminho_download_extrato']) > 0:
				path_extrato = folder_pdf+"PGDASD-EXTRATO-"+resultado['numero_das']+".pdf"
				if not ainda_nao_baixou:
					response = session.get("https://sinac.cav.receita.fazenda.gov.br" + resultado['caminho_download_extrato'])
					file = open(path_extrato, "wb")
					file.write(response.content)
					file.close()
				resultado['caminho_download_extrato'] = path_extrato
			else:
				resultado['caminho_download_extrato'] = ''


		except:
			pass

	return listaResulado

def get_cookies(cookie_path):
	cookies = []
	lineFields = []
	with open(cookie_path) as file_in:
		i = 1
		for line in file_in:
			i = i + 1
			if i > 5:
				lineFields = line.strip().split('\t')
				x = len(lineFields) -1
				cookies.append({
					'name': lineFields[x-1],
					'value': lineFields[x]
				})
	return cookies
	
def executar():
	cookie_path = sys.argv[1]
	folder_pdf = sys.argv[2]
	ano = sys.argv[3]
	hashmap = sys.argv[4]

	with requests.Session() as session:
			cookies = get_cookies(cookie_path)
			for cookie in cookies:
				session.cookies.set(cookie['name'], cookie['value'])
			session.headers.update({'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'})
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
					listaResulado.append(context)

			listaResulado = baixarAnexos(listaResulado, folder_pdf, session, hashmap)
			session.close()

			return(json.dumps(listaResulado))

print(executar())
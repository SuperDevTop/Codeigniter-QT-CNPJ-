
import sys
import requests
import warnings
from bs4 import BeautifulSoup
import json
import re
warnings.filterwarnings('ignore')

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

    try:
        with requests.Session() as session:
            cookies = get_cookies(cookie_path)
            for cookie in cookies:
                session.cookies.set(cookie['name'], cookie['value'])
            headers = {'Content-Type': 'application/x-www-form-urlencoded',
                       'Host': 'sinac.cav.receita.fazenda.gov.br',
                       'Origin': 'https://sinac.cav.receita.fazenda.gov.br',
                       'Referer': 'https://cav.receita.fazenda.gov.br/ecac/',
                       'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.61 Safari/537.36'}

            request = session.get('https://sinac.cav.receita.fazenda.gov.br/simplesnacional/aplicacoes/atspo/pgdasd2018.app/')
            request = session.get('https://sinac.cav.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgdasd2018.app/Debitos')

            soup = BeautifulSoup(request.text, 'html.parser')
            encontrou_informacao = True

            try:
                alerta = soup.find(attrs={"style": "width:80%; margin: 0 auto;"}).find_next('strong')
                if alerta.get_text().strip() == 'Não há débitos apurados no Simples Nacional no âmbito da RFB.':
                    encontrou_informacao = False
            except:
                pass

            if encontrou_informacao:
                resultado = []
                trs = []
                try:
                    trs = soup.find('div', {'id': 'collapse2018'}).find_next('table').find_all('tr')
                except:
                    pass

                for tr in trs:
                    try:
                        if (tr['style'] == 'background-color:#F0F0F0'):
                            continue
                    except:
                        pass
                    periodo_apuracao = tr.find_next('td').find_next('td').get_text().strip()
                    data_vencimento = tr.find_next('td').find_next('td').find_next('td').get_text().strip()
                    debito_declarado = tr.find_next('td').find_next('td').find_next('td').find_next('td').get_text().strip()
                    principal = tr.find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').get_text().strip()
                    multa = tr.find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').get_text().strip()
                    juros = tr.find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').get_text().strip()
                    total = tr.find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').get_text().strip()
                    exigibilidade_suspensa = tr.find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').find_next('td').get_text().strip()
                    resultado.append({
                        'periodo_apuracao':periodo_apuracao,
                        'data_vencimento':data_vencimento,
                        'debito_declarado':debito_declarado,
                        'principal':principal,
                        'multa':multa,
                        'juros':juros,
                        'total':total,
                        'exigibilidade_suspensa':exigibilidade_suspensa,
                    })

            session.close()
            return(json.dumps(resultado))
    except:
        pass
print(executar())
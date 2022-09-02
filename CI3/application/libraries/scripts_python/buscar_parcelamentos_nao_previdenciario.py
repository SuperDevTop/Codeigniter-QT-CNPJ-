import sys
from bs4.element import Tag
import requests
import warnings
from bs4 import BeautifulSoup
import json
import random

warnings.filterwarnings('ignore')

def obter_tributos_do_processo_negociados(session, cnpj_na_pagina, numero_processo, situacao):
    request = session.get('https://cav.receita.fazenda.gov.br/Servicos/ATSPO/parcweb.app/ExtratoRelacaoTributos.asp?Ni='+cnpj_na_pagina+'&TipoContr=1&NuProcesso='+numero_processo+'&SitProcesso='+situacao+'&SIST=PARCWEB')
    soup = BeautifulSoup(request.text, 'html.parser')
    pedidos = []
    table = soup.find('table',{'cellspacing':'2'})
    if table:
        rows = table.find_all('tr')
        
        i = 0
        for row in rows:
            if i >= 1:
                if row.find_all('td')[1].getText() == 'Ativo':
                    tributo = row.find_all('td')[0].getText()
                    situacao = row.find_all('td')[1].getText()
                    saldo = row.find_all('td')[2].getText()
                    total_em_atraso = row.find_all('td')[3].getText()
                    parcelas_em_atraso = row.find_all('td')[4].getText()
                    tributos_do_processo = {
                        'tributo' : tributo,
                        'situacao' : situacao,
                        'saldo' : saldo,
                        'total_em_atraso' : total_em_atraso,
                        'parcelas_em_atraso' : parcelas_em_atraso,
                        'demonstrativo_das_parcelas' : demonstrativo_das_parcelas(session, cnpj_na_pagina, numero_processo, situacao, parcelas_em_atraso, tributo)
                    }
                    pedidos.append(tributos_do_processo)
            i+=1
    return pedidos

def demonstrativo_das_parcelas(session, cnpj_na_pagina, numero_processo, situacao, parcelas_em_atraso, tributo):
    request = session.get('https://cav.receita.fazenda.gov.br/Servicos/ATSPO/parcweb.app/ExtratoRelacaoParcelas.asp?Ni='+cnpj_na_pagina+'&TipoContr=1&NuProcesso='+numero_processo+'&Situacao='+situacao+'&QtdParc='+parcelas_em_atraso+'&NomeTrib='+tributo+'&SIST=PARCWEB')
    soup = BeautifulSoup(request.text, 'html.parser')
    pedidos = []
    table = soup.find('table',{'cellspacing':'2'})
    if table:
        rows = table.find_all('tr')
        
        i = 0
        for row in rows:
            if i >= 1:
                numero_parcela = row.find_all('td')[0].getText()
                data_vencimento = row.find_all('td')[1].getText()
                valor_ate_vencimento = row.find_all('td')[2].getText()
                saldo_devedor_atual = row.find_all('td')[3].getText()
                situacao = row.find_all('td')[4].getText()
                demonstrativo = {
                    'numero_parcela' : numero_parcela,
                    'data_vencimento' : data_vencimento,
                    'valor_ate_vencimento' : valor_ate_vencimento,
                    'saldo_devedor_atual' : saldo_devedor_atual,
                    'situacao' : situacao
                }
                pedidos.append(demonstrativo)
            i+=1
    return pedidos

def executar():
    
    cookiecav = sys.argv[1]
    aspsession = sys.argv[2]
    
    with requests.Session() as session:
        session.cookies.set('COOKIECAV', cookiecav)
        session.cookies.set('ASP.NET_SessionId', aspsession)
        session.headers.update(
            {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'})
        # username = 'lum-customer-c_aeeb4574-zone-residential'
        # password = 'xcop8rz17amg'
        # port = 22225
        # session_id = random.random()
        # super_proxy_url = ('http://%s-country-br-session-%s:%s@zproxy.lum-superproxy.io:%d' %
        #                     (username, session_id, password, port))

        # proxies = {
        #     'http': super_proxy_url,
        #     'https': super_proxy_url,
        # }

        # session.proxies.update(proxies)

        processos_negociados = []

        request = session.get('https://cav.receita.fazenda.gov.br/Servicos/ATSPO/parcweb.app/parcweb_gerenciador.asp')
        soup = BeautifulSoup(request.text, 'html.parser')

        
        cnpj_na_pagina = soup.find('b').getText()[6:]

        request = session.get('https://cav.receita.fazenda.gov.br/Servicos/ATSPO/parcweb.app/ExtratoRelacaoProcessos.asp')
        soup = BeautifulSoup(request.text, 'html.parser')

        table = soup.find('table',{'cellspacing':'2'})
        if table:
            rows = table.find_all('tr')
            i = 0
            for row in rows:
                if i >= 1:
                    if row.find_all('center')[2].getText() == 'Parcelado':
                        numero_processo = row.find_all('center')[0].getText()
                        data_do_deferimento = row.find_all('center')[1].getText()
                        situacao = row.find_all('center')[2].getText()
                        processo = {
                            'processo' : numero_processo,
                            'data_do_deferimento' : data_do_deferimento,
                            'situacao' : situacao,
                            'tributos_do_processo_negociados' : obter_tributos_do_processo_negociados(session, cnpj_na_pagina, numero_processo, situacao)
                        }
                        processos_negociados.append(processo)
                i+=1
        session.close()

        return (json.dumps(processos_negociados))

print(executar())
# executar()

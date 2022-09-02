import sys
from bs4.element import Tag
import requests
import warnings
from bs4 import BeautifulSoup
import json
import random

warnings.filterwarnings('ignore')

def obter_demonstrativo_de_parcelas(session, id_formatado):
    request = session.get('https://sic.cav.receita.fazenda.gov.br/siefpar-internet/cons/api/private/parc/consulta/demonstrativo-parcela/demonstrativo?idParc=' + id_formatado)
    soup = BeautifulSoup(request.text, 'html.parser')

    return json.loads(soup.text)

def obter_demonstrativo_de_pagamentos(session, id_formatado):
    request = session.get('https://sic.cav.receita.fazenda.gov.br/siefpar-internet/cons/api/private/parc/consulta/demonstrativo-pagamento/obter-demonstrativo-pagamentos?idParc=' + id_formatado)
    soup = BeautifulSoup(request.text, 'html.parser')
    
    return json.loads(soup.text)

def executar():
    cookiecav = sys.argv[1]
    aspsession = sys.argv[2]
    cnpj = sys.argv[3]

    with requests.Session() as session:
        session.cookies.set('COOKIECAV', cookiecav)
        session.cookies.set('ASP.NET_SessionId', aspsession)
        session.headers.update(
            {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'})
    
        parcelamentos = []

        request = session.get('https://sic.cav.receita.fazenda.gov.br/siefpar-internet/home.html')
        soup = BeautifulSoup(request.text, 'html.parser')

        headers = {
            'Connection' : 'keep-alive',
            'sec-ch-ua': '"Chromium";v="94", "Google Chrome";v="94", ";Not A Brand";v="99"',
            'Accept' : 'application/json, text/plain, */*',
            'Content-Type' : 'application/json',
            'sec-ch-ua-mobile' : '?0',
            'sec-ch-ua-platform' : 'Windows',
            'Origin' : 'https://sic.cav.receita.fazenda.gov.br',
            'Sec-Fetch-Site' : 'same-origin',
            'Sec-Fetch-Mode' : 'cors',
            'Sec-Fetch-Dest' : 'empty',
            'Referer' : 'https://sic.cav.receita.fazenda.gov.br/siefpar-internet/ng/consultar-parcelamento/consultar-parcelamento',
            'Accept-Language' : 'pt-BR,pt;q=0.9',
        }
        data = '{"ni":"' + cnpj + '","mesInicial":null,"anoInicial":null,"mesFinal":null,"anoFinal":null}'
        request = session.post('https://sic.cav.receita.fazenda.gov.br/siefpar-internet/cons/api/private/parc/consulta/consultar-parcelamento/consultar', data = data, headers = headers)        
        
        soup = BeautifulSoup(request.text, 'html.parser')
        if 'Não existem parcelamentos com os critérios informados' in request.text:
            return ''
        response=''
        try:
            response = json.loads(soup.text)
        except:
            pass
        if response:
            for parcelamentos in response:
                for parcelamento in parcelamentos:
                    if 'idFormatado' in parcelamento:
                        parcelamento['demonstrativo_de_parcelas'] = obter_demonstrativo_de_parcelas(session, parcelamento['idFormatado'])
                        parcelamento['demonstrativo_de_pagamentos'] = obter_demonstrativo_de_pagamentos(session, parcelamento['idFormatado'])
        session.close()
        return (json.dumps(parcelamentos))
print(executar())

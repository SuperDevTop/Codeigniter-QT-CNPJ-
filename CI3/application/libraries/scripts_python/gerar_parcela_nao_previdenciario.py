import sys
from bs4.element import Tag
import requests
import warnings
from bs4 import BeautifulSoup
import json
import random
import re
from google.cloud import storage
import os
from google.oauth2 import service_account
import pdfkit
from flask import Flask
import uuid

warnings.filterwarnings('ignore')

def verifica_pdf_valido(content):
    if (re.search("^b'%PDF-1.", str(content))):
        return True
    else:
        return False
    
def upload_blob(blob_text, destination_blob_name):
    bucket_name = 'cron-veri-files-br'
    credentials = service_account.Credentials.from_service_account_file('/var/www/html/SistemaCronsCertificado/sp/windy-hangar-321019-daae49ffa513.json')
    storage_client = storage.Client(credentials=credentials)
    bucket = storage_client.bucket(bucket_name)
    blob = bucket.blob(destination_blob_name)
    file_path='/var/www/html/SistemaCronsCertificado/sp/arquivos/recibos-parcelamento-simplesnacional'+uuid.uuid4().hex+'.pdf'
    with open(file_path, 'wb') as f:
        f.write(blob_text)
    blob.upload_from_filename(file_path)
    # os.remove(file_path)

def executar():

    cookiecav = sys.argv[1]
    aspsession = sys.argv[2]
    numero_processo = sys.argv[3]
    nome_tributo = sys.argv[4]
    data_vencimento = sys.argv[5]
    valor_parcela = sys.argv[6]
    numero_parcela = sys.argv[7]
    caminho = sys.argv[8]

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

        request = session.get('https://cav.receita.fazenda.gov.br/Servicos/ATSPO/parcweb.app/parcweb_gerenciador.asp')
        soup = BeautifulSoup(request.text, 'html.parser')

        cnpj_na_pagina = soup.find('b').getText()[6:]

        request = session.get('https://cav.receita.fazenda.gov.br/Servicos/ATSPO/parcweb.app/ExtratoEmissaoDARF.asp?Ni='+cnpj_na_pagina+'&TipoContr=1&NuProcesso='+numero_processo+'&Tributo='+nome_tributo+'&DtVenc='+data_vencimento+'&VlParcela='+valor_parcela+'&NrParcela='+numero_parcela)

        html = request.text
        html = html.replace('https://cav.receita.fazenda.gov.br/Servicos/ATSPO/Darf.app/estiloDARF.css', 'https://veri-sp.com.br/assets/css/boleto-parcelamento/estiloDARF.css')
        html = html.replace('https://cav.receita.fazenda.gov.br/Servicos/ATSPO/Darf.app/estiloDARFprint.css', 'https://veri-sp.com.br/assets/css/boleto-parcelamento/estiloDARFprint.css')
        html = html.replace('https://cav.receita.fazenda.gov.br/Servicos/ATSPO/Darf.app/imagens/brasil.gif', 'https://veri-sp.com.br/assets/css/boleto-parcelamento/brasil.gif')
        pdf = pdfkit.from_string(html)

        if (verifica_pdf_valido(pdf)):
            upload_blob(pdf, caminho)
            return "https://storage.googleapis.com/cron-veri-files-br/" + caminho
        else: print('invalido')

print(executar())

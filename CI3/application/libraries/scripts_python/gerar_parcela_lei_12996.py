import sys
from google.cloud.storage import blob
import requests
import warnings
from bs4 import BeautifulSoup
import json
import random
import re
from google.cloud import storage
import os
from google.oauth2 import service_account
import uuid
warnings.filterwarnings('ignore')

def verifica_pdf_valido(content):
    if (re.search("^%PDF-1.", content)):
        return True
    else:
        return False
    
def upload_blob(blob_text, destination_blob_name):
    bucket_name = 'cron-veri-files-br'
    credentials = service_account.Credentials.from_service_account_file('/var/www/html/SistemaCronsCertificado/sp/windy-hangar-321019-daae49ffa513.json')
    storage_client = storage.Client(credentials=credentials )
    bucket = storage_client.bucket(bucket_name)
    blob = bucket.blob(destination_blob_name)
    file_path='/var/www/html/SistemaCronsCertificado/sp/arquivos/recibos-parcelamento-simplesnacional/'+uuid.uuid4().hex+'.pdf'
    with open(file_path, 'wb') as f:
        f.write(blob_text)
    blob.upload_from_filename(file_path)
    os.remove(file_path)

def executar():
    
    cookiecav = sys.argv[1]
    aspsession = sys.argv[2]
    cnpj = sys.argv[3]
    data_parcela = sys.argv[4]
    strCodReceita = sys.argv[5]
    caminho = sys.argv[6]

    with requests.Session() as session:
        session.cookies.set('COOKIECAV', cookiecav)
        session.cookies.set('ASP.NET_SessionId', aspsession)
        session.headers.update(
            {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'})
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

        request = session.get('https://www4.cav.receita.fazenda.gov.br/Servicos/ATSPO/paexlei12996.app/MenuLei12996.aspx')

        request = session.get('https://www4.cav.receita.fazenda.gov.br/Servicos/ATSPO/paexlei12996.app/Lei12996/Demonstrativo/Demonstrativo.aspx')
        soup = BeautifulSoup(request.text, 'html.parser')

        strCPFCNPJ = soup.find(id = "lblNi").getText()
        strNome= soup.find(id= "lblNiNome").getText()

        request = session.post('https://www4.cav.receita.fazenda.gov.br/Servicos/ATSPO/paexlei12996.app/Ajaj.aspx', data= {'act': 'InfoNiModalidades', 'dt': '{"TipoNi":"1","Ni":"'+cnpj+'"}'})

        soup = BeautifulSoup(request.text, 'html.parser')

        demonstrativos_json = json.loads(soup.text)

        strMunicipio = demonstrativos_json['Contribuinte']['NomeMunicipio']

        for modalidade in demonstrativos_json['Modalidades']:
            if(modalidade['NomeSituacao'] == 'Em Parcelamento'):
                
                post_data = {
                    'act': 'DemonstrativoPrestacoes', 
                    'dt': '{"Tipo":"1","Ni":"'+cnpj+'","Parcelamento":"'+modalidade['CodModalidade']+'","Msg":"","Operacao":""}'
                }
                request = session.post('https://www4.cav.receita.fazenda.gov.br/Servicos/ATSPO/paexlei12996.app/AjajNegociacaoLei12996.aspx', data= post_data)
                soup = BeautifulSoup(request.text, 'html.parser')
                demonstrativo_prestacoes_json = json.loads(soup.text)

                strDataLimitePagamento = demonstrativo_prestacoes_json['Proximo_Dia_Util']

                if (demonstrativo_prestacoes_json['Divida']['Codigo_receita'] == strCodReceita):
                    for parcela in demonstrativo_prestacoes_json['Parcelas']:
                        if (parcela['Data_Parcela'] == data_parcela):
                            

                            if (parcela['Indicador_Situacao_Parcela'] == 'A'):  
                                strValorJuros = ''
                                strValorPrincipal =  int(parcela['Saldo_Parc_Devedora']) + int(parcela['Juros_Parc_Deverdora'])
                            else:
                                strValorJuros = int(parcela['Juros_Parc_Deverdora'])
                                strValorPrincipal = int(parcela['Saldo_Parc_Devedora'])
                            
                            strValorTotal = int(parcela['Saldo_Parc_Devedora']) + int(parcela['Juros_Parc_Deverdora'])

                            post_data = {
                                'strCPFCNPJ' : strCPFCNPJ, 
                                'strCodReceita' : strCodReceita, 
                                'strDataLimitePagamento' : strDataLimitePagamento, 
                                'strDataVencimentoImposto' : parcela['Data_Parcela'], 
                                'strManualAutomatico' : 'A', 
                                'strMunicipio' : strMunicipio,
                                'strNome' : strNome,
                                'strPeriodoApuracao' : parcela['Data_Parcela'],
                                'strProgramaChamador' : 'Lei Nº 12.996 de 2014 – Parcelamento',
                                'strValorJuros' : strValorJuros,
                                'strValorPrincipal' : strValorPrincipal,
                                'strValorTotal' : strValorTotal,
                                'tipo' : '9',
                            }

                            request = session.post('https://www4.cav.receita.fazenda.gov.br/Servicos/ATSPO/paexlei12996.app/Lei12996/Darf/ImpressaoDarf.aspx', data = post_data)
                            soup = BeautifulSoup(request.text, 'html.parser')

                            tipo = soup.find('input', {'name' : 'tipo'}).get('value')
                            darfs = soup.find('input', {'name' : 'darfs'}).get('value') 

                            post_data = {
                                'darfs' : darfs, 
                                'tipo' : tipo, 
                            }

                            request = session.post('https://www4.cav.receita.fazenda.gov.br/Servicos/ATSPO/paexlei12996.app/Lei12996/Darf/ImpressaoDarf.aspx', data = post_data)

                            if (verifica_pdf_valido(request.text)):
                                upload_blob(request.content, caminho)
                                return "https://storage.googleapis.com/cron-veri-files-br/" + caminho
                            
        session.close()
        return ''

print(executar())
# executar()

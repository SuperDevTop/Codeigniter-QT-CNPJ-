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
    
    file_path='/var/www/html/SistemaCronsCertificado/sp/arquivos/recibos-parcelamento-simplesnacional'+uuid.uuid4().hex+'.pdf'

    with open(file_path, 'wb') as f:
        f.write(blob_text)
    blob.upload_from_filename(file_path)
    os.remove(file_path)

def executar():
    cookiecav = sys.argv[1]
    aspsession = sys.argv[2]
    id_formatado = sys.argv[3]
    id_parcela = sys.argv[4]
    caminho = sys.argv[5]

    with requests.Session() as session:
        session.cookies.set('COOKIECAV', cookiecav)
        session.cookies.set('ASP.NET_SessionId', aspsession)
        session.headers.update(
            {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'})
        
        request = session.get('https://sic.cav.receita.fazenda.gov.br/siefpar-internet/cons/api/private/parc/consulta/emissao/emitir-documento-arrecadacao-parcela?idParc='+id_formatado+'&idsParcelas='+id_parcela)
      
        if (verifica_pdf_valido(request.text)):
            upload_blob(request.content, caminho)
            return "https://storage.googleapis.com/cron-veri-files-br/" + caminho
                            
        session.close()
        return ''

print(executar())
# executar()

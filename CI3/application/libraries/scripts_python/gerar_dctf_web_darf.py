from io import open_code
import sys
from bs4.element import Tag
import requests
from requests.structures import CaseInsensitiveDict
import warnings
from bs4 import BeautifulSoup
import json
import re
from google.cloud import storage
import os
from google.oauth2 import service_account
import uuid
from datetime import date,timedelta


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
    file_path='/var/www/html/SistemaCronsCertificado/sp/arquivos/dctf-web/'+uuid.uuid4().hex+'.pdf'
    with open(file_path, 'wb') as f:
        f.write(blob_text)
    blob.upload_from_filename(file_path)
    os.remove(file_path)


def gera_parcela(session, soup, new_cookie):

    td_id_declaracoes = soup.find_all('td', {'class' :'coluna-checkbox-debito-pagar'})

    table = soup.find('table', {'id':'ctl00_cphConteudo_tabelaListagemDctf_GridViewDctfs'})
    
    if table:
        trs = table.find_all('tr')
        numero_linhas = int(len(trs)/3)
        for id in range(1, numero_linhas):
            situacao = table.find('span', {'id': 'ctl00_cphConteudo_tabelaListagemDctf_GridViewDctfs_ctl'+str(id+1).zfill(2)+'_lblSituacao'}).getText().strip()
            
            if situacao == 'Ativa':
                id_declaracoes = td_id_declaracoes[id].find('span').get('iddeclaracao').split(';')
            else:
                continue

            if ID_DECLARACAO == id_declaracoes[0][2:] and ID_CONTROLE == id_declaracoes[1][2:]:
                data = {
                    '__EVENTTARGET': 'ctl00$cphConteudo$tabelaListagemDctf$GridViewDctfs$ctl'+str(id+1).zfill(2)+'$lbkVisualizarDctf',
                    '__EVENTARGUMENT': '',
                    '__VIEWSTATE': soup.find('input', {'id':'__VIEWSTATE'})['value'],
                    '__VIEWSTATEGENERATOR': soup.find('input', {'id':'__VIEWSTATEGENERATOR'})['value'],
                    '__EVENTVALIDATION': soup.find('input', {'id':'__EVENTVALIDATION'})['value'],
                    'ctl00$cphConteudo$txtDataInicio': '',
                    'ctl00$cphConteudo$txtDataFinal': '',
                    'ctl00$cphConteudo$ddlCategoriaDeclaracao': '0',
                    'ctl00$cphConteudo$txtNumeroRecibo': '',
                    'ctl00$cphConteudo$chkEmAndamento': 'on',
                    'ctl00$cphConteudo$chkAtiva': 'on',
                    'ctl00$cphConteudo$chkTransmitidaUlt30Dias': 'on',
                }

                url = "https://dctfweb.cav.receita.fazenda.gov.br/AplicacoesWeb/DCTFWeb/Paginas/ListaDctfs.aspx"

                headers = CaseInsensitiveDict()
                headers["Connection"] = "keep-alive"
                headers["Cache-Control"] = "max-age=0"
                headers["sec-ch-ua"] = '" Not A;Brand";v="99", "Chromium";v="96", "Google Chrome";v="96"'
                headers["sec-ch-ua-mobile"] = "?0"
                headers["sec-ch-ua-platform"] = '"Windows"'
                headers["Upgrade-Insecure-Requests"] = "1"
                headers["Origin"] = "https://dctfweb.cav.receita.fazenda.gov.br"
                headers["Content-Type"] = "application/x-www-form-urlencoded"
                headers["User-Agent"] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36"
                headers["Accept"] = "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9"
                headers["Sec-Fetch-Site"] = "same-origin"
                headers["Sec-Fetch-Mode"] = "navigate"
                headers["Sec-Fetch-User"] = "?1"
                headers["Sec-Fetch-Dest"] = "iframe"
                headers["Referer"] = "https://dctfweb.cav.receita.fazenda.gov.br/AplicacoesWeb/DCTFWeb/Paginas/ListaDctfs.aspx"
                headers["Accept-Language"] = "pt-BR,pt;q=0.9"
                # pegar cookie da nova sessao 
                headers["Cookie"] = 'assinadoc_cert_type=A1; COOKIECAV='+new_cookie['COOKIECAV']+'; ASP.NET_SessionId='+new_cookie['ASP.NET_SessionId']+'; cw_dctfweb='+new_cookie['cw_dctfweb']

                response = session.post(url, headers = headers, data = data)
                
                soup = BeautifulSoup(response.text, 'html.parser')

                all_checkbox = soup.find_all('td', {'class':'coluna-checkbox-debito-pagar'})

                data = {
                    'ctl00$ctl24' : 'ctl00$cphConteudo$TabelaVinculacoesDARF$upBarraBotoes|ctl00$cphConteudo$TabelaVinculacoesDARF$LinkEmitirDARFIntegral',
                    '__EVENTTARGET': 'ctl00$cphConteudo$TabelaVinculacoesDARF$LinkEmitirDARFIntegral',
                    '__EVENTARGUMENT': '',
                    '__VIEWSTATE': soup.find('input', {'id':'__VIEWSTATE'})['value'],
                    '__VIEWSTATEGENERATOR': soup.find('input', {'id':'__VIEWSTATEGENERATOR'})['value'],
                    '__EVENTVALIDATION': soup.find('input', {'id':'__EVENTVALIDATION'})['value'],
                    'ctl00$cphConteudo$TabelaVinculacoesDARF$ddlOrigem': '',
                    'ctl00$cphConteudo$TabelaVinculacoesDARF$ddlPeriodicidade': '',
                    'ctl00$cphConteudo$TabelaVinculacoesDARF$ddlGrupoTributário': '',
                    'ctl00$cphConteudo$TabelaVinculacoesDARF$txtFiltroCodigoReceita': '',
                    'ctl00$cphConteudo$TabelaVinculacoesDARF$txtFiltroCno': '',
                    'ctl00$cphConteudo$TabelaVinculacoesDARF$txtFiltroCnpjPrestador': '',
                    'ctl00$cphConteudo$TabelaVinculacoesDARF$checkBoxTodosClicado': '',
                    'ctl00$cphConteudo$TabelaVinculacoesDARF$acaoCheckBoxSelecionarTodos': '',
                    'ctl00$cphConteudo$TabelaVinculacoesDARF$hdOrigem': '',
                    'ctl00$cphConteudo$TabelaVinculacoesDARF$hdPeriodicidade': '',
                    'ctl00$cphConteudo$TabelaVinculacoesDARF$hdGrupoTributario': '-1',
                    'ctl00$cphConteudo$TabelaVinculacoesDARF$hdCodigoReceita': '',
                    'ctl00$cphConteudo$TabelaVinculacoesDARF$hdCno': '',
                    'ctl00$cphConteudo$TabelaVinculacoesDARF$hdCnpjPrestador': '',
                    'ctl00$cphConteudo$TabelaVinculacoesDARF$modalEmissaoGuiaPagamento$txtDataAcolhimento$txtBSCaixaTexto' : '',
                    'ctl00$cphConteudo$TabelaVinculacoesDARF$modalAbaterPagamentosAnteriores$txtNumeroGuiaAbater' : '' ,
                    '__ASYNCPOST': 'true'
                }
                for checkbox in all_checkbox:
                    data[checkbox.input['name']] = 'on'
                
                url = "https://dctfweb.cav.receita.fazenda.gov.br/AplicacoesWeb/DCTFWeb/Paginas/ResumoVinculacoes/VinculacoesCompleta.aspx"

                headers = CaseInsensitiveDict()
                headers["Connection"] = "keep-alive"
                headers["sec-ch-ua"] = '" Not A;Brand";v="99", "Chromium";v="96", "Google Chrome";v="96"'
                headers["sec-ch-ua-mobile"] = "?0"
                headers["User-Agent"] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36"
                headers["Content-Type"] = "application/x-www-form-urlencoded; charset=UTF-8"
                headers["Cache-Control"] = "no-cache"
                headers["X-Requested-With"] = "XMLHttpRequest"
                headers["X-MicrosoftAjax"] = "Delta=true"
                headers["sec-ch-ua-platform"] = '"Windows"'
                headers["Accept"] = "*/*"
                headers["Origin"] = "https://dctfweb.cav.receita.fazenda.gov.br"
                headers["Sec-Fetch-Site"] = "same-origin"
                headers["Sec-Fetch-Mode"] = "cors"
                headers["Sec-Fetch-Dest"] = "empty"
                headers["Referer"] = "https://dctfweb.cav.receita.fazenda.gov.br/AplicacoesWeb/DCTFWeb/Paginas/ResumoVinculacoes/VinculacoesCompleta.aspx"
                headers["Accept-Language"] = "pt-BR,pt;q=0.9"
                headers["Cookie"] = 'assinadoc_cert_type=A1; COOKIECAV='+new_cookie['COOKIECAV']+'; ASP.NET_SessionId='+new_cookie['ASP.NET_SessionId']+'; cw_dctfweb='+new_cookie['cw_dctfweb']

                requests.post(url, headers=headers, data=data)

                url = "https://dctfweb.cav.receita.fazenda.gov.br/AplicacoesWeb/DCTFWeb/Paginas/GuiaPagamento/DownloadPdfGuiaPagamento.aspx"

                headers = CaseInsensitiveDict()
                headers["Connection"] = "keep-alive"
                headers["sec-ch-ua"] = '" Not A;Brand";v="99", "Chromium";v="96", "Google Chrome";v="96"'
                headers["sec-ch-ua-mobile"] = "?0"
                headers["sec-ch-ua-platform"] = '"Windows"'
                headers["Upgrade-Insecure-Requests"] = "1"
                headers["User-Agent"] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36"
                headers["Accept"] = "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9"
                headers["Sec-Fetch-Site"] = "same-origin"
                headers["Sec-Fetch-Mode"] = "navigate"
                headers["Sec-Fetch-User"] = "?1"
                headers["Sec-Fetch-Dest"] = "iframe"
                headers["Referer"] = "https://dctfweb.cav.receita.fazenda.gov.br/AplicacoesWeb/DCTFWeb/Paginas/ResumoVinculacoes/VinculacoesCompleta.aspx"
                headers["Accept-Language"] = "pt-BR,pt;q=0.9"
                headers["Cookie"] = 'assinadoc_cert_type=A1; COOKIECAV='+new_cookie['COOKIECAV']+'; ASP.NET_SessionId='+new_cookie['ASP.NET_SessionId']+'; cw_dctfweb='+new_cookie['cw_dctfweb']

                response = requests.get(url, headers=headers)

                if (verifica_pdf_valido(response.text)):
                    upload_blob(response.content, CAMINHO_DONWLOAD)
                    return "https://storage.googleapis.com/cron-veri-files-br/" + CAMINHO_DONWLOAD
    else:
        return ''    

def executar():
    with requests.Session() as session:
        session.cookies.set('COOKIECAV', cookiecav)
        session.cookies.set('ASP.NET_SessionId', aspsession)

        url = "https://cav.receita.fazenda.gov.br/ecac/Aplicacao.aspx?id=10015&origem=menu"
        session.get(url)

        url = "https://dctfweb.cav.receita.fazenda.gov.br/aplicacoesweb/DCTFWeb/Default.aspx"
        session.get(url)

        new_cookie = session.cookies.get_dict()

        url = "https://dctfweb.cav.receita.fazenda.gov.br/AplicacoesWeb/DCTFWeb/Paginas/ListaDctfs.aspx"

        headers = CaseInsensitiveDict()
        headers["Connection"] = "keep-alive"
        headers["Upgrade-Insecure-Requests"] = "1"
        headers["User-Agent"] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36"
        headers["Accept"] = "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9"
        headers["Sec-Fetch-Site"] = "same-site"
        headers["Sec-Fetch-Mode"] = "navigate"
        headers["Sec-Fetch-Dest"] = "iframe"
        headers["sec-ch-ua"] = '" Not A;Brand";v="99", "Chromium";v="96", "Google Chrome";v="96"'
        headers["sec-ch-ua-mobile"] = "?0"
        headers["sec-ch-ua-platform"] = '"Windows"'
        headers["Referer"] = "https://cav.receita.fazenda.gov.br/"
        headers["Accept-Language"] = "pt-BR,pt;q=0.9"
        # pegar cookie da nova sessao 
        headers["Cookie"] = 'assinadoc_cert_type=A1; COOKIECAV='+new_cookie['COOKIECAV']+'; ASP.NET_SessionId='+new_cookie['ASP.NET_SessionId']+'; cw_dctfweb='+new_cookie['cw_dctfweb']

        response = requests.get(url, headers=headers)

        soup = BeautifulSoup(response.text, 'html.parser')

        ###
        headers["Connection"] = "keep-alive"
        headers["Cache-Control"] = "max-age=0"
        headers["sec-ch-ua"] = '" Not A;Brand";v="99", "Chromium";v="98", "Google Chrome";v="98"'
        headers["sec-ch-ua-mobile"] = "?0"
        headers["sec-ch-ua-platform"] = '"Windows"'
        headers["Upgrade-Insecure-Requests"] = "1"
        headers["Origin"] = "https://dctfweb.cav.receita.fazenda.gov.br"
        headers["Content-Type"] = "application/x-www-form-urlencoded"
        headers["Accept"] = "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9"
        headers["Sec-Fetch-Site"] = "same-origin"
        headers["Sec-Fetch-Mode"] = "navigate"
        headers["Sec-Fetch-User"] = "?1"
        headers["Sec-Fetch-Dest"] = "iframe"
        headers["Referer"] = "https://dctfweb.cav.receita.fazenda.gov.br/AplicacoesWeb/DCTFWeb/Paginas/ListaDctfs.aspx"
        headers["Accept-Language"] = "pt-BR,pt;q=0.9"

        data_atual = date.today()
        data_ano_passado = data_atual - timedelta(365)

        data = {
            'ctl00$cphConteudo$txtDataInicio': data_ano_passado.strftime('%d/%m/%Y'),
            'ctl00$cphConteudo$txtDataFinal': data_atual.strftime('%d/%m/%Y'),
            'ctl00$cphConteudo$ddlCategoriaDeclaracao': '0',
            'ctl00$cphConteudo$txtNumeroRecibo': '',
            'ctl00$cphConteudo$chkTodas': 'on',
            '__EVENTTARGET': 'ctl00$cphConteudo$btnFiltar',
            '__EVENTARGUMENT': '',
            '__VIEWSTATE': soup.find('input', {'id': '__VIEWSTATE'})['value'],
            '__VIEWSTATEGENERATOR': soup.find('input', {'id': '__VIEWSTATEGENERATOR'})['value'],
            '__EVENTVALIDATION': soup.find('input', {'id': '__EVENTVALIDATION'})['value']
        }
        response = requests.post(url, headers=headers, data=data)
        ###
        soup = BeautifulSoup(response.text, 'html.parser')
       
        caminho_pdf = gera_parcela(session, soup, new_cookie)    
        
        #se for diferente de vazio já finaliza a chamada retornando caminho do pdf
        if caminho_pdf != '': 
            return caminho_pdf
    
        ##verifica paginação
        try:
            paginas = soup.find('a', {'id':'ctl00_cphConteudo_tabelaListagemDctf_paginacaoListagemDctf_lnkNextPage'}).get('class')
            if paginas:
                existe_pagina = True
                while existe_pagina:
                    data = {
                        'ctl00$cphConteudo$txtDataInicio': data_ano_passado.strftime('%d/%m/%Y'),
                        'ctl00$cphConteudo$txtDataFinal': data_atual.strftime('%d/%m/%Y'),
                        'ctl00$cphConteudo$ddlCategoriaDeclaracao': '0',
                        'ctl00$cphConteudo$txtNumeroRecibo': '',
                        'ctl00$cphConteudo$chkTodas': 'on',
                        '__EVENTTARGET': 'ctl00$cphConteudo$tabelaListagemDctf$paginacaoListagemDctf$lnkNextPage',
                        '__EVENTARGUMENT': '',
                        '__VIEWSTATE': soup.find('input', {'id': '__VIEWSTATE'})['value'],
                        '__VIEWSTATEGENERATOR': soup.find('input', {'id': '__VIEWSTATEGENERATOR'})['value'],
                        '__EVENTVALIDATION': soup.find('input', {'id': '__EVENTVALIDATION'})['value']
                    }
                    response = requests.post(url, headers=headers, data=data)

                    soup = BeautifulSoup(response.text, 'html.parser')
                    caminho_pdf = gera_parcela(session, soup, new_cookie)    
                    #se for diferente de vazio já finaliza a chamada retornando caminho do pdf
                    if caminho_pdf != '':
                        return caminho_pdf

                    paginas = soup.find('a', {'id':'ctl00_cphConteudo_tabelaListagemDctf_paginacaoListagemDctf_lnkNextPage'}).get('class')
                    
                    # verifica se está na ultima pagina
                    if 'Disabled' in paginas:
                        existe_pagina = False
        except:
            pass

        session.close()
        return ''

cookiecav = sys.argv[1]
aspsession = sys.argv[2]
CAMINHO_DONWLOAD = sys.argv[3]
ID_DECLARACAO = sys.argv[4]
ID_CONTROLE = sys.argv[5]
print(executar())
from operator import truediv
import sys
from bs4.element import Tag
import requests
from requests.structures import CaseInsensitiveDict
import warnings
from bs4 import BeautifulSoup
import json
from datetime import date,timedelta

warnings.filterwarnings('ignore')


def buscar_comprovantes_de_arrecadacao(session):
    url = "https://cav.receita.fazenda.gov.br/ecac/Aplicacao.aspx?id=4&origem=pesquisa"
    session.get(url)

    url = "https://cav.receita.fazenda.gov.br/Servicos/ATFLA/PagtoWeb.app/Default.aspx"
    response = session.get(url)
    soup = BeautifulSoup(response.text, 'html.parser')

    data_atual = date.today()
    tres_anos_antes = data_atual - timedelta(1095)

    data = {
        '__EVENTTARGET': '',
        '__EVENTARGUMENT': '',
        '__LASTFOCUS': '',
        '__VIEWSTATE': soup.find('input', {'id': '__VIEWSTATE'})['value'],
        '__VIEWSTATEGENERATOR': soup.find('input', {'id': '__VIEWSTATEGENERATOR'})['value'],
        '__EVENTVALIDATION': soup.find('input', {'id': '__EVENTVALIDATION'})['value'],
        'campoTipoDocumento': 'DARF',
        'campoDataArrecadacaoInicial': tres_anos_antes.strftime('%d/%m/%Y'),
        'campoDataArrecadacaoFinal': data_atual.strftime('%d/%m/%Y'),
        'campoCodReceita': '',
        'campoNumeroDocumento': '',
        'campoValorInicial': '',
        'campoValorFinal': '',
        'botaoConsultar': 'Consultar'

    }
    url = "https://cav.receita.fazenda.gov.br/Servicos/ATFLA/PagtoWeb.app/Default.aspx"
    session.post(url, data=data)

    url = "https://cav.receita.fazenda.gov.br/Servicos/ATFLA/PagtoWeb.app/PagtoWebList.aspx"
    response = session.get(url)
    soup = BeautifulSoup(response.text, 'html.parser')

    table = soup.find('table', {'id':'listagemDARF'})

    rows = table.find_all('tr')
    del rows[0]
    comprovantes = []
    for row in rows:
        comprovante = {
            'perido_apuracao': row.find_all('td')[4].getText().strip()[3:],
            'valor_total':row.find_all('td')[9].getText().strip()
        }
        comprovantes.append(comprovante)

    return comprovantes

def verifica_status(periodo_apuracao, saldo_a_pagar, comprovantes_de_arrecadacao):
    comprovante_consulta = {
        'perido_apuracao': periodo_apuracao,
        'valor_total': saldo_a_pagar
    }

    if comprovante_consulta in comprovantes_de_arrecadacao:
        return 1
    else:
        return 0

def buscar_detalhes(id, soup, session):
    data = {
        '__EVENTTARGET': 'ctl00$cphConteudo$tabelaListagemDctf$GridViewDctfs$ctl'+id+'$lbkVisualizarDctf',
        '__EVENTARGUMENT': '',
        '__VIEWSTATE': soup.find('input', {'id': '__VIEWSTATE'})['value'],
        '__VIEWSTATEGENERATOR': soup.find('input', {'id': '__VIEWSTATEGENERATOR'})['value'],
        '__EVENTVALIDATION': soup.find('input', {'id': '__EVENTVALIDATION'})['value'],
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

    response = session.post(url, headers=headers, data=data, cert=certificado, verify=False)

    soup = BeautifulSoup(response.text, 'html.parser')

    detalhes = []

    vinculacoes_empresa = soup.find_all(
        'tr', {'class': 'row-table-vinculacoes-empresa'})

    nivel = {
        '1': 0,
        '2': 0,
        '3': 0
    }

    for vinculacao in vinculacoes_empresa:
        nivel['1'] += 1
        detalhes.append(pegar_dados(str(nivel['1']-1), nivel, soup))
        linhas = vinculacao.td.img['filhos'].split(',')
        sub_niveis = vinculacao.td.img['filhosimediatos'].split(',')

        for i in linhas:
            nivel['3'] += 1
            if i in sub_niveis:
                nivel['2'] += 1
                nivel['3'] = 0
            detalhes.append(pegar_dados(i, nivel, soup))

    return detalhes


def pegar_dados(i, nivel, dados):
    nivel = str(nivel['1'])+'.'+str(nivel['2'])+'.'+str(nivel['3'])
    tributo = dados.find('span', {'id': 'cphConteudo_TabelaVinculacoesDARF_GridViewVinculacoes_lblTributos_'+i}).getText().strip()
    pa_debito = dados.find('span', {'id': 'cphConteudo_TabelaVinculacoesDARF_GridViewVinculacoes_lblPaDebito_'+i}).getText().strip()
    debito_apurado = dados.find('span', {'id': 'cphConteudo_TabelaVinculacoesDARF_GridViewVinculacoes_lblValorApurado_'+i}).getText().strip()
    try:
        credito_vinculado = dados.find('span', {'id': 'cphConteudo_TabelaVinculacoesDARF_GridViewVinculacoes_lblValorSalarioMaternidade_'+i}).getText().strip()
    except:
        credito_vinculado = ''
    saldo_a_pagar = dados.find('span', {'id': 'cphConteudo_TabelaVinculacoesDARF_GridViewVinculacoes_lblValorSaldoPagar_'+i}).getText().strip()

    return {
        'nivel': nivel,
        'tributo': tributo,
        'pa_debito': pa_debito,
        'debito_apurado': debito_apurado,
        'credito_vinculado': credito_vinculado,
        'saldo_a_pagar': saldo_a_pagar
    }

def get_declaracoes():
    print('teste')

def generatedHTML(html):
    with open("debug.html", 'wb') as file:
        file.write(html)

def executar():
    with requests.Session() as session:
        session.cookies.set('COOKIECAV', cookiecav)
        session.cookies.set('ASP.NET_SessionId', aspsession)

        # buscar comprovantes de arrecadação para verificar se já foi pago
        # comprovantes_de_arrecadacao = buscar_comprovantes_de_arrecadacao(session)
        # --------------------------------
        declaracoes = []

        url = "https://cav.receita.fazenda.gov.br/ecac/Aplicacao.aspx?id=10015&origem=menu"
        session.get(url, verify=False)

        url = "https://dctfweb.cav.receita.fazenda.gov.br/aplicacoesweb/DCTFWeb/Default.aspx"
        session.get(url, verify=False)
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
        # headers["Cookie"] = 'assinadoc_cert_type=A1; COOKIECAV='+new_cookie['COOKIECAV'] + \
        #     '; ASP.NET_SessionId=' + \
        #     new_cookie['ASP.NET_SessionId'] + \
        #     '; cw_dctfweb=1.4'

        response = session.get(url, headers=headers, cert=certificado, verify=False)
        # print(response.text)
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
        response = session.post(url, headers=headers, data=data, cert=certificado, verify=False)
        ###

        soup = BeautifulSoup(response.text, 'html.parser')

        td_id_declaracoes = soup.find_all(
            'td', {'class': 'coluna-checkbox-debito-pagar'})

        table = soup.find(
            'table', {'id': 'ctl00_cphConteudo_tabelaListagemDctf_GridViewDctfs'})     

        if table:
            trs = table.find_all('tr')
            numero_linhas = int(len(trs)/3)
            for id in range(1, numero_linhas):
                situacao = table.find('span', {'id': 'ctl00_cphConteudo_tabelaListagemDctf_GridViewDctfs_ctl'+str(id+1).zfill(2)+'_lblSituacao'}).getText().strip()
                periodo_apuracao = table.find('span', {'id': 'ctl00_cphConteudo_tabelaListagemDctf_GridViewDctfs_ctl'+str(id+1).zfill(2)+'_lblPeriodoApuracao'}).getText().strip()
                data_transmissao = table.find('span', {'id': 'ctl00_cphConteudo_tabelaListagemDctf_GridViewDctfs_ctl'+str(id+1).zfill(2)+'_lblDataTransmissao'}).getText().strip()
                categoria = table.find('span', {'id': 'ctl00_cphConteudo_tabelaListagemDctf_GridViewDctfs_ctl'+str(id+1).zfill(2)+'_lblCategoria'}).getText().strip()
                origem = table.find('span', {'id': 'ctl00_cphConteudo_tabelaListagemDctf_GridViewDctfs_ctl'+str(id+1).zfill(2)+'_lblOrigem'}).getText().strip()
                tipo = table.find('span', {'id': 'ctl00_cphConteudo_tabelaListagemDctf_GridViewDctfs_ctl'+str(id+1).zfill(2)+'_lblTipo'}).getText().strip()
                debito_apurado = table.find('span', {'id': 'ctl00_cphConteudo_tabelaListagemDctf_GridViewDctfs_ctl'+str(id+1).zfill(2)+'_lblValorApurado'}).getText().strip()
                saldo_a_pagar = table.find('span', {'id': 'ctl00_cphConteudo_tabelaListagemDctf_GridViewDctfs_ctl'+str(id+1).zfill(2)+'_lblSaldoPagar'}).getText().strip()

                # print(situacao)
                if situacao == 'Ativa':
                    # quando ativa pode gerar darf
                    if td_id_declaracoes[id].find('span') is not None:
                        id_declaracoes = td_id_declaracoes[id].find('span').get('iddeclaracao').split(';')
                        detalhes = buscar_detalhes(str(id+1).zfill(2), soup, session)
                    else:
                        id_declaracoes = ''
                        detalhes = ''
                        id_declaracoes = ['', '']
                    status = ''
                else:
                    id_declaracoes = ['', '']
                    detalhes = ''
                    status =  ''

                declaracao = {
                    'id_declaracao': id_declaracoes[0][2:].strip(),
                    'id_controle': id_declaracoes[1][2:].strip(),
                    'periodo_apuracao': periodo_apuracao,
                    'data_transmissao': data_transmissao,
                    'categoria': categoria,
                    'origem': origem,
                    'tipo': tipo,
                    'situacao': situacao,
                    'debito_apurado': debito_apurado,
                    'saldo_a_pagar': saldo_a_pagar,
                    'status': status,
                    'detalhes': detalhes
                }
                declaracoes.append(declaracao)
            
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
                        response = requests.post(url, headers=headers, data=data, cert=certificado, verify=False)

                        soup = BeautifulSoup(response.text, 'html.parser')

                        td_id_declaracoes = soup.find_all(
                            'td', {'class': 'coluna-checkbox-debito-pagar'})

                        table = soup.find(
                            'table', {'id': 'ctl00_cphConteudo_tabelaListagemDctf_GridViewDctfs'})     

                        if table:
                            trs = table.find_all('tr')
                            numero_linhas = int(len(trs)/3)
                            for id in range(1, numero_linhas):
                                situacao = table.find('span', {'id': 'ctl00_cphConteudo_tabelaListagemDctf_GridViewDctfs_ctl'+str(id+1).zfill(2)+'_lblSituacao'}).getText().strip()
                                periodo_apuracao = table.find('span', {'id': 'ctl00_cphConteudo_tabelaListagemDctf_GridViewDctfs_ctl'+str(id+1).zfill(2)+'_lblPeriodoApuracao'}).getText().strip()
                                data_transmissao = table.find('span', {'id': 'ctl00_cphConteudo_tabelaListagemDctf_GridViewDctfs_ctl'+str(id+1).zfill(2)+'_lblDataTransmissao'}).getText().strip()
                                categoria = table.find('span', {'id': 'ctl00_cphConteudo_tabelaListagemDctf_GridViewDctfs_ctl'+str(id+1).zfill(2)+'_lblCategoria'}).getText().strip()
                                origem = table.find('span', {'id': 'ctl00_cphConteudo_tabelaListagemDctf_GridViewDctfs_ctl'+str(id+1).zfill(2)+'_lblOrigem'}).getText().strip()
                                tipo = table.find('span', {'id': 'ctl00_cphConteudo_tabelaListagemDctf_GridViewDctfs_ctl'+str(id+1).zfill(2)+'_lblTipo'}).getText().strip()
                                debito_apurado = table.find('span', {'id': 'ctl00_cphConteudo_tabelaListagemDctf_GridViewDctfs_ctl'+str(id+1).zfill(2)+'_lblValorApurado'}).getText().strip()
                                saldo_a_pagar = table.find('span', {'id': 'ctl00_cphConteudo_tabelaListagemDctf_GridViewDctfs_ctl'+str(id+1).zfill(2)+'_lblSaldoPagar'}).getText().strip()

                                if situacao == 'Ativa':
                                    # quando ativa pode gerar darf
                                    id_declaracoes = td_id_declaracoes[id].find('span').get('iddeclaracao').split(';')
                                    detalhes = buscar_detalhes(str(id+1).zfill(2), soup, session)
                                    status = ''
                                else:
                                    id_declaracoes = ['', '']
                                    detalhes = ''
                                    status =  ''

                                declaracao = {
                                    'id_declaracao': id_declaracoes[0][2:].strip(),
                                    'id_controle': id_declaracoes[1][2:].strip(),
                                    'periodo_apuracao': periodo_apuracao,
                                    'data_transmissao': data_transmissao,
                                    'categoria': categoria,
                                    'origem': origem,
                                    'tipo': tipo,
                                    'situacao': situacao,
                                    'debito_apurado': debito_apurado,
                                    'saldo_a_pagar': saldo_a_pagar,
                                    'status': status,
                                    'detalhes': detalhes
                                }
                                declaracoes.append(declaracao)

                    paginas = soup.find('a', {'id':'ctl00_cphConteudo_tabelaListagemDctf_paginacaoListagemDctf_lnkNextPage'}).get('class')
                    if 'Disabled' in paginas:
                        existe_pagina = False
            except:
                pass

            session.close()

            return(json.dumps(declaracoes))

        return ''


cookiecav = sys.argv[1]
aspsession = sys.argv[2]
certificado = sys.argv[3]
print(executar())

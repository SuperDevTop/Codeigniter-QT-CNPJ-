import sys
import requests
import warnings
from bs4 import BeautifulSoup
import json
import random

warnings.filterwarnings('ignore')

def executar():
    
    cookiecav = sys.argv[1]
    aspsession = sys.argv[2]
    cnpj = sys.argv[3]

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

        demonstrativos = []
        request = session.get('https://www4.cav.receita.fazenda.gov.br/Servicos/ATSPO/paexlei12996.app/MenuLei12996.aspx')

        request = session.get('https://www4.cav.receita.fazenda.gov.br/Servicos/ATSPO/paexlei12996.app/Lei12996/Demonstrativo/Demonstrativo.aspx')
        soup = BeautifulSoup(request.text, 'html.parser')
        if soup.find(id = "lblNi"):
            cnpj_formatado = soup.find(id = "lblNi").getText()
            nome_empresarial= soup.find(id= "lblNiNome").getText()

            request = session.post('https://www4.cav.receita.fazenda.gov.br/Servicos/ATSPO/paexlei12996.app/Ajaj.aspx', data= {'act': 'InfoNiModalidades', 'dt': '{"TipoNi":"1","Ni":"'+cnpj+'"}'})

            soup = BeautifulSoup(request.text, 'html.parser')

            demonstrativos_json = json.loads(soup.text)

            municipio = demonstrativos_json['Contribuinte']['NomeMunicipio']

            for modalidade in demonstrativos_json['Modalidades']:
                if(modalidade['NomeSituacao'] == 'Em Parcelamento'):

                    demonstrativo = {}

                    demonstrativo['cnpj_formatado'] = cnpj_formatado
                    demonstrativo['nome_empresarial']  = nome_empresarial
                    demonstrativo['municipio'] = municipio
                    demonstrativo['data_adesao'] = modalidade['DataAdesao']
                    demonstrativo['data_validacao'] = modalidade['DataValidacao']
                    demonstrativo['data_negociacao'] = modalidade['DataNegociacao']
                    demonstrativo['data_efeito_exclusao'] = modalidade['DataEfeitoExclusao']
                    demonstrativo['data_ciencia'] = modalidade['DataCiencia']
                    demonstrativo['data_encerramento'] = modalidade['DataEncerramento']
                    demonstrativo['data_liquidacao_divida'] = modalidade['DataLiquidacaoDivida']
                    demonstrativo['data_exclusao'] = modalidade['DataExclusao']
                    demonstrativo['codigo_motivo_exclusao'] = modalidade['CodigoMotivoExclusao']
                    demonstrativo['in_solicitacao_reativacao'] = modalidade['InSolicitacaoReativacao']
                    demonstrativo['cod_fase'] = modalidade['CodFase']
                    demonstrativo['cod_modalidade'] = modalidade['CodModalidade']
                    demonstrativo['cod_situacao'] = modalidade['CodSituacao']
                    demonstrativo['nome_modalidade'] = modalidade['NomeModalidade']
                    demonstrativo['nome_situacao'] = modalidade['NomeSituacao']

                    post_data = {
                        'act': 'ExtratoDivida', 
                        'dt': '{"Tipo":"1","Ni":"'+cnpj+'","Parcelamento":"'+modalidade['CodModalidade']+'","Msg":"","Operacao":""}'
                    }
                    request = session.post('https://www4.cav.receita.fazenda.gov.br/Servicos/ATSPO/paexlei12996.app/AjajNegociacaoLei12996.aspx', data = post_data)
                    soup = BeautifulSoup(request.text, 'html.parser')
                    extrato_divida_json = json.loads(soup.text)
                    
                    post_data = {
                        'act': 'DemonstrativoPrestacoes', 
                        'dt': '{"Tipo":"1","Ni":"'+cnpj+'","Parcelamento":"'+modalidade['CodModalidade']+'","Msg":"","Operacao":""}'
                    }
                    request = session.post('https://www4.cav.receita.fazenda.gov.br/Servicos/ATSPO/paexlei12996.app/AjajNegociacaoLei12996.aspx', data= post_data)
                    soup = BeautifulSoup(request.text, 'html.parser')
                    demonstrativo_prestacoes_json = json.loads(soup.text)

                    demonstrativo['cod_receita'] = demonstrativo_prestacoes_json['Divida']['Codigo_receita']
                    demonstrativo['proximo_dia_util'] = demonstrativo_prestacoes_json['Proximo_Dia_Util']
                    parcelas = []
                    for p in demonstrativo_prestacoes_json['Parcelas']:
                        parcela = {}
                        parcela['parcela_id'] = p['Parcela_Id']
                        parcela['data_parcela'] = p['Data_Parcela']
                        parcela['valor_parc_minima'] = p['Valor_Parc_Minima']
                        parcela['valor_parcela_divida'] = p['Valor_Parcela_Divida']
                        parcela['valor_parc_calculada'] = p['Valor_Parc_Calculada']
                        parcela['saldo_parc_devedora'] = p['Saldo_Parc_Devedora']
                        parcela['juros_parc_deverdora'] = p['Juros_Parc_Deverdora']
                        parcela['indicador_parcela_devida'] = p['Indicador_Parcela_Devida']
                        parcela['indicador_situacao_parcela'] = p['Indicador_Situacao_Parcela']
                        parcela['indicador_reducao'] = p['Indicador_Reducao']
                        parcela['valor_total_arrecadacao'] = p['Valor_Total_Arrecadacao']
                        parcela['valor_reducao_mes'] = p['Valor_Reducao_Mes']
                        parcela['valor_antecipacao_mes'] = p['Valor_Antecipacao_Mes']
                        parcela['quantidade_parc_red'] = p['Quantidade_Parc_Red']
                        parcelas.append(parcela)
                    demonstrativo['parcelas'] = parcelas
                    
                    demonstrativos.append(demonstrativo)


        session.close()

        return (json.dumps(demonstrativos))

print(executar())
# executar()

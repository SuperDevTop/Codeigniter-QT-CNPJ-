import sys
import requests
import warnings
warnings.filterwarnings('ignore')

certKey = sys.argv[1]
priKey = sys.argv[2]
cnpj_procuracao = sys.argv[3]

try:
    with requests.Session() as session:
        request = session.get('https://certificado.sso.acesso.gov.br/authorize?response_type=code&client_id=cav.receita.fazenda.gov.br&scope=openid+govbr_recupera_certificadox509+govbr_confiabilidades&redirect_uri=https%3A%2F%2Fcav.receita.fazenda.gov.br%2Fautenticacao%2Flogin%2Fgovbrsso',
              cert = (certKey, priKey))
        headers = {'Content-Type': 'application/x-www-form-urlencoded',
                   'Host': 'sinac.cav.receita.fazenda.gov.br',
                   'Origin': 'https://sinac.cav.receita.fazenda.gov.br',
                   'Referer': 'https://cav.receita.fazenda.gov.br/ecac/',
                   'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.61 Safari/537.36'}


        request = session.post('https://cav.receita.fazenda.gov.br/autenticacao/api/mudarpapel/procuradorpj', data='=' + cnpj_procuracao, headers=headers)
        request = session.get('https://cav.receita.fazenda.gov.br/ecac/')
        request = session.get('https://www3.cav.receita.fazenda.gov.br/eprocessocontribuinte/api/processos?solidario=false')

        print(request.text)
except:
    print('')
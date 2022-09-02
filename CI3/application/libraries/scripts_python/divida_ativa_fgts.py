import sys
import requests
from furl import furl
from selenium import webdriver
import json
import warnings
import time
warnings.filterwarnings('ignore')

try:
    VIEWSTATE = sys.argv[1]
    VIEWSTATEGENERATOR = sys.argv[2]
    EVENTVALIDATION = sys.argv[3]
    mensagemComBase64 = sys.argv[4]
    pathDriver = sys.argv[5]

    payload = {
        '__VIEWSTATE': VIEWSTATE,
        '__VIEWSTATEGENERATOR': VIEWSTATEGENERATOR,
        '__EVENTVALIDATION': EVENTVALIDATION,
        'mensagemComBase64': mensagemComBase64
    }

    with requests.Session() as currentSession:
        retornoPost = currentSession.post('https://www2.pgfn.fazenda.gov.br/ecac/contribuinte/loginEcacReceita.jsf', data=payload)
        urlLogin = retornoPost.url
        driver = webdriver.PhantomJS(executable_path=pathDriver)
        driver.get(urlLogin)
        local = driver.execute_script("return window.localStorage.getItem('currentUser');")
        jsonParsed = json.loads(local)
        driver.set_script_timeout(-1)
        driver.set_page_load_timeout(-1)
        driver.get('https://www2.pgfn.fazenda.gov.br/ecac/contribuinte/loginJwt.jsf?fn=consultaDebitos&token=' + jsonParsed.get('token'))
        driver.find_element_by_xpath("/html/body/div[2]/table/tbody/tr[1]/td/form/table/tbody/tr/td[6]/table").click()
        driver.find_element_by_id("fgtsForm:consultarFgtsButton").click()

        contents = driver.execute_script("return document.documentElement.innerHTML;")
        print(contents)
    driver.close()
    driver.quit()
except:
  print("ERRO")

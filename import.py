import sys
import json
import pickle

from googleapiclient import discovery
import gspread
import httplib2
from oauth2client.service_account import ServiceAccountCredentials
from sklearn import tree
from sklearn.tree import export_graphviz


def connection_to_API(idTable, idSheet):
    CREDENTIALS_FILE = 'python-276507.json'  # Имя файла с закрытым ключом, вы должны подставить свое

    # Читаем ключи из файла
    credentials = ServiceAccountCredentials.from_json_keyfile_name(CREDENTIALS_FILE, ['https://www.googleapis.com/auth/spreadsheets', 'https://www.googleapis.com/auth/drive'])
    httpAuth = credentials.authorize(httplib2.Http()) # Авторизуемся в системе

    service = discovery.build('sheets', 'v4', http = httpAuth) # Выбираем работу с таблицами и 4 версию API 
    spreadsheetId = idTable #'1Q5bw0D9y3_WfyhFhimr7_GgJZxxUNSMltOEW7WAFsuo'

    # Получаем список листов, их Id и название
    spreadsheet = service.spreadsheets().get(spreadsheetId = spreadsheetId).execute()
    sheetList = spreadsheet.get('sheets')
    flag = False
    for sheet in sheetList:
        if sheet['properties']['sheetId'] == idSheet:
            flag = True
            sheet_name = sheet['properties']['title']
    if flag:
        return service, sheet_name
    else:
        return None

def get_urls(service, spreadsheetId, name, cells):
    ranges = [name+"!"+cells]
    results = service.spreadsheets().values().batchGet(spreadsheetId = spreadsheetId, 
                                        ranges = ranges, 
                                        valueRenderOption = 'FORMATTED_VALUE',  
                                        dateTimeRenderOption = 'FORMATTED_STRING').execute() 
    sheet_values = results['valueRanges'][0]['values']
    urls = []
    for i in sheet_values:
        for j in i:
            urls.append([j])
    return urls

    

def main(array):
    print(type(array), array, array.append(1))

if __name__ == '__main__':
    main(sys.argv[1])

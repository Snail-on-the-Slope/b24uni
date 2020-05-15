import sys
import json
import pickle
import re

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
    table_data = []
    for cell in cells:
        x = ''
        quantity = list(map(int, re.findall(r'\d+',cell)))
        for item in re.findall(r'\w+',cell):
            x+=item
        if len(quantity)!=len(re.findall(r'\D+', x)):
            return "Error cells "+cell

        ranges = [name+"!"+cell]
        results = service.spreadsheets().values().batchGet(spreadsheetId = spreadsheetId, 
                                            ranges = ranges, 
                                            valueRenderOption = 'FORMATTED_VALUE',  
                                            dateTimeRenderOption = 'FORMATTED_STRING').execute() 
        sheet_values = results['valueRanges'][0]['values']
        item_table = []
        for i in sheet_values:
            if i==[]:
                item_table.append('')
            elif len(i)>1:
                temp = ''
                for j in i:
                    temp+=j+' '
                item_table.append(temp[:-1])
            else:
                item_table.extend(i)

        if len(item_table) < (quantity[1]-quantity[0]+1):
            item_table.extend(['']*(quantity[1]-quantity[0]+1-len(item_table)))

        table_data.append(item_table)

    leng = len(table_data[0])
    for i in table_data:
        if len(i) != leng:
            print(i, leng)
            return "Error list cells building"

    if len(table_data)>1:
        table_data = list(map(list, zip(*table_data)))
    return table_data

def main(array):
    data = json.loads(array)
    if len(data)%2==1:
        print('Error data')
        return

    idTable = data[0]
    idSheet = data[1]
    cells = []
    for i in range(len(data)-2):
        if i % 2 == 1:
            cells.append(data[i+2])
    
    service, name = connection_to_API(idTable, int(idSheet))
    if service==None:
        print('Error connection to Google Sheets Table ')
        return

    table_data = get_urls(service, idTable, name, cells)
    if isinstance(table_data, str):
        print(table_data)
        return
    print(json.dumps(table_data))

    #print('OKK')
    #print(json.dumps(cells))

if __name__ == '__main__':
    main(sys.argv[1])

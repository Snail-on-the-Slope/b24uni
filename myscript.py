import sys
import httplib2
import gspread
import apiclient.discovery
from oauth2client.service_account import ServiceAccountCredentials
import json
import re
import pickle
from sklearn import tree
from sklearn.tree import export_graphviz
import parser1

def connection_to_API(idTable, idSheet):
    CREDENTIALS_FILE = 'python-276507.json'  # Имя файла с закрытым ключом, вы должны подставить свое

    # Читаем ключи из файла
    credentials = ServiceAccountCredentials.from_json_keyfile_name(CREDENTIALS_FILE, ['https://www.googleapis.com/auth/spreadsheets', 'https://www.googleapis.com/auth/drive'])
    httpAuth = credentials.authorize(httplib2.Http()) # Авторизуемся в системе

    service = apiclient.discovery.build('sheets', 'v4', http = httpAuth) # Выбираем работу с таблицами и 4 версию API 
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
    id_cell = re.findall(r'\d+',cells)[0]
    for i in sheet_values:
        for j in i:
            urls.append([len(urls)+id_cell,j])
    return urls, id_cell

def get_data():
    try:
        with open('data_parser2.json', encoding="utf8") as json_file:
            data_from_json = json.load(json_file)
            threads = [[site["num"], site["url"], site["inn"], site["ogrn"],
                        site["name"], site["address"], site["gain"], 
                        site["worktime"], site["count_of_employees"],
                        site["activity"], site["ml"]] for site in data_from_json]
            data_ml = [[site["ml"].split()] for site in data_from_json]
            return threads, data_ml
    except FileNotFoundError:
        print("File data_parser2.json doesn't exist")
        return None

def forecasting(data):
    filename = 'model.sav'
    loaded_model = pickle.load(open(filename, 'rb'))
    result = []
    for i in data:
            result.append(loaded_model.predict(i)[0])
    return result

def prepare_data(data):
    for i in data:
        for j in i:
            for item in j:
                if item.find('.')!=-1:
                    item = float(item)
                else:
                    item = int(item)

def model_ml(data):
    prepare_data(data)
    return forecasting(data)

def filling_table(service,spreadsheetId,name,print_list,id_cell):
    results = service.spreadsheets().values().batchUpdate(spreadsheetId = spreadsheetId, body = {
        "valueInputOption": "USER_ENTERED", # Данные воспринимаются, как вводимые пользователем (считается значение формул)
        "data": [
            {"range": name+"!T"+id_cell[0]+":AA"+id_cell[1],
            "majorDimension": "ROWS",     # Сначала заполнять строки, затем столбцы
            "values": print_list}
        ]
    }).execute()

def uploading_data_to_table(service, spreadsheetId, name, urls, id_cell):
    data, data_ml = get_data()
    classified = model_ml(data_ml)

    def index_item(i):
        for item in range(len(data)):
            if data[item][0]==i:
                return [data[item][5],9,data[item][6],data[item][4],
                        data[item][7],data[item][8],data[item][9],classified[item]]
        return ['Нет информации на сайте',0,'','','','','','']

    print_list = []
    for i in range(len(urls)):
        print_list.append(index_item(i+id_cell[0]))

    results = service.spreadsheets().values().batchUpdate(spreadsheetId = spreadsheetId, body = {
        "valueInputOption": "USER_ENTERED",
        "data": [
            {"range": name+"!T2:AA2",
            "majorDimension": "ROWS",
            "values": [['Комментарий','Результат','Оборот','Предприятие','Возраст компании','Количество сотрудников','Вид деятельности','Категория']]}
        ]
    }).execute()

    filling_table(service,spreadsheetId,name,print_list,id_cell)

    

def main(idTable, idSheet, cells):
    result = []
    result.append(connection_to_API(idTable, idSheet))
    if len(result) == 1:
        print('Error connection to Google Sheets Table')
        return
    urls, id_cell = get_urls(result[0], idTable, result[1], cells)
    print(len(urls))
    #parser1.main(urls)
    #uploading_data_to_table(result[0], idTable, result[1], urls, id_cell)

if __name__ == '__main__':
    main(sys.argv[1], sys.argv[2], sys.argv[3])
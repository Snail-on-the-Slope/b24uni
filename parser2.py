import json
import threading
from bs4 import BeautifulSoup
import requests
import re
import string
import csv
import pandas as pd 

n1_10 = (2, 4, 10, 3, 5, 9, 4, 6, 8, 0)
n1_12 = (7, 2, 4, 10, 3, 5, 9, 4, 6, 8, 0)
n2_12 = (3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8, 0)
activ_type = ['производство','торговля','сми','услуги','строительство','нко']

headers = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 '
                  'YaBrowser/19.12.4.25 Yowser/2.5 Safari/537.36'}


def validate_inn(inn):
    """
    Проверка корректности ИНН по контрольной(-ым) сумме(-ам)
    :param inn: ИНН или null
    :return: boolean
    """
    if not inn:
        return
    if len(inn) == 10:
        n1 = sum(int(digit) * n1_10[index] for (index, digit) in enumerate(inn)) % 11
        if n1 > 9:
            n1 = 0
        if n1 == int(inn[9]):
            return True
    elif len(inn) == 12:
        n1 = sum(int(inn[index]) * n1_12[index] for index in range(11)) % 11
        if n1 > 9:
            n1 = 0
        n2 = sum(int(digit) * n2_12[index] for (index, digit) in enumerate(inn)) % 11
        if n2 > 9:
            n2 = 0
        if n1 == int(inn[10]) and n2 == int(inn[11]):
            return True
    return False


class MyThread(threading.Thread):
    global data_to_dump

    def __init__(self, num, url, inn, ogrn):
        threading.Thread.__init__(self)
        self.num = num
        self.url = url
        self.inn = inn
        self.ogrn = ogrn
        self.company_name = None
        self.address = None
        self.gain = None
        self.work_time = None
        self.count_of_employees = None
        self.activity = None
        self.gainml = None
        self.work_timeml = None
        self.count_of_employeesml = None
        self.activityml = None

    def run(self):
        """Запрос на сайт sbis.ru, получение нужной информации и сохранение в data_to_dump"""
        try:
            if not validate_inn(self.inn):
                return
            info_url = "https://sbis.ru/contragents/" + self.inn
            response = requests.get(info_url, headers=headers)
            response.encoding = 'utf-8'
            soup = BeautifulSoup(response.text, 'html.parser')
            self.find_company_name(soup)
            self.find_address(soup)
            self.find_gain(soup)
            self.find_work_time(soup)
            self.find_count_of_employees(soup)
            self.find_activity(soup)
            self.save_to_json()
        except Exception as e:
            print(f'Exception {e} url {"https://sbis.ru/contragents/" + self.inn}')
            print(self.num)

    def find_gain(self, soup):
        """Поиск информации о выручке"""
        main_div = soup.find('div', 'cCard__Contacts-Revenue-Desktop cCard__Main-Grid-Element')
        if main_div:
            gain_div = next(main_div.children)
            *_, current_span = gain_div.children
            gain = current_span.text
            if gain:
                self.gain = gain.strip()
        self.for_gain()

    def find_work_time(self, soup):
        """Поиск информации о 'возрасте'"""
        div = soup.find('div', ['cCard__MainReq-Status', 'cCard__Status-Value'])
        if div:
            self.work_time = div.text.strip()
        self.for_work_time()

    def find_count_of_employees(self, soup):
        """Поиск информации о количестве сотрудников"""
        span = soup.find('span', 'cCard__EmployeeResult')
        if span:
            self.count_of_employees = span.text.strip()
        self.for_count_of_employees()

    def find_company_name(self, soup):
        """Поиск информации о предприятии"""
        div = soup.find('div', 'cCard__MainReq-Name') 
        if div:
            self.company_name = div.text.strip()

    def find_address(self, soup):
        """Поиск информации о предприятии"""
        div = soup.find('div', 'cCard__Contacts-Address') 
        if div:
            self.address = div.text.strip()

    def find_activity(self, soup):
        """Поиск информации о виде деятельности"""
        div = soup.find('div', 'cCard__OKVED-Name')
        if div:
            self.activity = div.text.strip()
        self.for_activity()


    def for_gain(self):
        if self.gain == None:
            self.gain = 'н/д'
            self.gainml = 0
        else:
            if self.gain.find('тыс') != -1:
                x = 10**3
            if self.gain.find('млн') != -1:
                x = 10**6
            if self.gain.find('млрд') != -1:
                x = 10**9
            self.gain = re.findall(r'\d+',self.gain)
            if len(self.gain) == 1:
                self.gain = int(self.gain[0])*x
                self.gainml = self.gain/(10**6)
            else:
                temp = self.gain[0]+'.'+self.gain[1]
                self.gain = int(float(temp)*x)
                self.gainml = self.gain/(10**6)
        
    def for_count_of_employees(self):
        if self.count_of_employees == None:
            self.count_of_employees = 'н/д'
            self.count_of_employeesml = 0
        else:
            self.count_of_employees = int(re.findall(r'\d+', self.count_of_employees)[0])
            self.count_of_employeesml = self.count_of_employees

    def for_work_time(self):
        if self.work_time == None:
            self.work_time = 'н/д'
            self.work_timeml = 0
        else:
            temp = re.findall(r'\d+',self.work_time)
            if len(temp)==1:
                self.work_time = int(temp[0])
                self.work_timeml = self.work_time
            elif self.work_time.find('полутора') == 0:
                self.work_time =  1.5
                self.work_timeml = 1.5
            else:
                self.work_time = 'не действует'
                self.work_timeml = 0
        

    def for_activity(self):
        if self.activity == None:
            self.activity = 'н/д'
            self.activityml = 0
        else:
            self.activity = re.findall(r'\w+', self.activity)[0].lower()
            try:
                self.activityml = activ_type.index(self.activity)
            except:
                activ_type.append(self.activity)
                self.activityml = activ_type.index(self.activity)

    def save_to_json(self):
        """Сохраненение информации (если добавилась какая-то новая) в список, который потом сохраняется в JSON"""
        if any((self.gain, self.work_time, self.count_of_employees, self.activity)):
            ml = str(self.activityml) + ' ' + str(self.gainml) + ' ' + str(self.work_timeml) + ' ' + str(self.count_of_employeesml)
            #act = ''
            #for i in activ_type:
            #    act+= i+' '
            #act = act[:-1]
            data_to_dump.append({
                'num': self.num,
                'url': self.url,
                'inn': self.inn,
                'ogrn': self.ogrn,
                'name': self.company_name,
                'address': self.address,
                'gain': self.gain,
                'worktime': self.work_time,
                'count_of_employees': self.count_of_employees,
                'activity': self.activity,
                'ml': ml,
                #'activity_type': act
            })


threads = []
data_to_dump = []

def get_data():
    global threads
    try:
        with open('data_parser1.json') as json_file:
            data_from_json = json.load(json_file)
            threads = [MyThread(site["num"], site["url"], site["inn"], site["ogrn"]) for site in data_from_json]
            return True
    except FileNotFoundError:
        print("File data_parser1.json doesn't exist")
        return None


def main():
    if not get_data():
        return
    if threads:
        iterator = 0
        offset = 0
        once = 100
        while iterator < len(threads):
            for iterator in range(offset, offset + once):
                if iterator < len(threads):
                    threads[iterator].start()
            for iterator in range(offset, offset + once):
                if iterator < len(threads):
                    threads[iterator].join()
            offset += once

    with open('data_parser2.json', 'w', encoding='utf-8') as outfile:
        json.dump(data_to_dump, outfile, ensure_ascii=False, indent=4)

if __name__ == '__main__':
    main()

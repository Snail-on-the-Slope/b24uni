import threading
import time
import openpyxl
import requests
from bs4 import BeautifulSoup
import re
import json
import parser2

headers = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 '
                  'YaBrowser/19.12.4.25 Yowser/2.5 Safari/537.36'}
contacts = ("контакты", "наши контакты", "контактная информация")
data_to_save = []
threads = []


class MyThread(threading.Thread):
    global data_to_save

    def __init__(self, url, num):
        threading.Thread.__init__(self)
        self.num=num
        self.url = url
        if not ("http" in url):
            self.url = "http://" + self.url
        self.contacts_url = None
        self.inn = None
        self.ogrn = None

    def run(self):
        try:
            response = requests.get(self.url, headers=headers)
            if response.status_code not in (200, 301, 101):
                return
            response.encoding = 'utf-8'
            soup = BeautifulSoup(response.text, 'html.parser')
            result = soup.find(
                lambda tag: tag.name == "a" and any(contact in tag.text.lower().strip() for contact in contacts))
            if result:
                self.url = response.url
                self.contacts_url = result["href"]
                self.get_contacts_info()
            if not result:
                self.get_info(soup)

        except requests.exceptions.ConnectionError:
            if "https" not in self.url:
                self.url = self.url.replace("http", "https")
                self.run()
            else:
                return
        except requests.exceptions.TooManyRedirects:
            return
        except requests.exceptions.InvalidURL:
            if "https" not in self.url:
                self.url = self.url.replace("http", "https")
                self.run()
            else:
                return
        except Exception as e:
            if "https" not in self.url:
                self.url = self.url.replace("http", "https")
                self.run()
            else:
                print(f'{self.url} unknown error {e}')
                return
        print(self.url + " done")
            

    def get_contacts_info(self):
        if "http" not in self.contacts_url:
            if not self.contacts_url.startswith("/"):
                self.contacts_url = "/" + self.contacts_url
            self.contacts_url = self.url + self.contacts_url
        try:
            response = requests.get(self.contacts_url, headers=headers)
            if response.status_code not in (200, 301, 101):
                return
            response.encoding = 'utf-8'
            soup = BeautifulSoup(response.text, 'html.parser')
            self.get_info(soup)
        except Exception as e:
            print(f'{e} {self.contacts_url}')

    def get_info(self, soup):
        txt = re.sub(r'\s+', ' ', soup.get_text(" "))
        self.try_to_get_INN(txt)
        self.try_to_get_OGRN(txt)
        if any((self.inn, self.ogrn)):
            data_to_save.append({
                'num': self.num,
                'url': self.url,
                'inn': self.inn,
                'ogrn': self.ogrn
            })

    def try_to_get_OGRN(self, text):
        result = re.search(r'(ОГРН|огрн)\)?\.?\s?(\(?[\w\s?]+)?\)?\s?:?([—–-])?\s?№?/?\s?[\d+]{13,15}\b', text)
        if result is not None:
            self.ogrn = re.search(r'\d+', result.group(0)).group(0)
            assert len(self.ogrn) in (13, 15)

    def try_to_get_INN(self, text):
        result = re.search(r'(ИНН|инн)\s?(([/\\])?\s?(\w+)?/?)?\s?:?\s?([—–-])?\s?№?(\u200e)?[\d+]{10,12}\b', text)
        if result is not None:
            self.inn = re.search(r'\d+', result.group(0)).group(0)
            assert len(self.inn) in (10, 12)

def prepare_data(urls):
    global threads
    threads = [MyThread(url[1],url[0]) for url in urls]


def ram_safe_start(once=100):
    global threads
    iterator = 0
    offset = 1
    while iterator < len(threads):
        for iterator in range(offset, offset + once):
            if iterator < len(threads):
                threads[iterator].start()
        for iterator in range(offset, offset + once):
            if iterator < len(threads):
                threads[iterator].join()
        offset += once
        print(f'offset = {offset}')


def full_start():
    global threads
    for thread in threads:
        thread.start()
    for thread in threads:
        thread.join()

def main(urls):
    start = time.time()
    prepare_data(urls)
    ram_safe_start(1000)
    #full_start()
    with open('data_parser11.json', 'w') as outfile:
        json.dump(data_to_save, outfile)
    print(f'Took {time.time() - start : .2f} seconds')
    parser2.main()


if __name__ == '__main__':
    main(urls)
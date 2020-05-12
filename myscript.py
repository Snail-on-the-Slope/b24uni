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

import parser1

def main(idTable, idSheet, cells):
    strr = str(len(cells))
    print(strr)

if __name__ == '__main__':
    #main(sys.argv[1], sys.argv[2], sys.argv[3])
    main(sys.argv[1], sys.argv[2], sys.argv[3])
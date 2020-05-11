import sys

def main(arg1,arg2):
    str = arg1 + ' - ' + arg2
    print(str)

if __name__ == '__main__':
    main(sys.argv[1], sys.argv[2])
from sqlalchemy import create_engine, text
from urllib.parse import urlparse
from http.server import BaseHTTPRequestHandler
from http.server import HTTPServer

from linear_chains.chain1 import chain1
from linear_chains.chain2 import chain2
from linear_chains.chain3 import chain3
from linear_chains.chain4 import chain4
from linear_chains.chain5 import chain5
from linear_chains.chain6 import chain6
from linear_chains.chain7 import chain7
from linear_chains.chain8 import chain8
from linear_chains.chain9 import chain9
from linear_chains.chain10 import chain10
from linear_chains.chain11 import chain11
from linear_chains.chain12 import chain12
from linear_chains.chain13 import chain13
from linear_chains.chain14 import chain14
from linear_chains.chain15 import chain15
from linear_chains.chain16 import chain16
from linear_chains.chain17 import chain17
from linear_chains.chain181920 import chain18, chain19, chain20
from linear_chains.chain21222324 import chain21, chain22, chain23, chain24

l_chains = [
    chain1,
    chain2,
    chain3,
    chain4,
    chain5,
    chain6,
    chain7,
    chain8,
    chain9,
    chain10,
    chain11,
    chain12,
    chain13,
    chain14,
    chain15,
    chain16,
    chain17,
    chain18, chain19, chain20,
    chain21, chain22, chain23, chain24,
]

from hard_chains.h_chain1_7_9_13 import *
from hard_chains.h_chain8 import h_chain8

h_chains = [
    h_chain1,
    h_chain2,
    h_chain3,
    h_chain4,
    h_chain5,
    h_chain6,
    h_chain7,
    h_chain8,
    h_chain9,
    h_chain10,
    h_chain11,
    h_chain12,
    h_chain13,
]

class HttpGetHandler(BaseHTTPRequestHandler):
    def do_GET(self):
        self.send_response(200)
        self.send_header("Content-type", "text/plane; charset=utf-8")
        self.end_headers()
        
        if (self.path.find("/chains") != -1):
            if (self.path == "/chains"):
                self.wfile.write("""
                        Доступно {0} линейных цепочек взаимосвязанных таблиц
                        в бд:
                    """.format(len(l_chains)).encode())
                for i in range(len(l_chains)):
                    self.wfile.write("""
            {0}:
                Таблицы: {1}
            """.format(i + 1, l_chains[i].get_table_dependencies()).encode())

                self.wfile.write("""
                Добавьте параметр запроса id в эндпоинт: /chains?id=1   (от 0 до {0})
            """.format(len(l_chains) - 1).encode())
                return
            query = urlparse(self.path).query
            id = 0
            try:
                query_components = dict(qc.split("=") for qc in query.split("&"))
                id = int(query_components["id"])
            except:
                self.wfile.write("""
                Добавьте параметр запроса id в эндпоинт: /chains?id=1   (от 0 до {0})
            """.format(len(l_chains) - 1).encode())
                return

            

            query = urlparse(self.path).query
            id = 0
            try:
                query_components = dict(qc.split("=") for qc in query.split("&"))
                id = int(query_components["id"])
            except:
                self.wfile.write("""
                Добавьте параметр запроса id в эндпоинт: /chains?id=1   (от 0 до {0})
            """.format(len(l_chains) - 1).encode())
                return
            if (id < 0 or id >= len(l_chains)):
                self.wfile.write("""
                id должен быть от 0 до {0} включительно
            """.format(len(l_chains) - 1).encode())
            
            self.wfile.write("""
                Цепочка связанных таблиц с номером {0}:
                {1}
            """.format(id, l_chains[id].get_table_dependencies()).encode())
            res = l_chains[id].call()
            if (res.strip() == ''):
                self.wfile.write("""
                В текущей цепочке все данные согласованны и нет расхождений в поле server_id
            """.encode())
            else:
                self.wfile.write(res.encode())
        
        
        elif self.path.find("/h_chains") != -1:
            if (self.path == "/h_chains"):
                self.wfile.write("""
                        Доступно {0} нелинейных цепочек взаимосвязанных таблиц
                        в бд:
                    """.format(len(h_chains)).encode())
                for i in range(len(h_chains)):
                    self.wfile.write("""
            {0}:
                {1}
            """.format(i + 1, h_chains[i].get_table_dependencies()).encode())

                self.wfile.write("""
                Добавьте параметр запроса id в эндпоинт: /h_chains?id=1   (от 0 до {0})
            """.format(len(h_chains) - 1).encode())
                return
            query = urlparse(self.path).query
            id = 0
            try:
                query_components = dict(qc.split("=") for qc in query.split("&"))
                id = int(query_components["id"])
            except:
                self.wfile.write("""
                Добавьте параметр запроса id в эндпоинт: /h_chains?id=1   (от 0 до {0})
            """.format(len(h_chains) - 1).encode())
                return
            
            query = urlparse(self.path).query
            id = 0
            try:
                query_components = dict(qc.split("=") for qc in query.split("&"))
                id = int(query_components["id"])
            except:
                self.wfile.write("""
                Добавьте параметр запроса id в эндпоинт: /h_chains?id=1   (от 0 до {0})
            """.format(len(h_chains) - 1).encode())
                return
            if (id < 0 or id >= len(h_chains)):
                self.wfile.write("""
                id должен быть от 0 до {0} включительно
            """.format(len(h_chains) - 1).encode())
            
            self.wfile.write("""
                Цепочка связанных таблиц с номером {0}:
                {1}
            """.format(id, h_chains[id].get_table_dependencies()).encode())
            res = h_chains[id].call()
            if (res.strip() == ''):
                self.wfile.write("""
                В текущей цепочке все данные согласованны и нет расхождений в поле server_id
            """.encode())
            else:
                self.wfile.write(res.encode())

        
        else:
            self.wfile.write("""
                Доступны эндпоинты:
                1. /chains
                    Зайдите на сервер по адресу 10.252.0.154:8000/chains
                    (опционально с параметром запроса id)
                2. /h_chains
                    Зайдите на сервер по адресу 10.252.0.154:8000/h_chains
                    (опционально с параметром запроса id)
            """.encode())

def run(server_class=HTTPServer, handler_class=HttpGetHandler):
    server_address = ('', 8000)
    httpd = server_class(server_address, handler_class)
    try:
        httpd.serve_forever()
    except KeyboardInterrupt:
        httpd.server_close()

run()
# for running: /bin/python3 /home/anatoliy/test/sqltask3/print_chains.py

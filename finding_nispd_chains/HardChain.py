from sqlalchemy import create_engine, text

import json
# configs:
config_file = './finding_nispd_config.json'
pg_user     = ''
pg_password = ''
pg_hostname = ''
pg_port     = ''
pg_dbname   = ''
with open(config_file) as json_data:
    data = json.load(json_data)
    pg_user     = data['pg_user']
    pg_password = data['pg_password']
    pg_hostname = data['pg_hostname']
    pg_port     = data['pg_port']
    pg_dbname   = data['pg_dbname']

# e = create_engine('postgresql://postgres:@localhost:43432/nispd_test')
e = create_engine(
    'postgresql://{0}:{1}@{2}:{3}/{4}'.format(
        pg_user,
        pg_password,
        pg_hostname,
        pg_port,
        pg_dbname
    )
)

def is_rus(server_id):
    if server_id in {81, 60, 82, 30, 61, 20, 19}:
        return False
    return True

def is_eur(server_id):
    return not is_rus(server_id)

_id = 1

class HardChain:
    def __init__(self, text, func):
        global _id
        self.text = text
        self.func = func
        self.id = _id
        _id += 1

    
    def get_table_dependencies(self):
        return self.text

    def call(self):
        return self.func()


def get_server_info(item):
    if item is None:
        return None
    if (is_rus(item["server_id"])):
        return "российскому серверу (server_id = {0})".format(item["server_id"])
    return "европейскому серверу (server_id = {0})".format(item["server_id"])

class SimpleTreeChain(HardChain):
    def __init__(self, root_table : str, table_conn_column : list, root_has_server_id : bool):
        """
        table_conn_column --- list of list of two elem (table, connect_column) # [[table, col], ...]
        """
        text = """
                Таблица {0} {1}
                ссылается на
""".format(root_table, "(имеет server_id)" if root_has_server_id else "")
        for item in table_conn_column:
            text += """
                    таблицу {0} через {1}
""".format(item[0], item[1])
        text += """
                , которые имеют server_id
        """
        self.text = text
        self.root_table = root_table
        self.table_conn_column = table_conn_column
        self.root_has_server_id = root_has_server_id

    def get_table_dependencies(self):
        return self.text

    def call(self):
        items = e.execute(text("select * from {0}".format(self.root_table)))
        res = ''
        count = 0
        for item in items:
            lst = []
            for dependency in self.table_conn_column:
                dep_elem = None if item[dependency[1]] == None else e.execute( \
                    text("select * from {0} where id = {1}".format(dependency[0], item[dependency[1]]))).first()
                lst.append(dep_elem)
            if (self.root_has_server_id):
                lst.append(item)
            if (all(is_rus(elem["server_id"]) for elem in [i for i in lst if i is not None]) or
                all(is_eur(elem["server_id"]) for elem in [i for i in lst if i is not None])):
                continue
            else:
                res += str(count) + """
                Поле
                {0} 
                в таблице {1} {2}
                ссылается на count={3}шт полей:
                """.format(
                    item,
                    self.root_table,
                    ", принадлежащее {0}".format(get_server_info(item)) if self.root_has_server_id else "",
                    len(self.table_conn_column)
                )
                for i in range(len(self.table_conn_column)):
                    if (lst[i] is None):
                        continue 
                    res += """
                    - поле (через {0})
                    {1}
                    таблицы {2}, принадлежащее {3}
            """.format(
                        self.table_conn_column[i][1],
                        lst[i],
                        self.table_conn_column[i][0],
                        get_server_info(lst[i])
                    )
                count += 1
        return res

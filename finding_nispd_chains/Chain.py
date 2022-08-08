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


def dependent_info(
    row_table_server_id : list,
    connect_columns : list
):
    res = ''
    for i in range(len(row_table_server_id)):
        if (i != 0):
            res += """
            |
            |   (через {0})
            V
            """.format(connect_columns[i - 1])
        res += """
    Поле
    {0}
    в таблице {1} принадлежит {2} серверу (server_id = {3})
""".format(
                row_table_server_id[i][0],
                row_table_server_id[i][1],
                "российскому" if is_rus(row_table_server_id[i][2]) else "европейскому",
                row_table_server_id[i][2]
            )
    res += '\n\n'
    return res

_id = 1
class Chain:
    def __init__(self, tables, func):
        global _id
        self.tables = tables
        self.func = func
        self.id = _id
        _id += 1

    def get_tables(self):
        return self.tables
    
    def get_table_dependencies(self):
        res = ''
        for i in range(len(self.tables)):
            if (i != 0):
                res += " -> "
            res += self.tables[i]
        return res

    def call(self):
        # get str with all chains
        return self.func()

def default2_linear_dependency_fabriqe(table1 : str, table2 : str, conn_col : str):
    def func():
        items = e.execute(text("select * from {0}".format(table1)))
        res = ''
        count = 0
        for item in items:
            if (item[conn_col] is None):
                continue
            dependent = e.execute(text("select * from {1} where id = {0}".format(
                item[conn_col],
                table2
            )))
            for d in dependent:
                if (is_rus(d["server_id"]) != is_rus(item["server_id"])):
                    res += str(count) + dependent_info(
                        [
                            [item, table1, item["server_id"]],
                            [d, table2, d["server_id"]]
                        ],
                        [
                            conn_col
                        ]
                    )
                    count += 1
        return res
    return func



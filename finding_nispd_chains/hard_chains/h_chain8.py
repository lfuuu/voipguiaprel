from sqlalchemy import text
import sys
sys.path.append('../')
import HardChain

def func():
    items = HardChain.e.execute(text("select * from auth.test_group"))
    res = ''
    count = 0
    for item in items:
        test_auth = HardChain.e.execute(text("select * from auth.test_auth where testgroup_id = {0}".format(
            item["id"]
        ))).first()
        test_call = HardChain.e.execute(text("select * from auth.test_call where testgroup_id = {0}".format(
            item["id"]
        ))).first()
        test_dial = HardChain.e.execute(text("select * from auth.test_dial where testgroup_id = {0}".format(
            item["id"]
        ))).first()
        lst = [test_auth, test_call, test_dial]
        lst = [i for i in lst if i is not None]
        if (all(HardChain.is_rus(elem["server_id"]) for elem in lst) or
            all(HardChain.is_eur(elem["server_id"]) for elem in lst)):
            continue
        else:
            res += str(count) + """
            - Поле
            {0}
            таблицы auth.test_auth принадлежит {1}

            - Поле
            {2}
            таблицы auth.test_call принадлежит {3}

            - Поле
            {4}
            таблицы auth.test_dial принадлежит {5}

            Поля ссылаются через столбец testgroup_id на поле
            {6}
            таблицы auth.test_group
            
            """.format(
                test_auth,
                HardChain.get_server_info(test_auth),
                test_call,
                HardChain.get_server_info(test_call),
                test_dial,
                HardChain.get_server_info(test_dial),
                item
            )
            count += 1
    return res



h_chain8 = HardChain.HardChain(
    """Таблицы
        auth.test_auth (через testgroup_id)
        auth.test_call (через testgroup_id)
        auth.test_dial (через testgroup_id)
        , имеющие server_id
        ссылаются на auth.test_group
        """,
    func
)
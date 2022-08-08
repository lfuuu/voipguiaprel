from sqlalchemy import create_engine, text
import sys
sys.path.append('../')
import Chain

def func():
    items = Chain.e.execute(text("select * from auth.route_replace"))
    res = ''
    count = 0
    for item in items:
        if (item["orig_trunk_id"] is None):
            continue
        dependent = Chain.e.execute(text("select * from auth.trunk where id = {0}".format(
            item["orig_trunk_id"]
        )))
        for d in dependent:
            if d["route_table_id"] is None:
                if (Chain.is_rus(d["server_id"]) != Chain.is_rus(item["server_id"])):
                    res += str(count) + Chain.dependent_info(
                        [
                            [item, 'auth.route_replace', item["server_id"]],
                            [d, 'auth.trunk', d["server_id"]],
                        ],
                        [
                            "orig_trunk_id",
                        ]
                    )
                    count += 1
                continue
            dependent2 = Chain.e.execute(text("select * from auth.route_table where id = {0}".format(
                d["route_table_id"]
            )))
            for d2 in dependent2:
                if not (Chain.is_rus(item["server_id"]) == Chain.is_rus(d["server_id"]) == Chain.is_rus(d2["server_id"])):
                    res += str(count) + Chain.dependent_info(
                        [
                            [item, 'auth.route_replace', item["server_id"]],
                            [d, 'auth.trunk', d["server_id"]],
                            [d2, 'auth.route_table', d2["server_id"]]
                        ],
                        [
                            "orig_trunk_id",
                            "route_table_id"
                        ]
                    )
                    count += 1
    return res
    

chain7 = Chain.Chain(['auth.route_replace', 'auth.trunk', 'auth.route_table'],
    func
)
from sqlalchemy import create_engine, text
import sys
sys.path.append('../')
import Chain

def func():
    items = Chain.e.execute(text("select * from auth.camel_gt_rules"))
    res = ''
    count = 0
    for item in items:
        if (item["camel_trunk_id"] is None):
            continue
        dependent = Chain.e.execute(text("select * from auth.camel_trunk where id = {0}".format(
            item["camel_trunk_id"]
        )))
        for d in dependent:
            if d["camel_route_table_id"] is None:
                if (Chain.is_rus(d["server_id"]) != Chain.is_rus(item["camel_server_id"])):
                    res += str(count) + Chain.dependent_info(
                        [
                            [item, 'auth.camel_gt_rules', item["camel_server_id"]],
                            [d, 'auth.camel_trunk', d["server_id"]],
                        ],
                        [
                            "camel_trunk_id",
                        ]
                    )
                    count += 1
                continue
            dependent2 = Chain.e.execute(text("select * from auth.camel_route_table where id = {0}".format(
                d["camel_route_table_id"]
            )))
            for d2 in dependent2:
                if not (Chain.is_rus(item["camel_server_id"]) == Chain.is_rus(d["server_id"]) == Chain.is_rus(d2["server_id"])):
                    res += str(count) + Chain.dependent_info(
                        [
                            [item, 'auth.camel_gt_rules', item["camel_server_id"]],
                            [d, 'auth.camel_trunk', d["server_id"]],
                            [d2, 'auth.camel_route_table', d2["server_id"]]
                        ],
                        [
                            "camel_trunk_id",
                            "camel_route_table_id"
                        ]
                    )
                    count += 1
    return res
    

chain3 = Chain.Chain(['auth.camel_gt_rules', 'auth.camel_trunk', 'auth.camel_route_table'],
    func
)
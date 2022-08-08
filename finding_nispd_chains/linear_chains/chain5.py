from sqlalchemy import create_engine, text
import sys
sys.path.append('../')
import Chain

def func():
    items = Chain.e.execute(text("select * from auth.route_replace"))
    res = ''
    count = 0
    for item in items:
        if (item["b_number_id"] is None):
            continue
        dependent = Chain.e.execute(text("select * from auth.number where id = {0}".format(
            item["b_number_id"]
        )))
        for d in dependent:
            if d["prefixlist_ids"] is None:
                if (Chain.is_rus(d["server_id"]) != Chain.is_rus(item["server_id"])):
                    res += str(count) + Chain.dependent_info(
                        [
                            [item, 'auth.route_replace', item["server_id"]],
                            [d, 'auth.number', d["server_id"]],
                        ],
                        [
                            "camel_trunk_id",
                        ]
                    )
                    count += 1
                continue

            for prefixlist_id in d["prefixlist_ids"]:
                dependent2 = Chain.e.execute(text("select * from auth.prefixlist where id = {0}".format(
                    prefixlist_id
                )))
                for d2 in dependent2:
                    if not (Chain.is_rus(item["server_id"]) == Chain.is_rus(d["server_id"]) == Chain.is_rus(d2["server_id"])):
                        res += str(count) + Chain.dependent_info(
                            [
                                [item, 'auth.route_replace', item["server_id"]],
                                [d, 'auth.number', d["server_id"]],
                                [d2, 'auth.prefixlist', d2["server_id"]]
                            ],
                            [
                                "b_number_id",
                                "prefixlist_ids"
                            ]
                        )
                        count += 1
    return res
    

chain5 = Chain.Chain(['auth.route_replace', 'auth.number', 'auth.prefixlist'],
    func
)
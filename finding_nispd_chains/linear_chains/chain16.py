from sqlalchemy import create_engine, text
import sys
sys.path.append('../')
import Chain


def func():
    items = Chain.e.execute(text("select * from auth.destination"))
    res = ''
    count = 0
    for item in items:
        if (item["prefixlist_ids"] is None):
            continue
        for prefixlist_id in item["prefixlist_ids"]:
            dependent = Chain.e.execute(text("select * from auth.prefixlist where id = {0}".format(
                prefixlist_id
            )))
            for d in dependent:
                if (Chain.is_rus(d["server_id"]) != Chain.is_rus(item["server_id"])):
                    res += str(count) + Chain.dependent_info(
                        [
                            [item, "auth.destination", item["server_id"]],
                            [d, "'auth.prefixlist'", d["server_id"]]
                        ],
                        [
                            "prefixlist_ids"
                        ]
                    )
                    count += 1
    return res
            

chain16 = Chain.Chain(['auth.destination', 'auth.prefixlist'],
    func
)
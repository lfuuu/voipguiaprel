from sqlalchemy import create_engine, text
import sys
sys.path.append('../')
import Chain


def func():
    items = Chain.e.execute(text("select * from auth.camel_trunk"))
    res = ''
    count = 0
    for item in items:
        if (item["prefixlist_id"] is None):
            continue
        dependent = Chain.e.execute(text("select * from auth.prefixlist where id = {0}".format(
            item["prefixlist_id"]
        )))
        for d in dependent:
            if (Chain.is_rus(d["server_id"]) != Chain.is_rus(item["server_id"])):
                res += str(count) + Chain.dependent_info(
                    [
                        [item, "auth.camel_trunk", item["server_id"]],
                        [d, "'auth.prefixlist'", d["server_id"]]
                    ],
                    [
                        "prefixlist_id"
                    ]
                )
                count += 1
    return res           

chain15 = Chain.Chain(['auth.camel_trunk', 'auth.prefixlist'],
    Chain.default2_linear_dependency_fabriqe('auth.camel_trunk',
                                            'auth.prefixlist',
                                            'prefixlist_id'
    )
)

from sqlalchemy import create_engine, text
import sys
sys.path.append('../')
import Chain


# def func():
#     items = Chain.e.execute(text("select * from auth.camel_trunk"))
#     res = ''
#     count = 0
#     for item in items:
#         if (item["camel_route_table_id"] is None):
#             continue
#         dependent = Chain.e.execute(text("select * from auth.camel_route_table where id = {0}".format(
#             item["camel_route_table_id"]
#         )))
#         for d in dependent:
#             if (Chain.is_rus(d["server_id"]) != Chain.is_rus(item["server_id"])):
#                 res += str(count) + Chain.dependent_info(
#                     [
#                         [item, "auth.camel_trunk", item["server_id"]],
#                         [d, "'auth.camel_route_table'", d["server_id"]]
#                     ],
#                     [
#                         "camel_route_table_id"
#                     ]
#                 )
#                 count += 1
#     return res
        

chain13 = Chain.Chain(['auth.camel_trunk', 'auth.camel_route_table'],
    Chain.default2_linear_dependency_fabriqe('auth.camel_trunk',
                                            'auth.camel_route_table',
                                            'camel_route_table_id'
    )
)
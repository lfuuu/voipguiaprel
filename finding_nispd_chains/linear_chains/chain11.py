from sqlalchemy import create_engine, text
import sys
sys.path.append('../')
import Chain


# def func():
#     items = Chain.e.execute(text("select * from auth.route_replace"))
#     res = ''
#     count = 0
#     for item in items:
#         if (item["term_trunk_group_id"] is None):
#             continue
#         dependent = Chain.e.execute(text("select * from auth.trunk_group where id = {0}".format(
#             item["term_trunk_group_id"]
#         )))
#         for d in dependent:
#             if (Chain.is_rus(d["server_id"]) != Chain.is_rus(item["server_id"])):
#                 res += str(count) + Chain.dependent_info(
#                     [
#                         [item, "auth.route_replace", item["server_id"]],
#                         [d, "'auth.trunk_group'", d["server_id"]]
#                     ],
#                     [
#                         "term_trunk_group_id"
#                     ]
#                 )
#                 count += 1
#     return res
            

chain11 = Chain.Chain(['auth.route_replace', 'auth.trunk_group'],
    Chain.default2_linear_dependency_fabriqe('auth.route_replace',
                                            'auth.trunk_group',
                                            'term_trunk_group_id'
    )
)
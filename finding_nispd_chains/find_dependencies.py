from sqlalchemy import create_engine, text



e = create_engine('postgresql://postgres:@localhost:43432/nispd_test')

res = e.execute(text("""
select kcu.table_schema || '.' ||kcu.table_name as foreign_table,
       'references' as rel,
       rel_tco.table_schema || '.' || rel_tco.table_name as primary_table,
       string_agg(kcu.column_name, ', ') as fk_columns,
       kcu.constraint_name
from information_schema.table_constraints tco
join information_schema.key_column_usage kcu
          on tco.constraint_schema = kcu.constraint_schema
          and tco.constraint_name = kcu.constraint_name
join information_schema.referential_constraints rco
          on tco.constraint_schema = rco.constraint_schema
          and tco.constraint_name = rco.constraint_name
join information_schema.table_constraints rel_tco
          on rco.unique_constraint_schema = rel_tco.constraint_schema
          and rco.unique_constraint_name = rel_tco.constraint_name
where tco.constraint_type = 'FOREIGN KEY'
group by kcu.table_schema,
         kcu.table_name,
         rel_tco.table_name,
         rel_tco.table_schema,
         kcu.constraint_name
order by kcu.table_schema,
         kcu.table_name;
"""))

d = dict()
for item in res:
    if item[0] not in d:
        d[item[0]] = [[item[2], item[3]]]
    else:
        d[item[0]].append([item[2], item[3]])
    if item[2] not in d:
        d[item[2]] = []



def find_cycles(d):
    colors = dict()
    for item in d:
        if item not in colors:
            colors[item] = "white"
    stack = []
    def __find_cycles(item, d, colors, stack):
        stack.append(item)
        colors[item] = "grey"
        for table_column in d[item]:
            if (colors[table_column[0]] == "white"):
                __find_cycles(table_column[0], d, colors, stack)
            if (colors[table_column[0]] == "grey"):
                print(stack)
        if len(d[item]) == 0:
            print(stack)
        colors[item] = "black"
        stack.pop()
    for item in d:
        __find_cycles(item, d, colors, stack)

find_cycles(d)
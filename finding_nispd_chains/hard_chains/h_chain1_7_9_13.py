from sqlalchemy import text
import sys
sys.path.append('../')
import HardChain

h_chain1 = HardChain.SimpleTreeChain(
    "auth.a2psms_route_table_route",
    [
        ['auth.number', 'a_number_id'],
        ['auth.number', 'b_number_id'],
        ['auth.a2psms_outcome', 'a2psms_outcome_id'],
        ['auth.a2psms_route_table', 'a2psms_route_table_id'],
    ],
    False
)

h_chain2 = HardChain.SimpleTreeChain(
    "auth.attribute_value",
    [
        ['auth.trunk', 'trunk_id'],
        ['auth.trunk_group', 'trunk_group_id'],
    ],
    False
)

h_chain3 = HardChain.SimpleTreeChain(
    "auth.camel_route_table_route",
    [
        ['auth.number', 'a_number_id'],
        ['auth.number', 'b_number_id'],
        ['auth.number', 'gt_number_id'],
        ['auth.camel_route_table', 'camel_route_table_id'],
        ['auth.camel_route_table', 'camel_outcome_route_table_id'],
    ],
    False
)

h_chain4 = HardChain.SimpleTreeChain(
    "auth.outcome",
    [
        ['auth.route_case', 'route_case_id'],
        ['auth.route_case', 'route_case_1_id'],
        ['auth.route_case', 'route_case_2_id'],
        ['auth.release_reason', 'release_reason_id'],
    ],
    True
)

h_chain5 = HardChain.SimpleTreeChain(
    "auth.route_case_trunk",
    [
        ['auth.trunk', 'trunk_id'],
        ['auth.route_case', 'route_case_id'],
    ],
    False
)

h_chain6 = HardChain.SimpleTreeChain(
    "auth.route_route_rule",
    [
        ['auth.route_table', 'route_table_id'],
        ['auth.trunk_group', 'trunk_group_id'],
        ['auth.number', 'number_id_filter_a'],
        ['auth.number', 'number_id_filter_b'],
        ['auth.header_rule', 'header_rule_id']
    ],
    False
)

h_chain7 = HardChain.SimpleTreeChain(
    "auth.route_table_route",
    [
        ['auth.route_table', 'route_table_id'],
        ['auth.number', 'a_number_id'],
        ['auth.number', 'b_number_id'],
        ['auth.number', 'c_number_id'], 
        ['auth.outcome', 'outcome_id'],
        ['auth.route_table', 'outcome_route_table_id'],
        ['auth.header_rule', 'header_rule_id'],
    ],
    False
)

h_chain9 = HardChain.SimpleTreeChain(
    "auth.trunk_abfilters_rule",
    [
        ['auth.prefixlist', 'prefixlist_id'],
        ['auth.trunk', 'trunk_id'],
    ],
    False
)

h_chain10 = HardChain.SimpleTreeChain(
    "auth.trunk_group_item",
    [
        ['auth.trunk_group', 'child_trunk_group_id'],
        ['auth.trunk_group', 'trunk_group_id'],
        ['auth.trunk', 'trunk_id'],
    ],
    False
)

h_chain11 = HardChain.SimpleTreeChain(
    "auth.trunk_load_limit",
    [
        ['auth.trunk', 'trunk_id'],
        ['auth.number', 'number_id_filter_a'],
        ['auth.number', 'number_id_filter_b'],
    ],
    False
)

h_chain12 = HardChain.SimpleTreeChain(
    "auth.trunk_priority",
    [
        ['auth.trunk', 'trunk_id'],
        ['auth.prefixlist', 'prefixlist_id'],
        ['auth.number', 'number_id_filter_a'],
        ['auth.number', 'number_id_filter_b'],
        ['auth.number', 'number_id_filter_c'],
    ],
    False
)

h_chain13 = HardChain.SimpleTreeChain(
    "auth.trunk_trunk_rule",
    [
        ['auth.trunk', 'trunk_id'],
        ['auth.trunk_group', 'trunk_group_id'],
        ['auth.number', 'number_id_filter_a'],
        ['auth.number', 'number_id_filter_b'],
        ['auth.number', 'number_id_filter_c'],
    ],
    False
)
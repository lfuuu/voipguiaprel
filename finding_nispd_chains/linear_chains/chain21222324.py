from sqlalchemy import create_engine, text
import sys
sys.path.append('../')
import Chain
 
chain21 = Chain.Chain(['auth.outcome_rule', 'auth.outcome'],
    Chain.default2_linear_dependency_fabriqe('auth.outcome_rule', 'auth.outcome', 'outcome_id')
)

chain22 = Chain.Chain(['auth.outcome_rule', 'auth.number'],
    Chain.default2_linear_dependency_fabriqe('auth.outcome_rule', 'auth.number', 'number_id_filter_a')
)

chain23 = Chain.Chain(['auth.outcome_rule', 'auth.number'],
    Chain.default2_linear_dependency_fabriqe('auth.outcome_rule', 'auth.number', 'number_id_filter_b')
)

chain24 = Chain.Chain(['auth.outcome_rule', 'auth.trunk_group'],
    Chain.default2_linear_dependency_fabriqe('auth.outcome_rule', 'auth.trunk_group', 'trunk_group_id')
)
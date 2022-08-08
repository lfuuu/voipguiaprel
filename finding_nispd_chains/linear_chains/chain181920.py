from sqlalchemy import create_engine, text
import sys
sys.path.append('../')
import Chain
 
chain18 = Chain.Chain(['auth.outcome', 'auth.route_case'],
    Chain.default2_linear_dependency_fabriqe('auth.outcome', 'auth.route_case', 'route_case_id')
)

chain19 = Chain.Chain(['auth.outcome', 'auth.route_case'],
    Chain.default2_linear_dependency_fabriqe('auth.outcome', 'auth.route_case', 'route_case_1_id')
)

chain20 = Chain.Chain(['auth.outcome', 'auth.route_case'],
    Chain.default2_linear_dependency_fabriqe('auth.outcome', 'auth.route_case', 'route_case_2_id')
)
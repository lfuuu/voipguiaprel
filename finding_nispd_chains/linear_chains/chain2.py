from sqlalchemy import create_engine, text
import sys
sys.path.append('../')
import Chain
    

chain2 = Chain.Chain(['auth.trunk', 'auth.route_table'],
    Chain.default2_linear_dependency_fabriqe('auth.trunk',
                                            'auth.route_table',
                                            'route_table_id'
    )
)
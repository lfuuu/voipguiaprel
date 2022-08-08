from sqlalchemy import create_engine, text
import sys
sys.path.append('../')
import Chain


chain1 = Chain.Chain(['auth.a2psms_route', 'auth.a2psms_route_table'],
    Chain.default2_linear_dependency_fabriqe('auth.a2psms_route',
                                            'auth.a2psms_route_table',
                                            'a2psms_route_table_id'
    )
)
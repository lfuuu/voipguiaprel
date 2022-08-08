from sqlalchemy import create_engine, text
import sys
sys.path.append('../')
import Chain
 
chain17 = Chain.Chain(['auth.outcome', 'auth.release_reason'],
    Chain.default2_linear_dependency_fabriqe('auth.outcome', 'auth.release_reason', 'release_reason_id')
)

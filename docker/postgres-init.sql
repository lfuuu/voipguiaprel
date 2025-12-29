-- Create user and grant privileges
CREATE USER voipgui WITH PASSWORD 'qwe123';
GRANT ALL PRIVILEGES ON DATABASE nispd_test TO voipgui;
ALTER DATABASE nispd_test OWNER TO voipgui;


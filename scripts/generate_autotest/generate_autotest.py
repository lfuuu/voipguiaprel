#!/bin/python
# -*- coding: utf-8 -*-
import psycopg2
import sys
import json
import argparse
import httplib
import urllib2
import re
reload(sys)
sys.setdefaultencoding('utf8')

OUR_CLIENT = 652
ERROR_LIST = []

class AutoTest:
    name = ""
    trunk = ""
    aNum = 0
    bNum = 0
    result = ""
    region = ""

    def __str__(self):
        output = '[' + self.region + ']-' + self.name + ":"
        output = output + self.trunk + " " + str(self.aNum) + "->"
        output = output + str(self.bNum) + " = "
        output = output + str(self.result)
        return output

def parseArgs():
    parser = argparse.ArgumentParser(description='billing voip autotest generation tool')
    parser = argparse.ArgumentParser()
    parser.add_argument('-c', help='name of json-config file')
    parser.add_argument('-dry', help='generate tests, but do not push them to DB (dry run)', const=1, default=None, action='store_const')
    parser.add_argument('-delete', help='delete autotests from DB', const=1, default=None, action='store_const')
    return parser.parse_args()

def readConfig(filename):
    try:
        data = open (filename, "r")
        result = json.load(data)
        data.close()
        return result
    except IOError as e:
        print ('Couldn\'t load file : ' + e.filename + ' : ' + e.strerror)
        return None
    except ValueError as e:
        print ('Parsing config file failed')
        return None
    return None

def retrieveTests(regions, db):
    conn = None
    try :
        conn = psycopg2.connect(db)
    except psycopg2.Error as e:
        print 'Cant connect to db to retrieve tests'
        print e.message
        sys.exit(1)

    cur = conn.cursor()
    try :
        sql = '''SELECT DISTINCT ON (auth.trunk.server_id) public.server.name,
                 public.server.name_short, auth.trunk.name, auth.trunk.server_id
                 FROM public.server, auth.trunk WHERE ( auth.trunk.name LIKE '%mcn_ast%'
                 OR auth.trunk.name LIKE 'mcn_%_ast%' )
                 AND public.server.id = auth.trunk.server_id;'''
        cur.execute(sql)
        rows = cur.fetchall()
        for serverName, serverNameShort, trunkName, serverId in rows:
            region = dict()
            region["short_name"] = serverNameShort.lower()
            region["city_name"] = serverName
            region["federald"] = str(serverId)
            region["trunk"] = str(trunkName)
            try:
                if trunkName.find(region["short_name"]) == 0:
                    region["asterisk"] = re.search ('.*mcn_ast([0-9]*)', trunkName).group(1)
                elif trunkName.find("mcn_") == 0:
                    region["asterisk"] = re.search ('.*_ast([0-9]*)', trunkName).group(1)
                else:
                    continue
            except:
                continue
            regions[str(serverId)] = region 

    except psycopg2.Error as e:
        print 'DB error retreiving tests'
        print e.message
    except Exception as e:
        print 'Error retrieving tests'
    conn.close()

    # Adding Moscow
    moscow = dict()
    moscow["short_name"] = "msk"
    moscow["city_name"] = "MSK"
    moscow["federald"] = "99"
    moscow["asterisk"] = "16"
    moscow["trunk"] = "mcn_msk_ast16_99"
    regions["99"] = moscow

def fillConfigWithNumbers(regions, db):
    conn = None
    deleteList = []
    try :
        conn = psycopg2.connect(db)
    except psycopg2.Error as e:
        print 'Cant connect to db, to fill config with numbers'
        print e.message
        sys.exit(1)

    cur = conn.cursor()
    for region, values in regions.iteritems():
        try :
            sql = 'SELECT did FROM billing.service_number WHERE '
            sql = sql + 'server_id=' + str(region) + ' AND client_account_id=' + str(OUR_CLIENT) + ' '
            sql = sql + 'AND activation_dt < now() AND expire_dt > now() AND is_for_autotest=TRUE '
            sql = sql + 'LIMIT 1'
            cur.execute(sql)
            rows = cur.fetchall()
            number = str(rows[0])
            values["number"] = re.search ('\\(\'*([0-9]*)', number).group(1)
        except psycopg2.Error as e:
            print 'Region ' + str(region) + ' skipped, because of DB error'
            print e.message
        except Exception as e:
            print 'Region ' + str(region) + ' skipped, because no service number was found'
            deleteList.append(str(region))
    conn.close()
    for region in deleteList :
        regions.pop(region)

def saveTest (autotests, db):
    try :
        conn = psycopg2.connect(db)
        cur = conn.cursor()
        for autotest in autotests:
            sql = 'DELETE FROM auth.test_auth WHERE server_id=' + autotest.region + ' AND name=\'' + autotest.name + '\''
            cur.execute(sql)
            sql = 'INSERT INTO auth.test_auth (server_id, name, trunk_name, src_number, dst_number, redirect_number,'
            sql = sql + 'src_noa, dst_noa, is_autotest, correct_answer, testgroup_id) VALUES ('
            sql = sql + autotest.region + ','
            sql = sql + '\'' + autotest.name + '\','
            sql = sql + '\'' + autotest.trunk + '\','
            sql = sql + autotest.aNum + ','
            sql = sql + autotest.bNum + ','
            sql = sql + '\'\',3,3,TRUE,'
            sql = sql + '\'' + autotest.result + '\','
            sql = sql + '2);'
            cur.execute(sql)
        conn.commit()
        conn.close()
    except psycopg2.Error as e:
        print 'DB error saving to db'
        print e.message
    except Exception as e:
        print 'Error saving to db\n'

def deleteTests (db):
    try :
        conn = psycopg2.connect(db)
        cur = conn.cursor()
        sql = 'DELETE FROM auth.test_auth WHERE name LIKE \'%652\';'
        cur.execute(sql)
        conn.commit()
        conn.close()
    except psycopg2.Error as e:
        print 'DB error deleting tests'
        print e.message
    except Exception as e:
        print 'Error deleting from db\n'

def getAuthRegexp(autotest, regexp, groupNum):
    A = autotest.aNum
    B = autotest.bNum
    region = autotest.region
    trunk = autotest.trunk
    try :
        route_case = None
        requestUrl = 'http://reg%(region)s.mcntelecom.ru:8032/test/auth?trunk_name=%(myTrunk)s&src_number=%(A)s&dst_number=%(B)s&src_noa=3&dst_noa=3' % {
                'A': A, 'B': B, 'region':region, 'myTrunk':trunk
        }
        routeReply = urllib2.urlopen(requestUrl, timeout=3).read()
        route_case = routeReply.split('\n')[-2]
        found = re.search(regexp, route_case).group(groupNum)
        return found     
    except Exception as err :
        error = 'ERROR ON HTTP: ' + requestUrl
        error += '\nEXPECTED: regexp(' + regexp + ') group(' + str(groupNum) + ')'
        if route_case == None:
            route_case = 'http error'
        error += '\nRECEIVED: ' + route_case
        ERROR_LIST.append(error)
        return None

def deleteInvalid(autotests):
    correctAutotests = []

    for test in autotests :
        if test.aNum == None or test.aNum == "" :
            continue
        if test.bNum == None or test.bNum == "" :
            continue
        if test.trunk == None or test.trunk == "" :
            continue
        if test.name == None or test.name == "" :
            continue
        if test.region == None or test.region == "" :
            continue
        if test.result == None or test.result == "" :
            continue
        correctAutotests.append(test)

    return correctAutotests

def generateTest1(originateParams, terminateParams, conf):
    autotest = AutoTest()
    autotest.name = 'To_' + terminateParams["city_name"] + '_Leg1_652'
    autotest.trunk = originateParams["trunk"]
    autotest.aNum = originateParams["number"]
    autotest.bNum = terminateParams["number"]
    autotest.result = originateParams["short_name"] + "_mcn_mgmn_loop"
    autotest.region = originateParams["federald"]
    return autotest

def generateTest2(originateParams, terminateParams, test1):
    autotest = AutoTest()
    autotest.name = 'To_' + terminateParams["city_name"] + '_Leg2_652'
    autotest.trunk = test1.result
    autotest.aNum = originateParams["number"]
    autotest.bNum = terminateParams["number"]
    autotest.result = '^RESULT\|ROUTE CASE\|ECSS_'
    autotest.region = originateParams["federald"]
    return autotest

def generateTest3(originateParams, terminateParams, test2):
    autotest = AutoTest()
    autotest.name = "From_" + originateParams["city_name"] + '_Leg3_652'
    autotest.trunk = getAuthRegexp(test2, '(.*ROUTE CASE\\|.*)(ECSS_[^,]*)(,.*)', 2)
    autotest.aNum = originateParams["number"]
    autotest.bNum = terminateParams["number"]
    autotest.result = terminateParams["short_name"] + "_mcn_mgmn_loop"
    autotest.region = terminateParams["federald"]
    return autotest

def generateTest4(originateParams, terminateParams, test3):
    autotest = AutoTest()
    autotest.name = "From_" + originateParams["city_name"] + '_Leg4_652'
    autotest.trunk = getAuthRegexp(test3, '(.*ROUTE CASE\\|)([^,]*)(.*)', 2)
    autotest.aNum = originateParams["number"]
    autotest.bNum = terminateParams["number"]
    resultName = str(terminateParams["short_name"])
    resultName = resultName.capitalize()
    autotest.result = 'RC_' + resultName + '_MCN_Ast'
    autotest.region = terminateParams["federald"]
    return autotest

def generateMoscowTest1(originateParams, terminateParams, conf):
    autotest = AutoTest()
    autotest.name = 'To_' + terminateParams["city_name"] + '_Leg1_652'
    autotest.trunk = originateParams["trunk"]
    autotest.aNum = originateParams["number"]
    autotest.bNum = terminateParams["number"]
    autotest.result = "^RESULT\|ROUTE CASE\|ECSS_"
    autotest.region = originateParams["federald"]
    return autotest

def generateMoscowTest4(originateParams, terminateParams, test1):
    autotest = AutoTest()
    autotest.name = "From_" + terminateParams["city_name"] + '_Leg4_652'
    autotest.trunk = getAuthRegexp(test1, '(.*ROUTE CASE\\|.*)(ECSS_[^,]*)(,.*)', 2)
    autotest.aNum = originateParams["number"]
    autotest.bNum = terminateParams["number"]
    autotest.result = "^RESULT\|ROUTE CASE\|mcn_msk_ast"
    autotest.region = terminateParams["federald"]
    return autotest

def generateTests(originateParams, terminateParams, config, result):
    test1 = generateTest1(originateParams, terminateParams, config)
    test2 = generateTest2(originateParams, terminateParams, test1)
    test3 = generateTest3(originateParams, terminateParams, test2)
    test4 = generateTest4(originateParams, terminateParams, test3)
    result.append(test1)
    result.append(test2)
    result.append(test3)
    result.append(test4)

def generateMoscowTests(moscowParams, terminateParams, conf, result):    
    if moscowParams["federald"] != "99":
        return 
    test1 = generateMoscowTest1(moscowParams, terminateParams, conf)
    test4 = generateMoscowTest4(moscowParams, terminateParams, test1)
    result.append(test1)
    result.append(test4)

args = parseArgs()
configFilename = args.c
if args.c == None:
    configFilename = 'config.json'

config = readConfig(configFilename)
if config == None:
    sys.exit(1)

if args.delete != None:
    deleteTests (config["db"])
    sys.exit(1)

config["regions"] = dict()
retrieveTests(config["regions"], config["db"])
fillConfigWithNumbers (config["regions"], config["db"])    

result = []    

size = len(config["regions"])
index = 0
for originateRegion, originateParams in config["regions"].iteritems():
    sys.stdout.write('  ' + str(float(index)/float(size)*100)[:4] + '% done\r')
    sys.stdout.flush()
    for terminateRegion, terminateParams in config["regions"].iteritems():
        if originateRegion == terminateRegion:
            continue
        if originateParams["federald"] == "99" or terminateParams["federald"] == "99":
            generateMoscowTests(originateParams, terminateParams, config, result)
        else:
            generateTests(originateParams, terminateParams, config, result)
    index += 1

result = deleteInvalid(result)

for i in ERROR_LIST:
    print i
    print ""

print 'Generated:' + str(len(result)) + ' tests'
print 'Not generated:' + str(len(ERROR_LIST))

if args.dry == None:
    saveTest(result, config["db"])

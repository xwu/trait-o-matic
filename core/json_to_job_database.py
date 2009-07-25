#!/usr/bin/python
# Filename: json_to_job_database.py

"""
usage: %prog json ... [options]
  --drop-tables: drop any existing tables for each job worked on
"""

# Read results from JSON format, copy to database
# ---
# This code is part of the Trait-o-matic project and is governed by its license.

import fileinput, os, string, sys, re, warnings
import MySQLdb
import simplejson as json
from utils import doc_optparse
from utils.biopython_utils import reverse_complement
from config import DB_HOST, GENOTYPE_USER, GENOTYPE_PASSWD, GENOTYPE_DATABASE

query_drop = "DROP TABLE IF EXISTS `%(table)s`"
query_create = '''
CREATE TABLE IF NOT EXISTS `%(table)s` (
  `chromosome` varchar(12) NOT NULL,
  `coordinates` int(10) unsigned NOT NULL,
  `module` varchar(15) NOT NULL,
  `genotype` varchar(3) default NULL,
  `ref_allele` char(1) default NULL,
  `trait_allele` char(1) default NULL,
  `gene` varchar(12) default NULL,
  `amino_acid_change` varchar(12) default NULL,
  `zygosity` enum('hom','het') default NULL,
  `variant` text,
  `phenotype` varchar(255) default NULL,
  `reference` text NOT NULL,
  `taf` varchar(255) default NULL,
  `maf` varchar(255) default NULL,
  PRIMARY KEY  (`chromosome`,`coordinates`,`module`,`phenotype`(128),`reference`(128))
) ENGINE=MyISAM DEFAULT CHARSET=utf8
'''

query = '''
set chromosome=%(chromosome)s, coordinates=%(coordinates)s, module=%(module)s, genotype=%(genotype)s, ref_allele=%(ref_allele)s, trait_allele=%(trait_allele)s, gene=%(gene)s, amino_acid_change=%(amino_acid_change)s, zygosity=%(zygosity)s, variant=%(variant)s, phenotype=%(phenotype)s, reference=%(reference)s, taf=%(taf)s, maf=%(maf)s;
'''

def main():
	# parse options
	option, args = doc_optparse.parse(__doc__)
	
	if len(args) < 1:
		doc_optparse.exit()
	
	# first, try to connect to the databases
	try:
		connection = MySQLdb.connect(host=DB_HOST, user=GENOTYPE_USER, passwd=GENOTYPE_PASSWD, db=GENOTYPE_DATABASE)
		cursor = connection.cursor()
	except MySQLdb.OperationalError, message:
		print "Error %d while connecting to database: %s" % (message[0], message[1])
		sys.exit()

	warnings.filterwarnings("ignore", "Table '.*' already exists")

	table_name_re = re.compile("^[0-9a-f]{32}[0-9a-f]{32}?$");
	last_table_name = None
	last_filename = None

	for line in fileinput.input(args):
		# print fileinput.filename() + ":" + str(fileinput.lineno())
		try:
			l = json.loads(line.strip())
		except:
			fileinput.nextfile()

		# sanity check--should always pass
		if not ("chromosome" in l or "coordinates" in l):
			continue

		for x in ("chromosome", "coordinates", "genotype", "ref_allele", "trait_allele", "gene", "amino_acid_change", "zygosity", "variant", "phenotype", "reference", "taf", "maf"):
			if not (x in l):
				l[x] = None

		if fileinput.filename() != last_filename:
			table_name = os.path.basename(os.path.dirname(fileinput.filename()))
			if not table_name_re.search(table_name):
				print "could not grok dirname, skipping " + fileinput.filename()
				fileinput.nextfile()
			(module_name, x) = os.path.splitext(os.path.basename(fileinput.filename()))
			print " on " + table_name + " " + module_name
			if table_name != last_table_name and option.drop_tables:
				cursor.execute(query_drop % { "table": table_name })
			cursor.execute(query_create % { "table": table_name })
			last_filename = fileinput.filename()
			last_table_name = table_name

		l['taf'] = json.dumps(l['taf'])
		l['maf'] = json.dumps(l['maf'])
		l['module'] = module_name

		cursor.execute("replace into `" + table_name + "` " + query, l);

	# close database cursor and connection
	cursor.close()
	connection.close()

if __name__ == "__main__":
	main()

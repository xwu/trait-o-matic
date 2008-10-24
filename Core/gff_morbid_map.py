#!/usr/bin/python
# Filename: gff_morbid_map.py

"""
usage: %prog gff_file
"""

# Output Morbid Map information in JSON format, if available
# ---
# This code is part of the Trait-o-matic project and is governed by its license.

import os, string, sys
import MySQLdb
import simplejson as json
import gff
from config import DB_HOST, DB_READ_USER, DB_READ_PASSWD, DB_READ_DATABASE

query = '''
SELECT disorder, omim FROM morbidmap WHERE
(symbols LIKE %s OR symbols LIKE %s OR symbols LIKE %s OR symbols LIKE %s);
'''
# the symbols column is comma separated, so in order to be sure to get all the
# possible positions for a gene name, it's necessary to supply four patterns,
# namely:
# 1) "foo"
# 2) "foo,%"
# 3) "% foo,%"
# 4) "% foo"

# substitution matrix
# from http://portal.open-bio.org/pipermail/biopython/2000-September/000418.html
blosum95 = {
	('W', 'F') : 0, ('L', 'R') : -3, ('S', 'P') : -2, ('V', 'T') : -1, 
	('Q', 'Q') : 7, ('N', 'A') : -2, ('Z', 'Y') : -4, ('W', 'R') : -4, 
	('Q', 'A') : -1, ('S', 'D') : -1, ('H', 'H') : 9, ('S', 'H') : -2, 
	('H', 'D') : -2, ('L', 'N') : -5, ('W', 'A') : -4, ('Y', 'M') : -3, 
	('G', 'R') : -4, ('Y', 'I') : -2, ('Y', 'E') : -4, ('B', 'Y') : -4, 
	('Y', 'A') : -3, ('V', 'D') : -5, ('B', 'S') : -1, ('Y', 'Y') : 8, 
	('G', 'N') : -1, ('E', 'C') : -6, ('Y', 'Q') : -3, ('Z', 'Z') : 4, 
	('V', 'A') : -1, ('C', 'C') : 9, ('M', 'R') : -2, ('V', 'E') : -3, 
	('T', 'N') : -1, ('P', 'P') : 8, ('V', 'I') : 3, ('V', 'S') : -3, 
	('Z', 'P') : -2, ('V', 'M') : 0, ('T', 'F') : -3, ('V', 'Q') : -3, 
	('K', 'K') : 6, ('P', 'D') : -3, ('I', 'H') : -4, ('I', 'D') : -5, 
	('T', 'R') : -2, ('P', 'L') : -4, ('K', 'G') : -3, ('M', 'N') : -3, 
	('P', 'H') : -3, ('F', 'Q') : -4, ('Z', 'G') : -3, ('X', 'L') : -2, 
	('T', 'M') : -1, ('Z', 'C') : -5, ('X', 'H') : -2, ('D', 'R') : -3, 
	('B', 'W') : -6, ('X', 'D') : -2, ('Z', 'K') : 0, ('F', 'A') : -3, 
	('Z', 'W') : -4, ('F', 'E') : -5, ('D', 'N') : 1, ('B', 'K') : -1, 
	('X', 'X') : -2, ('F', 'I') : -1, ('B', 'G') : -2, ('X', 'T') : -1, 
	('F', 'M') : -1, ('B', 'C') : -4, ('Z', 'I') : -4, ('Z', 'V') : -3, 
	('S', 'S') : 5, ('L', 'Q') : -3, ('W', 'E') : -5, ('Q', 'R') : 0, 
	('N', 'N') : 7, ('W', 'M') : -2, ('Q', 'C') : -4, ('W', 'I') : -4, 
	('S', 'C') : -2, ('L', 'A') : -2, ('S', 'G') : -1, ('L', 'E') : -4, 
	('W', 'Q') : -3, ('H', 'G') : -3, ('S', 'K') : -1, ('Q', 'N') : 0, 
	('N', 'R') : -1, ('H', 'C') : -5, ('Y', 'N') : -3, ('G', 'Q') : -3, 
	('Y', 'F') : 3, ('C', 'A') : -1, ('V', 'L') : 0, ('G', 'E') : -3, 
	('G', 'A') : -1, ('K', 'R') : 2, ('E', 'D') : 1, ('Y', 'R') : -3, 
	('M', 'Q') : -1, ('T', 'I') : -2, ('C', 'D') : -5, ('V', 'F') : -2, 
	('T', 'A') : 0, ('T', 'P') : -2, ('B', 'P') : -3, ('T', 'E') : -2, 
	('V', 'N') : -4, ('P', 'G') : -4, ('M', 'A') : -2, ('K', 'H') : -1, 
	('V', 'R') : -4, ('P', 'C') : -5, ('M', 'E') : -3, ('K', 'L') : -3, 
	('V', 'V') : 5, ('M', 'I') : 1, ('T', 'Q') : -1, ('I', 'G') : -6, 
	('P', 'K') : -2, ('M', 'M') : 7, ('K', 'D') : -2, ('I', 'C') : -2, 
	('Z', 'D') : 0, ('F', 'R') : -4, ('X', 'K') : -1, ('Q', 'D') : -1, 
	('X', 'G') : -3, ('Z', 'L') : -4, ('X', 'C') : -3, ('Z', 'H') : 0, 
	('B', 'L') : -5, ('B', 'H') : -1, ('F', 'F') : 7, ('X', 'W') : -4, 
	('B', 'D') : 4, ('D', 'A') : -3, ('S', 'L') : -3, ('X', 'S') : -1, 
	('F', 'N') : -4, ('S', 'R') : -2, ('W', 'D') : -6, ('V', 'Y') : -3, 
	('W', 'L') : -3, ('H', 'R') : -1, ('W', 'H') : -3, ('H', 'N') : 0, 
	('W', 'T') : -4, ('T', 'T') : 6, ('S', 'F') : -3, ('W', 'P') : -5, 
	('L', 'D') : -5, ('B', 'I') : -5, ('L', 'H') : -4, ('S', 'N') : 0, 
	('B', 'T') : -1, ('L', 'L') : 5, ('Y', 'K') : -3, ('E', 'Q') : 2, 
	('Y', 'G') : -5, ('Z', 'S') : -1, ('Y', 'C') : -4, ('G', 'D') : -2, 
	('B', 'V') : -5, ('E', 'A') : -1, ('Y', 'W') : 2, ('E', 'E') : 6, 
	('Y', 'S') : -3, ('C', 'N') : -4, ('V', 'C') : -2, ('T', 'H') : -2, 
	('P', 'R') : -3, ('V', 'G') : -5, ('T', 'L') : -2, ('V', 'K') : -3, 
	('K', 'Q') : 1, ('R', 'A') : -2, ('I', 'R') : -4, ('T', 'D') : -2, 
	('P', 'F') : -5, ('I', 'N') : -4, ('K', 'I') : -4, ('M', 'D') : -5, 
	('V', 'W') : -3, ('W', 'W') : 11, ('M', 'H') : -3, ('P', 'N') : -3, 
	('K', 'A') : -1, ('M', 'L') : 2, ('K', 'E') : 0, ('Z', 'E') : 4, 
	('X', 'N') : -2, ('Z', 'A') : -1, ('Z', 'M') : -2, ('X', 'F') : -2, 
	('K', 'C') : -5, ('B', 'Q') : -1, ('X', 'B') : -2, ('B', 'M') : -4, 
	('F', 'C') : -3, ('Z', 'Q') : 4, ('X', 'Z') : -1, ('F', 'G') : -5, 
	('B', 'E') : 0, ('X', 'V') : -2, ('F', 'K') : -4, ('B', 'A') : -3, 
	('X', 'R') : -2, ('D', 'D') : 7, ('W', 'G') : -5, ('Z', 'F') : -4, 
	('S', 'Q') : -1, ('W', 'C') : -4, ('W', 'K') : -5, ('H', 'Q') : 1, 
	('L', 'C') : -3, ('W', 'N') : -5, ('S', 'A') : 1, ('L', 'G') : -5, 
	('W', 'S') : -4, ('S', 'E') : -1, ('H', 'E') : -1, ('S', 'I') : -3, 
	('H', 'A') : -3, ('S', 'M') : -3, ('Y', 'L') : -2, ('Y', 'H') : 1, 
	('Y', 'D') : -5, ('E', 'R') : -1, ('X', 'P') : -3, ('G', 'G') : 6, 
	('G', 'C') : -5, ('E', 'N') : -1, ('Y', 'T') : -3, ('Y', 'P') : -5, 
	('T', 'K') : -1, ('A', 'A') : 5, ('P', 'Q') : -2, ('T', 'C') : -2, 
	('V', 'H') : -4, ('T', 'G') : -3, ('I', 'Q') : -4, ('Z', 'T') : -2, 
	('C', 'R') : -5, ('V', 'P') : -4, ('P', 'E') : -2, ('M', 'C') : -3, 
	('K', 'N') : 0, ('I', 'I') : 5, ('P', 'A') : -1, ('M', 'G') : -4, 
	('T', 'S') : 1, ('I', 'E') : -4, ('P', 'M') : -3, ('M', 'K') : -2, 
	('I', 'A') : -2, ('P', 'I') : -4, ('R', 'R') : 7, ('X', 'M') : -2, 
	('L', 'I') : 1, ('X', 'I') : -2, ('Z', 'B') : 0, ('X', 'E') : -2, 
	('Z', 'N') : -1, ('X', 'A') : -1, ('B', 'R') : -2, ('B', 'N') : 4, 
	('F', 'D') : -5, ('X', 'Y') : -2, ('Z', 'R') : -1, ('F', 'H') : -2, 
	('B', 'F') : -5, ('F', 'L') : 0, ('X', 'Q') : -1, ('B', 'B') : 4
}

# from same source as matrix
def sub_value(aa1, aa2):
	'''
	Returns the substitution value for two amino acids (order is
	unimportant because the matrix is symmetric).
	
	Raises KeyError if a substitution value for the two residues is
	not found.
	'''
	try:
		s = blosum95[(aa1, aa2)]
	except KeyError:
		s = blosum95[(aa2, aa1)]
	return s

def main():
	# return if we don't have the correct arguments
	if len(sys.argv) < 2:
		raise SystemExit(__doc__.replace("%prog", sys.argv[0]))
	
	# first, try to connect to the databases
	try:
		connection = MySQLdb.connect(host=DB_HOST, user=DB_READ_USER, passwd=DB_READ_PASSWD, db=DB_READ_DATABASE)
		cursor = connection.cursor()
	except MySQLdb.OperationalError, message:
		print "Error %d while connecting to database: %s" % (message[0], message[1])
		sys.exit()
	
	gff_file = gff.input(sys.argv[1])	
	for record in gff_file:
		# examine each amino acid change (this takes care of alternative splicings)
		amino_acid_changes = record.attributes["amino_acid"].strip("\"").split("/")
		
		# make sure not to duplicate what we print because of multiple alternative
		# splicings; so, initialize an empty list to hold previous output strings
		# so we can compare before printing
		previous_output_strings = []
		
		# examine each alternative splicing
		for a in amino_acid_changes:
			output_strings = []
			
			amino_acid = a.split(" ")
			gene = amino_acid.pop(0) # the first item is always the gene name
			
			# there should be only one amino acid change per coding sequence,
			# because there should be only two alleles, but if there are 3 or
			# more alleles (due to ambiguous sequencing), or if both alleles
			# are different from the reference sequence, then we need to look
			# at all of them
			for aa in amino_acid:
				ref_aa = aa[0]
				mut_aa = aa[-1]
				
				if ref_aa == "*" or mut_aa == "*":
					score = 10
				else:
					score = -1 * sub_value(ref_aa, mut_aa)
					if score <= 2:
						# right now, we don't really consider conservative changes...
						continue

				cursor.execute(query, (gene, gene + ",%", "% " + gene + ",%", "% " + gene))
				data = cursor.fetchall()
				
				# move on if we don't have info
				if cursor.rowcount <= 0:
					continue
				
				for d in data:
					disorder = d[0]
					omim = d[1]
					
					output = {
						"gene": gene,
						"variant": str(record),
						"phenotype": disorder,
						"reference": "omim:" + str(omim),
						"score": score
					}
					output_strings.append(json.dumps(output))
			
			# actually only output what's not duplicating previous 
			if output_strings != previous_output_strings:
				previous_output_strings = output_strings
				for o in output_strings:
					print o

	# close database cursor and connection
	cursor.close()
	connection.close()

if __name__ == "__main__":
	main()

#!/usr/bin/python
# Filename: gff_query_twobit.py

"""
usage: %prog gff_file twobit_file
"""

# Append ref_allele attribute with information from the 2bit file
# ---
# This code is part of the Trait-o-matic project and is governed by its license.

import sys
import gff, twobit

def main():
	# return if we don't have the correct arguments
	if len(sys.argv) < 3:
		raise SystemExit(__doc__.replace("%prog", sys.argv[0]))
	
	# try opening the file both ways, in case the arguments got confused
	try:
		gff_file = gff.input(sys.argv[2])
		twobit_file = twobit.input(sys.argv[1])
	except Exception:
		gff_file = gff.input(sys.argv[1])
		twobit_file = twobit.input(sys.argv[2])
	
	for record in gff_file:
		if record.seqname.startswith("chr"):
			chr = record.seqname
		else:
			chr = "chr" + record.seqname
		
		ref_seq = twobit_file[chr][(record.start - 1):record.end]
		record.attributes["ref_allele"] = ref_seq.upper()
		print record

if __name__ == "__main__":
	main()

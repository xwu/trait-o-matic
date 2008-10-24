#!/usr/bin/python
# Filename: gff_twobit_map.py

"""
usage: %prog gff_file twobit_file [options]
  -s, --strand: adjust output to match the strand indicated in the GFF record
"""

# Output FASTA record for intervals in the 2bit file as specified in each GFF record
# ---
# This code is part of the Trait-o-matic project and is governed by its license.

import sys
import doc_optparse, gff, twobit
from biopython_utils import reverse_complement
from fasta import FastaRecord

def main():
	# parse options
	option, args = doc_optparse.parse(__doc__)
	
	if len(args) < 2:
		doc_optparse.exit()
	
	# try opening the file both ways, in case the arguments got confused
	try:
		gff_file = gff.input(args[1])
		twobit_file = twobit.input(args[0])
	except Exception:
		gff_file = gff.input(args[0])
		twobit_file = twobit.input(args[1])
	
	for record in gff_file:
		if record.seqname.startswith("chr"):
			chr = record.seqname
		else:
			chr = "chr" + record.seqname
		
		ref_seq = twobit_file[chr][(record.start - 1):record.end]
		if option.strand and record.strand == "-":
			ref_seq = reverse_complement(ref_seq)
		print FastaRecord(str(record).replace("\t", "|"), ref_seq)

if __name__ == "__main__":
	main()

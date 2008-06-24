#!/usr/bin/python
# Filename: fasta.py

# A quick-and-dirty FASTA file parser
# based in part on the example at <http://www.dalkescientific.com/writings/NBN/parsing.html>
# and in part on the version by Thomas Mailund at <http://www.daimi.au.dk/~mailund/python/fasta.py>
# ---
# This code is part of the Trait-o-matic project and is governed by its license.

class FastaRecord(object):
	def __init__(self, title, sequence):
		self.title = title
		self.sequence = sequence

class SyntaxError(object):
	pass

def _fasta_iterator(src):
	# open the file if we're only provided a path
	if isinstance(src, str):
		f = open(src)
	elif isinstance(src, file):
		f = src
	else:
		raise TypeError
	
	# get started with the first title
	title = f.readline()	
	if not title.startswith(">"):
		raise SyntaxError
	title = title[1:].rstrip()
	
	# start reading in sequence
	sequence = []
	for line in f:
		# yield record if at next sequence, reset
		if line.startswith(">"):
			yield FastaRecord(title, "".join(sequence))
			
			title = line[1:].rstrip()
			sequence = []
			continue
		
		# otherwise, append to sequence list
		line = line.strip()
		sequence.append(line)
	
	# we're at the end of the file; yield the last record
	yield FastaRecord(title, "".join(sequence))
	
	# also, close the file if we opened it
	if isinstance(src, str):
		f.close()

class FastaFile:
	def __init__(self, src):
		self.__iterator = _fasta_iterator(src)
	
	def __iter__(self):
		return self
	
	def next(self):
		return self.__iterator.next()
	
	def __getitem__(self, key):
		key = key.rstrip()
		for record in iter(self):
			if key == record.title:
				return record
		return None
	
def get_sequence(src, title):
	return FastaFile(src)[title]
	
def input(src):
	return FastaFile(src)

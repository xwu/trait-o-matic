#!/usr/bin/python
# Filename: gff.py

# A GFF file parser
# ---
# This code is part of the Trait-o-matic project and is governed by its license.

class GFFRecord(object):
	def __init__(self, seqname, source, feature, start, end,
	             score, strand, frame, attributes, comments):
		self.id = seqname + ":" + str(start) + ".." + str(end) + " " + feature
		self.seqname = seqname
		self.source = source
		self.feature = feature
		self.start = start
		self.end = end
		self.score = score
		self.strand = strand
		self.frame = frame
		self.attributes = attributes
		self.comments = comments

class SyntaxError(object):
	pass

def _gff_iterator(src):
	# open the file if we're only provided a path
	if isinstance(src, str):
		f = open(src)
	elif isinstance(src, file):
		f = src
	else:
		raise TypeError

	# start reading line by line
	meta_comments_allowed = True
	for line in f:
		# parse only meta comments before anything else
		if meta_comments_allowed and not line.startswith('##'):
			meta_comments_allowed = False
		
		# if we have a comment, then move on unless it's meta
		if line.startswith('#'):
			if not meta_comments_allowed or not line.startswith('##'):
				continue
			
			line = line.strip()
			# process meta comments
			if line.startswith("##gff-version") and line != "##gff-version 2":
				raise TypeError # we don't want to interpret future versions incorrectly
			elif line.startswith("##RNA") or line.startswith("##Protein"):
				raise TypeError # we only do DNA
			else:
				continue
		
		# start parsing the line
		l = line.strip().split("\t")
		if len(l) < 9:
			raise SyntaxError
		
		# sanity check on start and end
		start = long(l[3])
		end = long(l[4])
		if end < start:
			raise SyntaxError
			
		# convert score to float
		score = l[5]
		if score != '.':
			score = float(score)
		
		# convert frame to int
		frame = l[7]
		if frame != '.':
			frame = int(frame)
		
		# parse attributes
		attributes = dict(attr.strip().split(' ', 1) for attr in l[8].split(';'))
		
		# parse comments, if they exist
		if len(l) >= 10:
			comments = [c.lstrip('#').strip() for c in l[9:]]
		else:
			comments = None
		
		# note how we don't do any processing on seqname, source, feature, or strand
		yield GFFRecord(l[0], l[1], l[2], start, end,
		                score, l[6], frame, attributes, comments)
	
	# also, close the file if we opened it
	if isinstance(src, str):
		f.close()

class GFFFile:
	def __init__(self, src):
		self.__iterator = _gff_iterator(src)
	
	def __iter__(self):
		return self
	
	def next(self):
		return self.__iterator.next()
	
	def __getitem__(self, key):
		key = key.strip()
		for record in iter(self):
			if key == record.id:
				return record
		return None
	
def input(src):
	return GFFFile(src)

#!/usr/bin/python
# Filename: server.py

"""
usage: %prog [options]
  -h, --host=STRING: the host on which to listen
  -p, --port=NUMBER: the port on which to listen
"""

# Start an XMLRPC server for Trait-o-matic
# ---
# This code is part of the Trait-o-matic project and is governed by its license.

import base64, hashlib, os, shutil, subprocess, sys, urllib2
from SimpleXMLRPCServer import SimpleXMLRPCServer as xrs
from tempfile import mkstemp
from utils import doc_optparse
from config import UPLOAD_DIR, REFERENCE_GENOME

def main():
	# parse options
	option, args = doc_optparse.parse(__doc__)
	
	host = option.host or "localhost"
	port = int(option.port or 8080)
	
	# create server
	server = xrs((host, port))
	server.register_introspection_functions()
	
	def submit(genotype_file, coverage_file=None, username=None, password=None):
		# get genotype file
		r = urllib2.Request(genotype_file)
		if username is not None:
			h = "Basic %s" % base64.encodestring('%s:%s' % (username, password)).strip()
			r.add_header("Authorization", h)
		handle = urllib2.urlopen(r)
		
		# write it to a temporary location while calculating its hash
		s = hashlib.sha1()
		output_handle, output_path = mkstemp()
		for line in handle:
			os.write(output_handle, line)
			s.update(line)
		os.close(output_handle)
		
		# now figure out where to store the file permanently
		permanent_dir = os.path.join(UPLOAD_DIR, s.hexdigest())
		permanent_file = os.path.join(permanent_dir, "genotype.gff")
		if not os.exists(permanent_dir):
			os.makedirs(permanent_dir)
			shutil.move(output_path, permanent_file)
		
		# run the query
		submit_local(permanent_file)
		return s
	server.register_function(submit)
	
	def submit_local(genotype_file, coverage_file=None):
		# execute script
		script_dir = os.path.dirname(sys.argv[0])
		output_dir = os.path.dirname(genotype_file)
		# letters refer to scripts; numbers refer to outputs
		args = { 'A': os.path.join(script_dir, "gff_query_twobit.py"),
		         'B': os.path.join(script_dir, "gff_query_dbsnp.py"),
		         'C': os.path.join(script_dir, "gff_nonsynonymous_filter.py"),
		         'D': os.path.join(script_dir, "gff_omim_map.py"),
		         'E': os.path.join(script_dir, "gff_hgmd_map.py"),
		         'F': os.path.join(script_dir, "gff_morbid_map.py"),
		         'in': genotype_file,
		         'reference': REFERENCE_GENOME,
		         '1': os.path.join(output_dir, "genotype.1.gff"),
		         '2': os.path.join(output_dir, "genotype.2.gff"),
		         '3': os.path.join(output_dir, "ns.gff"),
		         '4': os.path.join(output_dir, "omim.json"),
		         '5': os.path.join(output_dir, "hgmd.json"),
		         '6': os.path.join(output_dir, "morbid.json"),
		         '7': os.path.join(output_dir, "README") }
		cmd = '''(
		python '%(A)s' '%(in)s' '%(reference)s' > '%(1)s'
		python '%(B)s' '%(1)s' > '%(2)s'
		python '%(C)s' '%(2)s' '%(reference)s' > '%(3)s'
		python '%(D)s' '%(3)s' > '%(4)s'
		python '%(E)s' '%(3)s' > '%(5)s'
		python '%(F)s' '%(3)s' > '%(6)s'
		touch '%(7)s'
		)&''' % args
		subprocess.call(cmd, shell=True)
		return output_dir
	server.register_function(submit_local)
	
	# run the server's main loop
	server.serve_forever()

if __name__ == "__main__":
	main()
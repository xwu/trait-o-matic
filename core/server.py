#!/usr/bin/python
# Filename: server.py

"""
usage: %prog [options]
  -h, --host=STRING: the host on which to listen
  -p, --port=NUMBER: the port on which to listen
  -t, --trackback: invoke the server's trackback function with arguments url, path, kind, request_token (does not start a new server)
"""

# Start an XMLRPC server for Trait-o-matic
# ---
# This code is part of the Trait-o-matic project and is governed by its license.

import base64, hashlib, os, shutil, subprocess, sys, urllib, urllib2
from SimpleXMLRPCServer import SimpleXMLRPCServer as xrs
from tempfile import mkstemp
from utils import doc_optparse
from config import UPLOAD_DIR, REFERENCE_GENOME

def trackback(url, params):
	request = urllib2.Request(url, params)
	request.add_header('User-agent', 'Trait-o-matic/20090123 Python')
	request.add_header('Content-type', 'application/x-www-form-urlencoded;charset=utf-8')
	try:
		file = urllib2.urlopen(request)
	except URLError:
		return False
	file.close()
	return True

def main():
	# parse options
	option, args = doc_optparse.parse(__doc__)
	
	# deal with the trackback option
	if option.trackback:
		if len(args) < 4:
			doc_optparse.exit()
		url = args[0]
		path = args[1]
		kind = args[2]
		request_token = args[3]
		params = urllib.urlencode({ 'path': path, 'kind': kind, 'request_token': request_token })            
		trackback(url, params)
		return
	
	# otherwise, figure out the host and port
	host = option.host or "localhost"
	port = int(option.port or 8080)
	
	# create server
	server = xrs((host, port))
	server.register_introspection_functions()
	
	def submit(genotype_file, coverage_file='', username=None, password=None):
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
	
	def submit_local(genotype_file, coverage_file='', trackback_url='', request_token=''):
		# execute script
		script_dir = os.path.dirname(sys.argv[0])
		output_dir = os.path.dirname(genotype_file)
		# letters refer to scripts; numbers refer to outputs
		args = { 'A': os.path.join(script_dir, "gff_twobit_query.py"),
		         'B': os.path.join(script_dir, "gff_dbsnp_query.py"),
		         'C': os.path.join(script_dir, "gff_nonsynonymous_filter.py"),
		         'D': os.path.join(script_dir, "gff_omim_map.py"),
		         'E': os.path.join(script_dir, "gff_hgmd_map.py"),
		         'F': os.path.join(script_dir, "gff_morbid_map.py"),
		         'G': os.path.join(script_dir, "gff_snpedia_map.py"),
		         'H': os.path.join(script_dir, "json_allele_frequency_query.py"),
		         'I': os.path.join(script_dir, "json_to_job_database.py"),
		         'Z': os.path.join(script_dir, "server.py"),
		         'in': genotype_file,
		         'reference': REFERENCE_GENOME,
		         'url': trackback_url,
		         'token': request_token,
		         '1': os.path.join(output_dir, "genotype.gff"),
		         '2': os.path.join(output_dir, "genotype.dbsnp.gff"),
		         '3': os.path.join(output_dir, "ns.gff"),
		         '4': os.path.join(output_dir, "omim.json"),
		         '5': os.path.join(output_dir, "hgmd.json"),
		         '6': os.path.join(output_dir, "morbid.json"),
		         '7': os.path.join(output_dir, "snpedia.json"),
		         '8': "",
		         '0': os.path.join(output_dir, "README") }
		cmd = '''(
		python '%(A)s' '%(in)s' '%(reference)s' > '%(1)s'
		python '%(B)s' '%(1)s' > '%(2)s'
		python '%(C)s' '%(2)s' '%(reference)s' > '%(3)s'
		python '%(D)s' '%(3)s' > '%(4)s'
		python '%(E)s' '%(3)s' > '%(5)s'
		python '%(F)s' '%(3)s' > '%(6)s'
		python '%(G)s' '%(2)s' > '%(7)s'
		python '%(H)s' '%(4)s' '%(5)s' '%(6)s' '%(7)s' --in-place
		python '%(I)s' '%(4)s' '%(5)s' '%(6)s' '%(7)s'
		touch '%(0)s'
		python '%(Z)s' -t '%(url)s' '%(4)s' 'out/omim' '%(token)s'
		python '%(Z)s' -t '%(url)s' '%(5)s' 'out/hgmd' '%(token)s'
		python '%(Z)s' -t '%(url)s' '%(6)s' 'out/morbid' '%(token)s'
		python '%(Z)s' -t '%(url)s' '%(7)s' 'out/snpedia' '%(token)s'
		python '%(Z)s' -t '%(url)s' '%(0)s' 'out/readme' '%(token)s'
		)&''' % args
		subprocess.call(cmd, shell=True)
		return output_dir
	server.register_function(submit_local)
	
	# run the server's main loop
	server.serve_forever()

if __name__ == "__main__":
	main()
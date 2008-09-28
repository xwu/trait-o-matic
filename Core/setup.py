#!/usr/bin/python
# Filename: setup.py

# A setup script to install the _twobit module
# ---
# This code is part of the Trait-o-matic project and is governed by its license.

from distutils.core import setup
from distutils.extension import Extension
from Pyrex.Distutils import build_ext

extensions = []
extensions.append(Extension("_twobit", ["_twobit.pyx"]))
extensions.append(Extension("bitset", ["bitset.pyx", "binBits.c", "bits.c", "common.c"]))

def main():
	setup(name="trait",
		ext_modules=extensions,
		cmdclass={'build_ext': build_ext})
      
if __name__ == "__main__":
	main()
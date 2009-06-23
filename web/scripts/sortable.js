/*
 * Portions copyright (C) 2009 Xiaodi Wu.
 * Copyright (C) 2006 Andrew Tetlaw. MIT License.
 * http://tetlaw.id.au/view/blog/table-sorting-with-prototype/
 */

var Sortable = {
	init : function(elm, o) {
		Object.extend(Sortable.options, o || {});
		
		var table = $(elm);
		if (table.tagName != 'TABLE')
			return;
		table.identify();
		
		var cells = Sortable.getHeaderCells(table);
		var sortFirst;
		cells.each(function(c) {
			c = $(c);
			Event.observe(c, 'click', Sortable._sort.bindAsEventListener(c));
			c.addClassName(Sortable.options.columnClass);
			if (c.hasClassName(Sortable.options.firstAscendingClass) ||
			    c.hasClassName(Sortable.options.firstDecendingClass))
				sortFirst = c;
		});
		
		// keep track of the initial row order so that it can be restored
		var rows = Sortable.getBodyRows(table);
		rows.each(function(r, i) {
			r.keepIndex = i;
		});
		
		if (sortFirst) {
			if (sortFirst.hasClassName(Sortable.options.firstAscendingClass)) {
				Sortable.sort(table, sortFirst, 1);
			} else {
				Sortable.sort(table, sortFirst, -1);
			}
		} else { // just add row stripe classes
			//var rows = Sortable.getBodyRows(table);
			rows.each(function(r, i) {
				Sortable.addRowClass(r, i);
			});
		}
	},
	
	_sort : function(e) {
		Sortable.sort(null, this);
	},
	
	sort : function(table, index, order) {
		var op = Sortable.options;
		
		var cell;
		if (typeof index == 'number') {
			if (!table || (table.tagName && table.tagName != 'TABLE'))
				return;
			index = Math.min(table.rows[0].cells.length, index);
			index = Math.max(1, index);
			index -= 1;
			cell = (table.tHead && table.tHead.rows.length > 0) ?
			           $(table.tHead.rows[table.tHead.rows.length - 1].cells[index]) :
			           $(table.rows[0].cells[index]);
		} else {
			cell = $(index);
			table = table ? $(table) : table = cell.up('table');
			index = Sortable.getCellIndex(cell);
		}
		
		if (cell.hasClassName(op.noSortClass))
			return;
		
		order = order ? order : (cell.hasClassName(op.descendingClass) ? 1 : -1);
		
		var hCells = Sortable.getHeaderCells(null, cell);
		$A(hCells).each(function(c, i) {
			c = $(c);
			if (i == index) {
				if (order == 1) {
					c.removeClassName(op.descendingClass);
					c.addClassName(op.ascendingClass);
				} else {
					c.removeClassName(op.ascendingClass);
					c.addClassName(op.descendingClass);
				}
			} else {
				c.removeClassName(op.ascendingClass);
				c.removeClassName(op.descendingClass);
			}
		});
		
		var rows = Sortable.getBodyRows(table);
		var dataType = Sortable.getDataType(cell, index, table);
		if (dataType == 'keep') {
			rows.sort(function(a, b) {
				ai = a.keepIndex;
				bi = b.keepIndex;
				return order * Sortable.compare(ai, bi);
			});
		} else {
			rows.sort(function(a, b) {
				at = Sortable.getCellText(a.cells[index]);
				bt = Sortable.getCellText(b.cells[index])
				return order * Sortable.types[dataType](at, bt);
			});
		}
		rows.each(function(r, i) {
			table.tBodies[0].appendChild(r);
			Sortable.addRowClass(r, i);
		});
	},
	
	types : {
		number : function(a, b) {
			// this will grab the first thing that looks like a number from a string, so
			// you can use it to order a column of various strings containing numbers.
			var calc = function(v) {
				v = parseFloat(v.replace(/^.*?([-+]?[\d]*\.?[\d]+(?:[eE][-+]?[\d]+)?).*$/, "$1"));
				return isNaN(v) ? 0 : v;
			}
			return Sortable.compare(calc(a), calc(b));
		},
		text : function(a, b) {
			return Sortable.compare(a ? a.toLowerCase() : '', b ? b.toLowerCase() : '');
		},
		'case-sensitive-text' : function(a, b) {
			return Sortable.compare(a, b);
		},
		'data-size' : function(a, b) {
			var calc = function(v) {
				var r = v.match(/^([-+]?[\d]*\.?[\d]+([eE][-+]?[\d]+)?)\s?([k|m|g|t]?b)?/i);
				var b = r[1] ? Number(r[1]).valueOf() : 0;
				var m = r[3] ? r[3].substr(0, 1).toLowerCase() : '';
				switch (m) {
					case 'k':
						return b * 1024;
						break;
					case 'm':				
						return b * 1024 * 1024;
						break;
					case 'g':
						return b * 1024 * 1024 * 1024;
						break;
					case 't':
						return b * 1024 * 1024 * 1024 * 1024;
						break;
				}
				return b;
			}
			return Sortable.compare(calc(a), calc(b));
		},
		'date-uk' : function(a, b) {
			var calc = function(v) {
				var r = v.match(/^(\d{2})\/(\d{2})\/(\d{4})\s?(?:(\d{1,2})\:(\d{2})(?:\:(\d{2}))?\s?([a|p]?m?))?/i);
				var yr_num = r[3];
				var mo_num = parseInt(r[2]) - 1;
				var day_num = r[1];
				var hr_num = r[4] ? r[4] : 0;
				if(r[7] && r[7].toLowerCase().indexOf('p') != -1) {
					hr_num = parseInt(r[4]) + 12;
				}
				var min_num = r[5] ? r[5] : 0;
				var sec_num = r[6] ? r[6] : 0;
				return new Date(yr_num, mo_num, day_num, hr_num, min_num, sec_num, 0).valueOf();
			}
			return Sortable.compare(a ? calc(a) : 0, b ? calc(b) : 0);
		},
		'date-us' : function(a, b) {
			var calc = function(v) {
				var r = v.match(/^(\d{2})\/(\d{2})\/(\d{4})\s?(?:(\d{1,2})\:(\d{2})(?:\:(\d{2}))?\s?([a|p]?m?))?/i);
				var yr_num = r[3];
				var mo_num = parseInt(r[1]) - 1;
				var day_num = r[2];
				var hr_num = r[4] ? r[4] : 0;
				if(r[7] && r[7].toLowerCase().indexOf('p') != -1) {
					hr_num = parseInt(r[4]) + 12;
				}
				var min_num = r[5] ? r[5] : 0;
				var sec_num = r[6] ? r[6] : 0;
				return new Date(yr_num, mo_num, day_num, hr_num, min_num, sec_num, 0).valueOf();
			}
			return Sortable.compare(a ? calc(a) : 0, b ? calc(b) : 0);
		},
		'date-eu' : function(a, b) {
			var calc = function(v) {
				var r = v.match(/^(\d{2})-(\d{2})-(\d{4})/);
				var yr_num = r[3];
				var mo_num = parseInt(r[2]) - 1;
				var day_num = r[1];
				return new Date(yr_num, mo_num, day_num).valueOf();
			}
			return Sortable.compare(a ? calc(a) : 0, b ? calc(b) : 0);
		},
		'date-iso' : function(a, b) {
			// http://delete.me.uk/2005/03/iso8601.html ROCK!
			var calc = function(v) {
			    var d = v.match(/([\d]{4})(-([\d]{2})(-([\d]{2})(T([\d]{2}):([\d]{2})(:([\d]{2})(\.([\d]+))?)?(Z|(([-+])([\d]{2}):([\d]{2})))?)?)?)?/);
			
			    var offset = 0;
			    var date = new Date(d[1], 0, 1);
			
			    if (d[3])  { date.setMonth(d[3] - 1); }
			    if (d[5])  { date.setDate(d[5]); }
			    if (d[7])  { date.setHours(d[7]); }
			    if (d[8])  { date.setMinutes(d[8]); }
			    if (d[10]) { date.setSeconds(d[10]); }
			    if (d[12]) { date.setMilliseconds(Number("0." + d[12]) * 1000); }
			    if (d[14]) {
			        offset = (Number(d[16]) * 60) + Number(d[17]);
			        offset *= ((d[15] == '-') ? 1 : -1);
			    }
			    offset -= date.getTimezoneOffset();
			    if (offset != 0) {
			    	var time = (Number(date) + (offset * 60 * 1000));
			    	date.setTime(Number(time));
			    }
				return date.valueOf();
			}
			return Sortable.compare(a ? calc(a) : 0, b ? calc(b) : 0);

		},
		date : function(a, b) { // must be standard javascript date format
			if (a && b) {
				return Sortable.compare(new Date(a), new Date(b));
			} else {
				return Sortable.compare(a ? 1 : 0, b ? 1 : 0);
			}
			return Sortable.compare(a ? new Date(a).valueOf() : 0, b ? new Date(b).valueOf() : 0);
		},
		time : function(a, b) {
			var d = new Date();
			var ds = d.getMonth() + "/" + d.getDate() + "/" + d.getFullYear() + " ";
			return Sortable.compare(new Date(ds + a),new Date(ds + b));
		},
		currency : function(a, b) {
			a = parseFloat(a.replace(/[^-\d\.]/g, ''));
			b = parseFloat(b.replace(/[^-\d\.]/g, ''));
			return Sortable.compare(a, b);
		}
	},
	
	compare : function(a, b) {
		return a < b ? -1 : a == b ? 0 : 1;
	},
	
	detectors : $A([
		{re: /[\d]{4}-[\d]{2}-[\d]{2}(?:T[\d]{2}\:[\d]{2}(?:\:[\d]{2}(?:\.[\d]+)?)?(Z|([-+][\d]{2}:[\d]{2})?)?)?/, type : "date-iso"}, // 2005-03-26T19:51:34Z
		{re: /^sun|mon|tue|wed|thu|fri|sat\,\s\d{1,2}\sjan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec\s\d{4}(?:\s\d{2}\:\d{2}(?:\:\d{2})?(?:\sGMT(?:[+-]\d{4})?)?)?/i, type : "date"}, //Mon, 18 Dec 1995 17:28:35 GMT 
		{re: /^\d{2}-\d{2}-\d{4}/i, type : "date-eu"},
		{re: /^\d{2}\/\d{2}\/\d{4}\s?(?:\d{1,2}\:\d{2}(?:\:\d{2})?\s?[a|p]?m?)?/i, type : "date-us"},
		{re: /^\d{1,2}\:\d{2}(?:\:\d{2})?(?:\s[a|p]m)?$/i, type : "time"},
		{re: /^[$£¥€¤]/, type : "currency"}, // dollar, pound, yen, euro, generic currency symbol
		{re: /^[-+]?[\d]*\.?[\d]+(?:[eE][-+]?[\d]+)?\s?[k|m|g|t]b$/i, type : "data-size"},
		{re: /^[-+]?[\d]*\.?[\d]+(?:[eE][-+]?[\d]+)?/, type : "number"},
		{re: /^[A-Z]+$/, type : "case-sensitive-text"},
		{re: /.*/, type : "text"}
	]),
	
	addSortType : function(name, func) {
		Sortable.types[name] = func;
	},
	
	addDetector : function(regex, name) {
		Sortable.detectors.unshift({
			re: regex,
			type: name
		});
	},
	
	getBodyRows : function(table) {
		table = $(table);
		return (table.hasClassName(Sortable.options.tableScrollClass) ||
		           table.tHead && table.tHead.rows.length > 0) ? 
		               $A(table.tBodies[0].rows) :
		               $A(table.rows).without(table.rows[0]);
	},
	
	addRowClass : function(r, i) {
		r = $(r)
		r.removeClassName(Sortable.options.rowEvenClass);
		r.removeClassName(Sortable.options.rowOddClass);
		r.addClassName(((i + 1) % 2 == 0 ? Sortable.options.rowEvenClass : Sortable.options.rowOddClass));
	},
	
	getHeaderCells : function(table, cell) {
		if (!table)
			table = $(cell).up('table');
		return $A((table.tHead && table.tHead.rows.length > 0) ?
		           table.tHead.rows[table.tHead.rows.length-1].cells :
		           table.rows[0].cells);
	},
	
	getCellIndex : function(cell) {
		return $A(cell.parentNode.cells).indexOf(cell);
	},
	
	getCellText : function(cell) {
		if (!cell)
			return '';
		return cell.textContent ? cell.textContent : cell.innerText;
	},
	
	getDataType : function(cell, index, table) {
		cell = $(cell);
		// first look for a data type classname on the heading row cell
		var t = $w(cell.className).detect(function(n) {
			return (Sortable.types[n] || n == 'keep') ? true : false;
		});
		if (!t) {
			var i = index ? index : Sortable.getCellIndex(cell);
			var tbl = table ? table : cell.up('table')
			// grab same index cell from second row to try and match data type
			cell = tbl.tBodies[0].rows[0].cells[i];
			t = Sortable.detectors.detect(function(d) {
				return d.re.test(Sortable.getCellText(cell));
			})['type'];
		}
		return t;
	},
	
	setup : function(o) {
		Object.extend(Sortable.options, o || {});
		 // in case the user added more types/detectors in the setup options,
		 // we read them out and then erase them; this is so setup can be
		 // called multiple times to inject new types/detectors
		Object.extend(Sortable.types, Sortable.options.types || {});
		Sortable.options.types = {};
		if (Sortable.options.detectors) {
			Sortable.detectors = $A(Sortable.options.detectors).concat(Sortable.detectors);
			Sortable.options.detectors = [];
		}
	},
	
	options : {
		autoLoad : true,
		tableSelector : ['table.sortable'],
		columnClass : 'sort-column',
		descendingClass : 'sort-descending',
		ascendingClass : 'sort-ascending',
		noSortClass : 'no-sort',
		firstDecendingClass : 'sort-first-descending',
		firstAscendingClass : 'sort-first-ascending',
		rowEvenClass : 'even',
		rowOddClass : 'odd',
		tableScrollClass : 'scroll'
	},
	
	load : function() {
		if (Sortable.options.autoLoad) {
			$A(Sortable.options.tableSelector).each(function(s) {
				$$(s).each(function(t) {
					Sortable.init(t);
				});
			});
		}
	}
}

Event.observe(window, 'load', Sortable.load);
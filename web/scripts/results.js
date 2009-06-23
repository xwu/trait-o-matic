function _load(event) {
	// set up legend
	var l = new Legend("allele-frequency-legend", "results");
	// set up toggles
	$$(".toggle").each(function(e) {
		var t = new Toggle(e);
	});
}

Event.observe(window, "load", _load);
Event.observe(document, "ajax:update", _load);
function submit(event) {
	Event.element(event).select(".submit .label").each(function(s) {
		s.addClassName("js");
		s.update("Submitting&hellip; ");
	});
	$("asynchronous-upload-target").observe("load", function() {
		Event.element(event).select(".submit .label").each(function(s) {
			s.removeClassName("js");
			s.update();
		});
	}.bind(this));
}

function _load(event) {
	if ($("gene-form")) {
		// hidden iframe for asynchronous submission
		var n = "asynchronous-upload-target";
		if (!$(n)) {
			var a = new Element("iframe", {
				name: n,
				id: n,
				width: 0,
				height: 0
			});
			a.setStyle({ visibility: "hidden", overflow: "hidden" });
			$("foot").insert({ after: a });
		}
		$("gene-form").writeAttribute({ target: n });
		
		// hidden input to indicate that we're submitting into an iframe
		var h = new Element("input", {
			type: "hidden",
			name: "asynchronous-submission",
			value: "true"
		});
		$("gene-form").insert(h);
		
		// preload
		var i = new Image();
		i.src = "/media/blue-spinner.gif";
		$("gene-form").observe("submit", submit);
	}
}

Event.observe(window, "load", _load);
Event.observe(document, "ajax:update", _load);
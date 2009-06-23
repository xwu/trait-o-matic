function query(event) {
	var valid = true;
	
	var g = $("genotype").next(".error") ||
	        $("genotype").previous(".error");
	
	if (!$F("genotype")) {
		valid = false;
		if (g)
			g.update(" (required)");
	} else {
		if (g)
			g.update();
	}
	
	if (!valid) {
		event.stop();
		return false;
	}
	
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

function signup(event) {
	var valid = true;

	var u = $("new-username").next(".error") ||
	        $("new-username").previous(".error");
	
	if (!$F("new-username")) {
		valid = false;
		if (u)
			u.update(" (required)");
	} else {
		if (u)
			u.update();
	}
	
	var p = $("new-password").next(".error") ||
	        $("new-password").previous(".error");
	var v = $("verify-new-password").next(".error") ||
	        $("verify-new-password").previous(".error");

	if ($F("new-password") != $F("verify-new-password")) {
		valid = false;
		if (p)
			p.update();
		if (v)
			v.update(" (passwords do not match)");
	} else {
		if (p)
			p.update();
		if (v)
			v.update();
	}

	if (!valid) {
		event.stop();
		return false;
	}

	re = /^[a-z0-9,!#\$%&'\*\+\/=\?\^_`\{\|}~-]+(\.[a-z0-9,!#\$%&'\*\+\/=\?\^_`\{\|}~-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*\.([a-z]{2,})$/;
	if (!re.test($F("notification-email"))) {
		event.stop();
		var n = $("main").down(".prompt");
		if (n)
			n.update("Please provide a valid email address if you would like to be notified by email when your results are ready:");
	}
}

/*
function verify(event) {
	var a = $F("new-password");
	var b = $F("verify-new-password");
	var v = $("verify-new-password").next(".error") ||
	        $("verify-new-password").previous(".error");

	if (!v) return false;
	if (a && b && a != b) {
		v.update(" (passwords do not match)");
		return false;
	}
	v.update();
	return true;
}
*/

function _load(event) {
	if ($("query-form")) {
		// hidden iframe for asynchronous submission
		var n = "asynchronous-upload-target";
		var a = new Element("iframe", {
			name: n,
			id: n,
			width: 0,
			height: 0
		});
		a.setStyle({ visibility: "hidden", overflow: "hidden" });
		$("foot").insert({ after: a });
		$("query-form").writeAttribute({ target: n });
		
		// hidden input to indicate that we're submitting into an iframe
		var h = new Element("input", {
			type: "hidden",
			name: "asynchronous-submission",
			value: "true"
		});
		$("query-form").insert(h);
		
		// preload
		var i = new Image();
		i.src = "/media/blue-spinner.gif";
		$("query-form").observe("submit", query);
	} else if ($("signup-form")) {
/*
		$("new-password").observe("blur", verify);
		$("verify-new-password").observe("blur", verify);
*/
	
		$("signup-form").observe("submit", signup);
	}
}

Event.observe(window, "load", _load);
Event.observe(document, "ajax:update", _load);
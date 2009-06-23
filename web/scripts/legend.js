// legend.js -- MIT license
// (C) 2008 Xiaodi Wu

if (typeof Effect == "undefined") 
	throw("legend.js requires script.aculo.us effects.js");

var Legend = Class.create();

Legend.prototype = {
	container: null,
	target: null,

	initialize: function(container, target, options) {
		this.options = Object.extend({
			classNames: {
				js: "js" // appended to container when javascript is enabled
			},
			onEvent: "click",
			duration: 0.2
		}, options || {});

		this.container = $(container);
		this.target = $(target);
		
		this.container.wrap("form");
		this.container.addClassName(this.options.classNames.js);
		this.container.select("span").each(function(s) {
			var l = new Element("label");
			s.wrap(l);
			var c = new Element("input", {
				type: "checkbox"
			});
			s.insert({ before: c });
			s.insert({ before: " " });
			c.observe(this.options.onEvent, function() {
				$w(s.className).each(function(cn) {
					this.target.select("." + cn).invoke($F(c) ? "show" : "hide");
				}.bind(this));
			}.bind(this));
			c.checked = true;
		}.bind(this));
	}
}
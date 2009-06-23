// toggle.js -- MIT license
// (C) 2008 Xiaodi Wu

if (typeof Effect == "undefined") 
	throw("toggle.js requires script.aculo.us effects.js");

var Toggle = Class.create();

Toggle.prototype = {
	container: null,
	target: null,

	initialize: function(container, options) {
		this.options = Object.extend({
			classNames: {
				closed: "closed",
				target: "target",
				js: "js" // appended to container when javascript is enabled
			},
			onEvent: "click",
			duration: 0.2
		}, options || {});

		this.container = $(container);
		this.target = $(container).next();
		
		this.container.addClassName(this.options.classNames.js);
		this.container.observe(this.options.onEvent, function() {
			// explicitly written out instead of using toggle() to make sure
			// rapid clicks don't make the toggle widget go out of sync
			var c = this.container.hasClassName(this.options.classNames.closed);
			if (this.target.visible() && !c) {
				this.container.addClassName(this.options.classNames.closed);
			} else if (!this.target.visible() && c) {
				this.container.removeClassName(this.options.classNames.closed);
			}
			Effect.toggle(this.target, "appear", { duration: this.options.duration });
		}.bind(this));
	}
}
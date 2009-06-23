// glider.js -- MIT license
// (C) 2008 Xiaodi Wu
//
// Based on the original glider.js version 0.0.3 (2007)
// <http://www.missingmethod.com/projects/glider/>
// Copyright (C) 2007 Curbly LLC
// Author: Bruno Bornsztein <bruno@missingmethod.com>
//
// Original acknowledgments:
// Thanks to Andrew Dupont for refactoring help and code cleanup <http://andrewdupont.net/>

if (typeof Effect == "undefined") 
	throw("glider.js requires script.aculo.us effects.js");

var Glider = Class.create();

Glider.prototype = {
	container: null,
	scroller: null,
	anchors: null,
	sections: null,
	ticker: null,
	current: null,

	initialize: function(container, options) {
		this.options = Object.extend({
			classNames: {
				nav: "nav",
				prev: "prev",
				next: "next",
				scroller: "scroller",
				section: "section",
				current: "current",
				js: "js" // appended to container when javascript is enabled
			},
			onEvent: "click",
			duration: 0.5,
			frequency: 3
		}, options || {});

		$(container).addClassName(this.options.classNames.js);
		this.scrolling = false;
		this.container = $(container);
		this.previousAnchor = this.container.down("." + this.options.classNames.prev);
		this.nextAnchor = this.container.down("." + this.options.classNames.next);
		this.scroller = this.container.down("." + this.options.classNames.scroller);
		this.sections = this.container.select("." + this.options.classNames.section);
		this.anchors = this.container.select("." + this.options.classNames.nav + " a");

		// generate nav anchors if they don't yet exist
		if (!this.anchors.length) {
			var nav = this.container.down("." + this.options.classNames.nav);
			// do this only if there's a nav element specified
			if (nav) {
				this.sections.each(function(section, index) {
					var anchor = new Element("a", { href: "#" + section.identify() });
					nav.insert(anchor);
					this.anchors.push(anchor);
				}.bind(this));
			}
		}

		this.anchors.each(function(anchor, index) {
			var section = $(anchor.href.split("#")[1]);
			anchor._section = section;
			section._index = index;
		});
		this.anchors.invoke("observe", this.options.onEvent, this.select.bind(this));

		// try to hook up previous and next anchors; move on if they're not specified
		try {
			this.previousAnchor.observe(this.options.onEvent, this.previous.bind(this));
			this.nextAnchor.observe(this.options.onEvent, this.next.bind(this));
		} catch (e) {
		}
		
		// try to do pre-selection and other initialization; move on if there are no anchors
		if (this.anchors.length) {
			var preselected = this.options.preselected ?
				$(this.options.preselected) :
				this.anchors[0]._section;
			this.current = preselected;
			
			// useful for handling initial settings and edge cases
			this.initializeBackupExecuter();
		}
	},
	
	// initialize settings and handle edge cases
	initializeBackupExecuter: function() {
		this.backupExecuter = new PeriodicalExecuter(function() {
			if (this.scrolling && this.scrolling.finishOn > (new Date).getTime())
				return;
			if (!this.current || !this.current.visible() || !this.current.getWidth())
				return;
			var s = this.scroller.cumulativeOffset();
			var c = this.current.cumulativeOffset();
			var anchor = this.anchors[this.current._index];
			// move if we need to
			if (s < c || s > c) {
				this.moveTo(this.current, this.scroller, { 
					duration: this.options.duration
				});
			}
			// highlight the appropriate nav anchor if we need to
			else if (!anchor.hasClassName(this.options.classNames.current)) {
				this.anchors.invoke("removeClassName", this.options.classNames.current);	    
				anchor.addClassName(this.options.classNames.current);			
			}
			// start the ticker if we need to
			if (this.options.ticker && !this.ticker)
				this.start();
		}.bind(this), 0.1);
	},

	moveTo: function(element, container, options) {
		this.current = $(element);
	    var containerOffset = $(container).cumulativeOffset();
	    var elementOffset = $(element).cumulativeOffset();
	    
		this.scrolling = new Effect.SmoothScroll(container, {
			duration: options.duration,
			x: (elementOffset[0] - containerOffset[0]),
			y: (elementOffset[1] - containerOffset[1])
		});

		this.anchors.invoke("removeClassName", this.options.classNames.current);	    
		this.anchors[this.current._index].addClassName(this.options.classNames.current);			
		return false;
	},
	
	next: function() {
		var currentIndex = this.current._index;
		var nextIndex = (this.sections.length - 1 == currentIndex) ? 0 : currentIndex + 1;

		this.moveTo(this.anchors[nextIndex]._section, this.scroller, {
			duration:this.options.duration
		});
	},
	
	previous: function() {
		var currentIndex = this.current._index;
		var prevIndex = (currentIndex == 0) ? this.sections.length - 1 : currentIndex - 1;

		this.moveTo(this.anchors[prevIndex]._section, this.scroller, {
			duration:this.options.duration
		});
	},
	
	select: function(event) {
		this.stop();
		if (this.scrolling)
			this.scrolling.cancel();

		var anchor = Event.findElement(event, "a");
		this.moveTo(anchor._section, this.scroller, {
			duration:this.options.duration
		});
		Event.stop(event);
	},
	
	start: function() {
		this.ticker = new PeriodicalExecuter(this.next.bind(this), this.options.frequency);
		// disable edge case handling
		if (this.backupExecuter) {
			this.backupExecuter.stop();
			delete this.backupExecuter;
		}
	},
	
	stop: function() {
		if (this.ticker)
			this.ticker.stop();
		// re-enable edge case handling
		if (!this.backupExecuter)
			this.initializeBackupExecuter();
	}
}

Effect.SmoothScroll = Class.create();

Object.extend(Object.extend(Effect.SmoothScroll.prototype, Effect.Base.prototype), {
	initialize: function(element) {
		this.element = $(element);
		var options = Object.extend({
			x: 0,
			y: 0,
			mode: "absolute"
		}, arguments[1] || {});
		this.start(options);
	},
	
	setup: function() {
		if (this.options.continuous && !this.element._ext) {
			this.element.cleanWhitespace();
			this.element._ext = true;
			this.element.appendChild(this.element.firstChild);
		}
		
		this.originalLeft = this.element.scrollLeft;
		this.originalTop = this.element.scrollTop;
		
		if (this.options.mode == "absolute") {
			this.options.x -= this.originalLeft;
			this.options.y -= this.originalTop;
		} 
	},

	update: function(position) {   
		this.element.scrollLeft = this.options.x * position + this.originalLeft;
		this.element.scrollTop = this.options.y * position + this.originalTop;
	}
});
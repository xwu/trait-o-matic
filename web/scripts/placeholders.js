/* based on the example at:
   <http://www.beyondstandards.com/archives/input-placeholders/> */

function activatePlaceholders() {
	var detect = navigator.userAgent.toLowerCase(); 
	if (detect.indexOf("safari") > 0) {
		return false;
	}
	var inputs = document.getElementsByTagName("input");
	for (var i = 0; i < inputs.length; i++) {
		if (inputs[i].getAttribute("type") == "text" ||
		    inputs[i].getAttribute("type") == "search") {
			if (inputs[i].getAttribute("placeholder") &&
			    inputs[i].getAttribute("placeholder").length > 0) {

				if (inputs[i].value.length <= 0) {
					inputs[i].value = inputs[i].getAttribute("placeholder");
					inputs[i].className += inputs[i].className ? " placeholder" : "placeholder";
				}
				
				inputs[i].onfocus = function() {
					if (this.className.match("placeholder")) {
						this.value = "";
						var s = this.className.match(" placeholder") ? " placeholder" : "placeholder";
						this.className = this.className.replace(s,"");
					}
					return false;
				};
				inputs[i].onblur = function() {
					if (this.value.length <= 0) {
						this.value = this.getAttribute("placeholder");
						this.className += this.className ? " placeholder" : "placeholder";
					}
				};
			}
		}
	}
}

window.onload = function() {
	activatePlaceholders();
};
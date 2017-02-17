var Debugger = function() {
	this.debug = {}

	if (CONFIG_MODE_DEVELOPMENT == CURRENT_MODE) {
		for (var m in console)
			if (typeof console[m] == 'function')
				this.debug[m] = console[m].bind(window.console);
	}else{
		for (var m in console)
			if (typeof console[m] == 'function')
				this.debug[m] = function(){};
	}

	return this.debug
}
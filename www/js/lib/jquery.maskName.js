(function($) {
	var not_capitalize = ['de', 'da', 'do', 'das', 'dos', 'e'];
	$.fn.maskName = function(pos) {
		$(this).keypress(function(e){
			if(e.altKey || e.ctrlKey)
				return;

			var new_char = String.fromCharCode(e.which).toLowerCase();

			if(/[a-zà-ú\.\, ]/.test(new_char) || e.keyCode == 8){
				e.preventDefault();

				var start = this.selectionStart,
					end = this.selectionEnd;

				if(e.keyCode == 8){
					if(start == end)
						start--;

					new_char = '';
				}

				var new_value = [this.value.slice(0, start), new_char, this.value.slice(end)].join('');
				var words = new_value.split(' ');
				start += new_char.length;
				end = start;

				for (var i = 0; i < words.length; i++){
					words[i] = words[i].toLowerCase();

					if(not_capitalize.indexOf(words[i]) == -1)
						words[i] = PHP.ucfirst(words[i]);
				}

				this.value = words.join(' ');
				this.setSelectionRange(start, end);
			}
		});
	}

	$.fn.maskSimpleName = function(pos) {
		$(this).css('text-transform', 'lowercase').bind('blur change', function(){
			this.value = this.value.toLowerCase();
		});
	}

	$.fn.maskUpperName = function(pos) {
		$(this).css('text-transform', 'uppercase').bind('blur change input', function(){
			this.value = this.value.toUpperCase();
		});
	}
})(jQuery);
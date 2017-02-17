var PHP = {
	array_sum: function (array) {
		var key, sum = 0;

		if (array && typeof array === 'object' && array.change_key_case) { // Duck-type check for our own array()-created PHPJS_Array
			return array.sum.apply(array, Array.prototype.slice.call(arguments, 0));
		}

		// input sanitation
		if (typeof array !== 'object') {
			return null;
		}

		for (key in array) {
			if (!isNaN(parseFloat(array[key]))) {
				sum += parseFloat(array[key]);
			}
		}

		return sum;
	},

	basename: function(str){
		var base = new String(str).substring(str.lastIndexOf('/') + 1); 
		if(base.lastIndexOf(".") != -1)       
			base = base.substring(0, base.lastIndexOf("."));
		return base;
	},

	ucfirst: function (str) {
		str += '';
		var f = str.charAt(0)
		.toUpperCase();
		return f + str.substr(1);
	},

	is_numeric: function (mixed_var) {
		var whitespace =
		" \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000";
		return (
			typeof mixed_var === 'number' || (
				typeof mixed_var === 'string' && whitespace.indexOf(mixed_var.slice(-1)) === -1)
			) && mixed_var !== '' && !isNaN(mixed_var);
	},

	array_column: function (list, column, indice){
		var result;

		if(typeof indice != "undefined"){
			result = {};

			for(key in list)
				result[list[key][indice]] = list[key][column];
		}else{
			result = [];

			for(key in list)
				result.push( list[key][column] );
		}

		return result;
	},

	number_format: function (number, decimals, dec_point, thousands_sep) {
		number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
		var n = !isFinite(+number) ? 0 : +number,
		prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
		sep = (typeof thousands_sep === 'undefined') ? '.' : thousands_sep,
		dec = (typeof dec_point === 'undefined') ? ',' : dec_point,
		s = '',
		toFixedFix = function(n, prec) {
			var k = Math.pow(10, prec);
			return '' + (Math.round(n * k) / k).toFixed(prec);
		};
		// Fix for IE parseFloat(0.55).toFixed(0) = 0;
		s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
		if (s[0].length > 3) {
			s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
		}
		if ((s[1] || '').length < prec) {
			s[1] = s[1] || '';
			s[1] += new Array(prec - s[1].length + 1)
			.join('0');
		}
		return s.join(dec);
	},

	empty: function (mixed_var) {
		var undef, key, i, len;
		var emptyValues = [undef, null, false, 0, '', '0'];

		for (i = 0, len = emptyValues.length; i < len; i++) {
			if (mixed_var === emptyValues[i]) {
				return true;
			}
		}

		if (typeof mixed_var === 'object') {
			for (key in mixed_var) {
				return false;
			}
			return true;
		}

		return false;
	}, 

	trim: function (str, charlist) {
		var whitespace = [
			' ',
			'\n',
			'\r',
			'\t',
			'\f',
			'\x0b',
			'\xa0',
			'\u2000',
			'\u2001',
			'\u2002',
			'\u2003',
			'\u2004',
			'\u2005',
			'\u2006',
			'\u2007',
			'\u2008',
			'\u2009',
			'\u200a',
			'\u200b',
			'\u2028',
			'\u2029',
			'\u3000'
		].join('')
		var l = 0
		var i = 0
		str += ''

		if (charlist) {
			whitespace = (charlist + '').replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^:])/g, '$1')
		}

		l = str.length
		for (i = 0; i < l; i++) {
			if (whitespace.indexOf(str.charAt(i)) === -1) {
				str = str.substring(i)
				break
			}
		}

		l = str.length
		for (i = l - 1; i >= 0; i--) {
			if (whitespace.indexOf(str.charAt(i)) === -1) {
				str = str.substring(0, i + 1)
				break
			}
		}

		return whitespace.indexOf(str.charAt(0)) === -1 ? str : ''
	}
};
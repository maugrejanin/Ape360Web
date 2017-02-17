var UserTreat = {
	phone: function(value){
		value = DataTreat.numeric(value);
		var middle_length = value.length > 10? 5: 4;

		if(value.toString().length < 10)//(12) 3.4567.89'10'
			return '';

		return '(' + value.substring(0, 2) + ')' + 
					' ' + value.substring(2, middle_length + 2) + 
					'-' + value.substring(middle_length + 2, middle_length + 6);
	},

	time: function(value){
		var justtime_casedatetime = value.split(' ').pop();
		var time_parts = justtime_casedatetime.split(':');

		return time_parts[0] + ':' + (time_parts.length > 1? time_parts[1]: '00' ) + 'h';
	},

	mask: function(value, mask){
		if(PHP.empty(value))
			return value;

		var k = 0,
			result = '',
			chars = mask.split('');

		value = value.toString();
		for (var c = 0; c < chars.length; c++){
			if(chars[c] == '0'){
				result += value.charAt(k);
				k++;
			}else
				result += chars[c];
		}
			
		return result;
	},

	rg: function(value) { 
		if(!value)
			return value;
		
		var numbers = value.split('');

		return numbers.slice(0, 2).join('') + '.' + 
		numbers.slice(2, 5).join('') + '.' +
		numbers.slice(5, 8).join('') + '-' + 
		numbers.slice(8, 9).join('');
	},

	cnpj: function(value) { 
		if(!value)
			return value;
		
		var numbers = value.split('');

		return numbers.slice(0, 2).join('') + '.' + 
		numbers.slice(2, 5).join('') + '.' +
		numbers.slice(5, 8).join('') + '/' + 
		numbers.slice(8, 12).join('') + '-' +
		numbers.slice(12, 14).join('');
	},

	cpf: function(value) { 
		if(!value)
			return value;
		
		var numbers = value.split('');

		return numbers.slice(0, 3).join('') + '.' + 
		numbers.slice(3, 6).join('') + '.' +
		numbers.slice(6, 9).join('') + '-' + 
		numbers.slice(9, 11).join('');
	},

	cpfcnpj: function(value){
		if(value.length < 12)
			return UserTreat.cpf(value);
		else
			return UserTreat.cnpj(value);
	},

	date: function (date){
		if(date){
			var parts = date.split(' ').shift().split('-');
			if(parts.length == 3)
				return parts[2] + '/' + parts[1] + '/' + parts[0];

			return date;
		}

		return date;
	},

	datetime: function (datetime){
		if(!datetime)
			return datetime;
		
		var parts = datetime.split(" ");

		if(parts.length == 2){
			return UserTreat.date(parts[0]) + " - " + parts[1];
		}else
			return datetime;
	},

	percent: function (value){
		return (value? parseFloat(value) : 0) + "%";
	},

	sn: function(value){
		return (value == 'N')? "Não" : "Sim";
	},

	genre: function(value){
		return (value == 'M')? "Masculino" : "Feminino";
	},

	defaultMoney: function(value){
		return UserTreat.money(value);
	},

	money: function(value, symbol, decimals, dec_point, thousands_sep){
		symbol = typeof symbol == "undefined"? "R$" : symbol;
		decimals = typeof  decimals == "undefined"? 2 : decimals;
		dec_point = typeof dec_point == "undefined"? ',' : dec_point;
		thousands_sep = typeof thousands_sep == "undefined"? '.' : thousands_sep;

		return symbol + " " + PHP.number_format(parseFloat(value), decimals, dec_point, thousands_sep);
	}
};

var DataTreat = {
	treat: function(value){
		if(/\d{2}\/\d{2}\/\d{4}/.test(value))//99/99/9999
			return DataTreat.date(value);
		if(/^[^\d]*[+-]?[0-9]{1,3}(?:\.?[0-9]{3})*(?:\,[0-9]{2})?$/.test(value))//R$ 999.999,99
			return DataTreat.money(value);

		return value;
	},

	money: function(value){
		if(PHP.is_numeric(value))
			return parseFloat(value);

		value = value.toString().replace(/[^\d,]/g, '');
		return parseFloat(value.replace(/,/, '.'));
	},

	string: function(value){
		if( [null, false].indexOf(value) != -1 || typeof value === 'undefined')
			return '';
		else
			return value.toString();
	},

	percent: function(value){
		if(PHP.empty(value))
			return 0;
		else
			return parseFloat(value.replace('%',''));
	},

	numeric: function(value){
		if(PHP.empty(value))
			return 0;
		else
			return value.replace(/\D/g,'');
	},

	date: function(date){
		if(date){
			var parts = date.split('/');
			if(parts.length == 3)
				return parts[2] + '-' + parts[1] + '-' + parts[0];

			return date;
		}

		return date;
	}
};
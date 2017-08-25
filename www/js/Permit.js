function Permit(){

	function transform(){
		Transformer.select('id_role');
		Transformer.select('id_usuario');
		Transformer.select('id_usuario_perfil');
		Transformer.table('role_table');
	}

	function load(){
		Form.fillSelect('id_role', data_init.role_list, 'Escolha uma role');
		Form.fillSelect('id_usuario_perfil', data_init.profile_list, 'Escolha um perfil');
		$('#role_table').bootstrapTable('load', data_init.data_roles);
	}

	function userDialog(){
		Dialog.show({
			title: 'Escolha um usuário',
			message: __hidden.user_table_html,
			buttons: Dialog.getConfirmButtons(function(result){
				if(result){
					var user_record = requireTableSelect('user_table', 'Escolha um usuário para continuar');
					populateUserPermits(user_record.id_usuario);
				}
			}), 
			closable: false,
			onshown: function(dialog_ref){
				Transformer.table('user_table', {
					url: './server/permit/getuserlist',
					sidePagination: 'server'
				});
			}
		});
	}

	function populateRoles(){
		var id_usuario_perfil = $('#id_usuario_perfil').val();

		Diplomat.request({
			success: function(server_data){
				$('.role-profile-check').prop('checked', false);
				Form.populate('role_table', {id_role_profile: server_data.id_role_profile});
			},
			action: 'getProfileRoles',
			get: [id_usuario_perfil],
			loading: false
		});
	}

	function populateActions(){
		var id_role = $('#id_role').val();

		Diplomat.request({
			success: function(server_data){
				var form = document.getElementById('role_action_form');

				$('.server-action-check', form)
					.prop('checked', false)
					.not('.check-server-init')
					.prop('disabled', true);

				Form.populate('role_action_form', {id_permissoes: server_data.id_permissoes});

				$('.check-server-init', form).each(function(){
					var is_checked = $(this).is(':checked');

					var $actions_checks = $(this)
						.parents('.panel-role-action').eq(0)
						.find('.server-action-check')
						.not(this)
						.prop('disabled', !is_checked);

					if(!is_checked)
						$actions_checks.prop('checked', false);
				});
			},
			action: 'getRoleActions',
			get: [id_role],
			loading: false
		});
	}

	function populateUserPermits(id_usuario){
		Diplomat.request({
			success: function(server_data){
				var form = document.getElementById('user_action_form');

				$('.server-user-action-check', form)
					.prop('checked', false)
					.not('.check-server-init-user')
					.prop('disabled', true);

				Form.populate('user_action_form', {
					id_user_permissions: server_data.user_permission,
					id_permissoes: server_data.profile_permission
				});

				$('.check-server-init-user', form).each(function(){
					var is_checked = $(this).is(':checked');
					var $actions_checks = $(this)
						.parents('.panel-role-action').eq(0)
						.find('.server-user-action-check')
						.not(this)
						.prop('disabled', !is_checked);

					if(!is_checked)
						$actions_checks.prop('checked', false);
				});
			},
			action: 'getUserPermits',
			get: [id_usuario],
			loading: false
		});
	}

	function handleEvents(){
		$('#btn_usuario').click(userDialog);
		$('#id_usuario_perfil').change(populateRoles);
		$('#id_role').change(populateActions);
		$('#btn_submit_roles').click(persistRoles);
		$('table').on('click-row.bs.table', clickRowRoleTable);
		$('table td [type=checkbox]').click(adjustClickRowTableEvent);
		$('.btn-role-submit-actions').click(persistActions);
		$('.btn-user-submit-actions').click(persistUserActions);
		$('#btn_new_profile').click(createProfile);
		$('#btn_new_role').click(createRole);
		$('.check-server-init').change(function(){
			toggleServerInitCheck(this);
		});
		$('.check-server-init-user').change(function(){
			toggleUserServerInitCheck(this);
		});
	}

	function adjustClickRowTableEvent(e){
		e.stopImmediatePropagation();
	}

	function toggleUserServerInitCheck(check_obj){
		$(check_obj)
			.parents('.panel-role-action').eq(0)
			.find('.server-user-action-check')
			.not(check_obj)
			.prop('disabled', !$(check_obj).is(':checked'));
	}

	function toggleServerInitCheck(check_obj){
		$(check_obj)
			.parents('.panel-role-action').eq(0)
			.find('.server-action-check')
			.not(check_obj)
			.prop('disabled', !$(check_obj).is(':checked'));
	}

	function clickRowRoleTable(e, row, $element){
		var $role_profile_check = $element.find('[type=checkbox]');

		if(!$role_profile_check.is(':disabled'))
			$role_profile_check.prop('checked', !$role_profile_check.is(':checked'));
	}

	function persistRoles(){
		var role_form_data = new FormData(document.getElementById('role_form'));
		role_form_data.append('__action', 'persistRoles');

		Diplomat.fastRequest(function(server_data){
			Dialog.alert('Roles salvas com sucesso!');
		}, role_form_data);
	}

	function persistActions(){
		var action_form_data = new FormData(document.getElementById('role_action_form'));
		action_form_data.append('__action', 'persistActions');

		Diplomat.fastRequest(function(server_data){
			Dialog.alert('Permissões salvas com sucesso!');
		}, action_form_data);
	}

	function persistUserActions(){
		var user_action_form_data = new FormData(document.getElementById('user_action_form'));
		user_action_form_data.append('__action', 'persistUserActions');

		Diplomat.fastRequest(function(server_data){
			Dialog.alert('Permissões salvas com sucesso!');
		}, user_action_form_data);
	}

	function createProfile(){
		function internalCreateProfile(dialog_response, dialog_ref){
			if(!dialog_response)
				return;

			var form_new_profile = document.getElementById('form_new_profile');

			if(!Form.valid(form_new_profile))
				return false;

			var profile_form_data = new FormData(document.getElementById('form_new_profile'));
			profile_form_data.append('__action', 'createProfile');

			Diplomat.request({
				success: function(server_data){
					Dialog.alert('Perfil criado com sucesso!', Loader.refreshCurrentPage);
					dialog_ref.close();
				}, 
				data: profile_form_data,
				loading: dialog_ref,
				async: false
			});
		}

		Dialog.show({
			title: 'Novo Perfil',
			message: __hidden.new_profile_html,
			buttons: Dialog.getConfirmButtons(internalCreateProfile, {
				btnOKLabel: 'Inserir',
				btnCancelLabel: 'Cancelar'
			})
		});
	}

	function createRole(){
		function internalCreateRole(dialog_response, dialog_ref){
			if(!dialog_response)
				return;

			var form_new_role = document.getElementById('form_new_role');

			if(!Form.valid(form_new_role))
				return false;

			var role_form_data = new FormData(form_new_role);
			role_form_data.append('__action', 'createRole');

			Diplomat.request({
				success: function(server_data){
					Dialog.alert('Role criada com sucesso!', Loader.refreshCurrentPage);
					dialog_ref.close();
				}, 
				data: role_form_data,
				loading: dialog_ref,
				async: false
			});
		}

		Dialog.show({
			title: 'Nova Role',
			message: __hidden.new_role_html,
			buttons: Dialog.getConfirmButtons(internalCreateRole, {
				btnOKLabel: 'Inserir',
				btnCancelLabel: 'Cancelar'
			})
		});
	}

	function renderRoleActions(){
		var $server_actions_container = $('#check_role_action_container'),
			$server_container,
			data_actions,
			$table_action, 
			data_table,
			accordion_head_id,
			accordion_body_id;

		var init_data_actions = clone(data_init.data_actions);

		for(var server_name in init_data_actions){
			accordion_head_id = 'accordion_head_role_' + server_name;
			accordion_body_id = 'accordion_body_role_' + server_name;

			data_actions = init_data_actions[server_name];
			$server_container = $(getObjHTML(__hidden.panel_role_html));
			$server_container
				.find('.panel-heading')
				.attr('id', accordion_head_id)
				.prepend('<input type="checkbox" name="id_permissoes[' + data_actions[server_name].id_permissao + ']" class="server-action-check check-server-init">')
				.find('a')
				.attr('href', "#" + accordion_body_id)
				.html(data_actions[server_name].ds_titulo);
			$server_container
				.find('.panel-collapse')
				.attr({
					'aria-labelledby': accordion_head_id,
					'id': accordion_body_id
				})
				.find('.server-description')
				.html(data_actions[server_name].ds_permissao);

			delete data_actions[server_name];
			data_table = objectToArray(data_actions);

			if(data_table.length > 0){
				$table_action = $server_container.find('.action-server-table');
				Transformer.table($table_action, {
					pagination: false
				});
				$table_action.bootstrapTable('load', data_table);
			}else
				$server_container.find('.action-server-table').replaceWith(__hidden.no_action_server);

			$server_actions_container.append($server_container);
		}
	}

	function renderUserActions(){
		var $server_actions_container = $('#check_user_action_container'),
			$server_container,
			$server_init_checks,
			data_actions,
			$table_action, 
			data_table,
			accordion_head_id,
			accordion_body_id;

		var init_data_actions = clone(data_init.data_actions);

		for(var server_name in init_data_actions){
			accordion_head_id = 'accordion_head_user_' + server_name;
			accordion_body_id = 'accordion_body_user_' + server_name;

			data_actions = init_data_actions[server_name];
			$server_container = $(getObjHTML(__hidden.panel_user_html));
			$server_init_checks = $(getObjHTML(__hidden.server_init_checks));

			$server_init_checks
				.find('.server-action-check')
				.attr('name', 'id_permissoes[' + data_actions[server_name].id_permissao + ']')
			$server_init_checks
				.find('.server-user-action-check')
				.attr('name', 'id_user_permissions[' + data_actions[server_name].id_permissao + ']')

			$server_container
				.find('.panel-heading')
				.attr('id', accordion_head_id)
				.append($server_init_checks)
				.find('a')
				.attr('href', '#' + accordion_body_id)
				.html(data_actions[server_name].ds_titulo);
			$server_container
				.find('.panel-collapse')
				.attr({
					'aria-labelledby': accordion_head_id,
					id: accordion_body_id
				})
				.find('.server-description')
				.html(data_actions[server_name].ds_permissao);

			delete data_actions[server_name];
			data_table = objectToArray(data_actions);

			if(data_table.length > 0){
				$table_action = $server_container.find('.action-server-table');
				Transformer.table($table_action, {
					pagination: false
				});
				$table_action.bootstrapTable('load', data_table);
			}else{
				$server_container.find('.action-server-table').replaceWith(__hidden.no_action_server);
			}

			$server_actions_container.append($server_container);
		}
	}

	this.init = function(){
		Loader.setTitle();

		Diplomat.fastRequest(function(server_data){
			data_init = server_data;
			//console.log('data_init', data_init);
			renderRoleActions();
			renderUserActions();
			transform();
			load();
			handleEvents();
		}, 'init');
		Loader.removeLoad();
	}
}

Permit.treatCheckRole = function(value){
	return '<input type="checkbox" name="id_role_profile[' + value + ']" class="role-profile-check">';
}

Permit.treatCheckAction = function(value){
	return '<input disabled type="checkbox" name="id_permissoes[' + value + ']" class="server-action-check">';
}

Permit.treatCheckUserPermit = function(value){
	return '<input disabled type="checkbox" name="id_user_permissions[' + value + ']" class="server-user-action-check">';
}
$(document).ready(function() {

	var t = window.location.pathname.split('/');

	switch (t[t.length - 1]) {
		case 'addgame':
			$('#form-addgame').submit(function(e) {
				e.preventDefault();
				var $err = $('#form-addgame-error');

				var error = "";

				if ($('#score2').val() === $('#score1').val()) {
					error = 'Erreur : Les deux scores ne peuvent pas être égaux.';
				}

				if ($('#player1Team1').val() === $('#player2Team1').val() || $('#player1Team2').val() === $('#player2Team2').val()) {
					error = 'Erreur : Même joueur présent dans la même équipe';
				}

				if ($('#player1Team1').val() === $('#player1Team2').val() || $('#player1Team1').val() === $('#player2Team2').val() || $('#player2Team1').val() === $('#player1Team2').val() || $('#player2Team1').val() === $('#player2Team2').val()) {
					error = 'Erreur : Même joueur présent dans deux équipes';
				}

				if ($('#datepartie').val().trim() === '') {
					error = "Erreur : Veuillez spécifier une date";
				}

				if (error !== "") {
					$err.text(error).show("blind").delay(3000).hide("blind");
				} else {
					var data = {
						date: $('#datepartie').val(),
						joueur1equipe1: $('#player1Team1').val(),
						joueur2equipe1: $('#player2Team1').val(),
						joueur1equipe2: $('#player1Team2').val(),
						joueur2equipe2: $('#player2Team2').val(),
						score1: $('#score1').val(),
						score2: $('#score2').val()
					};
					$.post('savegame', data, function(ret) {
						if (ret === 'ok') {
							$('#submitformgame').prop('disabled', true);
							$err.removeClass('text-danger').addClass('text-success');
							$err.text('Partie enregistrée !')
							$err.removeClass('hidden');
							setTimeout(function(){ $err.addClass('hidden'); window.location.reload(); },1000);
						} else {
							$err.text('Erreur : La partie n\'a pas été enregistrée').removeClass("hidden");
							setTimeout(function(){ $err.addClass('hidden'); }, 2000);
						}
					});
				}
				return false;
			});

			$('#datepartie').pickadate({format: 'dd-mm-yyyy', formatSubmit: 'dd-mm-yyyy'});
			break;
		case 'game':
			$('#gamedate').pickadate({format: 'dd-mm-yyyy', formatSubmit: 'dd-mm-yyyy'});/*.change(function() {
				$('#formSearchGame').submit();
			});*/

			$('.del-game').click(function() {
				var tr = $(this).parent().parent();
				if (confirm('Supprimer cette partie ?')) {
					$.post('delgame', {id: $(this).data('id')}, function() {
						tr.remove();
					});
				}
			});
			break;
		case 'player':
			$('.player, .period-selector').click(function() {

				if ($(this).hasClass('period-selector')) {
					$('.period-selector.active').removeClass('active');
					$(this).addClass('active');
				} else {
					$('.player.active').removeClass('active');
					$(this).addClass('active');
				}

				var data = {
					playerId: $('.player.active').data('id'),
					date: $('.period-selector.active > a').data('value')
				};

				$.post('morestat', data, function(pdata) {

					if (typeof pdata.stats.nbGames !== 'undefined') {
						for (var i in pdata.stats) {
							if (pdata.stats[i] === null) {
								$('#stat-' + i).text('N/A');
							} else {
								$('#stat-' + i).text(pdata.stats[i]);
							}
						}
						$('#playerstatstable, #playerstatperiod, #playerstattitle').removeClass('hidden');
						$('#playerstatnotice').addClass('hidden');
						$('#playername').text($('.player.active').text());
					}

					$('#chart1').highcharts({
						chart: {type: 'line'},
						credits: {enabled: false},
						title: {text: ''},
						xAxis: {
							categories: pdata.graph.dates
						},
						yAxis: [{
								min: 0,
								title: {text: 'Parties jouées'}
							}, {
								min: 0,
								max: 1,
								title: {text: 'Ratio'},
								opposite: true
							}],
						series: [{
								yAxis: 0,
								name: 'Victoires',
								data: pdata.graph.victoires,
								color: '#77b300'
							}, {
								yAxis: 0,
								name: 'Défaites',
								data: pdata.graph.defaites,
								color: '#f04124'
							}, {
								yAxis: 1,
								name: 'Ratio',
								data: pdata.graph.ratio,
								color: '#2a9fd6'
							}],
						tooltip: {
							shared: true,
							borderRadius: 0
						}
					});
				}, 'json');

				$('html, body').animate({
					scrollTop: $('#morestat').offset().top - 45 // -45 because of bootstrap navbar
				}, 'slow');
				return false;
			});
			break;
		case 'playerstat' :
			$('.graphme').click(function() {
				$.post('playerstatgraph', {action: $(this).data('action')}, function(ret) {
					$('#customchart').highcharts({
						chart: {type: 'line'},
						credits: {enabled: false},
						title: {text: 'Nombre de parties jouées par jours'},
						xAxis: {categories: ret.date},
						yAxis: {
							min: 0,
							title: {text: 'Parties jouées'}
						},
						series: [{
								name: 'Nombre de parties',
								data: ret.data
							}]
					});
				}, 'json');
			});
			break;
		case 'matchmaker' :
			$('.clickme-player').click(function() {
				$(this).toggleClass('active');
			});

			$('.buttonmaker').click(function() {
				var players = [];
				$('.clickme-player.active').each(function() {
					players.push($(this).data('id'));
				});
				if (players.length < 4) {
					var $err = $('#error-matchmaking');
					$err.removeClass('hidden');
					setTimeout(function(){ $err.addClass('hidden'); },3000);
				} else {
					$.post('matchmaking', {ids: players}, function(pdata) {
						console.log(pdata);
					});
				}
			});
			break;
		case 'useradmin':
			$('.role-selector').chosen();
			$('.save-user').click(function(){
				var data = {
					id : $(this).data('id'),
					enabled : $(this).parent().parent().find('input[name=enabled]').prop('checked') ? 1 : 0,
					roles : $(this).parent().parent().find('select[name=roles]').val()
				};
				$.post('saveuser', data, function(ret){
					var span = $('#user-msg');
					span.removeClass('hidden text-success text-danger');
					if(ret === 'OK') {
						span.addClass('text-success')
							.text('Modifications enregistrées.');
					} else {
						span.addClass('text-danger')
							.text('Modifications non enregistrées.');
					}
					setTimeout(function(){ span.addClass('hidden'); },3000);
				});
			});
			break;
		case '':
			$.post('nbgame', {playerId: $(this).data('id')}, function(pdata) {
				$('#nbbaby').highcharts({
					chart: {type: 'column'},
					title: {text: 'Nombre de parties jouées par jours'},
					xAxis: {
						categories: pdata.date
					},
					yAxis: {
						min: 0,
						title: {text: ''}
					},
					series: [{
							name: 'Nombre de parties',
							data: pdata.nb
						}], credits: {enabled: false}
				});
			});
			break;
	}
});

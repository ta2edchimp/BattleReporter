    !function(battlereport, teamNames, combatants, editor, changes){
        if (!battlereport) return;
        teamNames = ['teamA', 'teamB', 'teamC'];
        combatants = [];
        changes = {};
        function getCombatant(id) {
            if (!id) return null;
            l = combatants.length;
            while (l--) {
                if ((c = combatants[l]).brCombatantID == id) return c;
            }
            return null;
        }
        function handle(action, el) {
            var combatant = getCombatant(el.attr('data-combatant-id')),
                oldTeam, oldTeamIdx, idx, newTeamName;
            if (!combatant) return;
            switch (action) {
                case 'move-left':
                    oldTeam = battlereport[combatant._team].members;
                    oldTeamIdx = teamNames.indexOf(combatant._team);
                    idx = oldTeam.indexOf(combatant);
                    newTeamName = teamNames[(oldTeamIdx == 0 ? teamNames.length : oldTeamIdx) - 1];
                    battlereport[(combatant._team = newTeamName)].members.push(combatant);
                    el.appendTo($('#battlereport-' + newTeamName));
                    changes[combatant.brCombatantID] = changes[combatant.brCombatantID] || {};
                    changes[combatant.brCombatantID].teamName = newTeamName;
                    break;
                case 'move-right':
                    oldTeam = battlereport[combatant._team].members;
                    oldTeamIdx = teamNames.indexOf(combatant._team);
                    idx = oldTeam.indexOf(combatant);
                    newTeamName = teamNames[((oldTeamIdx + 1) == teamNames.length ? -1 : oldTeamIdx) + 1];
                    battlereport[(combatant._team = newTeamName)].members.push(combatant);
                    el.appendTo($('#battlereport-' + newTeamName));
                    changes[combatant.brCombatantID] = changes[combatant.brCombatantID] || {};
                    changes[combatant.brCombatantID].teamName = newTeamName;
                    break;
                case "hide-on-br":
                    if (!combatant.died) {
                        combatant.brHidden = true;
                        el.addClass('hidden-on-br');
                        changes[combatant.brCombatantID] = changes[combatant.brCombatantID] || {};
                        changes[combatant.brCombatantID].brHidden = true;
                    }
                    break;
                case "show-on-br":
                    combatant.brHidden = false;
                    if (el.hasClass('hidden-on-br'))
                        el.removeClass('hidden-on-br');
                    changes[combatant.brCombatantID] = changes[combatant.brCombatantID] || {};
                    changes[combatant.brCombatantID].brHidden = false;
                    break;
                case "trash":
                    combatant.brDeleted = true;
                    el.detach();
                    changes[combatant.brCombatantID] = changes[combatant.brCombatantID] || {};
                    changes[combatant.brCombatantID].brDeleted = true;
                    break;
            }
        }
        for (var teamName in battlereport) {
            if (teamNames.indexOf(teamName) == -1)
                continue;
            var team = battlereport[teamName],
                l = team.members.length,
                combatant;
            while (l--) {
                combatants.push(combatant = team.members[l]);
                combatant._team = teamName;
            }
        }
        editor = $('#editor-panel').css({
            position: 'absolute',
            bottom: 0, right: 0,
            width: 'auto', height: 'auto'
        });
        ['move-left', 'move-right', 'show-on-br', 'hide-on-br', 'trash'].forEach(function (n) {
            $('#editor-panel .' + n).click(function (e) {
                e.preventDefault();
                handle(n, $(this).closest('.combatant'));
            });
        });
        $('*[data-combatant-id]').hover(function () {
            var el = $(this),
                cmbt = getCombatant(el.attr('data-combatant-id')),
                cnt = el.find('.combatant-details');
            if (!cmbt) return;
            cnt.css('position', 'relative');
            editor.attr('class', '');
            if (cmbt._team == 'teamA') editor.addClass('not-movable-left');
            if (cmbt._team == 'teamC') editor.addClass('not-movable-right');
            if (cmbt.brCombatantID == 0)
                editor.addClass('not-hideable').addClass('not-showable');
            else
                editor.addClass('not-trashable').addClass(cmbt.brHidden ? 'not-hideable' : 'not-showable');
            if (cmbt.died)
                editor.addClass(cmbt.brHidden ? 'not-showable' : 'not-hideable');
            editor.appendTo(cnt).show();
        }, function () {
            editor.hide();
        });
        $('#save-br').click(function () {
            $('#battleReportChanges').val(JSON.stringify(changes));
            $('#battleReportEditor').submit();
        });
    }({{ battleReport.toJSON()|raw }});

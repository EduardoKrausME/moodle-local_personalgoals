<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

/**
 * Personal goals for learner self-regulation.
 *
 * @package    local_personalgoals
 * @copyright  2026 Eduardo Kraus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['acceptgoal'] = 'Usar esta meta';
$string['activegoals'] = 'Metas ativas';
$string['allgoals'] = 'Todas as metas';
$string['allowcustomdates'] = 'Permitir datas personalizadas';
$string['allowedrange'] = 'Faixa permitida';
$string['backtogoals'] = 'Voltar para minhas metas';
$string['cancelconfirmbody'] = 'Seu progresso continuará no histórico pessoal e você poderá criar uma nova meta depois.';
$string['cancelconfirmtitle'] = 'Cancelar esta meta?';
$string['cancelgoal'] = 'Cancelar meta';
$string['cannotaccepttemplate'] = 'Você não pode aceitar uma meta sugerida para outro usuário.';
$string['cannotcancelgoal'] = 'Você não pode cancelar esta meta.';
$string['cannotcreategoal'] = 'Você não pode criar esta meta para outro usuário.';
$string['cannotrecreategoal'] = 'Você não pode recriar esta meta.';
$string['cannotviewgoal'] = 'Você não pode visualizar esta meta.';
$string['celebratecompletion'] = 'Solicitar celebração visual quando a meta for concluída';
$string['celebrationmessage'] = 'Você concluiu “{$a}”.';
$string['celebrationtitle'] = 'Meta concluída';
$string['completedcount'] = 'Concluídas';
$string['completionrewards'] = 'Recompensas opcionais pela conclusão';
$string['createagain'] = 'Criar nova meta como esta';
$string['createdcount'] = 'Criadas';
$string['creategoal'] = 'Criar meta';
$string['customdatesnotallowed'] = 'Datas personalizadas não estão habilitadas para este tipo de meta.';
$string['customend'] = 'Data de término';
$string['customendrequired'] = 'Escolha uma data de término para o período personalizado.';
$string['customstart'] = 'Data de início';
$string['dashboardintro'] = 'Defina objetivos de estudo que façam sentido para você e acompanhe a sua própria evolução.';
$string['durationtoolong'] = 'Esta meta pode durar no máximo {$a} dias.';
$string['editlimits'] = 'Editar limites do aluno';
$string['edittemplate'] = 'Editar meta sugerida';
$string['eventgoalcancelled'] = 'Meta pessoal cancelada';
$string['eventgoalcompleted'] = 'Meta pessoal concluída';
$string['eventgoalcreated'] = 'Meta pessoal criada';
$string['eventgoalexpired'] = 'Meta pessoal encerrada';
$string['expiredcount'] = 'Encerradas';
$string['expiredneutral'] = 'Esta meta terminou com {$a->current} de {$a->target} concluídos.';
$string['goalcancellednotice'] = 'A meta foi movida para o seu histórico.';
$string['goalcreated'] = 'Meta criada.';
$string['goalhistory'] = 'Histórico de metas';
$string['goalname'] = 'Nome da meta';
$string['goalnamerequired'] = 'Informe um nome para a meta.';
$string['goalrecreated'] = 'Uma nova meta foi criada com base na anterior.';
$string['goaltype'] = 'Tipo de meta';
$string['goaltype_active_days'] = 'Estudar em dias ativos';
$string['goaltype_active_days_desc'] = 'Estude em uma quantidade definida de dias diferentes.';
$string['goaltype_activity_completion'] = 'Concluir atividades';
$string['goaltype_activity_completion_desc'] = 'Conclua uma quantidade definida de atividades do curso depois que a meta começar.';
$string['goaltype_course_completion'] = 'Alcançar progresso no curso';
$string['goaltype_course_completion_desc'] = 'Alcance uma porcentagem definida de conclusão geral do curso.';
$string['goaltype_quiz_attempts'] = 'Concluir tentativas de quiz';
$string['goaltype_quiz_attempts_desc'] = 'Envie uma quantidade definida de tentativas de quiz.';
$string['goaltype_specific_activities'] = 'Concluir atividades específicas';
$string['goaltype_specific_activities_desc'] = 'Conclua um conjunto selecionado de atividades do curso.';
$string['goaltype_study_time'] = 'Tempo de estudo';
$string['goaltype_study_time_desc'] = 'Acumule uma quantidade definida de minutos de estudo a partir de interações significativas no curso.';
$string['goaltype_xp'] = 'Ganhar XP';
$string['goaltype_xp_desc'] = 'Ganhe uma quantidade definida de XP a partir do momento em que a meta começa.';
$string['goaltypeenabled'] = 'O aluno pode criar este tipo de meta';
$string['goaltypenotallowed'] = 'Este tipo de meta não está disponível para metas criadas pelo aluno neste curso.';
$string['historyintro'] = 'Veja o que você planejou e como suas metas evoluíram ao longo do tempo.';
$string['invaliddeadline'] = 'A data de término deve ser posterior à data de início.';
$string['invalidgoalconfiguration'] = 'A configuração da meta é inválida: {$a}';
$string['invalidperiod'] = 'Período de meta inválido.';
$string['limitssaved'] = 'Limites das metas salvos.';
$string['managegoals'] = 'Gerenciar sugestões de metas';
$string['manageintro'] = 'Crie sugestões opcionais para os alunos e defina os limites das metas que eles podem criar por conta própria.';
$string['maxactive'] = 'Máximo de metas ativas deste tipo';
$string['maxactivegoalsreached'] = 'Você já possui o máximo de {$a} metas ativas deste tipo.';
$string['maxdurationdays'] = 'Duração máxima em dias';
$string['maxmustbegreaterthanmin'] = 'O valor máximo deve ser maior ou igual ao mínimo.';
$string['maxtarget'] = 'Meta máxima';
$string['mintarget'] = 'Meta mínima';
$string['monthlyevolution'] = 'Evolução mensal';
$string['mygoals'] = 'Minhas metas';
$string['newtemplate'] = 'Nova meta sugerida';
$string['noactivegoals'] = 'Nenhuma meta ativa agora';
$string['noactivegoalsdesc'] = 'Você pode criar uma meta ou escolher uma das sugestões disponíveis neste curso.';
$string['nogoalhistory'] = 'Você ainda não possui histórico de metas.';
$string['nonnegativevalue'] = 'Use zero ou um valor positivo.';
$string['nosuggestions'] = 'Não há metas sugeridas para este curso neste momento.';
$string['notemplates'] = 'Nenhum modelo de meta sugerida foi criado.';
$string['percentagecannotexceed100'] = 'Metas de conclusão do curso não podem ultrapassar 100%.';
$string['period'] = 'Período';
$string['periodcustom'] = 'Datas personalizadas';
$string['perioddaily'] = 'Hoje';
$string['periodmonthly'] = 'Este mês';
$string['periodnone'] = 'Sem prazo';
$string['periodweekly'] = 'Esta semana';
$string['personalgoals:managelimits'] = 'Gerenciar limites de metas pessoais';
$string['personalgoals:manageown'] = 'Gerenciar as próprias metas pessoais';
$string['personalgoals:managetemplates'] = 'Gerenciar modelos sugeridos de metas pessoais';
$string['personalgoals:viewown'] = 'Visualizar as próprias metas pessoais';
$string['pluginname'] = 'Metas pessoais';
$string['positivevalue'] = 'Use um valor maior que zero.';
$string['privacy:metadata:days'] = 'Dias distintos de estudo usados pelas metas de dias ativos.';
$string['privacy:metadata:days:daykey'] = 'O dia do calendário no fuso horário do aluno.';
$string['privacy:metadata:goals'] = 'Metas pessoais de estudo criadas ou aceitas pelo aluno.';
$string['privacy:metadata:goals:completedat'] = 'Quando a meta foi concluída.';
$string['privacy:metadata:goals:configjson'] = 'Configuração específica da meta.';
$string['privacy:metadata:goals:courseid'] = 'O curso ao qual a meta se aplica.';
$string['privacy:metadata:goals:goaltype'] = 'O tipo da meta pessoal.';
$string['privacy:metadata:goals:name'] = 'O nome da meta apresentado ao aluno.';
$string['privacy:metadata:goals:status'] = 'Situação atual da meta.';
$string['privacy:metadata:goals:targetvalue'] = 'O valor definido como objetivo.';
$string['privacy:metadata:goals:timecreated'] = 'Quando a meta foi criada.';
$string['privacy:metadata:goals:timeend'] = 'Quando a meta termina, quando existe prazo.';
$string['privacy:metadata:goals:timemodified'] = 'Quando a meta foi alterada pela última vez.';
$string['privacy:metadata:goals:timestart'] = 'Quando a meta começa.';
$string['privacy:metadata:goals:userid'] = 'O aluno proprietário da meta.';
$string['privacy:metadata:progress'] = 'Progresso armazenado das metas pessoais.';
$string['privacy:metadata:sessions'] = 'A interação significativa mais recente no curso usada para estimar tempo de estudo.';
$string['privacy:metadata:sessions:lastactivity'] = 'Data e hora da interação significativa mais recente.';
$string['repeatrewardallowed'] = 'Permitir recompensa ao recriar esta meta';
$string['repeatrewardallowed_help'] = 'Por padrão, uma meta recriada não copia recompensas, evitando repetir uma meta fácil apenas para acumular recompensas.';
$string['rewardcredits'] = 'Créditos ao concluir';
$string['rewardxp'] = 'XP ao concluir';
$string['savelimits'] = 'Salvar limites';
$string['savetemplate'] = 'Salvar meta sugerida';
$string['selectatleastoneactivity'] = 'Selecione pelo menos uma atividade.';
$string['selfregulationhint'] = 'Suas metas são pessoais: não existe ranking, leaderboard nem comparação com outros alunos.';
$string['specificactivities'] = 'Atividades incluídas na meta';
$string['statusactive'] = 'Ativa';
$string['statuscancelled'] = 'Cancelada';
$string['statuscompleted'] = 'Concluída';
$string['statusexpired'] = 'Encerrada';
$string['studentlimits'] = 'Limites para metas do aluno';
$string['studentlimitsdesc'] = 'Os limites definem quais metas pessoais o aluno pode criar; eles não criam metas em nome do aluno.';
$string['suggestedgoals'] = 'Metas sugeridas';
$string['suggestedgoalsdesc'] = 'Estas são sugestões da equipe do curso. Aceitar uma delas é sempre uma decisão sua.';
$string['suggestedtemplates'] = 'Modelos de metas sugeridas';
$string['target'] = 'Objetivo';
$string['targetmustbepositive'] = 'A meta deve ser maior que zero.';
$string['targetoutsideallowedrange'] = 'Escolha uma meta entre {$a->min} e {$a->max}.';
$string['targetvalue'] = 'Valor da meta';
$string['taskcleanupeventlog'] = 'Limpar dados antigos de deduplicação de eventos de metas';
$string['taskexpiregoals'] = 'Encerrar metas pessoais cujo prazo terminou';
$string['templateaccepted'] = 'A meta sugerida foi adicionada às suas metas.';
$string['templateactive'] = 'Disponível para os alunos';
$string['templatedescription'] = 'Descrição';
$string['templatename'] = 'Nome da meta sugerida';
$string['templatesaved'] = 'Meta sugerida salva.';
$string['unknowngoaltype'] = 'Tipo de meta desconhecido: {$a}';
$string['until'] = 'Até';
$string['valueminutes'] = '{$a} min';
$string['valuepercentage'] = '{$a}%';
$string['valuexp'] = '{$a} XP';
$string['xpunavailable'] = 'Metas de XP exigem que o local_personalxp esteja disponível e habilitado.';

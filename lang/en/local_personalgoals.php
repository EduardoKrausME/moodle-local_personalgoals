<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Personal goals for learner self-regulation.
 *
 * @package    local_personalgoals
 * @copyright  2026 Eduardo Kraus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['acceptgoal'] = 'Use this goal';
$string['activegoals'] = 'Active goals';
$string['allgoals'] = 'All goals';
$string['allowcustomdates'] = 'Allow custom dates';
$string['allowedrange'] = 'Allowed target range';
$string['backtogoals'] = 'Back to my goals';
$string['cancelconfirmbody'] = 'Your progress will remain in your personal history, and you can create a new goal later.';
$string['cancelconfirmtitle'] = 'Cancel this goal?';
$string['cancelgoal'] = 'Cancel goal';
$string['cannotaccepttemplate'] = 'You cannot accept a suggested goal for another user.';
$string['cannotcancelgoal'] = 'You cannot cancel this goal.';
$string['cannotcreategoal'] = 'You cannot create this goal for another user.';
$string['cannotrecreategoal'] = 'You cannot recreate this goal.';
$string['cannotviewgoal'] = 'You cannot view this goal.';
$string['celebratecompletion'] = 'Request a visual celebration when completed';
$string['celebrationmessage'] = 'You completed “{$a}”.';
$string['celebrationtitle'] = 'Goal completed';
$string['completedcount'] = 'Completed';
$string['completionrewards'] = 'Optional completion rewards';
$string['createagain'] = 'Create a new goal like this';
$string['createdcount'] = 'Created';
$string['creategoal'] = 'Create goal';
$string['customdatesnotallowed'] = 'Custom dates are not enabled for this goal type.';
$string['customend'] = 'End date';
$string['customendrequired'] = 'Choose an end date for a custom period.';
$string['customstart'] = 'Start date';
$string['dashboardintro'] = 'Set study goals that make sense for you and follow your own progress.';
$string['durationtoolong'] = 'This goal may last at most {$a} days.';
$string['editlimits'] = 'Edit learner limits';
$string['edittemplate'] = 'Edit suggested goal';
$string['eventgoalcancelled'] = 'Personal goal cancelled';
$string['eventgoalcompleted'] = 'Personal goal completed';
$string['eventgoalcreated'] = 'Personal goal created';
$string['eventgoalexpired'] = 'Personal goal ended';
$string['expiredcount'] = 'Ended';
$string['expiredneutral'] = 'This goal ended with {$a->current} of {$a->target} completed.';
$string['goalcancellednotice'] = 'The goal was moved to your history.';
$string['goalcreated'] = 'Goal created.';
$string['goalhistory'] = 'Goal history';
$string['goalname'] = 'Goal name';
$string['goalnamerequired'] = 'Enter a name for the goal.';
$string['goalrecreated'] = 'A new goal was created from the previous one.';
$string['goaltype'] = 'Goal type';
$string['goaltype_active_days'] = 'Study on active days';
$string['goaltype_active_days_desc'] = 'Study on a chosen number of distinct days.';
$string['goaltype_activity_completion'] = 'Complete activities';
$string['goaltype_activity_completion_desc'] = 'Complete a chosen number of course activities after the goal starts.';
$string['goaltype_course_completion'] = 'Reach course progress';
$string['goaltype_course_completion_desc'] = 'Reach a chosen percentage of overall course completion.';
$string['goaltype_quiz_attempts'] = 'Complete quiz attempts';
$string['goaltype_quiz_attempts_desc'] = 'Submit a chosen number of quiz attempts.';
$string['goaltype_specific_activities'] = 'Complete specific activities';
$string['goaltype_specific_activities_desc'] = 'Complete a selected set of course activities.';
$string['goaltype_study_time'] = 'Study time';
$string['goaltype_study_time_desc'] = 'Accumulate a chosen number of study minutes from meaningful course interactions.';
$string['goaltype_xp'] = 'Gain XP';
$string['goaltype_xp_desc'] = 'Gain a chosen amount of XP from the moment the goal starts.';
$string['goaltypeenabled'] = 'Learners may create this goal type';
$string['goaltypenotallowed'] = 'This goal type is not available for learner-created goals in this course.';
$string['historyintro'] = 'Review what you planned and how your goals evolved over time.';
$string['invaliddeadline'] = 'The end date must be after the start date.';
$string['invalidgoalconfiguration'] = 'The goal configuration is invalid: {$a}';
$string['invalidperiod'] = 'Invalid goal period.';
$string['limitssaved'] = 'Goal limits saved.';
$string['managegoals'] = 'Manage goal suggestions';
$string['manageintro'] = 'Create optional suggestions for learners and define the boundaries for goals they create themselves.';
$string['maxactive'] = 'Maximum active goals of this type';
$string['maxactivegoalsreached'] = 'You already have the maximum of {$a} active goals of this type.';
$string['maxdurationdays'] = 'Maximum duration in days';
$string['maxmustbegreaterthanmin'] = 'The maximum must be greater than or equal to the minimum.';
$string['maxtarget'] = 'Maximum target';
$string['mintarget'] = 'Minimum target';
$string['monthlyevolution'] = 'Monthly evolution';
$string['mygoals'] = 'My goals';
$string['newtemplate'] = 'New suggested goal';
$string['noactivegoals'] = 'No active goals right now';
$string['noactivegoalsdesc'] = 'You can create a goal or choose one of the suggestions available in this course.';
$string['nogoalhistory'] = 'You do not have goal history yet.';
$string['nonnegativevalue'] = 'Use zero or a positive value.';
$string['nosuggestions'] = 'There are no suggested goals for this course right now.';
$string['notemplates'] = 'No suggested goal templates have been created.';
$string['percentagecannotexceed100'] = 'Course completion goals cannot exceed 100%.';
$string['period'] = 'Period';
$string['periodcustom'] = 'Custom dates';
$string['perioddaily'] = 'Today';
$string['periodmonthly'] = 'This month';
$string['periodnone'] = 'No deadline';
$string['periodweekly'] = 'This week';
$string['personalgoals:managelimits'] = 'Manage personal goal limits';
$string['personalgoals:manageown'] = 'Manage own personal goals';
$string['personalgoals:managetemplates'] = 'Manage suggested personal goal templates';
$string['personalgoals:viewown'] = 'View own personal goals';
$string['pluginname'] = 'Personal goals';
$string['positivevalue'] = 'Use a value greater than zero.';
$string['privacy:metadata:days'] = 'Distinct study days used by active-days goals.';
$string['privacy:metadata:days:daykey'] = 'The learner-local calendar day.';
$string['privacy:metadata:goals'] = 'Personal study goals created or accepted by the learner.';
$string['privacy:metadata:goals:completedat'] = 'When the goal was completed.';
$string['privacy:metadata:goals:configjson'] = 'Goal-specific configuration.';
$string['privacy:metadata:goals:courseid'] = 'The course where the goal applies.';
$string['privacy:metadata:goals:goaltype'] = 'The type of personal goal.';
$string['privacy:metadata:goals:name'] = 'The learner-facing name of the goal.';
$string['privacy:metadata:goals:status'] = 'Current goal status.';
$string['privacy:metadata:goals:targetvalue'] = 'The target selected for the goal.';
$string['privacy:metadata:goals:timecreated'] = 'When the goal was created.';
$string['privacy:metadata:goals:timeend'] = 'When the goal ends, if it has a deadline.';
$string['privacy:metadata:goals:timemodified'] = 'When the goal was last changed.';
$string['privacy:metadata:goals:timestart'] = 'When the goal begins.';
$string['privacy:metadata:goals:userid'] = 'The learner who owns the goal.';
$string['privacy:metadata:progress'] = 'Cached personal goal progress.';
$string['privacy:metadata:sessions'] = 'The most recent meaningful course interaction used to estimate study time.';
$string['privacy:metadata:sessions:lastactivity'] = 'Timestamp of the most recent meaningful interaction.';
$string['repeatrewardallowed'] = 'Allow reward when this goal is recreated';
$string['repeatrewardallowed_help'] = 'Normally a recreated goal does not copy rewards, which prevents repeatedly completing an easy goal only to accumulate rewards.';
$string['rewardcredits'] = 'Credits on completion';
$string['rewardxp'] = 'XP on completion';
$string['savelimits'] = 'Save limits';
$string['savetemplate'] = 'Save suggested goal';
$string['selectatleastoneactivity'] = 'Select at least one activity.';
$string['selfregulationhint'] = 'Your goals are personal: there is no ranking, leaderboard or comparison with other learners.';
$string['specificactivities'] = 'Activities included in the goal';
$string['statusactive'] = 'Active';
$string['statuscancelled'] = 'Cancelled';
$string['statuscompleted'] = 'Completed';
$string['statusexpired'] = 'Ended';
$string['studentlimits'] = 'Learner goal limits';
$string['studentlimitsdesc'] = 'Limits define which personal goals learners may create; they do not create goals on anyone’s behalf.';
$string['suggestedgoals'] = 'Suggested goals';
$string['suggestedgoalsdesc'] = 'These are suggestions from the course team. Choosing one is always your decision.';
$string['suggestedtemplates'] = 'Suggested goal templates';
$string['target'] = 'Target';
$string['targetmustbepositive'] = 'The target must be greater than zero.';
$string['targetoutsideallowedrange'] = 'Choose a target between {$a->min} and {$a->max}.';
$string['targetvalue'] = 'Target value';
$string['taskcleanupeventlog'] = 'Clean old personal goal event deduplication data';
$string['taskexpiregoals'] = 'End personal goals whose deadline has passed';
$string['templateaccepted'] = 'The suggested goal was added to your goals.';
$string['templateactive'] = 'Available to learners';
$string['templatedescription'] = 'Description';
$string['templatename'] = 'Suggested goal name';
$string['templatesaved'] = 'Suggested goal saved.';
$string['unknowngoaltype'] = 'Unknown goal type: {$a}';
$string['until'] = 'Until';
$string['valueminutes'] = '{$a} min';
$string['valuepercentage'] = '{$a}%';
$string['valuexp'] = '{$a} XP';
$string['xpunavailable'] = 'XP goals require local_personalxp to be available and enabled.';

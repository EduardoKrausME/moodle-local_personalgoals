# Personal Goals

`local_personalgoals` gives each learner a private space to define study goals and follow their own progress. The plugin is built around self-regulation: progress is always compared with the learner's own target, never with other learners.

There is no ranking, leaderboard, best-student indicator, class average used as competition, percentile, or message such as “you complete more goals than 70% of the class”.

## Goal types

The initial goal types are:

- **XP** — gain a chosen amount of XP after the goal starts. When `local_personalxp` is available, the current total is read through its public service API and the goal stores a baseline instead of reading its database tables.
- **Active days** — study on a chosen number of distinct learner-local calendar days.
- **Activity completion** — complete a chosen number of activities after the goal starts.
- **Course completion** — reach a chosen percentage of overall course progress.
- **Quiz attempts** — submit a chosen number of quiz attempts.
- **Specific activities** — complete a selected set of course activities.
- **Study time** — accumulate study minutes from meaningful course interactions. Long gaps are not treated as continuous study time.

A goal may apply to today, the current week, the current month, a custom date range, or have no deadline. Daily, weekly and monthly boundaries use the learner's Moodle timezone.

## Learner workflow

The learner opens **My goals**, creates a personal goal, and follows a progress bar such as:

- Study on 3 days this week — 2 / 3
- Complete 5 activities — 3 / 5
- Gain 500 XP — 460 / 500 XP

A completed goal moves to the personal history. A learner can also cancel an active goal or create a new goal based on an older one.

When a deadline passes, the language remains descriptive rather than punitive. For example, the history can show that a goal ended with 3 of 5 activities completed instead of saying that the learner failed.

The history includes goals created, completed, ended and cancelled, plus a month-by-month view of the learner's own activity.

## Suggested goals and teacher limits

Teachers can create optional suggested-goal templates such as “Study on three days this week” or “Complete two activities by the end of the week”. A suggestion does not create a goal for the learner; the learner must explicitly accept it.

Teachers can also define, per goal type:

- whether learners may create that type themselves;
- minimum and maximum target values;
- maximum number of simultaneously active goals;
- whether custom dates are allowed;
- maximum duration of a goal.

Suggested templates may define optional XP or credit rewards and may request a visual celebration. Self-created goals cannot inject reward values, which prevents a learner from creating trivial goals merely to generate rewards.

Recreating a previous goal does not copy its rewards by default. A teacher-created template can explicitly allow repeat rewards when that behavior is pedagogically intended.

## Event-driven progress

Progress is updated incrementally from Moodle events instead of browser polling. The plugin observes course interaction, activity completion, quiz submission, course completion and activity deletion events.

Processed Moodle events are deduplicated by hash, and goal completion itself is idempotent, so the same completion path does not emit the completion event or reward twice.

Deadline processing uses a lightweight scheduled task over the indexed `status` and `timeend` fields. There is no expensive periodic recalculation of every goal.

Study-day and session data are only recorded while the learner has an active goal that needs those metrics.

## Public PHP API

The main integration surface is `\local_personalgoals\api`:

```php
$goals = \local_personalgoals\api::get_user_goals($userid, $courseid);

$progress = \local_personalgoals\api::get_goal_progress($goalid);

$goal = \local_personalgoals\api::create_goal(
    $userid,
    $courseid,
    'activity_completion',
    'Complete five activities',
    5,
    [],
    'weekly'
);

\local_personalgoals\api::complete_goal($goalid);

$types = \local_personalgoals\api::get_available_goal_types($courseid);
```

`complete_goal()` verifies the current goal value before changing state and is safe to call more than once: only the first valid completion changes the goal and emits `goal_completed`.

## Extensible goal types

A goal type implements:

```php
\local_personalgoals\goal_type_interface
```

The contract exposes the goal name and description, current and target values, percentage progress, completion state and configuration validation.

Another plugin can expose additional types through a standard plugin callback named `<component>_personalgoals_goal_types()` in its `lib.php`:

```php
function local_example_personalgoals_goal_types(): array {
    return [
        'video_minutes' => \local_example\personalgoals\video_minutes::class,
    ];
}
```

The class must implement `\local_personalgoals\goal_type_interface`. The new type then participates in the same learner limits, templates, history and completion flow.

## Completion events

The plugin emits:

- `goal_created`
- `goal_completed`
- `goal_expired`
- `goal_cancelled`

These events allow other plugins to react without querying Personal Goals tables directly.

## Optional integrations

`local_personalxp` is optional. When present, XP goals use `\local_personalxp\service\xp_manager::get_total()` and teacher-configured XP rewards use `::award()` with a stable goal source, preserving the XP plugin's own idempotency rules.

When `local_xpcelebration` is present and exposes `\local_xpcelebration\api::queue()`, completing a goal can queue a `goal_completed` celebration.

Credit rewards are delegated to a public `local_rewardshop` API when that plugin is available. Personal Goals does not read or modify another plugin's wallet tables directly.

## Data and privacy

The plugin stores the learner's goals, incremental progress, active study days and the last meaningful course interaction needed for study-time goals. Privacy export and deletion follow Moodle's Privacy API at course context level.

Teacher templates and course limits are course configuration rather than learner personal data.

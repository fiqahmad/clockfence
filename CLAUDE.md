@AGENTS.md

## Claude Code

- Plan first for anything touching `app/Clock` or `public/js/clock.js`; list which of the 10 rules it affects.
- Write or update the test before the code; show it failing, then passing.
- Keep diffs small: one milestone issue per session, one commit per step.
- New architectural decision: draft an ADR in `docs/adr/` for the maintainer to edit.
- Never mark work done while tests, Pint or Larastan fail.

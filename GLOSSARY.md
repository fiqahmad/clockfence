# ClockFence

Employee attendance for one organisation: a Staff member proves they are at their Site with a phone location check, then scans the Site's poster to record a Clock.

## Language

### People

**Staff member**:
A User with a home Site, whose attendance is recorded, whatever their role. Every Staff member has exactly one home Site at a time.
_Avoid_: Employee, worker

**Invited**:
A Staff member who has been asked to join but has not logged in yet.

**Active**:
A Staff member who can log in and Clock.

**Inactive**:
A Staff member who can no longer log in or Clock and is never marked Absent. Staff members are made Inactive, never deleted.
_Avoid_: Deleted, archived, terminated

**Admin**:
A person who manages Sites, Staff members, holidays and attendance, and can do everything a Manager can.
_Avoid_: HR, owner

**Manager**:
A person who sees the daily board and reports for all Sites and can record Manual overrides, but cannot edit Sites, Staff members or holidays.
_Avoid_: Supervisor

**User**:
The login account behind a Staff member, Manager or Admin. Not a domain role.

### Places

**Site**:
One physical workplace with a geofence (centre point and radius), a start time and its own timezone.
_Avoid_: Branch, location, zone, geofence (when meaning the place)

**Archived**:
A Site that keeps its history but accepts no new Clocks. Only a Site that is no Active Staff member's home Site can be Archived; Sites are never deleted.
_Avoid_: Deleted, closed

**Poster**:
The static printed QR code at a Site's entrance that identifies the Site. It is not a secret.
_Avoid_: QR code (when meaning the printed sheet), kiosk

### Clocking

**Clock**:
One recorded attendance action by a Staff member, either a Clock-in or a Clock-out.
_Avoid_: Check-in, punch, tap

**Clock-in**:
The first Clock of a Staff member's day at their Site.

**Clock-out**:
The second Clock of a Staff member's day, done through the same Geo-check, Clock token and Poster scan as a Clock-in.

**Geo-check**:
One test of a Staff member's phone location against their home Site's geofence, which passes or fails.
_Avoid_: Location check, GPS check

**Clock token**:
A short-lived, single-use credential issued only by a passing Geo-check and spent by the Poster scan.
_Avoid_: QR token, session token

**Clock failure**:
A named reason a Clock was refused, shown to the Staff member with a fix they can do on the spot.
_Avoid_: Error, rejection

### Attendance

**Attendance**:
One Staff member's record for one Site day at the Site they were recorded at, holding their Clock-in, Clock-out and status.
_Avoid_: Timesheet, log, entry

**Register**:
The full set of Attendances: the source for the daily board and monthly reports.
_Avoid_: Roster

**Site day**:
A calendar day as observed in the Site's timezone. Which Attendance a Clock belongs to, and whether it was late, is judged on the Site day.

**Late minutes**:
How many minutes a Clock-in came after the Site's start time; zero when on time. No grace period.

**Present**:
The status of an Attendance whose Clock-in was on time, or which falls on a Rest day or Holiday.

**Late**:
The status of an Attendance whose Clock-in came after the Site's start time on a Working day.

**Absent**:
The status of an Attendance for a Staff member who made no Clock on a Working day, recorded once that Site day has ended.

**On leave**:
The status an Admin or Manager sets by Manual override for a Staff member who was excused. ClockFence does not manage leave itself.

**Working day**:
A weekday on which a Site expects its Staff members, set per Site.

**Rest day**:
A weekday that is not a Working day for the Site. No Attendance is recorded unless the Staff member Clocks anyway.
_Avoid_: Weekend, off day

**Holiday**:
A non-working day for one Site or all Sites; no Staff member is Absent on it, and Absent Attendances on a Holiday added later become Holiday.

**Manual override**:
A Clock or status recorded by an Admin or Manager instead of by the Staff member, always with a reason and the person who recorded it. Nobody can override their own Attendance.
_Avoid_: Correction, manual entry, edit
